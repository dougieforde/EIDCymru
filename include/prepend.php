<?php

//date_default_timezone_set('UTC');

date_default_timezone_set('GMTDT');

if(isset($_SERVER['SCOTEID_WEBSERVICES_ENV']))
  define('SCOTEID_WEBSERVICES_ENV', $_SERVER['SCOTEID_WEBSERVICES_ENV']);
elseif(@$_SERVER["HTTP_HOST"] == "api.test.scoteid.com")
  define('SCOTEID_WEBSERVICES_ENV', 'test');
elseif(@$_SERVER["HTTP_HOST"] == "api.scoteid.com")
  define('SCOTEID_WEBSERVICES_ENV', 'production');
else
  @define('SCOTEID_WEBSERVICES_ENV', 'development');

use_soap_error_handler(FALSE);

define('SCOTEID_WEBSERVICES_ROOT', realpath(dirname(__FILE__) . '/..'));
define('SCOTEID_WEBSERVICES_PUBLIC_ROOT', SCOTEID_WEBSERVICES_ROOT . '/public');
define('SCOTEID_WEBSERVICES_CONF_DIR', SCOTEID_WEBSERVICES_ROOT . '/conf');

if(SCOTEID_WEBSERVICES_ENV == 'development' || SCOTEID_WEBSERVICES_ENV == 'test' || SCOTEID_WEBSERVICES_ENV == 'staging') {
  error_reporting(E_ALL);
  ini_set("display_errors", true);
} else {
  error_reporting(0);
  ini_set("display_errors", false);

  function my_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
    if(!(error_reporting() & errno)) {
      return;
    }

    switch($errno) {
      case E_USER_ERROR:
        exit(1);
        break;
      default:
        break;
    }

    return true;
  }

  set_error_handler("my_error_handler");
}

if(isset($_SERVER["REMOTE_ADDR"]) && ($_SERVER["REMOTE_ADDR"] == '82.71.25.251' || $_SERVER["REMOTE_ADDR"] == '80.177.230.78' || $_SERVER["REMOTE_ADDR"] == '86.4.213.52' || $_SERVER["REMOTE_ADDR"] == '127.0.0.1' || $_SERVER["REMOTE_ADDR"] == '86.4.217.182' || $_SERVER["REMOTE_ADDR"] == '77.44.124.179' || $_SERVER["REMOTE_ADDR"] == '86.150.74.116')) {
  define('SCOTEID_WEBSERVICES_PRIVATE_API', true);
} else {
  define('SCOTEID_WEBSERVICES_PRIVATE_API', false);
}

require env_path('main.php');
require lib_path('dbw.php');

// legacy samu spreadsheet function
require lib_path('samu.php');

// define('SCOTEID_WEB_ROOT', $GLOBALS['_SCOTEID_WEBSERVICES_ENV']['web_root']);

ini_set("soap.wsdl_cache_enabled", "0"); // disable WSDL cache
ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . SCOTEID_WEBSERVICES_ROOT . "/include");

//
// Autoloading

function scoteid_autoload($klass) {
  @include('classes/' . $klass . '.php');
}

spl_autoload_register('scoteid_autoload', true, true);

//
// Environments

function env_path($file, $env = null) {
    if ($env === null) $env = SCOTEID_WEBSERVICES_ENV;
    return SCOTEID_WEBSERVICES_CONF_DIR . "/environment/$env/$file";
}

//
// Libraries

function lib_path($file) {
  return SCOTEID_WEBSERVICES_ROOT . '/include/lib/' . $file;
}


// Swift mailer
require_once SCOTEID_WEBSERVICES_ROOT . '/include/swift/swift_required.php';
?>
