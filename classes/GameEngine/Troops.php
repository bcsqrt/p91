<?php

namespace GameEngine;

use Database\Dbconnect;
use Otherfunction\Tools;
use GameEngine\Map;

class Troops extends Dbconnect
{
    protected $lat,$long,$speedrate,$speed,$carry,$armyarray;
    protected $carrywood,$carrystone,$carrygold,$carryfood;
    protected $mapinfo,$distance,$course,$ETA;
    

    public function firstStep($UID, $CID, $UWOOD, $USTONE, $UGOLD, $UFOOD)
    {

        if (isset($_POST['lat']) && isset($_POST['long']) && isset($_POST['speed'])
        ) {
            $tools=new Tools;
            $this->lat=$_POST['lat'];
            $this->long=$_POST['long'];
            $this->speedrate=$tools->trimSpeed($_POST['speed']);

            if ($tools->checkCoordinate($this->lat) 
                && $tools->checkCoordinate($this->long) 
            ) {
                if ($this->speedrate>0) {
                    $userTrops=$this->conn->prepare(
                        'SELECT USERID,COLONYID,TROOPID,VALUE,SPEED,CARRY
                        FROM usertroops WHERE USERID=? AND COLONYID=? AND VALUE>0'
                    );
                    $userTrops->bind_param('ii', $UID, $CID);
                    $userTrops->execute();
                    $userTropsDat=$userTrops->get_result();
                    $userTrops->close();
                    if ($userTropsDat->num_rows>0) {
                        $this->armyarray=array();
                        $this->speed=0;
                        while ($row=$userTropsDat->fetch_assoc()) {
                              $tid=$_POST['tid'.$row['TROOPID'].''];
                              $value=$_POST['value'.$row['TROOPID'].''];
                            if (isset($tid) && isset($value)) {
                                if (is_numeric($tid) && is_numeric($value)) {
                                    if ($value>0) {
                                        if ($value>$row['VALUE']) {
                                            $value=$row['VALUE'];
                                        }                                        
                                        if ($this->speed==0) {
                                            $this->speed=$row['SPEED'];
                                        } elseif ($this->speed>$row['SPEED']) {
                                            $this->speed=$row['SPEED'];
                                        }
                                        $this->carry+=($row['CARRY']*$value);
                                        $this->armyarray[$tid]=$value;
                                       
                                        // echo '{$tid} => {$value} <br>';
                                      
                                    }
                                }
                            }
                          
                        }
                        if (!empty($this->armyarray)) {
                            
                            $this->carrywood=$_POST['Wood'];
                            $this->carrystone=$_POST['Stone'];
                            $this->carrygold=$_POST['Gold'];
                            $this->carryfood=$_POST['Food'];   
                            
                            $this->carrywood=$tools->resourcecheck(
                                $UWOOD, $this->carrywood
                            );
                            $this->carrystone=$tools->resourcecheck(
                                $USTONE,  $this->carrystone
                            );
                            $this->carrygold=$tools->resourcecheck(
                                $UGOLD, $this->carrygold
                            );
                            $this->carryfood=$tools->resourcecheck(
                                $UFOOD,  $this->carryfood
                            );
                            $this->speed=($this->speed*$this->speedrate)
                            /100;
                            $map=new Map();
                            $this->mapinfo=$map->checkMapinfo(
                                $this->lat, $this->long
                            );                            
                        } else {
                            echo '<div class="row p-0 m-0 ">
                            <div class="col-sm p-0 m-0 ">
                                <div class="bg-danger rounded 
                                    p-2 m-0 d-flex justify-content-center">
                                        <div class="text-white">
                                            <i class="fas 
                                            fa-exclamation-circle fa-2x"> </i>
                                             You have not pick any troops
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                    } else {
                        echo '<div class="row p-0 m-0">
                        <div class="col-sm p-0 m-0">
                            <div class="bg-danger rounded 
                                p-2 m-0 d-flex justify-content-center">
                                    <div class="text-white">
                                        <i class="fas 
                                        fa-exclamation-circle fa-2x"> </i>
                                        You haven\'t enough troops.
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }

                } else {
                    echo '<div class="row p-0 m-0">
                    <div class="col-sm p-0 m-0">
                        <div class="bg-danger rounded 
                            p-2 m-0 d-flex justify-content-center">
                                <div class="text-white">
                                    <i class="fas fa-exclamation-circle fa-2x"> </i>
                                    Incorrect Speed Rate.
                                </div>
                            </div>
                        </div>
                    </div>';
                }

            } else {
                echo '<div class="row p-0 m-0">
                        <div class="col-sm p-0 m-0">
                            <div class="bg-danger rounded 
                            p-2 m-0 d-flex justify-content-center">
                                <div class="text-white">
                                    <i class="fas fa-exclamation-circle fa-2x"> </i>
                                    Incorrect coordinates.
                                </div>
                            </div>
                        </div>
                    </div>';
            }
            

        }

    }

    public function secondStep($UID, $CID, $UNAME, $ULAT, $ULONG)
    {
        $tools=new Tools;
        if (!empty($this->armyarray)) {
            $totalcarry=$this->carrywood+$this->carrystone+
            $this->carrygold+$this->carryfood;

            if ($this->carry>=$totalcarry) {
                $textcolor='text-success';
            } else {
                $textcolor='text-danger font-weight-bold';                
            }
            $this->distance=$tools->calculateDistance(
                $ULAT, $ULONG, $this->lat, $this->long
            );
            $this->course=$tools->calculateCourse(
                $ULAT, $ULONG, $this->lat, $this->long
            );
            $this->ETA=$tools->calculateEta($this->speed, $this->distance);
            $armytid=implode(',', array_keys($this->armyarray));
            $armyval=implode(',', $this->armyarray);

            echo '
          <div class="col-sm-3 m-2 p-2 border rounded bg-light">
          <form method="POST" action="?p=tmanagement&co='.$CID.'">
          <input type="hidden" value="'.$this->lat.'" name="lat">
          <input type="hidden" value="'.$this->long.'" name="long">
          <input type="hidden" value="'.$this->speedrate.'" name="speedrate">
          <input type="hidden" value="'.$this->carrywood.'" name="Wood">
          <input type="hidden" value="'.$this->carrystone.'" name="Stone">
          <input type="hidden" value="'.$this->carrygold.'" name="Gold">
          <input type="hidden" value="'.$this->carryfood.'" name="Food">
          <input type="hidden" value="'.$armytid.'" name="tid[]">
          <input type="hidden" value="'.$armyval.'" name="value[]">
          <input type="hidden" value="'.$this->mapinfo.'" name="target">
          <input type="hidden" value="'.$this->carry.'" name="capacity">
            <div class="font-weight-bold text-center 
            text-success m-2 p-2">TARGET DETAILS</div>
            <table class="w-100 border mb-3">
              <tr class="border">
                <td class="w-25 ">Target:</td>
                <td class="w-75 text-danger font-weight-bold">'.$this->mapinfo.'</td>
              </tr>
              <tr class="border">
                <td class="w-25">Coordinate:</td>
                <td class="w-75"> Lat: '.$this->lat.'  Long: '.$this->long.'</td>
              </tr>
              <tr class="border">
                <td class="w-25">Distance:</td>
                <td class="w-75">'.$this->distance.' Nm</td>
              </tr>
              <tr class="border">
                <td class="w-25">Course:</td>
                <td class="w-75">'.$this->course.'<sup>0</sup></td>
              </tr>
              <tr class="border">
                <td class="w-25">Speed:</td>
                <td class="w-75">'.$this->speed.' knots</td>
              </tr>
              <tr class="border">
                <td class="w-25">ETA:</td>
                <td class="w-75">'.$this->ETA.'</td>
              </tr>
              <tr class="border">
                <td class="w-25">Capacity:</td>
                <td class="w-75 '.$textcolor.'">'.$this->carry.' units</td>
              </tr>
              <tr class="border">
                <td class="w-25">Carry:</td>
                <td class="w-75">'.$totalcarry.' units</td>
              </tr>
            </table>
            <button type="submit" class="btn btn-success 
            btn-sm font-weight-bold w-100">Lock Target</button>
            </form></div> </div>';
        }
    }

    public function thirdStep($CID)
    {
        if (!empty($_POST['lat']) && !empty($_POST['long']) 
            && !empty($_POST['speedrate']) && !empty($_POST['tid'])
            && !empty($_POST['value']) && !empty($_POST['target'])
        ) { 
          
            $armytid=implode(',', $_POST['tid']);
            $armyval=implode(',', $_POST['value']);
            echo '<div class="col-sm-3 m-2 p-2 border rounded bg-light">
            <form method="POST" id="thirdtropform" action="#">
            <input type="hidden" value="'.$CID.'" name="cid">
            <input type="hidden" value="'.$_POST['lat'].'" name="lat">
            <input type="hidden" value="'.$_POST['long'].'" name="long">
            <input type="hidden" value="'.$_POST['speedrate'].'" name="speedrate">
            <input type="hidden" value="'.$_POST['Wood'].'" name="Wood">
            <input type="hidden" value="'.$_POST['Stone'].'" name="Stone">
            <input type="hidden" value="'.$_POST['Gold'].'" name="Gold">
            <input type="hidden" value="'.$_POST['Food'].'" name="Food">
            <input type="hidden" value="'.$armytid.'" name="tid">
            <input type="hidden" value="'.$armyval.'" name="value">
            <div class="font-weight-bold text-center
            text-danger m-2 p-2">MISSION</div>
            <table class="w-100 mb-1">
              <tr>
                <td>
                  <div class="radio radio-info radio-inline">
                    <input type="radio" name="do"
                     id="radio1" value="Spy">
                    <label for="radio1">Spy</label>
                  </div>
                </td>
                <td></td>
              </tr>
              <tr>
                <td>
                  <div class="radio radio-danger radio-inline">
                    <input type="radio" name="do" 
                    id="radio2" value="Attack" >
                      <label for="radio2">Attack</label>
                  </div>
                </td>
                <td></td>
              </tr>
              <tr>
                <td>
                  <div class="radio radio-success radio-inline">
                    <input type="radio" name="do" 
                    id="radio3" value="Trade" >
                    <label for="radio3">Trade</label>
                  </div>
                </td>
                <td></td>
              </tr>
              <tr>
                <td colspan="2">
                  <div class="radio radio-warning">
                    <input type="radio" name="do" 
                    id="radio4" value="Colonization" >
                    <label for="radio4">Colonization</label>
                  </div>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <div class="radio radio-secondary">
                    <input type="radio" name="do" 
                    id="radio5" value="Deployment" >
                    <label for="radio5">Deployment</label>
                  </div>
                </td>
              </tr>
              <tr>
                <td colspan="2">
                  <div class="radio radio-primary">
                    <input type="radio" name="do" 
                    id="radio6" value="Research" >
                    <label for="radio6">Research</label>
                  </div>
                </td>
              </tr>
              <tr>
               <td colspan="2"></td>
              </tr>
            </table>
            <button type="submit" class="btn btn-danger 
            btn-sm font-weight-bold w-100 mt-3" >Send</button>
          </div></form>
        </div>';

        }

    }
    
    public function userTrooplist($CID)
    {
        $TroopList=$this->conn->prepare(
            'SELECT ID,TNAME,TIMG FROM troops ORDER BY ID'
        );
        $TroopList->execute();
        $TroopListDat=$TroopList->get_result();
        $TroopList->close();
        $result='<form method="post" action="?p=tmanagement&co='.$CID.'">';
        $result.='<div class="row m-0 p-2 justify-content-center" >'; 

        while ($row=$TroopListDat->fetch_assoc()) {
            $usertroopval=$this->conn->prepare(
                'SELECT COLONYID,TROOPID,VALUE 
                FROM usertroops WHERE TROOPID=? AND COLONYID=?'
            );
            $usertroopval->bind_param('ii', $row['ID'], $CID);
            $usertroopval->execute();
            $usertroopvaldat=$usertroopval->get_result();
            $usertroopval->close();
            if ($usertroopvaldat->num_rows>0) {
                $row2=$usertroopvaldat->fetch_assoc();
                $valuetroop=$row2['VALUE'];
            } else {
                $valuetroop=0;
            }

            $result.='<div class="col-sm-1 p-1 m-1 rounded font-weight-bold
            troopsx text-center ">';
            $result.='<span class="tvalue" tvalue="'.$valuetroop.'">
            <img alt="'.$row['TNAME'].'" title="'.$row['TNAME'].'" src="'.$row['TIMG'].'"> 
              <br> 
              '.$valuetroop.'</span>';
            $result.='  <div class="form-group mt-1 m-0 p-0 rounded">
                        <input type="hidden" value="'.$row['ID'].'" 
                        name="tid'.$row['ID'].'">
                        <input type="text" class="form-control 
                        form-control-sm text-center font-weight-bold" 
                        placeholder="0" name="value'.$row['ID'].'">
                    </div>
                    </div>';
        }
        $result.='</div>';
        if (isset($_GET['lat'])) {
            $maplat=$_GET['lat'];
        } else {
            $maplat='';
        }
        if (isset($_GET['long'])) {
            $maplong=$_GET['long'];
        } else {
            $maplong='';
        }
        $result.='<div class="row mt-2 m-0 p-1 justify-content-center">
        <div class="col-sm-3 m-2 p-2 bg-light border rounded">
          <div class="font-weight-bold text-center m-2 p-2">TARGET</div>
          <table class="w-100 mb-3">
            <tr>
              <td>
                <div class="input-group mb input-group-sm">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Lat&nbsp;&nbsp;&nbsp;&nbsp;</span>
                  </div>
                  <input type="text" class="form-control form-control-sm 
                  text-center font-weight-bold" placeholder="0" value="'.$maplat.'" name="lat">
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <div class="input-group mb input-group-sm">
                  <div class="input-group-prepend">
                    <span class="input-group-text">Long</span>
                  </div>
                    <input type="text" class="form-control form-control-sm 
                    text-center font-weight-bold" placeholder="0" value="'.$maplong.'" name="long">
                </div>
              </td>
            </tr>
            <tr>
              <td><input type="range" class="custom-range mt-1" 
              id="customRange" value="100" name="speed"></td>
            </tr>
            <tr>
              <td class="m-0 p-2 w-100">
                <div class="input-group mb input-group-sm">
                  <div class="input-group-prepend">
                    <span class="input-group-text"><img src="img/Icons/SmLumberWC2.gif"></span>
                  </div>
                  <input type="text" class="form-control form-control-sm 
                  text-center font-weight-bold" placeholder="0" name="Wood">

                  <div class="input-group-prepend ml-1">
                    <span class="input-group-text"><img src="img/Icons/Smstone.gif"></span>
                  </div>
                  <input type="text" class="form-control form-control-sm 
                  text-center font-weight-bold" placeholder="0" name="Stone">
                </div>
                <div class="input-group mb input-group-sm mt-1">
                  <div class="input-group-prepend ">
                  <span class="input-group-text"><img src="img/Icons/SmGoldWC2.gif"></span>
                  </div>
                  <input type="text" class="form-control form-control-sm 
                  text-center font-weight-bold" placeholder="0" name="Gold">

                  <div class="input-group-prepend ml-1">
                  <span class="input-group-text"><img src="img/Icons/WC3food.gif"></span>
                  </div>
                  <input type="text" class="form-control form-control-sm 
                  text-center font-weight-bold" placeholder="0" name="Food">
                </div>
              </td>
              <td >&nbsp; &nbsp; </td>
            </tr>
          </table>
          <button type="submit" class="btn btn-primary 
          btn-sm mb-0 mt-2 font-weight-bold w-100">Check Target</button>
          </form></div> ';
        if ($_SERVER['REQUEST_METHOD']!='POST') {
            $result.='</div>';      
        }
        echo $result;
    }
}

?>

     
  
