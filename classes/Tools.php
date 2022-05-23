<?php
namespace MRZX;
use DB;

class Tools
{

    public function __construct(){

    }

    public static function safeReadInput($t,$html=false)
    {
        $t=trim($t);
        $t=stripslashes($t);
        if(!$html)
            $t=htmlspecialchars($t);

        return $t;
    }

    public static function sSql($t,$html=false,$mysqli=null){
        if(is_null($mysqli))
           $mysqli = DB::get();
           
        $t=$mysqli->real_escape_string(Tools::safeReadInput($t,$html));
        return $t;
    }

    public static function postValue($str,$defValue=false)
    {
        if (!isset($str) || empty($str) || !is_string($str)) {
            return false;
        }
        $str=Tools::safeReadInput($str);
        $str=(isset($_POST[$str]))?Tools::safeReadInput($_POST[$str]):$defValue;
        return $str;
    }

    public static function getValue($str,$defValue=false)
    {
        if (!isset($str) || empty($str) || !is_string($str)) {
            return false;
        }
        $str=Tools::safeReadInput($str);
        $str=(isset($_GET[$str]))?Tools::safeReadInput($_GET[$str]):$defValue;
        return $str;
    }

    public static function test()
    {
        echo 'TOOLS Module is work!';
    }

    public static function encrypt($str){
        return md5(_KEY_.$str);
    }

    public static function isEmail($email)
    {
        return !empty($email) && preg_match(Tools::cleanNonUnicodeSupport('/^[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+(?:[.]?[_a-z\p{L}0-9-])*\.[a-z\p{L}0-9]+$/ui'), $email);
    }

    public static function isPhone($number)
    {
        return !empty($number) && preg_match('/^([0]{1})([9]{1})([0-9]{9})$/', $number);
    }

    public static function isString($data)
    {
        return is_string($data);
    }

    public static function isUrl($url)
    {
        return preg_match(Tools::cleanNonUnicodeSupport('/^[~:#,$%&_=\(\)\.\? \+\-@\/a-zA-Z0-9\pL\pS-]+$/u'), $url);
    }

    public static function cleanNonUnicodeSupport($pattern)
    {
        if (!defined('PREG_BAD_UTF8_OFFSET')) {
            return $pattern;
        }
        return preg_replace('/\\\[px]\{[a-z]{1,2}\}|(\/[a-z]*)u([a-z]*)$/i', '$1$2', $pattern);
    }

    public static function strtolower($str)
    {
        if (is_array($str)) {
            return false;
        }
        if (function_exists('mb_strtolower')) {
            return mb_strtolower($str, 'utf-8');
        }
        return strtolower($str);
    }

    public static function getRequestUri($n=0)
    {
        $req = Tools::safeReadInput(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
        $req=explode('?',$req)[0];
        if(_BASE_PATH_!='/')
            $req=str_replace(_BASE_PATH_,'',$req);
        if(count(explode('/',$req))<=$n )
            return false;
        $req=explode('/',$req)[$n];
        return $req;
    }

    /**
     * get current client ip
     *
     * @return string
     */
    public static function getClientIp() {
        if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) )
            $ip = Tools::safeReadInput($_SERVER['HTTP_CLIENT_IP']);
        elseif ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
            $ip = Tools::safeReadInput($_SERVER['HTTP_X_FORWARDED_FOR']);
        else
            $ip = Tools::safeReadInput($_SERVER['REMOTE_ADDR']);
        
        return $ip;
    }

    /**
     * get current client user_agent
     *
     * @return string
     */
    public static function getClientAgent(){
        return Tools::safeReadInput($_SERVER['HTTP_USER_AGENT']);
    }

    public static function sessionStart()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
