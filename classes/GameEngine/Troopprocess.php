<?php
namespace GameEngine;

use Database\Dbconnect;
use Otherfunction\Tools;

class Troopprocess extends Dbconnect
{
    private $_cid,$_lat,$_long,$_speedrate,$_tid,$_value;
    private $_troopspeed,$_troopcarry;
    private $_wood,$_stone,$_gold,$_food,$_totalresource;
    private $_do;
    private $_userlat,$_userlong;
    private $_targetname,$_targetuserid,$_targetcid;

    
    public function checkPostelement()
    {
        $tools=new Tools;
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            if (!empty($_POST['cid']) && !empty($_POST['lat']) 
                && !empty($_POST['long']) && !empty($_POST['speedrate']) 
                && !empty($_POST['tid']) && !empty($_POST['value']) 
                && !empty($_POST['do']) 
            ) {
                $this->_wood=$tools->emptyValuecheck($_POST['Wood']);
                $this->_stone=$tools->emptyValuecheck($_POST['Stone']);
                $this->_gold=$tools->emptyValuecheck($_POST['Gold']);
                $this->_food=$tools->emptyValuecheck($_POST['Food']); 
                $this->_cid=$_POST['cid'];
                $this->_lat=$_POST['lat'];
                $this->_long=$_POST['long'];
                $this->_speedrate=$tools->trimSpeed($_POST['speedrate']);
                $this->_tid=$_POST['tid'];
                $this->_value=$_POST['value'];
                $this->_do=$_POST['do'];
                if ($tools->checkCoordinate($this->_lat) 
                    && $tools->checkCoordinate($this->_long)
                ) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function userData($UID, $UNAME)
    {
        // UserColony Exist
        $tools= new Tools;
        $checkColony=$this->conn->prepare(
            'SELECT ID,USERID,CLAT,CLONG FROM usercolony WHERE USERID=? AND ID=?'
        );
        $checkColony->bind_param(
            'ii', $UID, $this->_cid
        );
        $checkColony->execute();
        $checkColonydat=$checkColony->get_result();
        if ($checkColonydat->num_rows>0) {
            $row=$checkColonydat->fetch_assoc();
            $this->_userlat=$row['CLAT'];
            $this->_userlong=$row['CLONG'];
            // resource check
            $checkresource=$this->conn->prepare(
                'SELECT USERID,COLONYID,RNAME,RVALUE FROM resources 
                WHERE USERID=? AND COLONYID=?'
            );
            $checkresource->bind_param('ii', $UID, $this->_cid);
            $checkresource->execute();
            $checkresourcedat=$checkresource->get_result();
            $checkresource->close();
            if ($checkresourcedat->num_rows>0) {
                while ($row=$checkresourcedat->fetch_assoc()) {
                    if ($row['RNAME']=='Food') {
                        $this->_food=$tools->resourcecheck(
                            $this->_food, $row['RVALUE']
                        );
                    }
                    if ($row['RNAME']=='Wood') {
                        $this->_wood=$tools->resourcecheck(
                            $this->_wood, $row['RVALUE']
                        );
                    }
                    if ($row['RNAME']=='Stone') {
                        $this->_stone=$tools->resourcecheck(
                            $this->_stone, $row['RVALUE']
                        );
                    }
                    if ($row['RNAME']=='Gold') {
                        $this->_gold=$tools->resourcecheck(
                            $this->_gold, $row['RVALUE']
                        );
                    }
                }

                // target check
                $targetcheck=$this->conn->prepare(
                    'SELECT MLAT,MLONG,USER,COLONYID,UNAME,NPC,USERID
                    FROM map WHERE MLAT=? AND MLONG=?'
                );
                $targetcheck->bind_param('ii', $this->_lat, $this->_long);
                $targetcheck->execute();
                $targetcheckdat=$targetcheck->get_result();
                $targetcheck->close();
                if ($targetcheckdat->num_rows>0) {
                    $row=$targetcheckdat->fetch_assoc();
                    if ($row['USER']==1) {
                        $this->_targetname=$row['UNAME'];
                        $this->_targetuserid=$row['USERID'];
                        $this->_targetcid=$row['COLONYID'];
                    } elseif ($row['NPC']==1) {
                        $this->_targetname='NPC';
                        $this->_targetuserid=0;
                        $this->_targetcid=0;
                    } elseif ($row['USER']==0 && $row['NPC']==0 
                        && $row['UNAME']=='' && $row['COLONYID']==0 
                        && $row['USERID']==0
                    ) {
                        $this->_targetname='Empty';
                        $this->_targetuserid=0;
                        $this->_targetcid=0;
                    }
                    if ($this->_speedrate!=0) {
                        $this->_missionCheck($UID, $UNAME);
                    }
                } else {
                    echo 'There is no target';
                }
            } else {
                echo 'Something Wrong 2';
            }
        } else {
            echo 'Something Wrong 1';
        }
        
    }

    private function _troopsCheck($UID, $UNAME)
    {
        $this->_troopspeed=0;
        $this->_troopcarry=0;
        $this->_totalresource=$this->_wood+$this->_stone+$this->_gold+$this->_food;
        if (count($this->_tid)==count($this->_value)) {
            $count=count($this->_tid);
            for ($i=0; $i<$count; $i++) {
                if (is_numeric($this->_tid[$i]) && is_numeric($this->_value[$i])) {
                    if ($this->_tid[$i]>0 && $this->_value[$i]>0) {
                        $checktroops=$this->conn->prepare(
                            'SELECT USERID,COLONYID,TROOPID,VALUE,SPEED,CARRY
                            FROM usertroops 
                            WHERE USERID=? AND COLONYID=? AND TROOPID=?'
                        );
                        $checktroops->bind_param(
                            'iii', $UID, $this->_cid, $this->_tid[$i]
                        );
                        $checktroops->execute();
                        $checktroopsdat=$checktroops->get_result();
                        $checktroops->close();
                        if ($checktroopsdat->num_rows>0) {
                            $row=$checktroopsdat->fetch_assoc();
                            if ($this->_value[$i]>$row['VALUE']) {
                                $this->_value[$i]=$row['VALUE'];
                            }
                            if ($this->_troopspeed==0) {
                                $this->_troopspeed=$row['SPEED'];
                            } elseif ($this->_troopspeed>$row['SPEED']) {
                                $this->_troopspeed=$row['SPEED'];
                            }
                            $this->_troopcarry+=$row['CARRY']*$this->_value[$i];
        
                        } else {
                            unset($this->_tid[$i]);
                            unset($this->_value[$i]);
                        } 

                    } else {
                        unset($this->_tid[$i]);
                        unset($this->_value[$i]);
                    }
                } else {
                    unset($this->_tid[$i]);
                    unset($this->_value[$i]);
                }
                
            }
            $this->_tid=array_values($this->_tid);
            $this->_value=array_values($this->_value);
            $this->_troopspeed=round(($this->_troopspeed*$this->_speedrate)/100, 2);
            if ($this->_totalresource>$this->_troopcarry) {
                echo 'You exceeded the carry limit';
            } else {
                if (count($this->_tid)>0) {
                    if ($this->_troopspeed>0) {
                        $this->_createMission($UID, $UNAME);
                    } else {
                        echo 'Troop\'s speed must be more than 0';
                    }
                } else {
                    echo 'Something Wrong 4';
                }
            }

        } else {
            echo 'Something Wrong 3';
        } 

    }

    private function _missionCheck($UID, $UNAME)
    {
        $this->_tid=explode(',', $this->_tid);
        $this->_value=explode(',', $this->_value);
        // SPY
        if ($this->_do=='Spy') {
            if ($this->_targetuserid!=0 && $this->_targetuserid!=$UID 
                && $this->_speedrate>0
            ) {
                if (count($this->_tid)==1 && count($this->_value)==1) {
                    if ($this->_totalresource==0) {
                        $missioncheck=$this->conn->prepare(
                            'SELECT USERID,COLONYID,TROOPID,VALUE,SPY 
                            FROM usertroops 
                            WHERE USERID=? AND COLONYID=? AND TROOPID=? 
                            AND VALUE>0 AND SPY=1'
                        );
                        $missioncheck->bind_param(
                            'iii', $UID, $this->_cid, $this->_tid[0]
                        );
                        $missioncheck->execute();
                        $missioncheckdat=$missioncheck->get_result();
                        $missioncheck->close();
                        if ($missioncheckdat->num_rows>0) {
                            /// DO SPYING
                            $this->_troopsCheck($UID, $UNAME);
                        } else {
                            echo 'I am not spy.';
                        }
                    } else {
                        echo 'I am spy, i cant carry any resource';
                    }
                } else {
                    echo 'Only spys can spying';
                }
            } else {
                echo 'I can only spying other village.';
            }
            //ATTACK
        } elseif ($this->_do=='Attack') {
            if ($this->_targetuserid!=0 && $this->_targetuserid!=$UID 
                && $this->_speedrate>0
            ) {
                if (count($this->_tid)==count($this->_value)) {
                    $count=count($this->_tid)+1;
                    for ($i=0; $i<$count; $i++) {
                        $checkattacktrops=$this->conn->prepare(
                            'SELECT USERID,COLONYID,TROOPID,VALUE,ATTACK
                            FROM usertroops 
                            WHERE USERID=? AND COLONYID=? AND TROOPID=?
                            AND VALUE>0 AND ATTACK=1'
                        );
                        $checkattacktrops->bind_param(
                            'iii', $UID, $this->_cid, $this->_tid[$i]
                        );
                        $checkattacktrops->execute();
                        $checkattacktropsdat=$checkattacktrops->get_result();
                        $checkattacktrops->close();
                        if ($checkattacktropsdat->num_rows==0) {
                            unset($this->_tid[$i]);
                            unset($this->_value[$i]);
                        }
                    }
                    $this->_tid=array_values($this->_tid);
                    $this->_value=array_values($this->_value);

                    if (count($this->_tid)>0) {
                        // DO ATTACK
                        $this->_troopsCheck($UID, $UNAME);
                    } else {
                        echo 'Some thing wrong! x5';
                    }

                } else {
                    echo 'Some thing wrong! x4';
                }
            } else {
                echo 'I can only attack other village.';
            }
            //TRADE
        } elseif ($this->_do=='Trade') {
            if ($this->_targetuserid!=0 && $this->_targetcid!=$this->_cid 
                && $this->_speedrate!=0
            ) { 
                if (count($this->_tid)==count($this->_value)) {
                    $count=count($this->_tid)+1;
                    for ($i=0; $i<$count; $i++) {
                        $checkattacktrops=$this->conn->prepare(
                            'SELECT USERID,COLONYID,TROOPID,VALUE,ATTACK
                            FROM usertroops 
                            WHERE USERID=? AND COLONYID=? AND TROOPID=?
                            AND VALUE>0 AND TRADE=1'
                        );
                        $checkattacktrops->bind_param(
                            'iii', $UID, $this->_cid, $this->_tid[$i]
                        );
                        $checkattacktrops->execute();
                        $checkattacktropsdat=$checkattacktrops->get_result();
                        $checkattacktrops->close();
                        if ($checkattacktropsdat->num_rows==0) {
                            unset($this->_tid[$i]);
                            unset($this->_value[$i]);
                        }
                    }
                    $this->_tid=array_values($this->_tid);
                    $this->_value=array_values($this->_value);

                    if (count($this->_tid)>0) {
                        // DO TRADE
                        $this->_troopsCheck($UID, $UNAME);
                    } else {
                            echo 'Some thing wrong! x7';
                    }

                } else {
                    echo 'Some thing wrong! x6';
                }

            } else {
                echo 'Something not right! xx6';
            }
            
            //COLONI
        } elseif ($this->_do=='Colonization') {
            if ($this->_targetuserid==0 && $this->_targetcid==0 
                && $this->_speedrate!=0 && $this->_targetname=='Empty'
            ) { 
                if (count($this->_tid)==count($this->_value)) {
                    $count=count($this->_tid)+1;
                    if ($this->_checkUsermaxcolony($UID)) {
                        for ($i=0; $i<$count; $i++) {
                            $checkattacktrops=$this->conn->prepare(
                                'SELECT USERID,COLONYID,TROOPID,VALUE,ATTACK
                                FROM usertroops 
                                WHERE USERID=? AND COLONYID=? AND TROOPID=?
                                AND VALUE>0 AND COLONIZATION=1'
                            );
                            $checkattacktrops->bind_param(
                                'iii', $UID, $this->_cid, $this->_tid[$i]
                            );
                            $checkattacktrops->execute();
                            $checkattacktropsdat=$checkattacktrops->get_result();
                            $checkattacktrops->close();
                            if ($checkattacktropsdat->num_rows==0) {
                                unset($this->_tid[$i]);
                                unset($this->_value[$i]);
                            }
                        }
                        $this->_tid=array_values($this->_tid);
                        $this->_value=array_values($this->_value);
    
                        if (count($this->_tid)>0) {
                            // DO COLONI
                            $this->_troopsCheck($UID, $UNAME);
                        } else {
                                echo 'Some thing wrong! x9';
                        }
                    } else {
                        echo 'You have already reached the maximum colony number';
                    }
                    
                } else {
                    echo 'Some thing wrong! x8';
                }

            } else {
                echo 'I can only colonize empty area.';
            }
        

            //DEPLOY
        } elseif ($this->_do=='Deployment') {
            if ($this->_targetuserid!=0 && $this->_targetcid!=$this->_cid 
                && $this->_speedrate!=0
            ) { 
                if (count($this->_tid)==count($this->_value)) {
                    $count=count($this->_tid)+1;
                    for ($i=0; $i<$count; $i++) {
                        $checkattacktrops=$this->conn->prepare(
                            'SELECT USERID,COLONYID,TROOPID,VALUE,ATTACK
                            FROM usertroops 
                            WHERE USERID=? AND COLONYID=? AND TROOPID=?
                            AND VALUE>0 AND DEPLOYMENT=1'
                        );
                        $checkattacktrops->bind_param(
                            'iii', $UID, $this->_cid, $this->_tid[$i]
                        );
                        $checkattacktrops->execute();
                        $checkattacktropsdat=$checkattacktrops->get_result();
                        $checkattacktrops->close();
                        if ($checkattacktropsdat->num_rows==0) {
                            unset($this->_tid[$i]);
                            unset($this->_value[$i]);
                        }
                    }
                    $this->_tid=array_values($this->_tid);
                    $this->_value=array_values($this->_value);

                    if (count($this->_tid)>0) {
                        // DO DEPLOYMENT
                        $this->_troopsCheck($UID, $UNAME);
                    } else {
                            echo 'Some thing wrong! x10';
                    }

                } else {
                    echo 'Some thing wrong! x9';
                }

            } else {
                echo 'There is no target.';
            }
                        
            //RESEARCH
        } elseif ($this->_do=='Research') {
            if ($this->_targetuserid==0 && $this->_targetcid==0
                && $this->_speedrate!=0 && $this->_targetname=='NPC'
            ) { 
                if (count($this->_tid)==count($this->_value)) {
                    $count=count($this->_tid)+1;
                    for ($i=0; $i<$count; $i++) {
                        $checkattacktrops=$this->conn->prepare(
                            'SELECT USERID,COLONYID,TROOPID,VALUE,ATTACK
                            FROM usertroops 
                            WHERE USERID=? AND COLONYID=? AND TROOPID=?
                            AND VALUE>0 AND RESEARCH=1'
                        );
                        $checkattacktrops->bind_param(
                            'iii', $UID, $this->_cid, $this->_tid[$i]
                        );
                        $checkattacktrops->execute();
                        $checkattacktropsdat=$checkattacktrops->get_result();
                        $checkattacktrops->close();
                        if ($checkattacktropsdat->num_rows==0) {
                            unset($this->_tid[$i]);
                            unset($this->_value[$i]);
                        }
                    }
                    $this->_tid=array_values($this->_tid);
                    $this->_value=array_values($this->_value);

                    if (count($this->_tid)>0) {
                        // DO Research
                        $this->_troopsCheck($UID, $UNAME);
                    } else {
                            echo 'Some thing wrong! x11';
                    }

                } else {
                    echo 'Some thing wrong! x10';
                }

            } else {
                echo 'There is no dungeon.';
            }               
            //ERROR
        } else {
            echo 'What is our mission? ';
        }
    }

    private function _createMission($UID, $UNAME)
    {
        $tools= new Tools;
        if ($this->_wood>0) {
            $wood=$this->_wood;
        } else {
            $wood=0;
        }

        if ($this->_stone>0) {
            $stone=$this->_stone;
        } else {
            $stone=0;
        }

        if ($this->_gold>0) {
            $gold=$this->_gold;
        } else {
            $gold=0;
        }

        if ($this->_food>0) {
            $food=$this->_food;
        } else {
            $food=0;
        }

        $course=$tools->calculateCourse(
            $this->_userlat, $this->_userlong, $this->_lat, $this->_long
        );

        $this->conn->begin_transaction();

        $selectlastmissionid=$this->conn->prepare(
            'SELECT MAX(ARMY) AS LASTID FROM usermissionarmy'
        );
        $selectlastmissionid->execute();
        $selectlastmissioniddat=$selectlastmissionid->get_result();
        $selectlastmissionid->close();
        if ($selectlastmissioniddat->num_rows>0) {
            $row=$selectlastmissioniddat->fetch_assoc();
            $lastid=$row['LASTID']+1;
        } else {
            $lastid=$UID+1;
        }

        $count=count($this->_tid);

        $addmissionarmy=$this->conn->prepare(
            'INSERT INTO usermissionarmy 
            (ARMY,USERID,COLONYID,TROOPID,VALUE,WOOD,STONE,GOLD,FOOD) 
            VALUES (?,?,?,?,?,?,?,?,?)'
        );

        $addmissionarmy->bind_param(
            'iiiiidddd', $lastid, $UID, $this->_cid, $troopid, $troopvalue,
            $wood2, $stone2, $gold2, $food2
        );
        for ($i=0; $i<$count; $i++) {
            $troopid=$this->_tid[$i];
            $troopvalue=$this->_value[$i];

            if ($i==0) {
                $wood2=$wood;
                $stone2=$stone;
                $gold2=$gold;
                $food2=$food;
            } else {
                $wood2=0;
                $stone2=0;
                $gold2=0;
                $food2=0;
            }
            $addmissionarmy->execute();
        }
        $addmissionarmy->close();

        $deletetroops=$this->conn->prepare(
            'UPDATE usertroops SET VALUE=VALUE-? 
            WHERE USERID=? AND COLONYID=? AND TROOPID=?'
        );
        $deletetroops->bind_param('iiii', $value, $UID, $this->_cid, $TID);
        for ($i=0; $i<$count; $i++) {
            $TID=$this->_tid[$i];
            $value=$this->_value[$i];
            $deletetroops->execute();
        }
        $deletetroops->close();

        $updateresource=$this->conn->prepare(
            'UPDATE resources SET RVALUE=RVALUE-? 
            WHERE USERID=? AND COLONYID=? AND RNAME=?'
        );
        $updateresource->bind_param('diis', $rvalue, $UID, $this->_cid, $rname);
        $rname='Food';
        $rvalue=$food;
        $updateresource->execute();
        $rname='Wood    ';
        $rvalue=$wood;
        $updateresource->execute();
        $rname='Stone';
        $rvalue=$stone;
        $updateresource->execute();
        $rname='Gold';
        $rvalue=$gold;
        $updateresource->execute();
        $updateresource->close();

        $distance=$tools->calculateDistance(
            $this->_userlat, $this->_userlong, $this->_lat, $this->_long
        );
        $etd=time();
        $eta=$tools->calculateEta($this->_troopspeed, $distance);
        $addmission=$this->conn->prepare(
            'INSERT INTO usermissions 
            (1NAME,1USERID,1COLONYID,1LAT,1LONG,
            2NAME,2USERID,2COLONYID,2LAT,2LONG,
            ETD,ETA,DISTANCE,COURSE,SPEED,ARMYID,DO) VALUES
            (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $addmission->bind_param(
            'siiiisiiiiiidddis', $UNAME, $UID, $this->_cid, $this->_userlat, 
            $this->_userlong, $this->_targetname, $this->_targetuserid,
            $this->_targetcid, $this->_lat, $this->_long, $etd, $eta, $distance,
            $course, $this->_troopspeed, $lastid, $this->_do
        );
        $addmission->execute();
        $addmission->close();

        if ($selectlastmissionid && $addmissionarmy 
            && $deletetroops && $updateresource && $addmission
        ) {
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        }

        echo 'Troops sent';

    }

    private function _checkUsermaxcolony($UID)
    {
        $ColonyCount=$this->conn->prepare(
            'SELECT COUNT(usercolony.ID) AS TOTALCOLONY,usercolony.USERID,
            userstats.USERID,userstats.MCOLONY
            FROM usercolony INNER JOIN userstats 
            ON usercolony.USERID=userstats.USERID
            WHERE usercolony.USERID=? AND userstats.COLONYID=?'
        );
        $ColonyCount->bind_param('ii', $UID, $this->_cid);
        $ColonyCount->execute();
        $ColonyCountData=$ColonyCount->get_result();
        $ColonyCount->close();
        if ($ColonyCountData->num_rows>0) {
            $row2=$ColonyCountData->fetch_assoc();
            if ($row2['MCOLONY']>$row2['TOTALCOLONY']) { 
                return true;
            } else {
                return false;
            } 
        } else {
            return false;
        }       
    }
    
}

?>
