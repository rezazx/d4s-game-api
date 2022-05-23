<?php

if(!defined('_APP_VERSION_'))
    exit();

use MRZX\Tools;
use MRZX\User;
use MRZX\D4S;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('X-Powered-By: MRZX.ir');
header('Access-Control-Allow-Headers:Content-Type,Authorization,X-Requested-With,Accept,Origin');
header('HTTP/1.1 200 OK');

$auth=mb_substr(Tools::postValue('auth'),0,32);

$game=mb_substr(Tools::postValue('game_auth'),0,32);
$user=new User();

if($user->checkLogin($auth)){

    try {
        $players=D4S::playersID($game);
        $d4s=new D4S($players['p1_id'],$players['p2_id'],$game);
        
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