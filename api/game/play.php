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

$p1=(int)Tools::postValue("p1");
$p2=(int)Tools::postValue("p2");
$push=(int)Tools::postValue("push");
$game=mb_substr(Tools::postValue('game_auth'),0,32);
$user=new User();

if($user->checkLogin($auth) && ($user->id==$p1 || $user->id=$p2 ) && $p1!=$p2 ){

    try {
        $d4s=new D4S($p1,$p2,$game);
        $d4s->pushToCol($push,$user->id);
        
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