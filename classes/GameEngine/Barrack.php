<?php

namespace GameEngine;

use Database\Dbconnect;

require_once '../inc/autoloader.php';

class Barrack extends Dbconnect
{
    public function getUserTroops($UID, $CID, $TROOPTYPE, $PAGE)
    {
        $checkbarrack=$this->conn->prepare(
            'SELECT USERID,COLONYID,UNITNAME,UNITLEVEL 
            FROM userunits 
            WHERE USERID=? AND COLONYID=? AND UNITNAME=? AND UNITLEVEL>0'
        );
        $checkbarrack->bind_param('iis', $UID, $CID, $TROOPTYPE);
        $checkbarrack->execute();
        $checkbarrackdat=$checkbarrack->get_result();
        $checkbarrack->close();
        if ($checkbarrackdat->num_rows>0) {
            $barracklvlrow=$checkbarrackdat->fetch_assoc();
            $barracklvl=$barracklvlrow['UNITLEVEL'];
            $gettroops=$this->conn->prepare(
                'SELECT ID,USERID,COLONYID,TROOPID,VALUE,TNAME,TIMG,TDESCP,DAMAGE,
                ARMOR,SPEED,CARRY,WOOD,STONE,GOLD,FOOD,TIME,BLEVEL,TROOPTYPE 
                FROM usertroops 
                WHERE USERID=? AND COLONYID=? AND TROOPTYPE=? ORDER BY ORDERT'
            );
            $gettroops->bind_param('iis', $UID, $CID, $TROOPTYPE);
            $gettroops->execute();
            $gettroopsdat=$gettroops->get_result();
            $gettroops->close();
            if ($gettroopsdat->num_rows>0) {
                $return ='<div class="row barrackmain p-2 m-1">';
                while ($row=$gettroopsdat->fetch_assoc()) {
                    $return.='
                    <div class="col-sm-3 troops bg-white p-2 m-2">
                        <div class="row">
                            <div class="col-xs ml-3 mr-0 p-2">
                                <img src="'.$row['TIMG'].'">
                            </div>
                            <div class="col-xs m-0 p-0 pt-3 pl-3 font-weight-bold">
                                '.$row['TNAME'].' (<abbr title="Troops you have">
                                '.$row['VALUE'].' </abbr>)
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mt-1 ml-2 mr-0 p-2">
                                <span class="w-100 text-center ml-1 
                                font-weight-bold">Statistics </span> 
                                <hr class="m-0 border border-primary">
                                <img src="img/Icons/damage.png"> 
                                '.$row['DAMAGE'].' 
                                <img src="img/Icons/def.png" class="ml-2"> 
                                '.$row['ARMOR'].'<br>
                                <img src="img/Icons/speed.png"> '.$row['SPEED'].' 
                                <img src="img/Icons/Bag_CenarionHerbBag.png"> 
                                 '.$row['CARRY'].'
                                
                            </div>
                            <div class="col mt-1 ml-0 mr-2 p-2">
                                <span class="w-100 text-center 
                                ml-1 font-weight-bold">Production </span> 
                                <hr class="m-0 border border-primary">
                                <img src="img/Icons/SmLumberWC2.gif"> 
                                '.$row['WOOD'].' 
                                <img src="img/Icons/Smstone.gif" class="ml-2"> 
                                '.$row['STONE'].'<br>
                                <img src="img/Icons/SmGoldWC2.gif"> 
                                '.$row['GOLD'].'  
                                <img src="img/Icons/WC3food.gif" class="ml-2"> 
                                '.$row['FOOD'].'    <br>
                                <img src="img/Icons/PocketWatch_02.png"> 
                                '.$row['TIME'].'            
                            </div>
                        </div>
                        <div class="row">
                            <div class="col font-weight-light text-justify">
                                '.$row['TDESCP'].' <br><br> '.$TROOPTYPE.' level:
                                '.$row['BLEVEL'].'
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col">
                                <form method="post" 
                                action="?p='.$PAGE.'&co='.$CID.'">
                                <div class="input-group mb-3 ">
                                    <input type="hidden" name="tid" 
                                    value="'.$row['ID'].'">
                                    <input type="text" name="value"
                                    class="form-control form-control-sm" 
                                    placeholder="0">
                                    <div class="input-group-append">';
                                    if ($barracklvl>=$row['BLEVEL']) {
                                        $return.='<input type="submit" 
                                        class="btn btn-success btn-sm" value="OK">';
                                    } else {
                                        $return.='<input type="submit" 
                                        class="btn btn-success btn-sm"
                                        value="OK" disabled>';
                                    }                 
                                    $return.='</div> 
                                </div>
                                </form>
                            </div>
                        </div>
                    </div>';
                }
                $return.='</div>';
                
                echo $return;
            } else {
                echo '<div class="row p-0 m-0">
                <div class="col-sm p-0 m-0">
                    <div class="bg-danger 
                    rounded p-2 m-0 d-flex justify-content-center">
                        <div class="text-white">
                            <i class="fas fa-exclamation-circle fa-2x"> </i>
                            You haven"t any soul of lordaeron. 
                            You can find a soul by exploring the universe
                        </div>
                    </div>
                </div>
            </div>';
            }
        } else {
            echo '<div class="row p-0 m-0">
            <div class="col-sm p-0 m-0">
                <div class="bg-danger rounded p-2 m-0 d-flex justify-content-center">
                    <div class="text-white">
                        <i class="fas fa-exclamation-circle fa-2x"> </i>
                          You have to build '.$TROOPTYPE.' on your area.
                    </div>
                </div>
            </div>
        </div>';
        }


    }

    public function getUserTroopProduce($UID, $CID, $TROOPTYPE)
    {
        $produce=$this->conn->prepare(
            'SELECT usertroopsproduce.ID,usertroopsproduce.USERID,
            usertroopsproduce.COLONYID,usertroopsproduce.TROOPID,
            usertroopsproduce.VALUE,usertroopsproduce.FTIME,
            usertroopsproduce.TROOPTYPE,usertroops.TNAME FROM usertroopsproduce 
            INNER JOIN usertroops ON usertroopsproduce.TROOPID=usertroops.ID  
            WHERE usertroopsproduce.USERID=? AND usertroopsproduce.COLONYID=?
            AND usertroopsproduce.TROOPTYPE=?'
        );
        $produce->bind_param('iis', $UID, $CID, $TROOPTYPE);
        $produce->execute();
        $producedat=$produce->get_result();
        $produce->close();
        if ($producedat->num_rows>0) {
            $return='
            <div class="row p-0 m-0">
                <div class="col-sm p-0 m-0">
                    <div class="jumbotron p-2 m-0 d-flex justify-content-left">
                        <div class="pt-2">
                            <dl>';
            while ($row=$producedat->fetch_assoc()) {
                                $time=$row['FTIME']-time();
                                $return.='
                                <dt><span class="text-primary font-weight-bold">
                                '.$row['VALUE'].'</span> X 
                                <span class="text-dark font-weight-bold">
                                '.$row['TNAME'].'</span> 
                                are being produced</dt>
                                <dd>- Production completion time: 
                                <span class="text-success font-weight-bold">
                                '.$time.'</span></dd>';
            }
            $return.=' 
                            </dl>     
                        </div>
                    </div>
                </div>
            </div>';
            echo $return;
        }
    }

    public function produceUserTroop(
        $UID, $CID, $Wood, $Stone, $Gold, $Food, $TROOPTYPE, $PAGE
    ) {
        if (isset($_POST['tid']) && isset($_POST['value'])) {
            if (is_numeric($_POST['tid']) && is_numeric($_POST['value'])) {
                $tid=intval($_POST['tid']);
                $tvalue=(int) intval($_POST['value']);
                if ($_POST['tid']>0 && $_POST['value']>0) {
                    $checkresource=$this->conn->prepare(
                        'SELECT usertroops.ID,usertroops.USERID,usertroops.COLONYID,
                        usertroops.VALUE,usertroops.WOOD,usertroops.STONE,
                        usertroops.GOLD,usertroops.FOOD,usertroops.TIME,
                        usertroops.BLEVEL,userunits.UNITLEVEL 
                        FROM usertroops INNER JOIN userunits
                        ON  usertroops.USERID=userunits.USERID AND 
                        usertroops.COLONYID=userunits.COLONYID
                        WHERE usertroops.ID=? AND usertroops.USERID=? AND 
                        usertroops.COLONYID=? AND userunits.UNITNAME=?'
                    );
                    $checkresource->bind_param(
                        'iiis', $tid, $UID, $CID, $TROOPTYPE
                    );
                    $checkresource->execute();
                    $checkresourcedat=$checkresource->get_result();
                    $checkresource->close();
                    if ($checkresourcedat->num_rows>0) {
                        $row=$checkresourcedat->fetch_assoc();
                        if ($row['UNITLEVEL']>=$row['BLEVEL']) { 
                            $now=time();
                            $requrewood=$row['WOOD']*$tvalue;
                            $requrestone=$row['STONE']*$tvalue;
                            $requregold=$row['GOLD']*$tvalue;
                            $requrefood=$row['FOOD']*$tvalue;
                            $requretrotime=$row['TIME'];
    
                            if ($Wood>=$requrewood && $Stone>=$requrestone 
                                && $Gold>=$requregold && $Food>=$requrefood
                            ) {
                                $checktrooppro=$this->conn->prepare(
                                    'SELECT ID,USERID,COLONYID,FTIME,TROOPTYPE
                                    FROM usertroopsproduce
                                    WHERE USERID=? AND COLONYID=? AND TROOPTYPE=?
                                    ORDER BY ID DESC'
                                );
                                $checktrooppro->bind_param(
                                    'iis', $UID, $CID, $TROOPTYPE
                                );
                                $checktrooppro->execute();
                                $checktroopprodat=$checktrooppro->get_result();
                                $checktrooppro->close();
                                if ($checktroopprodat->num_rows>0) {
                                    $row=$checktroopprodat->fetch_assoc();
                                    $trooonefinishtime=(int) $row['FTIME']
                                    +$requretrotime;
                                    $troofinishtime=(int) $row['FTIME']
                                    +($requretrotime*$tvalue);
                                } else {
                                    $trooonefinishtime=$requretrotime+$now;
                                    $troofinishtime=($requretrotime*$tvalue)+$now;
                                }
                                $this->conn->begin_transaction();
                                $updateresorce=$this->conn->prepare(
                                    'UPDATE resources SET RVALUE=?,RLUPDATE=?
                                    WHERE USERID=? AND COLONYID=? AND RNAME=?'
                                );
                                $newvalue=$Wood-$requrewood;
                                $rname='Wood';
                                $updateresorce->bind_param(
                                    'diiis', $newvalue, $now, $UID, $CID, $rname
                                );
                                $updateresorce->execute();
    
                                $newvalue=$Stone-$requrestone;
                                $rname='Stone';
                                $updateresorce->bind_param(
                                    'diiis', $newvalue, $now, $UID, $CID, $rname
                                );
                                $updateresorce->execute();
    
                                $newvalue=$Gold-$requregold;
                                $rname='Gold';
                                $updateresorce->bind_param(
                                    'diiis', $newvalue, $now, $UID, $CID, $rname
                                );
                                $updateresorce->execute();
    
                                $newvalue=$Food-$requrefood;
                                $rname='Food';
                                $updateresorce->bind_param(
                                    'diiis', $newvalue, $now, $UID, $CID, $rname
                                );
                                $updateresorce->execute();
                                $updateresorce->close();
                                
                                $addevent=$this->conn->prepare(
                                    'INSERT INTO usertroopsproduce 
                                    (USERID,COLONYID,TROOPID,VALUE,
                                    TIME,ONEFTIME,FTIME,TROOPTYPE)
                                    VALUES (?,?,?,?,?,?,?,?)'
                                );
                                $addevent->bind_param(
                                    'iiiiiiis', $UID, $CID, $tid, $tvalue,
                                    $requretrotime, $trooonefinishtime,
                                    $troofinishtime, $TROOPTYPE
                                );
                                $addevent->execute();
                                $addevent->close();                          
                                if ($updateresorce && $addevent) {
                                    $this->conn->commit();
                                } else {
                                    $this->conn->rollback();
                                } 
                                header('location: index.php?p='.$PAGE.'&co='.$CID.'');
                                                            
                            } else {
                                echo '<div class="row p-0 m-0">
                                <div class="col-sm p-0 m-0">
                                    <div class="bg-danger rounded p-2 
                                    m-0 d-flex justify-content-center">
                                        <div class="text-white">
                                            <i class="fas fa-exclamation-circle
                                             fa-2x"> </i>
                                             You have not enough resources.
                                        </div>
                                    </div>
                                </div>
                                </div>';
                            }
                            
                        } else {
                            echo '<div class="row p-0 m-0">
                            <div class="col-sm p-0 m-0">
                                <div class="bg-danger rounded p-2 
                                m-0 d-flex justify-content-center">
                                    <div class="text-white">
                                        <i class="fas fa-exclamation-circle
                                         fa-2x"> </i>
                                         You have not enough '.$TROOPTYPE.' level.
                                    </div>
                                </div>
                            </div>
                            </div>';
                        }
                       
                    } else {
                        echo '<div class="row p-0 m-0">
                            <div class="col-sm p-0 m-0">
                                <div class="bg-danger rounded p-2 
                                m-0 d-flex justify-content-center">
                                    <div class="text-white">
                                        <i class="fas fa-exclamation-circle
                                         fa-2x"> </i>
                                        Something wrong.
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                }
            }
        }
    }

    public function usersTroopproduce()
    {
        $now=time();
        $checkproduce=$this->conn->prepare(
            'SELECT ID,USERID,COLONYID,TROOPID,VALUE,TIME,ONEFTIME,FTIME
            FROM usertroopsproduce WHERE ONEFTIME<=?'
        );
        $checkproduce->bind_param('i', $now);
        $checkproduce->execute();
        $checkproducedat=$checkproduce->get_result();
        $checkproduce->close();
        if ($checkproducedat->num_rows>0) {
            while ($row=$checkproducedat->fetch_assoc()) {
                if ($row['VALUE']>1) {
                    $newoneftime=$row['ONEFTIME']+$row['TIME'];
                    if ($newoneftime>$row['FTIME']) {
                        $newoneftime=$row['FTIME'];
                    }
                    $this->conn->begin_transaction();
                    $updatetroops=$this->conn->prepare(
                        'UPDATE usertroops SET VALUE=VALUE+1 WHERE ID=?'
                    );
                    $updatetroops->bind_param('i', $row['TROOPID']);
                    $updatetroops->execute();
                    $updatetroops->close();
                    $updateproduce=$this->conn->prepare(
                        'UPDATE usertroopsproduce SET VALUE=VALUE-1,ONEFTIME=?
                        WHERE ID=?'
                    );
                    $updateproduce->bind_param(
                        'ii', $newoneftime, $row['ID']
                    );
                    $updateproduce->execute();
                    $updateproduce->close();
                    if ($updatetroops && $updateproduce) {
                        $this->conn->commit();
                    } else {
                        $this->conn->rollback();
                    }

                } else {
                    $this->conn->begin_transaction();
                    $updatetroops=$this->conn->prepare(
                        'UPDATE usertroops SET VALUE=VALUE+1 WHERE ID=?'
                    );
                    $updatetroops->bind_param('i', $row['TROOPID']);
                    $updatetroops->execute();
                    $updatetroops->close();
                    $updateproduce=$this->conn->prepare(
                        'DELETE FROM usertroopsproduce WHERE ID=?'
                    );
                    $updateproduce->bind_param(
                        'i', $row['ID']
                    );
                    $updateproduce->execute();
                    $updateproduce->close();
                    if ($updatetroops && $updateproduce) {
                        $this->conn->commit();
                    } else {
                        $this->conn->rollback();
                    }

                }
            }
        }
    }

    public function usercheckPro($UID, $CID, $TROOPTYPE)
    {
        $check=$this->conn->prepare(
            'SELECT USERID,COLONYID,TROOPTYPE FROM usertroopsproduce
             WHERE USERID=? AND COLONYID=? AND TROOPTYPE=?'
        );
        $check->bind_param('iis', $UID, $CID, $TROOPTYPE);
        $check->execute();
        $checkdata=$check->get_result();
        $check->close();
        $count=$checkdata->num_rows;
        if ($count>0) {
            return $count;
        } else {
            return false;
        }

    }
}

?>