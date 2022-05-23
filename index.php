<?php
/**
 * 
 */
require_once 'defines.php';

use MRZX\Tools;

function convertPhpInputToFormInput(){
    $data =json_decode(file_get_contents('php://input'));
    $type=Tools::strtolower($_SERVER['REQUEST_METHOD']);
    if(empty($data))
        return;
    if($type==='post')
    {
        foreach($data as $k=>$d){
            $_POST[Tools::safeReadInput($k)]=Tools::safeReadInput($d);
        }
    }
    elseif($type==='get'){
        foreach($data as $k=>$d){
            $_GET[Tools::safeReadInput($k)]=Tools::safeReadInput($d);
        }
    }
}

function apiSuccessHeaders(){
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('X-Powered-By: MRZX.ir');
    header('Access-Control-Allow-Headers:Content-Type,Authorization,X-Requested-With,Accept,Origin');
    header('HTTP/1.1 200 OK');
}

$request = Tools::getRequestUri();

$router=new \Bramus\Router\Router();

$router->get('/', function() {

    header('Location: https://mrzx.ir/');
    exit;
});

$router->match('GET|POST','/connection', function() {
    apiSuccessHeaders();
    die(json_encode(
        array(
        'hasError' => false,
        'message' => 'welcome to D4S game!')
        ));
});

$router->post('/user/register', function() {
    convertPhpInputToFormInput();
    include __DIR__.'/api/user/register.php';
});

$router->post('/user/login', function() {
    convertPhpInputToFormInput();
    include __DIR__.'/api/user/login.php';
});

$router->post('/user/players', function() {
    convertPhpInputToFormInput();
    include __DIR__.'/api/user/players.php';
});

$router->get('/user/{user_id}', function($user_id) {
    $_GET['user_id']=Tools::safeReadInput($user_id);
    include __DIR__.'/api/user/info.php';
});


$router->post('/game/start', function() {
    convertPhpInputToFormInput();
    include __DIR__.'/api/game/start_random.php';
});

$router->post('/game/startrobot', function() {
    convertPhpInputToFormInput();
    include __DIR__.'/api/game/start_robot.php';
});


$router->post('/game/play', function() {
    convertPhpInputToFormInput();
    include __DIR__.'/api/game/play.php';
});

$router->post('/game/content', function() {
    convertPhpInputToFormInput();
    include __DIR__.'/api/game/content.php';
});

$router->post('/game/contentrobot', function() {
    convertPhpInputToFormInput();
    include __DIR__.'/api/game/content_robot.php';
});

$router->get('/install', function() {
    include __DIR__.'/install.php';
});

$router->set404(function() {
    header('HTTP/1.1 404 Not Found');
    echo " ERROR 404 : PAGE NOT FOUND !!!";
});

$router->run();