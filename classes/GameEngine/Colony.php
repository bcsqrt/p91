<?php

namespace GameEngine;

use Database\Dbconnect;

require_once '../inc/autoloader.php';

class Colony extends Dbconnect
{
    public function getColony($UID, $CID)
    {
        $GetColony=$this->conn->prepare(
            'SELECT ID,USERID,CLAT,CLONG FROM usercolony WHERE USERID=?'
        );
        $GetColony->bind_param('i', $UID);
        $GetColony->execute();
        $GetColonyData=$GetColony->get_result();
        $GetColony->close();
        $return='<div class="row m-auto d-flex align-items-start">';
        if ($GetColonyData->num_rows>0) {
            while ($row=$GetColonyData->fetch_assoc()) {
                if ($row['ID']==$CID) {
                    $bgcolor='warning';
                    $txtcolor='dark';
                } else {
                    $bgcolor='primary';
                    $txtcolor='white';
                }
                $return .='<div class="col-sm-2 m-2 p-2 border border-primary 
                text-'.$txtcolor.' bg-'.$bgcolor.' text-center">
                <span class="font-weight-bold">Area-'.$row['ID'].'</span> <br>
                <a href="?p=colony&co='.$row['ID'].'">
                <img src="img/units/townhallmap.png"> <br></a>
                <u>Position:</u> '.$row['CLAT'].' '.$row['CLONG'].' <br>
                <hr>
                <a class="text-'.$txtcolor.'" href="?p=colony&co='.$row['ID'].'">
                <i class="fas fa-exchange-alt fa-2x"></i></a>
                <i class="fas fa-times-circle fa-2x abandon" 
                colony="'.$row['ID'].'"></i>
                </div>';
            }
        }
        $return .='</div>';
        echo $return;
    }

    public function getColonydetails($MID)
    {
        $ColonyDetail=$this->conn->prepare(
            'SELECT ID,MLAT,MLONG,USER,COLONYID,UNAME,NPC,USERID,DESCP
            FROM map WHERE ID=?'
        );
        $ColonyDetail->bind_param('i', $MID);
        $ColonyDetail->execute();
        $ColonyDetailDat=$ColonyDetail->get_result();
        $ColonyDetail->close();
        if ($ColonyDetailDat->num_rows>0) {
            $row=$ColonyDetailDat->fetch_assoc();
            // Empty Slots
            if ($row['USER']==0 && $row['COLONYID']==0  
                && $row['NPC']==0 && $row['USERID']==0
            ) {
                echo '<div class="text-center">
                <span class="font-weight-bold">'.$row['MLAT'].' - 
                '.$row['MLONG'].'</span>
                <hr>
                <img src="img/units/empty.jpg"> <br>
                This area is abandoned 
                <hr>
                <input type="hidden" id="mapbtaction" lat="'.$row['MLAT'].'" 
                long="'.$row['MLONG'].'">
                </div>';
                    
            }
            // User's Slots
            if ($row['USER']!=0 && $row['COLONYID']!=0  
                && $row['NPC']==0 && $row['USERID']!=0
            ) {
                echo '<div class="text-center">
                <span class="font-weight-bold">'.$row['MLAT'].' - 
                '.$row['MLONG'].'</span>
                <hr>
                <img src="img/units/townhallmap.png"> <br>
                User Rate: 23
                <hr>
                <input type="hidden" id="mapbtaction" lat="'.$row['MLAT'].'" 
                long="'.$row['MLONG'].'">
                </div>
                ';
            }
            // NPC Slots
            if ($row['USER']==0 && $row['COLONYID']==0  
                && $row['NPC']==1 && $row['USERID']==0
            ) {
                echo '<div class="text-center">
                <span class="font-weight-bold">'.$row['MLAT'].' - 
                '.$row['MLONG'].'</span>
                <hr>
                <img src="img/units/npc_detail.png"> <br>
                <span class="font-italic">'.$row['DESCP'].'</span>
                <hr>
                <input type="hidden" id="mapbtaction" lat="'.$row['MLAT'].'" 
                long="'.$row['MLONG'].'">
                </div>
                ';
            }



        }
    }

    public function getNewColony($MID, $UID, $CID, $MCO, $UNAME) 
    {
        $CheckMap=$this->conn->prepare(
            'SELECT ID,MLAT,MLONG,USER,COLONYID,UNAME,NPC,USERID
            FROM map WHERE ID=?'
        );
        $CheckMap->bind_param('i', $MID);
        $CheckMap->execute();
        $CheckMapData=$CheckMap->get_result();
        $CheckMap->close();
        if ($CheckMapData->num_rows>0) {
            $row=$CheckMapData->fetch_assoc();
            if ($row['USER']==0 && $row['COLONYID']==0 
                && $row['USERID']==0 && $row['NPC']==0
            ) {
                $ColonyCount=$this->conn->prepare(
                    'SELECT COUNT(ID),USERID FROM usercolony WHERE USERID=?'
                );
                $ColonyCount->bind_param('i', $UID);
                $ColonyCount->execute();
                $ColonyCountData=$ColonyCount->get_result();
                $ColonyCount->close();
                if ($ColonyCountData->num_rows>0) {
                    $row2=$ColonyCountData->fetch_assoc();
                    if ($MCO>$row2['COUNT(ID)']) {
                        $ShowMaxLat=UMLAT+UMRANGE;
                        $ShowMaxLong=UMLONG+UMRANGE;

                        $ShowMinLat=UMLAT-UMRANGE;
                        $ShowMinLong=UMLONG-UMRANGE;
                        if ($row['MLAT']<=$ShowMaxLat && $row['MLONG']<=$ShowMaxLong 
                            && $row['MLAT']>=$ShowMinLat 
                            && $row['MLONG']>=$ShowMinLong
                        ) {
                            $this->conn->begin_transaction();
                            $AddColony=$this->conn->prepare(
                                'INSERT INTO usercolony 
                                (USERID,CLAT,CLONG) 
                                VALUES (?,?,?)'
                            );
                            $AddColony->bind_param(
                                'iii', $UID, $row['MLAT'], $row['MLONG']
                            );
                            $AddColony->execute();
                            $NewColonyid=$AddColony->insert_id;
                            $AddColony->close();

                            $getTroops=$this->conn->prepare(
                                'INSERT INTO usertroops 
                                (USERID,COLONYID,TROOPID,TNAME,TIMG,TDESCP,
                                DAMAGE,ARMOR,SPEED,CARRY,AIR,WOOD,STONE,GOLD,
                                FOOD,TIME,BLEVEL,TROOPTYPE,ORDERT,SPY,ATTACK,
                                TRADE,COLONIZATION,DEPLOYMENT,RESEARCH) 
                                SELECT ?,?,ID,TNAME,TIMG,TDESCP,DAMAGE,ARMOR,
                                SPEED,CARRY,AIR,WOOD,STONE,GOLD,FOOD,TIME,
                                BLEVEL,TROOPTYPE,ORDERT,SPY,ATTACK,TRADE,
                                COLONIZATION,DEPLOYMENT,RESEARCH FROM troops'
                            );
                            $getTroops->bind_param('ii', $UID, $NewColonyid);
                            $getTroops->execute();
                            $getTroops->close();

                            $InsertStat=$this->conn->prepare(
                                'INSERT INTO userstats (USERID,COLONYID,MCOLONY)
                                VALUES (?,?,?)'
                            );
                            $InsertStat->bind_param('iii', $UID, $NewColonyid, $MCO);
                            $InsertStat->execute();
                            $InsertStat->close();
                            $UpdateMap=$this->conn->prepare(
                                'UPDATE map SET USER=1,COLONYID=?,UNAME=?,NPC=0,USERID=?
                                WHERE ID=?'
                            );
                            $UpdateMap->bind_param(
                                'isii', $NewColonyid, $UNAME, $UID, $MID
                            );
                            $UpdateMap->execute();
                            $UpdateMap->close();
                            $NewUserResources=$this->conn->prepare(
                                'INSERT INTO resources 
                                (USERID,COLONYID,RNAME,RVALUE,RLUPDATE)
                                VALUES (?,?,?,?,?)'
                            );
                            $LastUpdate=time();
                            $NewUserResources->bind_param(
                                'iisdi', $UID, $NewColonyid, 
                                $RNAME, $RVALUE, $LastUpdate
                            );
                            $RNAME='Food';
                            $RVALUE=0;
                            $NewUserResources->execute();
                            $RNAME='Wood';
                            $RVALUE=200;
                            $NewUserResources->execute();
                            $RNAME='Stone';
                            $RVALUE=100;
                            $NewUserResources->execute();
                            $RNAME='Gold';
                            $RVALUE=50;
                            $NewUserResources->execute();                                                         
                            $NewUserResources->close();
                            $GetUnits=$this->conn->prepare(
                                'INSERT INTO userunits 
                                (USERID,COLONYID,UNITID,UNITNAME,UNITDESCP,UNITIMG,
                                UNITTYPE,RWOOD,RSTONE,RGOLD,RFOOD,RTIME,
                                EVENTNAME,EVENTVAL,ORDERU,STTYPE,RLVL) 
                                SELECT ?,?,ID,UNITNAME,UNITDESCP,UNITIMG,
                                UNITTYPE,RWOOD,RSTONE,RGOLD,
                                RFOOD,RTIME,EVENTNAME,EVENTVAL,ORDERU,STTYPE,RLVL
                                FROM units'
                            );
                            $GetUnits->bind_param('ii', $UID, $NewColonyid);
                            $GetUnits->execute();
                            $GetUnits->close();
                            if ($AddColony && $UpdateMap && $NewUserResources
                                && $GetUnits && $InsertStat && $getTroops
                            ) {
                                $this->conn->commit();
                            } else {
                                $this->conn->rollback();
                            }
                            header('location: index.php?p=colony&co='.$CID.'');
                            $return=''; 
                        } else {
                            $return='Out of range';
                        }
                    } else {
                        $return='You have already reached the maximum colony number';
                    }
                } else {
                    $return='You have already reached the maximum colony number';
                } 
            } else {
                $return='You cant do that!';
            }

        } else {
            $return='Something got wrong!';
        }

        echo '<div class="border border-danger rounded w-50 p-2 m-2 
        bg-danger text-white">'.$return.'</div>
        ';

       

    }

    public function abandonColony($CID, $UID)
    {
        $CheckColony=$this->conn->prepare(
            'SELECT ID,USERID,CLAT,CLONG 
            FROM usercolony WHERE USERID=?'
        );
        $CheckColony->bind_param('i', $UID);
        $CheckColony->execute();
        $CheckColonyDat=$CheckColony->get_result();
        $CheckColony->close();
        if ($CheckColonyDat->num_rows>1) {
            while ($row=$CheckColonyDat->fetch_assoc()) {
                if ($row['ID']==$CID) {
                    $this->conn->begin_transaction();
                    $DelColony=$this->conn->prepare(
                        'DELETE FROM usercolony WHERE ID=?'
                    );
                    $DelColony->bind_param('i', $CID);
                    $DelColony->execute();
                    $Upmap=$this->conn->prepare(
                        'UPDATE map SET USER=0,COLONYID=0,UNAME="",
                        NPC=0,USERID=0
                        WHERE MLAT=? AND MLONG=?'
                    );
                    $Upmap->bind_param('ii', $row['CLAT'], $row['CLONG']);
                    $Upmap->execute();
                    if ($DelColony && $Upmap) {
                        $this->conn->commit();
                    } else {
                        $this->conn->rollback();
                    }
                }  
            }
        }
        header('location: index.php?p=colony'); 

    }
}

?>