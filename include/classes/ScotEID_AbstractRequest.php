<?php
abstract class ScotEID_AbstractRequest
{
  public $ApplicationName;
  public $ApplicationVersion;
  public $SchemaVersion;
  public $Timestamp;
  
  private $uid = null;
  
  private static $_current_request = null;
  
  public function get_application_name() { return $this->ApplicationName; }
  public function get_application_version() { return $this->ApplicationVersion; }
  public function get_schema_version() { return $this->SchemaVersion; }

  public function is_schema_version_gte($version) {
    return version_compare($this->get_schema_version(), $version) >= 0; 
  }

  public function handle() {
    throw new SoapFault("Server", "Not implemented yet");
  }
  
  protected static function set_current_request($request) {
    self::$_current_request = $request;
  }
  
  public static function get_current_request() {
    return self::$_current_request;
  }
  
  public function before()
  {    
    ScotEID_AbstractRequest::set_current_request($this);
    
    $this->validate_schema_version();
    $this->validate_application_details();
    $this->authenticate();
  }
  
  private function validate_schema_version() {
    $schemaVersions = array(
        '1.0a',
        '1.0a2',
        '1.0a3',
        '1.0b',
        '1.0',
        '1.0.1',   // accept UK abattoir numbers
        '1.1',     // incomplete lot management
        '1.2',     // added create complete lot operation and animal information (last destination, ETAS verified)
        '1.2.1',
        '1.2.2',   // aded ability to update incomplete lot
        '1.2.3',   // updating items by lot reference
        '1.2.4',   // SAMU additions,
        '1.2.5',   // addition of manual entry,
        '1.3',     // addition of partner farm methods    
        '1.4',     // addition of pig database attributes    
        '1.4.1',   // addition of species code to (Find|Get)LotsBy(Destination|Departure)Location requests
                   // and addition of identification type to lots
        '1.4.2'    // addition of CancelCompleteLotByReference
    );
      
    if(empty($this->ApplicationName) ||
       empty($this->ApplicationVersion) ||
       empty($this->Timestamp)) 
    {
      throw new SoapFault("Server", null, null,
        array('ErrorDescription' => "Basic request information was missing. Please ensure you are supplying ApplicationName, ApplicationVersion, Timestamp and SchemaVersion"),
        "MalformedRequestFault");
    }
    
    if(empty($this->SchemaVersion) || !in_array($this->SchemaVersion, $schemaVersions)) {
        $message = "Schema versions other than " . implode(",", $schemaVersions) . " are not supported.";
        
      throw new SoapFault("Server", 
                          null, 
                          null, 
                          array('ErrorDescription' => $message), "UnsupportedSchemaVersionFault");
    }  
  }
  
  private function validate_application_details() {
    if(SCOTEID_WEBSERVICES_ENV == 'production') {
      $applications = array(
        'Newline EID'                 => array('2.0.0'),
        'tgl ScotEID'                 => array('1.0'),
        'ScotEID Desktop'             => array('1.2','1.3','*'),
        'iWAMS.DMS.CLIENT'            => array('3.3.1.12','*'),
        'SoapUI Test Suite'           => array('*'),
        'SAMU'                        => array('*'),
        'SHEEPdata'                   => array('*'),
        'FarmIT 3000'                 => array('*'),
        'MorrisonsFarming'            => array('*'),
        'Everysite ScotEID Interface' => array('*')
      );
    
      $ok = true;
    
      if($applications[$this->ApplicationName]) {
        if(!in_array($this->ApplicationVersion, $applications[$this->ApplicationName])) {
          if(!in_array('*', $applications[$this->ApplicationName])) {
            $ok = false;
          } else {
            $ok = true;
          }
        }
      } else {
        $ok = false;
      }
    
      if(!$ok) {
        $this->authentication_failed();
      }
    }
  }
  
  public function get_uid() {
    return $this->uid;
  }
  
  protected function authenticate() {        
    if(!isset($_SERVER['PHP_AUTH_USER'])) {
      $this->authentication_failed();
    } else {
      $username = $_SERVER['PHP_AUTH_USER'];
      $password = $_SERVER['PHP_AUTH_PW'];
            
      $uid = ScotEID_User::authenticate($username, $password);
      
      if($uid === false) {
        $this->authentication_failed();
      } else {
        $this->uid = $uid;
      }
    }
  }
  
  protected function authentication_failed() {
    throw new ScotEID_AuthenticationError();
  }
  
  /* Dstructively modify any lots that are about to be sent so that they're 
     compatible with the schema version submitted by the client */
  protected function prepare_lots($lots, $options = array()) {
    $r = array();
    foreach($lots as $lot) {
      $r[] = $this->prepare_lot($lot, $options);
    }
    return $r;
    
    return array_map($this->prepare_lot, $lots);
  }
  
  protected function prepare_samu_lots($lots, $options = array()) {
    
    // TODO: add pig_identities back in here once we're happy with the
    // quality of data
    $o = array_merge($options, array(
      'only' => array(
        'lot_number',
        'lot_date_string',
        'species_id',
        'head_count',
        'doa_count',
        'batch_mark',
        'id_type',
        'departure_location',
        'read_location',
        'destination_location'
      )
    ));
    
    return $this->prepare_lots($lots, $o);
  }
  
  protected function prepare_lot($lot, $options = array()) {  
    $ignore = array();
    $only   = isset($options['only']) ? $options['only'] : null;  
      
    if(!$this->is_schema_version_gte('1.2')) {
      $ignore[] = 'sams_movement_reference';
      $ignore[] = 'read_count';
    }
    
    if(!$this->is_schema_version_gte('1.4')) {
      $lot->ExternalReference = null;
      $lot->BatchMark         = null;
      $lot->LotDescription    = null;
      $lot->PigIdentities     = null;
      $lot->DepartureKeeper   = null;
      $lot->DestinationKeeper = null;
      $lot->FCI               = null;
      $lot->DOACount          = null;
      $lot->Species           = null;
      $lot->VisuallyRead      = null;
    }
        
    if(isset($options['tag_readings']) && $options['tag_readings'] === false) {
      $ignore[] = 'tag_readings';
    }
    
    if(isset($options['flock_tags']) && $options['flock_tags'] === false) {
      $ignore[] = 'flock_tags';
    }

    $lot = $lot->to_property_array($ignore, $only);
    
    return $lot;
  }
  
  protected function extract_lots($lots) {
    return array_map($this->extract_lot, $lots);
  }
  
  protected function extract_lot($lot_data) {
    $l = new ScotEID_Lot();
    
    // 
    // the old transport information was never used - is no longer
    // valid
    if(!$this->is_schema_version_gte('1.4')) {
      $lot_data->TransportInformation = null;
    }
    
    $l->extract_properties($lot_data);
    // FIXME: it'd be nice if i could figure out why this isn't working for 
    // PigIdentities but works perfectly for FlockTags and TagReadings
    if(isset($lot_data->PigIdentities)) {
      $l->set_attribute("pig_identities", $lot_data->PigIdentities);
    }
    return $l;
  }
}
?>