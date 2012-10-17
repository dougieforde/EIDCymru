<?php
require dirname(__FILE__) . '/../include/prepend.php';

if(isset($_GET['wsdl'])) {
  $wsdl = ScotEID_Service::getWSDLPath();
  header('Content-Type: text/xml');  
  require $wsdl;
  exit;
} else {
  $service = new ScotEID_Service();
  $service->handle();
}

?>
