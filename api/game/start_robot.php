<?php

if(!defined('_APP_VERSION_'))
    exit();

use MRZX\Tools;
use MRZX\User;
use MRZX\D4SRobot;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('X-Powered-By: MRZX.ir');
header('Access-Control-Allow-Headers:Content-Type,Authorization,X-Requested-With,Accept,Origin');
header('HTTP/1.1 200 OK');

$auth=mb_substr(Tools::postValue('auth'),0,32);

$user=new User();

if($user->checkLogin($auth) ){

    try {
        $d4s=new D4SRobot($user->id,null);
        die(json_encode(
            array(
            'hasError' => false,
            'game' => $d4s->get())
            ));
    } catch (Exception $e) {
        die(json_encode(
            array(
            'hasError' => true,
            'errors' => $e->getMessage())
            ));
    }

}
die(json_encode(
    array(
    'hasError' => true,
    'errors' => 'Invalid request!')
    ));