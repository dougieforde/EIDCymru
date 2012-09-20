<?php
/* Example of ROOT/conf/environment/(development|test|production)/main.php */
$GLOBALS['_SCOTEID_WEBSERVICES_ENV'] = array(
    'db' => array(
        'hostname'        => '127.0.0.1',
        'username'        => 'root',
        'password'        => '',
        'database'        => 'scoteid_trialbasic',
        'drupal_database' => 'scoteid_drupal'
    ),
    'smtp' => array(
      'hostname'          => '127.0.0.1',
      'username'          => null,
      'password'          => null
    ),
    'bpex' => array(
      'token'             => '',
      'integration_mode'  => false,
      'debug'             => false,
      'send'              => false
    )
);
?>
