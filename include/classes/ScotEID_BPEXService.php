<?php
class ScotEID_BPEXService
{    
  //private $token            = '8ee729a1ae114fdbf9f16ff77b68bd1b6a77c666';
  
  private $token            = null;
  private $integration_mode = false;
  private $debug            = false;
  private $send             = false;
  
  private $source_profiles      = 4;
  private $destination_profiles = 2;

  function __construct() {
    if(isset($GLOBALS['_SCOTEID_WEBSERVICES_ENV']) && isset($GLOBALS['_SCOTEID_WEBSERVICES_ENV']['bpex'])) {
      $e = $GLOBALS['_SCOTEID_WEBSERVICES_ENV']['bpex'];
      
      if(isset($e['token']))            $this->token            = $e['token'];
      if(isset($e['integration_mode'])) $this->integration_mode = $e['integration_mode'];
      if(isset($e['debug']))            $this->debug            = $e['debug'];
      if(isset($e['send']))             $this->send             = $e['send'];
    }
  }
  
	const WARNING_SOURCE_SITE_NOT_FOUND        = "Source Site not found";
	const WARNING_DEST_SITE_NOT_FOUND          = "Destination Site not found";
	const WARNING_SOURCE_SITE_MATCHES_MULTIPLE = "Source Site matches multiple sites";
	const WARNING_DEST_SITE_MATCHES_MULTIPLE   = "Destination Site matches multiple sites";

  public function try_with_next_source_profile($lot, $options = array()) {    
    // profiles are used to determine which set of information to send for a site
    $source_profile      = $options['source_profile'];
    $destination_profile = $options['destination_profile'];
    
    print "!! asked to retry with next source profile ($source_profile, $destination_profile)\n";
    // return false;
    
    if($source_profile == $this->source_profiles) {
		  // it's a failure
      return false;
		} else {
		  // try different data
		  $this->register_batch(array($lot), 
  		  array(
  		    'source_profile'      => $source_profile+1, 
  		    'destination_profile' => $destination_profile
  		  )
  		);
		  return true;
		}
		
  }
  
  public function try_with_next_destination_profile($lot, $options = array()) {
    // profiles are used to determine which set of information to send for a site
    $source_profile      = $options['source_profile'];
    $destination_profile = $options['destination_profile'];
    
    print "!! asked to retry with next destination profile ($source_profile, $destination_profile)\n";
    // return false;
    
    if($destination_profile == $this->destination_profiles) {
		  // it's a failure
      return false;
		} else {
		  // try different data
		  $this->register_batch(array($lot), 
  		  array(
  		    'source_profile'      => $source_profile, 
  		    'destination_profile' => $destination_profile+1
  		  )
  		);
		  return true;
		}
  }

  public function register_batch($lots = array(), $options = array()) {
    if(!isset($options['source_profile'])) $options['source_profile'] = 1;
    if(!isset($options['destination_profile'])) $options['destination_profile'] = 1;
    
    if($this->token === null) {
      throw new Exception("BPEX service not configured");
    }
     
    try {
      
      if(count($lots) > 0) {        
        
        $request = $this->buildRequest($lots, $options);        
        $result = $this->sendRequest($request);
        
        foreach($lots as $lot) {
          if(isset($result[$lot->get_sams_movement_reference()])) {
            $details = $result[$lot->get_sams_movement_reference()];
            
            $failed  = false;
            $retried = false;
            
            if(isset($details['guid'])) {
              $ref = $details['guid'];
              if(!empty($ref))
                $lot->set_foreign_reference($details['guid']);
            } else if(!$this->integration_mode) {
              $failed = true;
            }
						
						$error_status = ScotEID_Lot::BPEX_ERROR;
            
            $source_failure = false;
            $destination_failure = false;
            
            if(isset($details['warnings'])) {
              foreach($details['warnings'] as $warning) {
                if(isset($warning['level']) && $warning['level'] == 4) {
                  $failed = true;
									if($warning == static::WARNING_SOURCE_SITE_NOT_FOUND) {
										$error_status = $error_status | ScotEID_Lot::BPEX_ERROR_SOURCE_SITE;
										$source_failure = true;
									} elseif($warning == static::WARNING_SOURCE_SITE_MATCHES_MULTIPLE) {
									  $error_status = $error_status | ScotEID_Lot::BPEX_ERROR_MULTIPLE_SOURCE_SITE;
										$source_failure = true;
									} elseif($warning == static::WARNING_DEST_SITE_NOT_FOUND) {
										$error_status = $error_status | ScotEID_Lot::BPEX_ERROR_DEST_SITE;
									  $destination_failure = true;
									} elseif($warning == static::WARNING_DEST_SITE_MATCHES_MULTIPLE) {
									  $error_status = $error_status | ScotEID_Lot::BPEX_ERROR_MULTIPLE_DEST_SITE;
									  $destination_failure = true;
									}
                }
              }
            }
            
            if($source_failure) {
              $retried = $this->try_with_next_source_profile($lot, $options);
            } else if($destination_failure) {
              $retried = $this->try_with_next_destination_profile($lot, $options);
            }
            
            // only handle the failure if we didn't retry which means a subsequent call of this function
            // would have handled the failure
            if(!$retried) {
              if(!$failed) {
                $lot->set_sent_to_bpex(ScotEID_Lot::BPEX_SENT);
              } else {
                // don't bother updating the status if we're already re-trying because
                // of a failure so that the ORIGINAL error is preserved
                if($lot->get_sent_to_bpex() == ScotEID_Lot::BPEX_UNSENT)
  							  $lot->set_sent_to_bpex($error_status);
  						}
            
              if($failed) {
                ScotEID_Mailer::deliver_bpex_warnings($lot, 
                  $request->saveXML(), 
                  var_export($result, true)
                );
              }
            
              $lot->save();
            }
          }
        }
      }
      
    } catch(Exception $ex) {
      ScotEID_Mailer::deliver_exception($ex);
    }
  }
  
  /** XML building code **/
  
  private $request = null;
  
  private function addXSDElement($parent, $name, $type, $value) {
    $n = $parent->appendChild(
      $this->request->createElement($name));

    $n->appendChild(
        $this->request->createTextNode($value));

    $n->appendChild(
      $this->request->createAttributeNS("http://www.w3.org/2001/XMLSchema-instance", "xsi:type"))->appendChild(
        $this->request->createTextNode($type));
  }
  
  private function addSite($parent, 
                           $site_name, 
                           $slap = null, 
                           $cph = null, 
                           $assurance_number = null, 
                           $postcode = null, 
                           $options = array()) 
  {
    $source_profile       = $options['source_profile'];
    $destination_profile  = $options['destination_profile'];
    
    print "addSite, source profile: " . $source_profile . "\n";
    
    $source = $parent->appendChild(
      $this->request->createElementNS("", $site_name));

    //
    // Sanitization of data
    
    $cph = (string) $cph;
    if($assurance_number != 0 & $assurance_number < 999) {
      $assurance_number = sprintf("S%04d", $assurance_number);
    }

    if($site_name == 'Source') {
      if(preg_match('#[0-9]{2,2}/[0-9]{3,3}/8[0-9]{3,3}#', $cph)) {
        $h = ScotEID_Holding::first(array('conditions' => array('cph' => $cph)));
        
        if($source_profile == 1) {
          // default scenario - try postcode for market only
          if($h) $postcode = $h->get_postcode();
          // $slap             = null;
          $assurance_number = null;
        } else {
          // second scenario for markets - try cph only
          // $slap             = null;
          $assurance_number = null;
          $postcode         = null;
        }
        
      } else {
        
        if($source_profile == 1) {
          // default scenario - slap only
          $assurance_number = null;
        } else if($source_profile == 2) {
          // third scenario - slap and asurance number
        } else if($source_profile == 3) {
          // fourth scenario - slap and postcode
          $assurance_number = null;
          $h = ScotEID_Holding::first(array('conditions' => array('cph' => $cph)));
          if($h) $postcode = $h->get_postcode();
        } else {
          // final scenario - cph only
          // $slap             = null;
          $assurance_number = null;
          $postcode         = null;
        }
    
      }
    }

    if($site_name == 'Destination') {
      if($destination_profile == 1) {
      } else {
        $postcode = null;
      }
    }
    
    if($slap)
      $this->addXSDElement($source, "SlapMark", "xsd:string", $slap);
    if($cph)
      $this->addXSDElement($source, "CPH", "xsd:string", $cph);
    if($postcode)
      $this->addXSDElement($source, "Postcode", "xsd:string", $postcode);
    if($assurance_number)
      $this->addXSDElement($source, "AssuranceNumber", "xsd:string", $assurance_number);
  }
  
  private function addTextNode($parent, $name, $value) {
    $parent->appendChild(
      $this->request->createElementNS("", $name))->appendChild(
        $this->request->createTextNode($value));
  }
  
  private function addMovementRegistrations($parent, $lots = array(), $options) {
    foreach($lots as $lot) {
      $transport_information = $lot->get_transport_details();
      
      $movementRegistration = $parent->appendChild(
        $this->request->createElement("MovementRegistration"));

      $movementRegistration->appendChild(
        $this->request->createAttribute("ref"))->appendChild(
          $this->request->createTextNode($lot->get_sams_movement_reference()));

      if($lot->get_foreign_reference()) {
        $movementRegistration->appendChild(
          $this->request->createAttribute("guid"))->appendChild(
            $this->request->createTextNode($lot->get_foreign_reference()));
      }
      
			$id_type = $transport_information ? $transport_information->get_id_type() : null;

      if(!ScotEID_User::user_has_role($lot->get_uid(), ScotEID_User::ROLE_BPEX)) {
        $this->addSite($movementRegistration, "Source", 
          ($id_type == ScotEID_TransportDetails::ID_TYPE_BATCH ? $lot->get_batch_mark() : null), 
          $lot->get_departure_location(),
          $transport_information ? $transport_information->get_departure_assurance_number() : null,
          null,
          $options
        ); 
        
        $this->addSite($movementRegistration, "Destination", 
          null, 
          $lot->get_destination_location(),
          $transport_information ? $transport_information->get_destination_assurance_number() : null,
          $transport_information ? $transport_information->get_destination_postcode() : null,
          $options
        );    

        if($lot->get_bpex_movement_type() != null) {
          $this->addTextNode($movementRegistration, "MovementType", $lot->get_bpex_movement_type()->get_name());
        }
      }
      $this->addTextNode($movementRegistration, "BatchSize", $lot->get_head_count());
      $this->addTextNode($movementRegistration, "StartDate", date("Y-m-d", $lot->get_lot_date()) . "T00:00:00");

			if($id_type == ScotEID_TransportDetails::ID_TYPE_TEMP || $id_type == ScotEID_TransportDetails::ID_TYPE_BATCH) {
				$this->addTextNode($movementRegistration, "LotNumber", 
					sprintf("%s(%d)", $lot->get_batch_mark(), $lot->get_head_count()));
			} else if($id_type == ScotEID_TransportDetails::ID_TYPE_INDIVIDUAL_IDS) {
				$this->addTextNode($movementRegistration, "LotNumber",
					implode(',', $lot->get_transport_details()->get_individual_ids_array()));
			}
      
      if($lot->get_arrival_date())
        $this->addTextNode($movementRegistration, "EndDate", date("Y-m-d", $lot->get_arrival_date()));
      
      if($transport_information) {
        // VehicleReg
        if($transport_information->get_vehicle_id())
          $this->addTextNode($movementRegistration, "VehicleReg", $transport_information->get_vehicle_id());
        
        // HaulierName
        if($transport_information->get_haulier_business())
          $this->addTextNode($movementRegistration, "HaulierName", $transport_information->get_haulier_business());
        
        // HaulierAssuranceNumber
        if($transport_information->get_haulier_permit_number())
          $this->addTextNode($movementRegistration, 
            "HaulierAssuranceNumber", $transport_information->get_haulier_permit_number());
          
        // JourneyDuration
        if($transport_information->get_expected_duration() !== null) {
          $this->addTextNode($movementRegistration, 
            "JourneyDuration", sprintf("PT%dH", $transport_information->get_expected_duration()));
        }            
      }
      
      // DOA
      if($lot->get_doa_count() !== null)
        $this->addTextNode($movementRegistration, "DOA", $lot->get_doa_count());
      
      // FCI
      //if($lot->get_fci_declaration() !== null) {
      //  $fci = $movementRegistration->appendChild(
      //    $this->request->createElement("FCI"));
      //    
      //  $this->addTextNode($fci, "WithdrawalPeriodsMet", $lot->get_fci_declaration() ? "true" : "false");
      //}      
    }
  }
  
  private function buildRequest($lots = array(), $options) {
    $this->request = new DOMDocument();
    
    $soapEnvelope = $this->request->appendChild(
      $this->request->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', "soap:Envelope"));
      
    $soapEnvelope->appendChild(
      $this->request->createAttribute("xmlns:xsd"))->appendChild(
        $this->request->createTextNode("http://www.w3.org/2001/XMLSchema"));         

    $soapBody = $soapEnvelope->appendChild($this->request->createElement("soap:Body"));

    $movementRegistrationBatch = $soapBody->appendChild(
      $this->request->createElementNS("http://bpex.org/services/aml2/registration", "MovementRegistrationBatch"));

    $movementRegistrationBatch->appendChild(
      $this->request->createAttribute("token"))->appendChild(
        $this->request->createTextNode($this->token));

    $this->addMovementRegistrations($movementRegistrationBatch, $lots, $options);
    
    return $this->request;
  }
  
  /** Request sending */
  
  private function sendRequest($soap_request) {
    $result = array();
    
    $soap_request = $soap_request->saveXML();
    
    if($this->debug)
      print $soap_request;

    if(!$this->send) {
      print "SEND DISABLED!";
      return array();
    }

    $header = array(
      "Content-type: text/xml;charset=\"utf-8\"",
      "Accept: text/xml",
      "Cache-Control: no-cache",
      "Pragma: no-cache",
      "SOAPAction: \"http://bpex.org/services/aml2/registration/service/RegisterBatch\"",
      "Content-length: ".strlen($soap_request),
    );
    
    $curl = curl_init();    
    curl_setopt($curl, CURLOPT_URL,            "http://www.eaml2.org.uk/aml2/services/registration.asmx");
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($curl, CURLOPT_TIMEOUT,        0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_POST,           true );
    curl_setopt($curl, CURLOPT_POSTFIELDS,     $soap_request);
    curl_setopt($curl, CURLOPT_HTTPHEADER,     $header);
    
    $string = curl_exec($curl);
    
    if($this->debug)
      print $string;
    
    if($string === false) {
      $error = curl_error($curl);
      var_dump($error);
      curl_close($curl);
      throw new Exception("Curl error: " . $curl);
    }
        
    curl_close($curl);
    
    libxml_use_internal_errors(true);

    $xml = simplexml_load_string($string);
    
    if($xml === false) {
      throw new Exception("Failed to parse response XML");
    } else {

      $e = $xml->children("http://schemas.xmlsoap.org/soap/envelope/");

      if(count($e) == 0) {
        throw new Exception("Failed to parse response - couldn't find SOAP envelope");
      }

      $batch = $e->children("http://bpex.org/services/aml2/registration");

      if(count($batch) == 0) {
        throw new Exception("Failed to parse response - couldn't find MovementResponseBatch");
      }

      $movement_responses = $batch->MovementResponseBatch->MovementResponse;

      if(count($movement_responses) == 0) {
        throw new Exception("Failed to parse response - couldn't find any MovementResponse");
      }

      for($i = 0; $i < count($movement_responses); $i++) {
        // our ref
        $ref = (string) $movement_responses[$i]->attributes()->ref;

        // their ref
        $guid = (string) $movement_responses[$i]->attributes()->guid;

        $ns_data = $movement_responses[$i]->children("");

        $short_code = $ns_data->ShortCode;
        $status     = $ns_data->Status;
        $warnings   = $ns_data->Warnings->children("");
        
        $result[$ref] = array(
          'guid'       => $guid,
          'short_code' => $short_code,
          'status'     => $status,
          'warnings'   => $warnings
        );
      }
    }
    
    return $result;
  }
  
}
?>
