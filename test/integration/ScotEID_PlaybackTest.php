<?php
require_once './test/test_helper.php';

// function header($string, $replace = true, int $http_response) {
//   print $string . "\n";
// }

class ScotEID_PlaybackTest extends ScotEID_BaseTest
{
  public function testPlaybackRequests() {
    
    $_SERVER['PHP_AUTH_USER']     = 'test1';
    $_SERVER['PHP_AUTH_PASSWORD'] = 'password1';
    
    $service = new ScotEID_Service();
        
    $path = dirname(__FILE__)  . '/../soap';
    
    if($handle = opendir($path)) {
      while(false !== ($entry = readdir($handle))) {
        
        if($entry == '.' || $entry == '..')
          continue;
        
        if(!preg_match('/_response\.xml$/', $entry, $matches)) {
          $response = str_replace($entry, '.xml', '_response.xml');          
          ob_end_flush();
          ob_start();
          $result = $service->handle(file_get_contents($path . '/' . $entry));
          print ob_get_contents();
        }
        
      }
      closedir($handle);
      // die();
    } else {
      $this->assertTrue(false);
    }
        
    ob_end_flush();
    
    $this->assertTrue(false);
  }
}
?>