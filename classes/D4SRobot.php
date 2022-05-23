<?php

namespace MRZX;
use MRZX\D4S;

class D4SRobot extends D4S{
    const robot_id=1;
    private $robotColor;
    private $userColor;

    public function __construct($p2,$auth=null){
        parent::__construct(self::robot_id,$p2,$auth);
        $this->robotColor=parent::COLOR['blue'];
        $this->userColor=parent::COLOR['red'];
    }

    public function checkHandout(){
        if($this->isFinished())
        {
            return false;
        }
        if($this->robotColor==parent::COLOR['blue'] && $this->red_size>=$this->blue_size)
        {
            $this->enableUpdateDb=false;//disable update datebase on change board value...
            $y=$this->play();
            $this->enableUpdateDb=true;
            return $this->pushToCol($y,self::robot_id);
        }
        return false;
    }

    private function popFromCol($y)
    {
        for($i=self::_ROW_SIZE_-1;$i>=0;$i--)
        {
        if($this->getField($i,$y)!=parent::COLOR['white'] &&
         ($this->getField($i-1,$y)==parent::COLOR['white'] || $this->getField($i-1,$y)==parent::COLOR['nocolor']))
            {
            //set_Feild(i,y,parent::COLOR['white']);
                $f=$this->getField($i,$y);
                $this->board[$i][$y]=parent::COLOR['white'];

                if($f==parent::COLOR['blue'])
                    $this->blue_size--;
                else if($f==parent::COLOR['red'])
                    $this->red_size--;

            }
        }    
    }

    private function getColLastField($y){
        for($i=self::_ROW_SIZE_-1;$i>=0;$i--)
        {
            if($this->getField($i,$y)==parent::COLOR['white'])
            {
                return $i;
            }
        }
        return -1;//the col is full or not exist ...
    }

    private function arrayGetMax($arr,$size)
    {
        $max=0;
        $max_col=0;
    
        for($i=0;$i<$size;$i++)
        {
            if($arr[$i]>$max)
            {
                $max=$arr[$i];
                $max_col=$i;
            }
        }
    
        return $max_col;
    }

    private function randomColArray(){
        $a = range(0,self::_COL_SIZE_-1);
        shuffle($a);
        return $a;
    }

    private function play(){
        $arCols=$this->randomColArray();

        if($this->red_size+$this->blue_size==0 && $this->robotColor==parent::COLOR['blue'] ) //select middle col when start game...
            return 3;
        for($j=0;$j<self::_COL_SIZE_;$j++)//check pc is can win select...
        {
            if($this->_pushToCol($j,$this->robotColor))
            {   
                ////console.log("he send push to : "+$this->_checkWin());
                if($this->_checkWin()==$this->robotColor)
                {                
                    $this->popFromCol($j);                
                    //console.log("--->>f 1 "+j);
                    return $j;
                }
                else
                {
                    $this->popFromCol($j);
                }
            }
        }    

        for($j=0;$j<self::_COL_SIZE_;$j++)//if user can win select cell...
        {
            if($this->_pushToCol($j,$this->userColor))
            {
                if($this->_checkWin()==$this->userColor)
                {
                    $this->popFromCol($j);                
                    //console.log("--->>f 2 ");
                    return $j;
                }
                $this->popFromCol($j);
            }
        }

        for($k=0;$k<count($arCols);$k++)//pc is can create 3points and after can win 4 points (2 empty cell)
        {
            $j=$arCols[$k];
            $cur_row=$this->getColLastField($j);
            
            if($cur_row==-1)
                continue;
            if(!$this->_pushToCol($j,$this->robotColor))
                continue;
            $cell=$this->getField($cur_row,$j);
            if(
                    (($cell==$this->getField($cur_row,$j+1) && $this->getField($cur_row,$j+2)==$cell) &&
                    (($this->getField($cur_row,$j+3)==parent::COLOR['white']) || ($this->getField($cur_row,$j-1)==parent::COLOR['white'])))||
                    (($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j-2)==$cell)&&
                    (($this->getField($cur_row,$j-3)==parent::COLOR['white']) || ($this->getField($cur_row,$j+1)==parent::COLOR['white'])))||
                    (($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j+1)==$cell)&&
                    (($this->getField($cur_row,$j-2)==parent::COLOR['white'] || $this->getField($cur_row,$j+2)==parent::COLOR['white'])))||
                    (($cell==$this->getField($cur_row-1,$j-1) && $this->getField($cur_row+1,$j+1)==$cell) &&
                    ($this->getField($cur_row-2,$j-2)==parent::COLOR['white'] || $this->getField($cur_row+2,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j-1) && $this->getField($cur_row-1,$j+1)==$cell) &&
                    ($this->getField($cur_row+2,$j-2)==parent::COLOR['white'] || $this->getField($cur_row-2,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j+1) && $this->getField($cur_row+2,$j+2)==$cell) &&
                    ($this->getField($cur_row+3,$j+3)==parent::COLOR['white'] || $this->getField($cur_row-1,$j-1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row-1,$j-1) && $this->getField($cur_row-2,$j-2)==$cell) &&
                    ($this->getField($cur_row-3,$j-3)==parent::COLOR['white'] || $this->getField($cur_row+1,$j+1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row+1,$j-1) && $this->getField($cur_row+2,$j-2)==$cell) &&
                    ($this->getField($cur_row+3,$j-3)==parent::COLOR['white'] || $this->getField($cur_row-1,$j+1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row-1,$j+1) && $this->getField($cur_row-2,$j+2)==$cell) &&
                    ($this->getField($cur_row-3,$j+3)==parent::COLOR['white'] || $this->getField($cur_row+1,$j-1)==parent::COLOR['white']))
                    )
            {
                if($this->_pushToCol($j,$this->userColor))
                {
                    if($this->_checkWin()!=$this->userColor)
                    {
                    $this->popFromCol($j);
                    $this->popFromCol($j);
                    //console.log("--->>f 30A ");
                    return $j;
                    }
                    else
                    {
                        $this->popFromCol($j);
                        $this->popFromCol($j);
                    }
                }
                else
                {
                    $this->popFromCol($j); 
                    //console.log("--->>f 30B ");               
                    return $j;
                }
            }
            else
            $this->popFromCol($j);
        }

        for($k=0;$k<count($arCols);$k++)//user is can create 3points and after can win 4 points (2 empty cell)
        {
        $j=$arCols[$k];
            $cur_row=$this->getColLastField($j);
            //console.log("j= "+j+"  - current= "+cur_row);
            if($cur_row==-1)
                continue;
            if(!$this->_pushToCol($j,$this->userColor))
                continue;
            $cell=$this->getField($cur_row,$j);
            if(
                    (($cell==$this->getField($cur_row,$j+1) && $this->getField($cur_row,$j+2)==$cell) &&
                    (($this->getField($cur_row,$j+3)==parent::COLOR['white']) || ($this->getField($cur_row,$j-1)==parent::COLOR['white'])))||
                    (($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j-2)==$cell)&&
                    (($this->getField($cur_row,$j-3)==parent::COLOR['white']) || ($this->getField($cur_row,$j+1)==parent::COLOR['white'])))||
                    (($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j+1)==$cell)&&
                    (($this->getField($cur_row,$j-2)==parent::COLOR['white'] || $this->getField($cur_row,$j+2)==parent::COLOR['white'])))||
                    (($cell==$this->getField($cur_row-1,$j-1) && $this->getField($cur_row+1,$j+1)==$cell) &&
                    ($this->getField($cur_row-2,$j-2)==parent::COLOR['white'] || $this->getField($cur_row+2,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j-1) && $this->getField($cur_row-1,$j+1)==$cell) &&
                    ($this->getField($cur_row+2,$j-2)==parent::COLOR['white'] || $this->getField($cur_row-2,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j+1) && $this->getField($cur_row+2,$j+2)==$cell) &&
                    ($this->getField($cur_row+3,$j+3)==parent::COLOR['white'] || $this->getField($cur_row-1,$j-1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row-1,$j-1) && $this->getField($cur_row-2,$j-2)==$cell) &&
                    ($this->getField($cur_row-3,$j-3)==parent::COLOR['white'] || $this->getField($cur_row+1,$j+1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row+1,$j-1) && $this->getField($cur_row+2,$j-2)==$cell) &&
                    ($this->getField($cur_row+3,$j-3)==parent::COLOR['white'] || $this->getField($cur_row-1,$j+1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row-1,$j+1) && $this->getField($cur_row-2,$j+2)==$cell) &&
                    ($this->getField($cur_row-3,$j+3)==parent::COLOR['white'] || $this->getField($cur_row+1,$j-1)==parent::COLOR['white']))
                    )
            {
                if($this->_pushToCol($j,$this->userColor))
                {
                    if($this->_checkWin()!=$this->userColor)
                    {
                    $this->popFromCol($j);
                    $this->popFromCol($j);
                    //console.log("--->>f 40A ");
                    return $j;
                    }
                    else
                    {
                        $this->popFromCol($j);
                        $this->popFromCol($j);

                    }
                }
                else
                {
                    $this->popFromCol($j);
                    //console.log("--->>f 40B ");
                    return $j;
                }
            }
            else
            $this->popFromCol($j);
        }


        for($k=0;$k<count($arCols);$k++)//pc is can create 3points and after can win 4 points (empty cell)
        {
        $j=$arCols[$k];
            $cur_row=$this->getColLastField($j);
            //console.log("j= "+j+"  - current= "+cur_row);
            if($cur_row==-1)
                continue;
            if(!$this->_pushToCol($j,$this->robotColor))
                continue;
            $cell=$this->getField($cur_row,$j);
            if((($cell==$this->getField($cur_row,$j+1) && $this->getField($cur_row,$j+2)==$cell) &&
                ($this->getField($cur_row,$j+3)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j-2)==$cell)&&
                    ($this->getField($cur_row,$j-3)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j+1)==$cell)&&
                    ($this->getField($cur_row,$j-2)==parent::COLOR['white'] || $this->getField($cur_row,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j) && $this->getField($cur_row+2,$j)==$cell) &&
                    ($this->getField($cur_row-1,$j)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row-1,$j-1) && $this->getField($cur_row+1,$j+1)==$cell) &&
                    ($this->getField($cur_row-2,$j-2)==parent::COLOR['white'] || $this->getField($cur_row+2,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j-1) && $this->getField($cur_row-1,$j+1)==$cell) &&
                    ($this->getField($cur_row+2,$j-2)==parent::COLOR['white'] || $this->getField($cur_row-2,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j+1) && $this->getField($cur_row+2,$j+2)==$cell) &&
                    ($this->getField($cur_row+3,$j+3)==parent::COLOR['white'] || $this->getField($cur_row-1,$j-1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row-1,$j-1) && $this->getField($cur_row-2,$j-2)==$cell) &&
                    ($this->getField($cur_row-3,$j-3)==parent::COLOR['white'] || $this->getField($cur_row+1,$j+1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row+1,$j-1) && $this->getField($cur_row+2,$j-2)==$cell) &&
                    ($this->getField($cur_row+3,$j-3)==parent::COLOR['white'] || $this->getField($cur_row-1,$j+1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row-1,$j+1) && $this->getField($cur_row-2,$j+2)==$cell) &&
                    ($this->getField($cur_row-3,$j+3)==parent::COLOR['white'] || $this->getField($cur_row+1,$j-1)==parent::COLOR['white']))
                    )
            {
                if($this->_pushToCol($j,$this->userColor))
                {
                    if($this->_checkWin()!=$this->userColor)
                    {
                    $this->popFromCol($j);
                    $this->popFromCol($j);
                    //console.log("--->>f 50A ");
                    return $j;
                    }
                    else
                    {
                        $this->popFromCol($j);
                        $this->popFromCol($j);

                    }
                }
                else
                {
                    $this->popFromCol($j);
                    //console.log("--->>f 50B ");
                    return $j;
                }
            }
            else
            $this->popFromCol($j);
        }

        for($k=0;$k<count($arCols);$k++)//user is can create 3points and after can win 4 points (empty cell)
        {
        $j=$arCols[$k];
            $cur_row=$this->getColLastField($j);
            //console.log("j= "+j+"  - current= "+cur_row);
            if($cur_row==-1)
                continue;
            if(!$this->_pushToCol($j,$this->userColor))
                continue;
            $cell=$this->getField($cur_row,$j);
            if((($cell==$this->getField($cur_row,$j+1) && $this->getField($cur_row,$j+2)==$cell) &&
                ($this->getField($cur_row,$j+3)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j-2)==$cell)&&
                    ($this->getField($cur_row,$j-3)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j+1)==$cell)&&
                    ($this->getField($cur_row,$j-2)==parent::COLOR['white'] || $this->getField($cur_row,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j) && $this->getField($cur_row+2,$j)==$cell) &&
                    ($this->getField($cur_row-1,$j)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row-1,$j-1) && $this->getField($cur_row+1,$j+1)==$cell) &&
                    ($this->getField($cur_row-2,$j-2)==parent::COLOR['white'] || $this->getField($cur_row+2,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j-1) && $this->getField($cur_row-1,$j+1)==$cell) &&
                    ($this->getField($cur_row+2,$j-2)==parent::COLOR['white'] || $this->getField($cur_row-2,$j+2)==parent::COLOR['white']))||
                    (($cell==$this->getField($cur_row+1,$j+1) && $this->getField($cur_row+2,$j+2)==$cell) &&
                    ($this->getField($cur_row+3,$j+3)==parent::COLOR['white'] || $this->getField($cur_row-1,$j-1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row-1,$j-1) && $this->getField($cur_row-2,$j-2)==$cell) &&
                    ($this->getField($cur_row-3,$j-3)==parent::COLOR['white'] || $this->getField($cur_row+1,$j+1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row+1,$j-1) && $this->getField($cur_row+2,$j-2)==$cell) &&
                    ($this->getField($cur_row+3,$j-3)==parent::COLOR['white'] || $this->getField($cur_row-1,$j+1)==parent::COLOR['white'])) ||
                    (($cell==$this->getField($cur_row-1,$j+1) && $this->getField($cur_row-2,$j+2)==$cell) &&
                    ($this->getField($cur_row-3,$j+3)==parent::COLOR['white'] || $this->getField($cur_row+1,$j-1)==parent::COLOR['white']))
                    )
            {
                if($this->_pushToCol($j,$this->userColor))
                {
                    if($this->_checkWin()!=$this->userColor)
                    {
                    $this->popFromCol($j);
                    $this->popFromCol($j);
                    //console.log("--->>f 60A ");
                    return $j;
                    }
                    else
                    {
                        $this->popFromCol($j);
                        $this->popFromCol($j);

                    }
                }
                else
                {
                    $this->popFromCol($j);
                    //console.log("--->>f 60B ");
                    return $j;
                }
            }
            else
            $this->popFromCol($j);
        }

        for($k=0;$k<count($arCols);$k++)//pc is can create 3points
        {
        $j=$arCols[$k];
            $cur_row=$this->getColLastField($j);
            //console.log("j= "+j+"  - current= "+cur_row);
            if($cur_row==-1)
                continue;
            if(!$this->_pushToCol($j,$this->robotColor))
                continue;
            $cell=$this->getField($cur_row,$j);
            if(($cell==$this->getField($cur_row,$j+1) && $this->getField($cur_row,$j+2)==$cell)||
                    ($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j-2)==$cell)||
                    ($cell==$this->getField($cur_row,$j-1) && $this->getField($cur_row,$j+1)==$cell)||
                    ($cell==$this->getField($cur_row+1,$j) && $this->getField($cur_row+2,$j)==$cell)||
                    ($cell==$this->getField($cur_row-1,$j-1) && $this->getField($cur_row+1,$j+1)==$cell)||
                    ($cell==$this->getField($cur_row+1,$j-1) && $this->getField($cur_row-1,$j+1)==$cell))
            {
                if($this->_pushToCol($j,$this->userColor))
                {
                    if($this->_checkWin()!=$this->userColor)
                    {
                    $this->popFromCol($j);
                    $this->popFromCol($j);
                    //console.log("--->>f 70A ");
                    return $j;
                    }
                    else
                    {
                        $this->popFromCol($j);
                        $this->popFromCol($j);

                    }
                }
                else
                {
                    $this->popFromCol($j);
                    //console.log("--->>f 70B ");
                    return $j;
                }
            }
            else
            $this->popFromCol($j);
        }

        $find_of_col=array_fill(0,self::_COL_SIZE_,0);
        for($k=0;$k<count($arCols);$k++)//find empty cell whit max pc color hole
        {
            $j=$arCols[$k];
            $cur_row=$this->getColLastField($j);
            if($cur_row==-1)
                continue;

            if($this->_pushToCol($j,$this->robotColor))
            {
                $cell=$this->getField($cur_row,$j);
                $find=0;
                if($cell==$this->getField($cur_row,$j-1))
                    $find++;
                if($cell==$this->getField($cur_row,$j+1))
                    $find++;
                if($cell==$this->getField($cur_row-1,$j-1))
                    $find++;
                if($cell==$this->getField($cur_row+1,$j+1))
                    $find++;
                if($cell==$this->getField($cur_row+1,$j-1))
                    $find++;
                if($cell==$this->getField($cur_row-1,$j+1))
                    $find++;
                if($cell==$this->getField($cur_row-1,$j))
                    $find++;
                if($cell==$this->getField($cur_row+1,$j))
                    $find++;
            $find_of_col[$j]=$find;
            $this->popFromCol($j);
            }
            else
                $find_of_col[$j]=0;
        }
        
        $maxcol= $this->arrayGetMax($find_of_col,self::_COL_SIZE_);//insert in emptycell with max pc color cell
        if($this->_pushToCol($maxcol,$this->robotColor))
        {
            if($this->_pushToCol($maxcol,$this->userColor))
            {
                if($this->_checkWin()==$this->userColor)
                {
                    $this->popFromCol($maxcol);
                    $this->popFromCol($maxcol);
                }
                else
                {
                    $this->popFromCol($maxcol);
                    $this->popFromCol($maxcol);
                    //console.log("--->>f 33MaxA ");
                    return $maxcol;
                }
            }
            else
            {
                $this->popFromCol($maxcol);
                //console.log("--->>f 33MaxB ");
                return $maxcol;
            }
        }

        for($k=0;$k<count($arCols);$k++)
        {
        $j=$arCols[$k];
            $current_row=$this->getColLastField($j);
            if($current_row==-1)
                continue;
            if(!$this->_pushToCol($j,$this->robotColor))
                continue;
            if($this->getField($current_row,$j-1)== $this->getField($current_row,$j) ||
                    $this->getField($current_row,$j+1)==$this->getField($current_row,$j) ||
                    $this->getField($current_row-1,$j-1)==$this->getField($current_row,$j)||
                    $this->getField($current_row+1,$j+1)==$this->getField($current_row,$j)||
                    $this->getField($current_row+1,$j-1)==$this->getField($current_row,$j)||
                    $this->getField($current_row-1,$j+1)==$this->getField($current_row,$j)||
                    $this->getField($current_row+1,$j)==$this->getField($current_row,$j))
            {
                if($this->_pushToCol($j,$this->userColor))
                {
                    if($this->_checkWin()!=$this->userColor)
                    {
                    $this->popFromCol($j);
                    $this->popFromCol($j);
                    //console.log("--->>f 5A ");
                    return $j;
                    }
                    else
                    {
                        $this->popFromCol($j);
                        $this->popFromCol($j);

                    }
                }
                else
                {
                    $this->popFromCol($j);
                    //console.log("--->>f 5B ");
                    return $j;
                }
            }
            else
            $this->popFromCol($j);
        }

        for($k=0;$k<count($arCols);$k++)
        {
        $j=$arCols[$k];
            $current_row=$this->getColLastField($j);
            if($current_row==-1)
                continue;
            if(!$this->_pushToCol($j,$this->userColor))
                continue;
            if($this->getField($current_row,$j-1)== $this->getField($current_row,$j) ||
                    $this->getField($current_row,$j+1)==$this->getField($current_row,$j) ||
                    $this->getField($current_row-1,$j-1)==$this->getField($current_row,$j)||
                    $this->getField($current_row+1,$j+1)==$this->getField($current_row,$j)||
                    $this->getField($current_row+1,$j-1)==$this->getField($current_row,$j)||
                    $this->getField($current_row-1,$j+1)==$this->getField($current_row,$j)||
                    $this->getField($current_row+1,$j)==$this->getField($current_row,$j))
            {
                if($this->_pushToCol($j,$this->userColor))
                {
                    if($this->_checkWin()!=$this->userColor)
                    {
                    $this->popFromCol($j);
                    $this->popFromCol($j);
                    //console.log("--->>f 6A ");
                    return $j;
                    }
                    else
                    {
                        $this->popFromCol($j);
                        $this->popFromCol($j);

                    }
                }
                else
                {
                    $this->popFromCol($j);
                    //console.log("--->>f 6B ");
                    return $j;
                }
            }
            else
            $this->popFromCol($j);
        }

        for($k=0;$k<count($arCols);$k++) //insert first empty cell except when a higher field causes the user to win
        {
            $j=$arCols[$k];
            if($this->_pushToCol($j,$this->robotColor))
            {
                if($this->_pushToCol($j,$this->userColor))
                {
                    if($this->_checkWin() !=$this->userColor)
                    {
                        $this->popFromCol($j);
                        $this->popFromCol($j);
                        //console.log("--->>f 7A ");
                        return $j;
                    }
                    else
                    {
                        $this->popFromCol($j);
                        $this->popFromCol($j);
                    }
                }
                else
                {
                    $this->popFromCol($j);
                    //console.log("--->>f 7B ");
                    return $j;
                }
            }
        }

        for($k=0;$k<count($arCols);$k++)//insert first empty cell 
        {
            $j=$arCols[$k];
            if($this->_pushToCol($j,$this->robotColor))
            {
                $this->popFromCol($j);
                //console.log("--->>f 8 ");
                return $j;
            }
        }
        //console.log(" play Error");
        return -1;
    }

}