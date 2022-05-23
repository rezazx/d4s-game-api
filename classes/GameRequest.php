<?php
namespace MRZX;
use DB;
class GameRequest{

    public static function addRequset($user_id)
    {
        if(DB::insert(_DB_PERFIX_.'game_request',array
                    (
                        'user_id'=>Tools::sSql($user_id),
                        'target'=>'0',
                        'create_at'=>date("Y-m-d H:i:s", time()) 
                    ) 
        ))
            return DB::insertId();
        return false;
    }

    public static function getValidRequests()
    {
        $d=date("Y-m-d H:i:s", time()-60);
        $list=DB::query("SELECT * From "._DB_PERFIX_."game_request 
        WHERE create_at >= %t AND target=%s  ",$d,'0');

        return $list;
    }

    public static function getRequestTarget($user_id,$request_id)
    {
        $d=date("Y-m-d H:i:s", time()-60);

        $target=DB::queryFirstField("SELECT `target` From "._DB_PERFIX_."game_request 
        WHERE ( id=%i AND user_id=%i AND create_at >= %t )",Tools::sSql($request_id),Tools::sSql($user_id),$d);
        return $target;
    }
    public static function updateTarget($request_id,$target)
    {
        DB::query("UPDATE "._DB_PERFIX_."game_request SET target=%s WHERE id=%i ",
            Tools::sSql($target),Tools::sSql($request_id));
    }
}