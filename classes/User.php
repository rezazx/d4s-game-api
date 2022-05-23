<?php
namespace MRZX;
use DB;
class User{

    public $id;
    public $nickname;
    public $email;
    public $auth;
    public $create_at;
    public $last_login;
    public $status;
    public function __construct($id=null){
        if(!is_null($id))
        {
            if(!User::exists($id))
            {
                throw new Exception('invalid ID');
            }
            else{
                //retrieve  user info
                $this->setUser($id);
            }
        }
        else
        {
            $this->id=null;
        }

    }

    public static function exists($value,$field='id')
    {
        if(empty($value))
            return false;
        $field=Tools::strtolower($field);

        return intval( DB::queryFirstField( "SELECT id FROM "._DB_PERFIX_."users WHERE $field=%s",Tools::sSql($value) ) );
    }

    public function setUser($id)
    {
        if(User::exists($id)){
            $info=DB::queryFirstRow("SELECT * FROM "._DB_PERFIX_."users WHERE id=%s", Tools::sSql($id));
            $this->id=$info['id'];
            $this->nickname=$info['nickname'];
            $this->email=$info['email'];
            $this->auth=$info['auth'];
            $this->create_at=$info['create_at'];
            $this->last_login=$info['last_login'];
            $this->status=$info['game_status'];

            return true;
        }

        return false;
    }

    public function checkLogin($auth)
    {
        if(empty($auth))
            return false;

        $user=DB::queryFirstRow("SELECT * FROM "._DB_PERFIX_."users WHERE auth=%s", Tools::sSql($auth) );

        if(!empty($user))
        {
            $this->id=$user['id'];
            $this->nickname=$user['nickname'];
            $this->email=$user['email'];
            $this->auth=$user['auth'];
            $this->create_at=$user['create_at'];
            $this->last_login=$user['last_login'];
            $this->status=$user['game_status'];
            setcookie('d4s_user',$auth,time() + (86400 * 120),'/');
            setcookie('d4s_user_id',$user['id'],time() + (86400 * 120),'/');
            setcookie('d4s_user_nickname',$user['nickname'],time() + (86400 * 120),'/');
            setcookie('d4s_user_email',$user['email'],time() + (86400 * 120),'/');

            DB::query("UPDATE "._DB_PERFIX_."users SET last_login=%t WHERE id=%i ",
            date("Y-m-d H:i:s", time()),Tools::sSql($user['id']));
            return true;
        }

        return false;
    }

    public static function getUserByAuth($auth)
    {
        if(empty($auth))
            return false;
        $info=DB::queryFirstRow("SELECT * FROM "._DB_PERFIX_."users WHERE auth=%s", Tools::sSql($auth));
        if(!empty($info))
            return $info;
        return false;
    }

    public static function getUserInfo($id)
    {
        if(empty($id))
            return false;
        $info=DB::queryFirstRow("SELECT * FROM "._DB_PERFIX_."users WHERE id=%s", Tools::sSql($id));
        if(!empty($info)){
            if(isset($info['auth']))
                unset($info['auth']);
            return $info;
        }
        return false;
    }
    public function register($nickname,$email)
    {
        $email=Tools::safeReadInput($email);
        $nickname=Tools::safeReadInput($nickname);

        if(!Tools::isString($nickname))
            return false;

        if(Tools::isPhone($email))
            $email='d4s_'.$email.'@mrzx.ir';
        if(!Tools::isEmail($email))
            return false;
        //check user not exist !?
        if(isset($_COOKIE['d4s_user']) && !empty($_COOKIE['d4s_user']))
        {
            $info=User::getUserByAuth(Tools::safeReadInput($_COOKIE['d4s_user']));
            if(!empty($info) && $info['email']==$email)
                return $this->checkLogin($info['auth']);
        }


        $hash=str_shuffle('aAbBcCdDeEfFgGhH1234567890');
        $auth=Tools::encrypt($email.'_'.$hash);
        if(
            DB::insert(_DB_PERFIX_.'users',array
                    (
                        'email'=>Tools::sSql($email),
                        'nickname'=>Tools::sSql($nickname),
                        'auth'=>$auth,
                        'create_at'=>date("Y-m-d H:i:s", time()) ,
                        'last_login'=>date("Y-m-d H:i:s", time()),
                        'game_status'=> 0
                    ) 
                )
            ){
                return $this->checkLogin($auth);
            }

        return false;

    }

    public static function setStatus($id,$s)
    {
        DB::query("UPDATE "._DB_PERFIX_."users SET game_status=%i WHERE id=%i ",
            Tools::sSql($s),Tools::sSql($id));
    }

    public static function onlinePlayers()
    {
        $d=date("Y-m-d H:i:s", time()-600);
        $list=DB::query("SELECT id,nickname,game_status From "._DB_PERFIX_."users 
        WHERE last_login >= %t
        ORDER BY last_login DESC ",$d);

        return $list;
    }
}