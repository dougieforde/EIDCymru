<?php
class ScotEID_VionXMLProcessor
{
  const ELEMENT_LOT                 = "qry_scoteid";
  const ELEMENT_ID                  = "id";
  const ELEMENT_DEPARTURE_LOCATION  = "Unit_x0020_Details.CPH";
  const ELEMENT_FLOCK_NUMBER        = "Flock_x0020_No";
  const ELEMENT_UPLIFT_NAME         = "Uplift_x0020_Farm_x0020_Address";
  const ELEMENT_UPLIFT_ADDRESS_1    = "Unit_x0020_Details.Address_x0020_1";
  const ELEMENT_UPLIFT_ADDRESS_2    = "Unit_x0020_Details.Address_x0020_2";
  const ELEMENT_UPLIFT_ADDRESS_3    = "Unit_x0020_Details.Address_x0020_3";
  const ELEMENT_UPLIFT_ADDRESS_4    = "Unit_x0020_Details.Address_x0020_4";
  const ELEMENT_TEL                 = "Tel";
  const ELEMENT_QUANTITY            = "Quantity_x0020_Moved_x0020_-_x0020_Mixed";
  const ELEMENT_QUANTITY_BOARS      = "Quantity_x0020_Moved_x0020_-_x0020_Boars";
  const ELEMENT_QUANTITY_GILTS      = "Quantity_x0020_Moved_x0020_-_x0020_Gilts";
  const ELEMENT_SPECIES             = "Species";
  const ELEMENT_SLAP                = "Slap_x0020_No_x0020_-_x0020_1";
  const ELEMENT_DATE                = "Date_x0020_of_x0020_Movement";
  const ELEMENT_DELIVERY_CPH        = "Unit_x0020_Details_1.CPH";
  const ELEMENT_DELIVERY_NAME       = "Delivery_x0020_Farm_x0020_Address";
  const ELEMENT_DELIVERY_ADDRESS_1  = "Unit_x0020_Details_1.Address_x0020_1";
  const ELEMENT_DELIVERY_ADDRESS_2  = "Unit_x0020_Details_1.Address_x0020_2";
  const ELEMENT_DELIVERY_ADDRESS_3  = "Unit_x0020_Details_1.Address_x0020_3";
  const ELEMENT_DELIVERY_ADDRESS_4  = "Unit_x0020_Details_1.Address_x0020_4";
  const ELEMENT_LICENSE_FAXED       = "Licence_x0020_faxed_x0020_to_x0020_Dept";
  const ELEMENT_DRIVER              = "Driver";
  const ELEMENT_HAULIER             = "Haulier";
  const ELEMENT_MARK                = "ID_x0020_Mark";
  const ELEMENT_PIG_IDS             = "PIG_x0020_ID";
  const ELEMENT_FAXED               = "Licence_x0020_faxed_x0020_to_x0020_Dept";
  const ELEMENT_QMS_NO              = "SPII_x0020_No";
  const ELEMENT_FREEDOM_FOOD_NO     = "Freedom_x0020_Food_x0020_No";
  const ELEMENT_HAULIER_QA          = "Haulier_x0020_QA_x0020_No";
  const ELEMENT_MOVEMENT_TYPE       = "Type_x0020_of_x0020_Movement";
  
  private $uid;
  private $filename;

  private static $DESCRIPTION_MAP = array(
    '30kg weaner'     => ScotEID_LotDescription::DESC_WEANERS,
    'babies'          => ScotEID_LotDescription::DESC_BABIES,
    'breeding'        => ScotEID_LotDescription::DESC_BREEDING_PIGS,
    'breeding stores' => ScotEID_LotDescription::DESC_BREEDING_STORES,
    'culls'           => ScotEID_LotDescription::DESC_CULLS,
    'fat'             => ScotEID_LotDescription::DESC_FAT,
    'finisher stores' => ScotEID_LotDescription::DESC_FINISHER_STORES,
    'foster huts'     => ScotEID_LotDescription::DESC_FOSTER_HUTS,
    'nursery stores'  => ScotEID_LotDescription::DESC_NURSERY_STORES,
    'in pig sows'     => ScotEID_LotDescription::DESC_IN_PIG_SOWS
  );
  
  public function __construct($filename, $uid) {
    $this->uid      = $uid;
    $this->filename = $filename;
  }
  
  public function process() {
    $lots = array();
    
  	$xml = simplexml_load_file($this->filename);
  	if(!$xml)
  	{
      return false;
  	}
    foreach($xml->xpath(static::ELEMENT_LOT) as $lot_data) {
        
      // check for existing lot first
      $idn = $lot_data->xpath(static::ELEMENT_ID);
      if(count($idn) != 1)
        continue;
        
      $id = (string) $idn[0];  
      
      $lot = ScotEID_CompletedLot::first(array('conditions' => array('foreign_reference' => $idn, 'uid' => $this->uid)));
      if($lot === null) {
        $lot = new ScotEID_CompletedLot();
        $lot->set_lot_number($id);
        $lot->set_foreign_reference($id);
        $lot->set_species_id(3);
        $lot->set_movement_type_id(3);
        $lot->set_uid($this->uid);
      }
      
      $lot->set_head_count(0); // reset counter since we sum all groups
      
      $transport_details = $lot->get_or_create_transport_details();
      $transport_details->set_departure_assurance_number("");
              
      foreach($lot_data->children() as $child_node) {
                
        $child = (string) $child_node;
                
        switch($child_node->getName()) {
          case static::ELEMENT_DEPARTURE_LOCATION:
            $lot->set_departure_location($child);
            break;
          case static::ELEMENT_FLOCK_NUMBER:
            // print "flock number: " . $child . "\n";
            break;
          case static::ELEMENT_UPLIFT_NAME:
            $transport_details->set_departure_name($child);
            $transport_details->set_departure_business($child);
            break;
          case static::ELEMENT_UPLIFT_ADDRESS_1:
            $transport_details->set_departure_address_3($child);
            break;
          case static::ELEMENT_UPLIFT_ADDRESS_2:
            $transport_details->set_departure_address_4($child);
            break;
          case static::ELEMENT_UPLIFT_ADDRESS_4:
            $transport_details->set_departure_postcode($child);
            break;
          case static::ELEMENT_TEL:
            $transport_details->set_departure_tel($child);
            break;
          case static::ELEMENT_QUANTITY_BOARS:
          case static::ELEMENT_QUANTITY_GILTS:
          case static::ELEMENT_QUANTITY:
            $lot->set_head_count($lot->get_head_count() + $child);
            break;
          case static::ELEMENT_SPECIES:
            break;
          case static::ELEMENT_MARK:
            $lot->set_batch_mark($child);
            $transport_details->set_id_type("temp");            
            break;            
          case static::ELEMENT_SLAP:
            $lot->set_batch_mark($child);
            $transport_details->set_id_type("batch");
            break;
          case static::ELEMENT_PIG_IDS:
            $transport_details->set_individual_ids($child);
            $transport_details->set_id_type("ids");
            break;
          case static::ELEMENT_DATE:
            $lot->set_lot_date($child);
            break;
          case static::ELEMENT_DELIVERY_CPH:
            $lot->set_destination_location($child);
            break;
          case static::ELEMENT_DELIVERY_NAME:
            $transport_details->set_destination_name($child);
            $transport_details->set_destination_business($child);
            break;
          case static::ELEMENT_DELIVERY_ADDRESS_1:
            $transport_details->set_destination_address_3($child);
            break;
          case static::ELEMENT_DELIVERY_ADDRESS_2:
            $transport_details->set_destination_address_4($child);
            break;
          case static::ELEMENT_DELIVERY_ADDRESS_4:
            $transport_details->set_destination_postcode($child);
            break;
          case static::ELEMENT_HAULIER:
            $transport_details->set_haulier_business($child);
            break;
          case static::ELEMENT_DRIVER:
            $transport_details->set_haulier_name($child);
            break;
          case static::ELEMENT_HAULIER_QA:
            $transport_details->set_haulier_permit_number($child);
            break;
          case static::ELEMENT_FREEDOM_FOOD_NO:
            $other_assurance = ($transport_details->get_departure_assurance_number()) ? $transport_details->get_departure_assurance_number() . " / " : "";
            $transport_details->set_departure_assurance_number($other_assurance . $child);
            break;
          case static::ELEMENT_QMS_NO:
            $other_assurance = ($transport_details->get_departure_assurance_number()) ? $transport_details->get_departure_assurance_number() . " / " : "";
            $transport_details->set_departure_assurance_number($other_assurance . $child);
            break;            
          case static::ELEMENT_FAXED:
            if($child === 'Yes') {
              $lot->set_receiving_keeper_id($this->uid);
              $lot->set_doa_count(0); // FIXME
            }
          case static::ELEMENT_MOVEMENT_TYPE:
            $child = strtolower(trim($child));
            if(array_key_exists($child, static::$DESCRIPTION_MAP)) {
              $lot->set_description_id(static::$DESCRIPTION_MAP[$child]);
            }
        }
        // set holding of birth if not known for temporary identified animals to departure cph
        if($transport_details->get_id_type() == "temp" && $lot->get_departure_location() && !$transport_details->get_birth_cph())
        {
          $transport_details->set_birth_cph($lot->get_departure_location());
        }
      }
      // put in a BPEX movement type. Moves from Vion will either go to farm or abattoir
      $destination =  ScotEID_Holding::find(array('conditions' => array('cph' =>  $lot->get_destination_location()) ));
      if($destination[0])
      {
        if($destination[0]->is_abattoir()) {
          $lot->set_bpex_movement_type_id(4);
        } else {
          $lot->set_bpex_movement_type_id(1);
        }
      } else {
        // assume if we are unaware of a destination location it is a farm
        $lot->set_bpex_movement_type_id(1);
      }
      try {
        $lot->save();
        
      } catch(DuplicateKeyException $ex) {
        $lot->add_error("lot_number", "must be unique");
      }
      
      $lots[] = $lot;
    }
    return $lots;
  }
}
?>
