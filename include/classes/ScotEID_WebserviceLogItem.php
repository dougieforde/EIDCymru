<?php
class ScotEID_WebserviceLogItem extends ScotEID_ExtendedModel
{
  const LEN_OPERATION           = 50;
  const LEN_APPLICATION_NAME    = 50;
  const LEN_APPLICATION_VERSION = 50;
  const LEN_SCHEMA_VERSION      = 50;
  const LEN_ERROR_MESSAGE       = 125;
  const LEN_FILENAME            = 50;
  const LEN_IP_ADDRESS          = 15;
  
  public static function get_table_name() {
    return "tblwebservicelog";
  }
  
  public function get_primary_key() {
    return array(
      'id' => quote_int($this->get_id())
    );
  }
  
  protected static function get_attribute_definitions() {
    return array(
      'id'                  => array('type' => 'serial'),
      'user_id'             => array('type' => 'integer'),
      'operation'           => array('type' => 'string', 'length' => static::LEN_OPERATION),
      'application_name'    => array('type' => 'string', 'length' => static::LEN_APPLICATION_NAME),
      'application_version' => array('type' => 'string', 'length' => static::LEN_APPLICATION_VERSION),
      'schema_version'      => array('type' => 'string', 'length' => static::LEN_SCHEMA_VERSION),
      'ip_address'          => array('type' => 'string', 'length' => static::LEN_IP_ADDRESS),
      'successful'          => array('type' => 'boolean', 'default' => false),
      'error_message'       => array('type' => 'string', 'length' => static::LEN_ERROR_MESSAGE),
      'filename'            => array('type' => 'string', 'length' => static::LEN_FILENAME),
      'old_filename'        => array('type' => 'string', 'length' => static::LEN_FILENAME)
    );
  }
  
  private function emu_getallheaders() { 
    foreach ($_SERVER as $name => $value) 
    { 
      if (substr($name, 0, 5) == 'HTTP_') 
      { 
        $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5))))); 
        $headers[$name] = $value; 
      } else if ($name == "CONTENT_TYPE") { 
        $headers["Content-Type"] = $value; 
      } else if ($name == "CONTENT_LENGTH") { 
        $headers["Content-Length"] = $value; 
      } 
    } 
    return $headers; 
  }
  
  private function create_log_filename($logdir) {
    
    if($this->get_user_id() !== null) {
      $r = uniqid($this->get_user_id() . "_");
    } else {
      $r = uniqid("UNKNOWN_");
    }
    $r .= ".xml";
    
    if(file_exists($logdir . "/" . $r)) {
      return $this->create_log_filename($logdir);
    } else {
      return $r;
    }
  }
  
  private function log_request() {
    $logdir = SCOTEID_WEBSERVICES_ROOT . "/logs/" . date("dmy", time());
    if(!is_dir($logdir)) {
      @mkdir($logdir, 0755, true);
    }
    $filename = $this->get_filename();
    if(empty($filename) ){
      $this->set_filename($this->create_log_filename($logdir));;
    }
    
    $fh = fopen($logdir . "/" . $this->get_filename(), 'w+');
    fwrite($fh, file_get_contents('php://input'));
    $headers = $this->emu_getallheaders();    
    fwrite($fh, "\n--\n");
    fwrite($fh, serialize($headers));
    fclose($fh);
  }
  
  protected function before_save_on_create() {
    $this->log_request();
  }
}
?>
