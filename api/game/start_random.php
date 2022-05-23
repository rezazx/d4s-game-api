<?php

if(!defined('_APP_VERSION_'))
    exit();

use MRZX\Tools;
use MRZX\User;
use MRZX\D4S;
use MRZX\GameRequest;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('X-Powered-By: MRZX.ir');
header('Access-Control-Allow-Headers:Content-Type,Authorization,X-Requested-With,Accept,Origin');
header('HTTP/1.1 200 OK');

$auth=mb_substr(Tools::postValue('auth'),0,32);

$user=new User();
$req_id=(int)Tools::postValue('req_id');

if($user->checkLogin($auth) ){
    if($req_id)
    {
        $target=GameRequest::getRequestTarget($user->id,$req_id);
        if(!empty($target))
        {
            try {
                $players=D4S::playersID($target);
                $d4s=new D4S($players['p1_id'],$players['p2_id'],$target);
                
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

        if(is_null($target))
            die(json_encode(
                array(
                'hasError' => false,
                'game' => 'TIMEOUT',
                'request_id'=>$req_id)
                ));
        else
            die(json_encode(
                array(
                'hasError' => false,
                'game' => 'RESERVE',
                'request_id'=>$req_id)
                ));
    }
    $req=GameRequest::getValidRequests();
    $userReserved=false;
    if(!empty($req))
        foreach($req as $r)
        {
            if($r['user_id'] == $user->id)
                {
                    $userReserved=$r['id'];
                    continue;
                }
            try {
                $d4s=new D4S($r['user_id'],$user->id,null);
                GameRequest::updateTarget($r['id'],$d4s->auth);
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
    if(!$userReserved)
        $userReserved=GameRequest::addRequset($user->id);

    die(json_encode(
        array(
        'hasError' => false,
        'game' => 'RESERVE',
        'request_id'=>$userReserved)
        ));

}
die(json_encode(
    array(
    'hasError' => true,
    'errors' => 'Invalid request!')
    ));