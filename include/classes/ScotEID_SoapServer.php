<?php
/**
 * Server class used by native SoapServer, on method per operation defined in the WSDL
 */ 
class ScotEID_SoapServer {
  public function __call($method, $arguments) {
    $log_item = new ScotEID_WebserviceLogItem();

    try {
      if($arguments[0] && $arguments[0] instanceof ScotEID_AbstractRequest) {
        $request = $arguments[0];      

        // Copy application details from abstract request to log item
        $log_item->set_application_name($request->get_application_name());
        $log_item->set_application_version($request->get_application_version());
        $log_item->set_schema_version($request->get_schema_version());
        $log_item->set_operation(get_class($request));
        $log_item->set_ip_address($_SERVER['REMOTE_ADDR']);

        // Do pre-checks including authentication and store authenticated user id
        $request->before();
        $log_item->set_user_id($request->get_uid());

        // Now handle the request and set successful if no fault is raised
        $response = $request->handle();
        $log_item->set_successful(true);
        $log_item->save();
        
        if($response instanceof ScotEID_AbstractResponse)
          $response->Timestamp = time();
          return $response;
      } else {
        $log_item->set_error_message("Unsupported operation");
        throw new SoapFault("Server", "Unsupported operation");
      }
    } catch(ScotEID_AuthenticationError $e) {
      try {
        // log all requests regardless of whether credentials were supplied
        // doing this to debug SAMU requests where they aren't sending username
        // and password via their proxy
        //if(isset($_SERVER['PHP_AUTH_USER'])) {
          $log_item->save();
        //}
      } catch(Exception $ex) {
      }
      header('WWW-Authenticate: Basic realm="ScotEID API"');
      header('HTTP/1.0 401 Unauthorized');
      die();
    } catch(SoapFault $soapFault) {
      try {
        ScotEID_Mailer::deliver_soap_fault($request, $soapFault);
      } catch(Exception $innerEx) {
      }
      try {
        $log_item->set_user_id($request->get_uid());
        $log_item->set_error_message($soapFault->_name);
        $log_item->save();
      } catch(Exception $innerEx) { 
      }
      throw $soapFault;
    } catch(Exception $ex) {
      try {
        $log_item->set_error_message("Uncaught exception");
        $log_item->save();
      } catch(Exception $innerEx) { }
      if(SCOTEID_WEBSERVICES_ENV == 'development') {
        ScotEID_Mailer::deliver_exception($ex);
        throw $ex;
      } else {
        try {
          ScotEID_Mailer::deliver_exception($ex);
        } catch(Exception $innerEx) {}
        throw new SoapFault("Server",
          "An unknown error occurred. If this problem persists, please contact support@scoteid.com"
        );
      }
    }
  }
}
?>