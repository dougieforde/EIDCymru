<?php
class ScotEID_Mailer
{
  private static $FROM = array('support@scoteid.com' => 'ScotEID Support');
  
  private static $TO = array(
    'Newline EID' => 'nick@newlineasp.com'
  );
  
  public static function deliver_bpex_warnings($lot, $request, $response) {
    try {
      $body = "Error sending lot {$lot->get_sams_movement_reference()} to BPEX\n\n";
      $body .= "Request\n";
      $body .= "=======\n\n";
      $body .= $request;
      $body .= "\n\n";
      $body .= "Response\n";
      $body .= "========\n\n";
      $body .= $response;
      $message = Swift_Message::newInstance()
        ->setSubject('BPEX Warnings')
        ->setFrom(ScotEID_Mailer::$FROM)
        ->setTo('lewis@thebackwoods.co.uk')
        ->setBody($body);
      if(!(self::mailer()->send($message, $failures))) {
          return false;
      }
    } catch(Exception $ex) {
      ScotEID_Mailer::deliver_exception($ex);
      return false;
    }
    return true;
  }
  
  public static function deliver_samu_spreadsheet($lot_date, $uid) {
    $user = ScotEID_User::get($uid);
    try {
      $body = "Please check and forward the attached SAMS movement sheet to SAMS\r\nComma separated SAMS movement sheet attached";
    
      $email = $user->get_email();
      
      $attachment = Swift_Attachment::newInstance(csv_abattoir_samu_sheet(strftime('%Y-%m-%d', $lot_date), $uid), 
                                                  strftime("%d%m%Y",$lot_date) . ".csv",
                                                  "text/csv");
    
      $message = Swift_Message::newInstance()
        ->setSubject('Samu movement spreadsheet')
        ->setFrom(ScotEID_Mailer::$FROM)
        ->setTo($email)
        ->setBody($body)
        ->attach($attachment);

        if(!(self::mailer()->send($message, $failures))) {
            return false;
        }
      } catch(Exception $ex) {
	ScotEID_Mailer::deliver_exception($ex);
        return false;
      }
      return true;
  }
  
  public static function deliver_read_reminder($schedule_item) {   
    if(SCOTEID_WEBSERVICES_ENV == 'production') {
      $to = array('lewis@scoteid.com','help@scoteid.com','support@scoteid.com');
    } else {
      $to = array('lewis@scoteid.com');    
    }
    
    $desc = $schedule_item->get_read_location()->get_name_and_address();
    
    $body = "** THIS IS A TEST EMAIL PLEASE DO NOT ACT UPON IT YET **\n\n";
    
    $body .= "No reads received yet for the following location\n" .
             "================================================\n" .
             "Read location: " . $schedule_item->get_read_location() . (empty($desc) ? "" : " ($desc)") . "\n" .
             "Start time:    " . strftime("%d/%m/%y %H:%M", $schedule_item->get_starts_at()) . "\n" .
             "Sheep:         " . ($schedule_item->get_sheep() ? "YES" : "NO") . "\n" .
             "Cattle:        " . ($schedule_item->get_cattle() ? "YES" : "NO");
            
    $c = $schedule_item->get_comments();
    if(!empty($c)) {
      $body .= "\nComments:      "     . $schedule_item->get_comments(); 
    }        
            
    $message  = Swift_Message::newInstance()
      ->setSubject('[ScotEID] No reads warning for ' . $schedule_item->get_read_location())
      ->setFrom(ScotEID_Mailer::$FROM)
      ->setTo($to)
      ->setBody($body);
      
    self::mailer()->send($message);
  }
  
  public static function deliver_exception($ex) {
    if(isset($GLOBALS['_SCOTEID_WEBSERVICES_ENV']) && isset($GLOBALS['_SCOTEID_WEBSERVICES_ENV']['smtp'])) {
      $e = $GLOBALS['_SCOTEID_WEBSERVICES_ENV']['smtp'];
    
      try {
        $to = array('lewis@scoteid.com');
        $body = ScotEID_Mailer::format_exception($ex);
        $message = Swift_Message::newInstance()
          ->setSubject('[ScotEID] Unhandled exception')
          ->setFrom(ScotEID_Mailer::$FROM)
          ->setTo($to)
          ->setBody($body);
        
        $transport = Swift_SmtpTransport::newInstance($e['hostname']);

         if($e['username'] && $e['password']) {
           $transport->setUsername($e['username']);
           $transport->setPassword($e['password']);
         }

         $mailer = Swift_Mailer::newInstance($transport);
         $mailer->send($message);
      } catch(Exception $ex) {}
    }
  }
  
  public static function deliver_soap_fault($request, $soapFault) {
    $e = $GLOBALS['_SCOTEID_WEBSERVICES_ENV']['smtp'];

    if(!$soapFault instanceof ScotEID_LotValidationFault) {
      return;
    }
    
    try {
      $request_attachment = Swift_Attachment::fromPath('php://input')
        ->setFilename('request.log');
    
      $to = array('lewis@scoteid.com');

      $body = "";

      if(isset(ScotEID_Mailer::$TO[$request->get_application_name()])) {
        $to[] = ScotEID_Mailer::$TO[$request->get_application_name()];
        $body .= "To: " . ScotEID_Mailer::$TO[$request->get_application_name()] . "\n";
      }
      
      $user = ScotEID_User::get($request->get_uid());
      
      $body .= "User: " . $user->get_name();
      $body .= "\nFault: " . $soapFault->ErrorDescription;
      if($soapFault instanceof ScotEID_LotValidationFault) {
        $body .= "\n\nErrors\n======\n";
        foreach($soapFault->Errors as $error) {
          $body .= $error['Property'] . ": " . $error['Message'] . "\n"; 
        }
      }
    
      $message = Swift_Message::newInstance()
        ->setSubject('[ScotEID] Soap fault')
        ->setFrom(ScotEID_Mailer::$FROM)
        ->setTo($to)
        ->setBody($body)
        ->attach($request_attachment);
    
      $transport = Swift_SmtpTransport::newInstance($e['hostname']);
      
      if($e['username'] && $e['password']) {
        $transport->setUsername($e['username']);
        $transport->setPassword($e['password']);
      }
      
      $mailer = Swift_Mailer::newInstance($transport);
      //$mailer->send($message);  
    } catch(Exception $ex) {
      
    }
  }
  
  public static function format_exception(Exception $exception) {    
    $traceline = "#%s %s(%s): %s(%s)";
    $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s";

    // alter your trace as you please, here
    $trace = $exception->getTrace();
    foreach ($trace as $key => $stackPoint) {
        // I'm converting arguments to their type
        // (prevents passwords from ever getting logged as anything other than 'string')
        $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
    }

    // build your tracelines
    $result = array();
    foreach ($trace as $key => $stackPoint) {
        $result[] = sprintf(
            $traceline,
            $key,
            $stackPoint['file'],
            $stackPoint['line'],
            $stackPoint['function'],
            implode(', ', $stackPoint['args'])
        );
    }
    // trace always ends with {main}
    $result[] = '#' . ++$key . ' {main}';

    // write tracelines into main template
    $msg = sprintf(
        $msg,
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        implode("\n", $result),
        $exception->getFile(),
        $exception->getLine()
    );

    return $msg;
  }
  
  public static function smtp_settings() {
    return $GLOBALS['_SCOTEID_WEBSERVICES_ENV']['smtp'];
  }
  
  public static function mailer() {
    $e = self::smtp_settings();
    $transport = Swift_SmtpTransport::newInstance($e['hostname']);

     if($e['username'] && $e['password']) {
       $transport->setUsername($e['username']);
       $transport->setPassword($e['password']);
     }

     $mailer = Swift_Mailer::newInstance($transport);
     return $mailer;
  }
  
  public static function deliver_movement_document($lots,$uid) {
    require_once('tcpdf/config/lang/eng.php');
  	require_once('tcpdf/tcpdf.php');
  	
  	if(is_array($lots)) {
  	  $lot = $lots[0];
  	  if(!$lot){return false;}
  	} else {
  	  $lot = $lots;
  	  if(!$lot){return false;}
  	  if(!$lot->is_saved()){ return false; }
  	}

    $user = ScotEID_User::get($uid);
    
    try {      
      $body = "";
      
      if(count($lots) == 1) {
        $batch_mark = $lot->get_batch_mark();
        $lot_date = date("Y-m-d",$lot->get_lot_date());
        // $weekno = "week ". date("W",$lot->get_lot_date());
        $dep_cph = $lot->get_departure_location();
        $tr = $lot->get_or_create_transport_details();

        $keeper = $tr->get_departure_name() .", ". $tr->get_departure_business;
        
        $subject  = "Move Doc. for $weekno - $lot_date $keeper $batch_mark";
        $body .= "<p>Movement document for :<br> $keeper <br> $dep_cph <br> $lot_date <br> $batch_mark</p>" ;
      } else {
        $subject = "Movement documents created " . date("d/m/Y");
        $body .= "<table><tr><th>Lot No</th><th>Keeper</th><th>Departure CPH</th><th>Lot Date</th><th>Batch Mark</th>";
        foreach($lots as $lot) {
          if($lot->is_saved())
          {
            $lot_no = $lot->get_lot_number();
            $batch_mark = $lot->get_batch_mark();
            $lot_date = date("Y-m-d",$lot->get_lot_date());
            // $weekno = "week ". date("W",$lot->get_lot_date());
            $dep_cph = $lot->get_departure_location();
            $tr = $lot->get_or_create_transport_details();
            
            $keeper = $tr->get_departure_name() .", ". $tr->get_departure_business;
            $body .= "<tr><td>$lot_no</td><td>$keeper</td><td>$dep_cph</td><td>$lot_date</td><td>$batch_mark</td></tr>";
          }
        }
        $body .= "</table>";
      }
      
	    
	    $body .= "<p>Document generated: " .  date("H:i d/m/Y") . "</p>";
	
	    $body .= "<p>If your pigs do not comply with the Food Chain Information (FCI) declaration on the movement document you should attach a supplementary FCI document. You should contact your abattoir for their preferred format for this information. An example for Broxburn abattoir can be downloaded from the ScotEID website. </p>";
          
      // send to appropriate address
      if(ScotEID_User::user_has_role($uid, array(ScotEID_User::ROLE_ACCOUNT_ADMIN)))
      {
          $email = "help@scoteid.com";
      }
      else
      {
          $email = $user->get_email();
	    }

      $samref = $lot->get_sams_movement_reference();
      $pdf = new ScotEID_MoveDocPDF($lots);
      $filename = "movement_doc_" . $samref  . ".pdf";
	    $pdf_text = $pdf->Output($filename, 'S');
        
      $attachment = Swift_Attachment::newInstance($pdf_text, $filename,'application/pdf');
    
  		$message = Swift_Message::newInstance()
  		  ->setSubject($subject)
  		  ->setFrom(ScotEID_Mailer::$FROM)
  		  ->setTo($email)
  		  ->setBody($body)
  		  ->setContentType("text/html")
  		  ->attach($attachment);

      if(!(self::mailer()->send($message, $failures))) {
          return false;
      }
		} catch(Exception $ex) {
		  return false;
		}
		return true;
  }  
  
}
?>
