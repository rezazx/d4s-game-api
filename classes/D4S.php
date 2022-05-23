<?php
namespace MRZX;
use DB;
use Exception;

class D4S{

    public $id=null;//game id
    public $auth;//game unique Code
    public $p1_id;//blue
    public $p2_id;//red
    public $board;
    public $winner;
    public $create_at;
    protected $last_update;

    private $winArray=array(
        'x1'=>-1,'y1'=>-1,
        'x2'=>-1,'y2'=>-1,
        'x3'=>-1,'y1'=>-1,
        'x4'=>-1,'y4'=>-1
    );

    public $blue_size=0;
    public $red_size=0;

    const COLOR=array(
        'nocolor'=>-1,
        'white'=> 0,
        'blue'=> 1,
        'red'=> 2,
    );

    const _ROW_SIZE_=6;
    const _COL_SIZE_=7;

    protected $enableUpdateDb=true;

    public function __construct($p1,$p2,$auth=null){
        if(!User::exists($p1))
            throw new Exception("player 1 ID invalid.");
            
        if(!User::exists($p2))
            throw new Exception("player 2 ID invalid.");

        $this->p1_id=$p1;
        $this->p2_id=$p2;

        if(is_null($auth)){//create new game
            if(!$this->createGame())
                throw new Exception("There is a problem creating the new game.");
        }
        else//load game by AUTH
        {
            if(!$this->loadGame($auth))
                throw new Exception("The game can not be found with this AUTH.");

            // if($this->winner!==self::COLOR['nocolor']){
            //     if($this->winner===self::COLOR['white'])
            //         throw new Exception("The game ends in a draw.");
            //     else{
            //         $id=($this->winner===self::COLOR['blue'])?$this->p1_id:$this->p2_id;
            //         $name=User::getUserInfo($id)['nickname'];
            //         throw new Exception("The game is over and `".$name."` wins the game.");
            //     }
            // }
        }
    }

    private function createGame(){
        //$count = intval(DB::queryFirstField("SELECT COUNT(id) FROM "._DB_PERFIX_."d4s WHERE 1"));
        $this->createBoard();
        $hash=str_shuffle('aAbBcCdDeEfFgGhH1234567890');
        $auth=Tools::encrypt($hash.'_'.time());
        if(DB::insert(_DB_PERFIX_.'d4s',array
            (
                'p1_id'=>Tools::sSql($this->p1_id),
                'p2_id'=>Tools::sSql($this->p2_id),
                'board'=>Tools::sSql(json_encode($this->board)),
                'auth'=>$auth,
                'winner'=>-1,
                'create_at'=>date("Y-m-d H:i:s", time()),
                'last_update'=>date("Y-m-d H:i:s", time())

            ) 
        ))
        {
            $this->id=DB::insertId();
            $this->auth=$auth;
            $this->winner=-1;
            $this->create_at=date("Y-m-d H:i:s", time());
            $this->last_update=$this->create_at;
            User::setStatus($this->p1_id,1);
            User::setStatus($this->p2_id,1);
            return true;
        }
        return false;
    }

    private function createBoard()
    {
        $this->board=array();
        for($i=0;$i<self::_ROW_SIZE_;$i++){
            $this->board[]=array();

            for($j=0;$j<self::_COL_SIZE_;$j++){
                $this->board[$i][]=self::COLOR['white'];
            }

        }

        return $this->board;
    }
    
    private function loadGame($auth)
    {
        $game=DB::queryFirstRow("SELECT * FROM "._DB_PERFIX_."d4s WHERE auth=%s", Tools::sSql($auth));

        if(!empty($game))
        {
            if($game['p1_id'] != $this->p1_id || $game['p2_id'] != $this->p2_id)
                return false;
            $this->id=$game['id'];
            $this->auth=$game['auth'];
            $this->winner=$game['winner'];
            $this->create_at=$game['create_at'];
            $this->last_update=$game['last_update'];

            $this->board=json_decode($game['board']);
            $this->checkWinner();
            $this->blue_size=0;
            $this->red_size=0;
            for($i=0;$i<self::_ROW_SIZE_;$i++)
            {

                for($j=0;$j<self::_COL_SIZE_;$j++)
                {
                    if($this->board[$i][$j]==self::COLOR['blue'])
                        $this->blue_size++;
                    elseif($this->board[$i][$j]==self::COLOR['red'])
                        $this->red_size++;
                }
            } 
            $this->checkTimeOut();
            return true;
        }
        return false;
    }

    private function checkTimeOut()
    {
        if($this->winner !=self::COLOR['nocolor'])
            return false;
        
        if(!(strtotime($this->last_update) + 30 < time()))
            return false;
        

        if($this->blue_size<= $this->red_size )//handout :blue => red is winner
        {
            $this->winner=self::COLOR['red'];
        }else{//handout red => blue is winner
            $this->winner=self::COLOR['blue'];
        }

        DB::query("UPDATE "._DB_PERFIX_."d4s SET winner=%i WHERE id=%i ",
        Tools::sSql($this->winner),Tools::sSql($this->id));
        User::setStatus($this->p1_id,0);
        User::setStatus($this->p2_id,0);
        return true;
    }

    private function updateDB()
    {
        if($this->enableUpdateDb)
            DB::query("UPDATE "._DB_PERFIX_."d4s SET board=%s , last_update=%t WHERE id=%i ",
                    Tools::sSql(json_encode($this->board)),Tools::sSql(date("Y-m-d H:i:s", time())),Tools::sSql($this->id));
    }

    public function colIsMax($y)
    {
        for($i=self::_ROW_SIZE_-1;$i>=0;$i--)
        {
            if($this->getField($i,$y)==self::COLOR['white'])
                return false;            
        }
        return true;
    }

    public function isEmpty($x,$y)
    {
        if($this->getField($x,$y)==self::COLOR['white'])
            return true;
        
        return false;
    }

    public function getField($x,$y)
    {
        $color=self::COLOR['nocolor'];
        if(($x>=0 && $x<self::_ROW_SIZE_ ) && ($y>=0 && $y<self::_COL_SIZE_ ))
        {
            $color=$this->board[$x][$y];
        }
        return $color;
    }

    private function setField($x,$y,$color)
    {
        if(($x>=0 && $x<self::_ROW_SIZE_) && ($y>=0 && $y<self::_COL_SIZE_ ) && $this->isEmpty($x,$y) )
        {
            $this->board[$x][$y]=$color;
            $this->updateDB();
            if($color==self::COLOR['blue'])
                $this->blue_size++;
            else if($color==self::COLOR['red'])
                $this->red_size++;
        }
    }

    public function pushToCol($y,$player_id){
        if($this->winner!==self::COLOR['nocolor'])
            return false;
        $color=0;
        if($player_id==$this->p1_id)
            $color=self::COLOR['blue'];
        elseif($player_id==$this->p2_id)
            $color=self::COLOR['red'];
        
        if( ($color==self::COLOR['blue'] && $this->blue_size <= $this->red_size) ||
            ($color==self::COLOR['red']  && $this->blue_size > $this->red_size) )
                return $this->_pushToCol($y,$color);
        return false;
    }

    protected function _pushToCol($y,$color)
    {
        if(!in_array($color,self::COLOR))
            return false;
        if($this->colIsMax($y)){            
            return false;
        }

        for($i=self::_ROW_SIZE_-1;$i>=0;$i--)
        {
            if($this->getField($i,$y)==self::COLOR['white'])
            {
                $this->setField($i,$y,$color);
                return true;
            }
        }
        return false;
    }

    public function checkWinner(){
        $this->winner=$this->_checkWin();
        DB::query("UPDATE "._DB_PERFIX_."d4s SET winner=%i WHERE id=%i ",
                Tools::sSql($this->winner),Tools::sSql($this->id));
        if($this->winner !==self::COLOR['nocolor'])
        {
            User::setStatus($this->p1_id,0);
            User::setStatus($this->p2_id,0);
        }

        return $this->winner;
    }

    protected function _checkWin()
    {

        $find=0;
        $currentColor=self::COLOR['nocolor'];
        $previousColor=self::COLOR['nocolor'];
                    
        //Horizontal checking    
        for($i=0;$i<self::_ROW_SIZE_;$i++)
        {
            $find=0;$previousColor=self::COLOR['white'];
            $this->resetWinnArray();
            for($j=0;$j<self::_COL_SIZE_;$j++)
            {
                $currentColor=$this->getField($i,$j);
                if($currentColor==$previousColor && $currentColor!=self::COLOR['white'])
                {    
                    $find++;
                    $this->setWinnArray($i,$j);
                }
                else if( ($find < 3) && $currentColor!=$previousColor)
                {
                    $find=0;
                    $this->resetWinnArray();
                    $this->setWinnArray($i,$j);
                }
                if($find>=3)
                    return $previousColor;
                $previousColor=$currentColor;
            }
        }        
        
        //Vertical checking    
        for($j=0;$j<self::_COL_SIZE_;$j++)
        {
            $find=0;$previousColor=self::COLOR['nocolor'];
            $this->resetWinnArray();                    
            for($i=0;$i<self::_ROW_SIZE_;$i++)
            {
                $currentColor=$this->getField($i,$j);
                if($currentColor==$previousColor && $currentColor!=self::COLOR['white'])
                {
                    $find++;                    
                    $this->setWinnArray($i,$j);
                }
                else if($find <3 && $currentColor!=$previousColor)
                {
                    $find=0;
                    $this->resetWinnArray();
                    $this->setWinnArray($i,$j);
                }
                if($find>=3)
                    return $previousColor;
                $previousColor=$currentColor;
            }
        }    
    
        //Checking diagonally from top left to bottom right    
        for($k=0;$k<self::_COL_SIZE_;$k++)
        {
            $find=0;$previousColor=self::COLOR['nocolor'];
            $this->resetWinnArray();
            for($i=0,$j=self::_COL_SIZE_-$k-1;$i<=$k && $i<self::_ROW_SIZE_ && $j<self::_COL_SIZE_;$j++,$i++)
            {
                $currentColor=$this->getField($i,$j);
                if($currentColor==$previousColor && $currentColor!=self::COLOR['white'])
                {
                    $find++;                    
                    $this->setWinnArray($i,$j);
                }
                else if($find <3 && $currentColor!=$previousColor)
                {
                    $find=0;
                    $this->resetWinnArray();
                    $this->setWinnArray($i,$j);
                }
                if($find>=3)
                    return $previousColor;
                $previousColor=$currentColor;
            }
        }
    
        for($k=0;$k<self::_ROW_SIZE_;$k++)
        {
            $find=0;$previousColor=self::COLOR['nocolor'];
            $this->resetWinnArray();                    
            for($i=$k+1,$j=0;$j<=self::_COL_SIZE_-$k && $i<self::_ROW_SIZE_;$i++,$j++)
            {
                $currentColor=$this->getField($i,$j);
                if($currentColor==$previousColor && $currentColor!=self::COLOR['white'])
                {
                    $find++;                                    
                    $this->setWinnArray($i,$j);
                }
                else if($find <3 && $currentColor!=$previousColor)
                {
                    $find=0;
                    $this->resetWinnArray();
                    $this->setWinnArray($i,$j);
                }
                if($find>=3)
                    return $previousColor;
                $previousColor=$currentColor;
            }
        }
            
        //Checking diagonally from bottom left to top right
        for($k=0;$k<self::_ROW_SIZE_;$k++)
        {
            $find=0;$previousColor=self::COLOR['nocolor'];
            $this->resetWinnArray();
            for($i=$k,$j=0;$j<=$k && $i>=0;$i--,$j++)
            {

                $currentColor=$this->getField($i,$j);                
                if($currentColor==$previousColor && $currentColor!=self::COLOR['white'])
                {
                    $find++;                    
                    $this->setWinnArray($i,$j);
                }
                else if($find <3 && $currentColor!=$previousColor)
                {
                    $find=0;
                    $this->resetWinnArray();
                    $this->setWinnArray($i,$j);
                }
                if($find>=3)
                    return $previousColor;
                $previousColor=$currentColor;
            }
        }
    
        for($k=0;$k<self::_ROW_SIZE_;$k++)
        {
            $find=0;$previousColor=self::COLOR['nocolor'];
            $this->resetWinnArray();                    
            for($i=self::_ROW_SIZE_-1,$j=$k+1;$i>=0 && $j<self::_COL_SIZE_;$i--,$j++)
            {
                $currentColor=$this->getField($i,$j);
                if($currentColor==$previousColor && $currentColor!=self::COLOR['white'])
                {
                    $find++;                    
                    $this->setWinnArray($i,$j);
                }
                else if($find <3 && $currentColor!=$previousColor)
                {
                    $find=0;
                    $this->resetWinnArray();
                    $this->setWinnArray($i,$j);
                }
                if($find>=3)
                    return $previousColor;
                $previousColor=$currentColor;
            }
        }
        
        $this->resetWinnArray();
        for($i=0;$i<self::_ROW_SIZE_;$i++)
            for($j=0;$j<self::_COL_SIZE_;$j++)
            {
                if($this->getField($i,$j)==self::COLOR['white']){
                    return self::COLOR['nocolor'];//game no  end ,and can continue...
                }
            }

        return self::COLOR['white']; //Equal
    }


    private function setWinnArray($x,$y)
    {
        if(!$this->enableUpdateDb)
            return;

        if($this->winArray['x1']==-1)
        {
            $this->winArray['x1']=$x;
            $this->winArray['y1']=$y;
        }
        else  if($this->winArray['x2']==-1)
        {
            $this->winArray['x2']=$x;
            $this->winArray['y2']=$y;
        }      
        else  if($this->winArray['x3']==-1)
        {
            $this->winArray['x3']=$x;
            $this->winArray['y3']=$y;
        }      
        else  if($this->winArray['x4']==-1)
        {
            $this->winArray['x4']=$x;
            $this->winArray['y4']=$y;
        }
    }

    public function getWinnArray($n)
    {        
        return $this->winArray;
    }

    private function resetWinnArray()
    {
        $this->winArray['x1']=-1;
        $this->winArray['y1']=-1;

        $this->winArray['x2']=-1;
        $this->winArray['y2']=-1;

        $this->winArray['x3']=-1;
        $this->winArray['y3']=-1;

        $this->winArray['x4']=-1;
        $this->winArray['y4']=-1;
    }

    public function get()
    {
        $game=array(
            'auth'=>$this->auth,
            'p1'=>User::getUserInfo($this->p1_id),
            'p2'=>User::getUserInfo($this->p2_id),
            'board'=>$this->board,
            'winner'=>$this->winner,
            'create_at'=>$this->create_at,
            'p1_size'=>$this->blue_size,
            'p2_size'=>$this->red_size,
            'win_array'=>$this->winArray
        );

        return $game;
    }

    public static function playersID($auth)
    {
        $r=DB::queryFirstRow("SELECT p1_id , p2_id From "._DB_PERFIX_."d4s 
        WHERE auth=%s ",Tools::sSql($auth));
        return $r;
    }

    public function isFinished()
    {
        if($this->winner!=self::COLOR['nocolor'])
            return true;
        return false;
    }

}