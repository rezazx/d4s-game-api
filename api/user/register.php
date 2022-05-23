<?php

if(!defined('_APP_VERSION_'))
    exit();

use MRZX\Tools;
use MRZX\User;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('X-Powered-By: MRZX.ir');
header('Access-Control-Allow-Headers:Content-Type,Authorization,X-Requested-With,Accept,Origin');
header('HTTP/1.1 200 OK');

$nickname=mb_substr(Tools::postValue('nickname'),0,48);
$email=mb_substr(Tools::postValue('email'),0,128);
//$phone=Tools::postValue('phone');
$user=new User();
if($user->register($nickname,$email)){
    die(json_encode(
        array(
        'hasError' => false,
        'user' => $user)
        ));
}
die(json_encode(
    array(
    'hasError' => true,
    'errors' => 'Error registering, please enter the required fields correctly.')
    ));