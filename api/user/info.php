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

$id=(int)Tools::getValue('user_id');
$user=new User();
if(User::exists($id)){
    die(json_encode(
        array(
        'hasError' => false,
        'user' => User::getUserInfo($id))
        ));
}
die(json_encode(
    array(
    'hasError' => true,
    'errors' => 'No user found with this ID.')
    ));