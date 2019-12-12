<?php
namespace GameEngine;

use Database\Dbconnect;
use UserProcess\Userdb;

require_once '../inc/autoloader.php';

class Units extends Dbconnect
{
    public function getUnits($UID, $CID, $unit, $page, $rlvl)
    {
        $Units=$this->conn->prepare(
            'SELECT ID,USERID,COLONYID,UNITNAME,UNITDESCP,UNITIMG,UNITTYPE,
            UNITLEVEL,RWOOD,RSTONE,RGOLD,RFOOD,RTIME,UPGRADE,UPGRADEFTIME,EVENTNAME,
            EVENTVAL,ORDERU,STTYPE FROM userunits
            WHERE USERID=? AND COLONYID=? AND STTYPE=? AND RLVL<=? ORDER BY ORDERU'
        );
        $Units->bind_param('iisi', $UID, $CID, $unit, $rlvl);
        $Units->execute();
        $UnitData=$Units->get_result();
        $Units->close();
        $Return='';  
        $UnitUpCheck=$this->conn->prepare(
            'SELECT USERID,COLONYID,UPGRADE,STTYPE 
            FROM userunits WHERE USERID=? AND COLONYID=? AND
            UPGRADE=1 AND STTYPE=?'
        );
        $UnitUpCheck->bind_param('iis', $UID, $CID, $unit);
        $UnitUpCheck->execute();
        $UnitUpData=$UnitUpCheck->get_result();
        $UnitUpCheck->close();
        $UnitUpNum=$UnitUpData->num_rows;
        if ($UnitData->num_rows>0) {
            $Return.='<div class="row m-auto d-flex justify-content-center">';  
            while ($row=$UnitData->fetch_assoc()) {   
                if ($row['UPGRADE']==1) {
                    $TextColor='danger';
                    $Bgcolor='dark';
                } elseif ($UnitUpNum>0) {
                    $TextColor='dark';
                    $Bgcolor='secondary';                    
                } else {
                    $TextColor='warning';
                    $Bgcolor='primary';
                }
                $ColorFood='black';
                if (UFOOD<$row['RFOOD']) {
                    $ColorFood='danger';
                }
                $ColorWood='black';
                if (WOODVAL<$row['RWOOD']) {
                    $ColorWood='danger';
                }
                $ColorStone='black';
                if (STONEVAL<$row['RSTONE']) {
                    $ColorStone='danger';
                }
                $ColorGold='black';
                if (GOLDVAL<$row['RGOLD']) {
                    $ColorGold='danger';
                }
                $TimeCounter='';
                $Upelement='<i class="fas fa-angle-double-up fa-2x"></i>';
                if ($row['UPGRADE']==1) {
                    $TimeCounter=$row['UPGRADEFTIME']-time();
                    $Upelement='<i class="fas fa-window-close fa-2x"></i>';
                    $Proces=1;
                } else {
                    $Proces=0;
                }
                $Return .='
                <div class="col-sm-3 m-2 p-0  
                border border-'.$Bgcolor.' text-left rounded">
                    <table class="m-0 p-0 w-100 h-100 bg-light">
                        <tr>
                            <td colspan="2" class="bg-'.$Bgcolor.'">
                            <p class="m-0 text-'.$TextColor.'  font-weight-bold 
                            text-center">
                            '.$row['UNITNAME'].' - '.$row['UNITLEVEL'].'</p></td>
                        </tr>
                        <tr>
                            <td class="text-center h-100" 
                            style="vertical-align:top;">
                            <img src="'.$row['UNITIMG'].'"></td>
                            <td class="text-justify pr-1 h-100"
                            style="vertical-align:top;">
                            Build time: '.$row['RTIME'].' <br>
                            <u>Requirement</u><br> 
                            Food: <span class="text-'.$ColorFood.' font-weight-bold">
                            '.$row['RFOOD'].'</span> 
                            Wood: <span class="text-'.$ColorWood.' 
                            font-weight-bold"> 
                            '.$row['RWOOD'].'</span> <br> 
                            Stone: <span class="text-'.$ColorStone.' 
                            font-weight-bold"> 
                            '.$row['RSTONE'].'</span> 
                            Gold: <span class="text-'.$ColorGold.' 
                            font-weight-bold"> 
                            '.$row['RGOLD'].'</span> <br>
                            <u>Production</u> <br>
                            '.$row['EVENTNAME'].' +'.$row['EVENTVAL'].'<br> 
                            <span class="font-italic text-secondary">
                            '.$row['UNITDESCP'].'</span></td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-right pr-1 
                            text-'.$TextColor.' bg-'.$Bgcolor.'"> 
                            '.$TimeCounter.' 
                            <a href=
                            "?p='.$page.'&co='.$CID.'&u='.$row['ID'].'&i='.$Proces.'"
                            class="text-'.$TextColor.'">
                            '.$Upelement.'</a></td>
                        </tr>
                    </table>
                </div> ';              
            }
        } 
        $Return.='</div>';
        return $Return;

    }

    public function upUnit($wood, $stone, $gold, $food, $CID, $unit, $page, $rlvl)
    {
        if (isset($_GET['u'])) {
            if (is_numeric($_GET['u']) && is_numeric($_GET['i'])) {
                $UnitID=(int)$_GET['u'];
                $upgrd=$_GET['i'];
                $return='';
                $upcheck=new Userdb();
                if ($upcheck->userUpgcheck($_SESSION['UID'], $CID, $unit) 
                    && $upgrd==1
                ) {  
                    $CancelUp=$this->conn->prepare(
                        'SELECT ID,USERID,COLONYID,UNITLEVEL,RTIME,RWOOD,RSTONE,
                        RGOLD,RFOOD,UNITTYPE,STTYPE 
                        FROM userunits WHERE USERID=? AND COLONYID=? AND
                        UPGRADE=1 AND STTYPE=?'
                    );
                    $CancelUp->bind_param('iis', $_SESSION['UID'], $CID, $unit);
                    $CancelUp->execute();
                    $CancelDt=$CancelUp->get_result();
                    $CancelUp->close();
                    if ($CancelDt->num_rows>0) {
                        $row=$CancelDt->fetch_assoc();
                        $this->_upUnit2(
                            $row['ID'], $_SESSION['UID'], 
                            $row['UNITLEVEL'], $row['RTIME'], $wood, $stone, 
                            $gold, $food, $row['RWOOD'], $row['RSTONE'], 
                            $row['RGOLD'], $row['RFOOD'], $row['UNITTYPE'], 'cancel',
                            $CID, $page
                        );
                    }
                } elseif (!$upcheck->userUpgcheck($_SESSION['UID'], $CID, $unit) 
                    && $upgrd==0
                ) {
                    $UpgradeUnitCheck=$this->conn->prepare(
                        'SELECT ID,USERID,COLONYID,UNITTYPE,UNITLEVEL,
                        RWOOD,RSTONE,RGOLD,RFOOD,RTIME,UPGRADE,STTYPE 
                        FROM userunits WHERE ID=? AND USERID=? AND COLONYID=? 
                        AND RWOOD<=?  AND RSTONE<=? AND RGOLD<=? AND UPGRADE=?
                        AND STTYPE=? AND RLVL<=?'
                    );
                    $UpgradeUnitCheck->bind_param(
                        'iiidddisi', $UnitID, $_SESSION['UID'], $CID, $wood,
                        $stone, $gold, $upgrd, $unit, $rlvl
                    );
                    $UpgradeUnitCheck->execute();
                    $UpgradeUnitCheckData=$UpgradeUnitCheck->get_result();
                    $UpgradeUnitCheck->close();
                    if ($UpgradeUnitCheckData->num_rows>0) {
                        if (!isset($_GET['s'])) {
                            $return='<div id="Unitconfirm" 
                            title="Confirm Update">
                            <p><span class="ui-icon ui-icon-alert" 
                            style="float:left; margin:12px 12px 20px 0;">
                            </span>Are you sure?</p>
                            </div> ';  
                        } elseif ($_GET['s']==1) {
                            $row=$UpgradeUnitCheckData->fetch_assoc();
                            $this->_upUnit2(
                                $row['ID'], $_SESSION['UID'], 
                                $row['UNITLEVEL'], $row['RTIME'], $wood, $stone, 
                                $gold, $food, $row['RWOOD'], $row['RSTONE'], 
                                $row['RGOLD'], $row['RFOOD'], $row['UNITTYPE'],
                                'build', $CID, $page
                            );
                        }                  
                    } else {
                        $return='<div id="Uniterror" 
                        title="You Cant">
                        <p><span class="ui-icon ui-icon-alert" 
                        style="float:left; margin:12px 12px 20px 0;">
                        </span>Not enough resources</p>
                        </div> '; 
                    }
                } else {
                    $return='<div id="Uniterror" 
                        title="You Cant">
                        <p><span class="ui-icon ui-icon-alert" 
                        style="float:left; margin:12px 12px 20px 0;">
                        </span>You are already building unit</p>
                        </div> ';                     
                }
                echo $return;
            }
        }
    }

    private function _upUnit2($UnitID, $UserID, $Unitlevel, $Rtime, $Uwood,
        $Ustone, $Ugold, $UFood, $Rwood, $Rstone, $Rgold, $RFood, $Unittype, $do,
        $CID, $page
    ) {
        
        if ($do=='build') {
            $NewUnitLevel=$Unitlevel+1;
            $UpgradeFinishTime=$Rtime+time();
            $NewUWood=$Uwood-$Rwood;
            $NewUStone=$Ustone-$Rstone;
            $NewUGold=$Ugold-$Rgold;
            $NewUFood=$UFood-$RFood;
            $Upg=1;
        } elseif ($do=='cancel') {
            $NewUnitLevel=$Unitlevel-1;
            $UpgradeFinishTime=0;
            $NewUWood=$Uwood+$Rwood;
            $NewUStone=$Ustone+$Rstone;
            $NewUGold=$Ugold+$Rgold;
            $NewUFood=$UFood+$RFood;
            $Upg=0;
        }


        $this->conn->begin_transaction();
            $UserResourceUp=$this->conn->prepare(
                'UPDATE resources SET RVALUE=? 
                WHERE RNAME=? AND USERID=? AND COLONYID=?'
            );
            $Name='Wood';
            $UserResourceUp->bind_param(
                'dsii', $NewUWood, $Name, $UserID, $CID
            );
            $UserResourceUp->execute();
            $Name='Stone';
            $UserResourceUp->bind_param(
                'dsii', $NewUStone, $Name, $UserID, $CID
            );
            $UserResourceUp->execute();
            $Name='Gold';
            $UserResourceUp->bind_param(
                'dsii', $NewUGold, $Name, $UserID, $CID
            );
            $UserResourceUp->execute();
            $Name='Food';
            $UserResourceUp->bind_param(
                'dsii', $NewUFood, $Name, $UserID, $CID
            );
            $UserResourceUp->execute();
            $UserResourceUp->close();

            $UserUnitUp=$this->conn->prepare(
                'UPDATE userunits SET UPGRADE=?,UPGRADEFTIME=? 
                WHERE ID=? AND USERID=? AND COLONYID=?'
            );
            $UserUnitUp->bind_param(
                'iiiii', $Upg, $UpgradeFinishTime, $UnitID, $UserID, $CID
            );
            $UserUnitUp->execute();
            $UserUnitUp->close();
        if ($UserResourceUp && $UserUnitUp) {
            $this->conn->commit();    
        } else {
            $this->conn->rollback();
        }
        header('location:?p='.$page.'&co='.$CID.'');
        exit();
    }

    public function buildingUnits() 
    {
        $now=time();
        $GetUnits=$this->conn->prepare(
            'SELECT ID,USERID,COLONYID,UNITID,UNITTYPE, UNITLEVEL,RWOOD,RSTONE,
            RGOLD,RTIME,RFOOD,UPGRADE,UPGRADEFTIME,EVENTNAME,EVENTVAL 
            FROM userunits WHERE UPGRADE=1 AND UPGRADEFTIME<=?'
        );
        $GetUnits->bind_param('i', $now);
        $GetUnits->execute();
        $GetUnitsData=$GetUnits->get_result();
        $GetUnits->close();
        if ($GetUnitsData->num_rows>0) {
            while ($row=$GetUnitsData->fetch_assoc()) {
                // Resource unit building
                if ($row['UNITTYPE']=='Resource') {
                    $this->_buildingResorce(
                        $row['ID'], $row['USERID'], $row['UNITLEVEL'],
                        $row['RWOOD'], $row['RSTONE'], $row['RGOLD'],
                        $row['RFOOD'], $row['RTIME'],
                        $row['EVENTNAME'], $row['EVENTVAL'], $row['UPGRADEFTIME'],
                        $row['COLONYID']
                    );
                }
                // Farm unit building
                if ($row['UNITTYPE']=='Farm') {
                    $this->_buildingFarm(
                        $row['ID'], $row['USERID'], $row['UNITLEVEL'],
                        $row['RWOOD'], $row['RSTONE'], $row['RGOLD'],
                        $row['RFOOD'], $row['RTIME'], $row['UPGRADEFTIME'],
                        $row['EVENTVAL'], $row['COLONYID']
                    );
                }
                // Storage unit building
                if ($row['UNITTYPE']=='ResourceStore') {
                    $this->_buildingStorage(
                        $row['ID'], $row['USERID'], $row['UNITLEVEL'],
                        $row['RWOOD'], $row['RSTONE'], $row['RGOLD'],
                        $row['RFOOD'], $row['RTIME'], $row['UPGRADEFTIME'],
                        $row['EVENTNAME'], $row['EVENTVAL'], $row['COLONYID']
                    );

                }
                // Barrack unit building
                if ($row['UNITTYPE']=='Barrack') {
                    $this->_buildingBarrack(
                        $row['ID'], $row['USERID'], $row['UNITLEVEL'],
                        $row['RWOOD'], $row['RSTONE'], $row['RGOLD'],
                        $row['RFOOD'], $row['RTIME'], $row['UPGRADEFTIME'],
                        $row['EVENTNAME'], $row['EVENTVAL'], $row['COLONYID']
                    );

                }               
                // Blacksmith unit building
                if ($row['UNITTYPE']=='Blacksmith') {
                    $this->_buildingBlacksmith(
                        $row['ID'], $row['USERID'], $row['UNITLEVEL'],
                        $row['RWOOD'], $row['RSTONE'], $row['RGOLD'],
                        $row['RFOOD'], $row['RTIME'], $row['UPGRADEFTIME'],
                        $row['EVENTNAME'], $row['EVENTVAL'], $row['COLONYID']
                    );

                }
                // Defansive unit building
                if ($row['UNITTYPE']=='Defansive') {
                    $this->_buildingDefansive(
                        $row['ID'], $row['USERID'], $row['UNITLEVEL'],
                        $row['RWOOD'], $row['RSTONE'], $row['RGOLD'],
                        $row['RFOOD'], $row['RTIME'], $row['UPGRADEFTIME'],
                        $row['EVENTNAME'], $row['EVENTVAL'], $row['COLONYID']
                    );

                }
                // Church building
                if ($row['UNITTYPE']=='Church') {
                    $this->_buildingChurch(
                        $row['ID'], $row['USERID'], $row['UNITLEVEL'],
                        $row['RWOOD'], $row['RSTONE'], $row['RGOLD'],
                        $row['RFOOD'], $row['RTIME'], $row['UPGRADEFTIME'],
                        $row['EVENTNAME'], $row['EVENTVAL'], $row['COLONYID']
                    );

                }
                // GnomishInventor building
                if ($row['UNITTYPE']=='GnomishInventor') {
                    $this->_buildingGnomishInventor(
                        $row['ID'], $row['USERID'], $row['UNITLEVEL'],
                        $row['RWOOD'], $row['RSTONE'], $row['RGOLD'],
                        $row['RFOOD'], $row['RTIME'], $row['UPGRADEFTIME'],
                        $row['EVENTNAME'], $row['EVENTVAL'], $row['COLONYID']
                    );

                }
                

            }
        }

    }

    private function _buildingGnomishInventor(
        $UID, $USERID, $ULVL, $RW, $RS, $RG, $RF, $RT, $UFT, $EN, $EV, $CID
    ) {
        $NewUnitLevel=$ULVL+1;
        $NewRW=$RW*2;
        $NewRS=$RS*2;
        $NewRG=$RG*2;
        $NewRF=$RF*2;
        $NewRT=$RT*2;
        $NewEV=$EV;
        $this->conn->begin_transaction();
        $uprange=$this->conn->prepare(
            'UPDATE userstats SET MRANGE=MRANGE+1 WHERE USERID=? AND COLONYID=?'
        );
        $uprange->bind_param('ii', $USERID, $CID);
        $uprange->execute();
        $uprange->close();

        $upunit=$this->_userUnitUpg(
            $UID, $USERID, $NewUnitLevel, $NewRW, 
            $NewRS, $NewRG, $NewRF, $NewRT, $NewEV, $CID
        );
 
        if ($uprange && $upunit) {
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        }

    }

    private function _buildingChurch(
        $UID, $USERID, $ULVL, $RW, $RS, $RG, $RF, $RT, $UFT, $EN, $EV, $CID
    ) {
        $NewUnitLevel=$ULVL+1;
        $NewRW=$RW*2;
        $NewRS=$RS*2;
        $NewRG=$RG*2;
        $NewRF=$RF*2;
        $NewRT=$RT*2;
        $NewEV=$EV; 
        
        $this->conn->begin_transaction();
        $getcolonynumber=$this->conn->prepare(
            'UPDATE userstats SET MCOLONY=MCOLONY+1 WHERE USERID IN (?) '
        );
        $getcolonynumber->bind_param('i', $USERID);
        $getcolonynumber->execute();
        $getcolonynumber->close();
        $upunit=$this->_userUnitUpg(
            $UID, $USERID, $NewUnitLevel, $NewRW, 
            $NewRS, $NewRG, $NewRF, $NewRT, $NewEV, $CID
        );
        
        if ($getcolonynumber && $upunit) {
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        }                             

    }

    private function _buildingDefansive(
        $UID, $USERID, $ULVL, $RW, $RS, $RG, $RF, $RT, $UFT, $EN, $EV, $CID
    ) {
        $NewUnitLevel=$ULVL+1;
        $NewRW=$RW*2;
        $NewRS=$RS*2;
        $NewRG=$RG*2;
        $NewRF=$RF*2;
        $NewRT=$RT*2;
        $NewEV=$EV*2; 
        $this->conn->begin_transaction();
        if ($EN=='Ground Defanse') {
            $updefstat=$this->conn->prepare(
                'UPDATE userstats SET GROUNDDEF=GROUNDDEF+? WHERE USERID=? AND COLONYID=?'
            );
            $updefstat->bind_param('iii', $EV, $USERID, $CID);
            $updefstat->execute();
            $updefstat->close();
        } elseif ($EN=='Air Defanse') {
            $updefstat=$this->conn->prepare(
                'UPDATE userstats SET AIRDEF=AIRDEF+? WHERE USERID=? AND COLONYID=?'
            );
            $updefstat->bind_param('iii', $EV, $USERID, $CID);
            $updefstat->execute();
            $updefstat->close();
        } elseif ($EN=='Ground and Air Defanse') {
            $updefstat=$this->conn->prepare(
                'UPDATE userstats SET AIRDEF=AIRDEF+?,GROUNDDEF=GROUNDDEF+? 
                WHERE USERID=? AND COLONYID=?'
            );
            $updefstat->bind_param('iiii', $EV, $EV, $USERID, $CID);
            $updefstat->execute();
            $updefstat->close();

        }
        
        $upunit=$this->_userUnitUpg(
            $UID, $USERID, $NewUnitLevel, $NewRW, 
            $NewRS, $NewRG, $NewRF, $NewRT, $NewEV, $CID
        );
        if ($updefstat && $upunit) {
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        }
    }

    private function _buildingBlacksmith(
        $UID, $USERID, $ULVL, $RW, $RS, $RG, $RF, $RT, $UFT, $EN, $EV, $CID
    ) {
        $NewUnitLevel=$ULVL+1;
        $NewRW=$RW*2;
        $NewRS=$RS*2;
        $NewRG=$RG*2;
        $NewRF=$RF*2;
        $NewRT=$RT*2;
        $NewEV=$EV; 
        $this->_userUnitUpg(
            $UID, $USERID, $NewUnitLevel, $NewRW, 
            $NewRS, $NewRG, $NewRF, $NewRT, $NewEV, $CID
        );

    }

    private function _buildingBarrack(
        $UID, $USERID, $ULVL, $RW, $RS, $RG, $RF, $RT, $UFT, $EN, $EV, $CID
    ) {
        $NewUnitLevel=$ULVL+1;
        $NewRW=$RW*2;
        $NewRS=$RS*2;
        $NewRG=$RG*2;
        $NewRF=$RF*2;
        $NewRT=$RT*2;
        $NewEV=$EV;
        $uunitup=$this->_userUnitUpg(
            $UID, $USERID, $NewUnitLevel, $NewRW, 
            $NewRS, $NewRG, $NewRF, $NewRT, $NewEV, $CID
        );          
    }

    private function _buildingStorage(
        $UID, $USERID, $ULVL, $RW, $RS, $RG, $RF, $RT, $UFT, $EN, $EV, $CID 
    ) {
        $now=time();

        $NewUnitLevel=$ULVL+1;
        $NewRW=$RW*2;
        $NewRS=$RS*2;
        $NewRG=$RG*2;
        $NewRF=$RF*2;
        $NewRT=$RT*2;
        $NewEV=$EV*2;

        $this->conn->begin_transaction();
        $GetUserResources=$this->conn->prepare(
            'SELECT ID,USERID,COLONYID,RNAME,RVALUE,RRATE,RLUPDATE,RTANK
            FROM resources WHERE USERID=? AND RNAME=? AND COLONYID=?'
        );        
        $GetUserResources->bind_param(
            'isi', $USERID, $EN, $CID
        );    
        $GetUserResources->execute();
        $GetUserResourcesData=$GetUserResources->get_result();
        $GetUserResources->close();
        if ($GetUserResourcesData->num_rows>0) {
            $row=$GetUserResourcesData->fetch_assoc();
            $NewTank=$row['RTANK'] + $EV;
            if ($row['RVALUE']>=$NewTank) {
                $NewVal=$row['RVALUE'];
            } else {
                $NewVal=$row['RVALUE']+(($now-$UFT)*$row['RRATE']);
            }
            if ($NewVal>$NewTank) {
                $NewVal=$NewTank;
            }
           
        }

        $uresup=$this->_userResourceUpg(
            $row['ID'], $USERID, $row['RNAME'], $NewVal, 
            $row['RRATE'], $now, $NewTank, $CID
        );

        $uunitup=$this->_userUnitUpg(
            $UID, $USERID, $NewUnitLevel, $NewRW, 
            $NewRS, $NewRG, $NewRF, $NewRT, $NewEV, $CID
        );

        if ($GetUserResources && $uresup && $uunitup) {
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        }

    }

    private function _buildingFarm(
        $UID, $USERID, $ULVL, $RW, $RS, $RG, $RF, $RT, $UFT, $EV, $CID 
    ) {
        $now=time();
        $NewUnitLevel=$ULVL+1;
        $NewUnitWood=$RW*2;
        $NewUnitStone=$RS*2;
        $NewUnitGold=$RG*2;
        $NewUnitFood=$RF*2;
        $NewUnitTime=$RT*2;
        $NewEventEV=$EV*2;

        $UserFood=$this->_userFoodval($USERID, $CID);
        $NewFood=$UserFood+$EV;

        $this->conn->begin_transaction();
        $GetUResorce=$this->conn->prepare(
            'SELECT * 
            FROM resources WHERE USERID=? AND COLONYID=?'
        );
        $GetUResorce->bind_param(
            'ii', $USERID, $CID
        );
        $GetUResorce->execute();
        $GetUResorceData=$GetUResorce->get_result();
        $GetUResorce->close();
        if ($GetUResorceData->num_rows>0) {
            while ($row=$GetUResorceData->fetch_assoc()) {
                if ($UserFood<0 && $NewFood>=0 && $row['RVALUE']<$row['RTANK']) {
                    $NewVal=(($now-$UFT)*$row['RRATE'])+$row['RVALUE'];
                    if ($NewVal>$row['RTANK']) {
                        $NewVal=$row['RTANK'];
                    }
                } else {
                    $NewVal=$row['RVALUE'];
                }

                if ($row['RNAME']=='Food') {
                    $NewVal=$NewFood;
                }
                $UPG=$this->_userResourceUpg(
                    $row['ID'], $row['USERID'], $row['RNAME'], $NewVal, 
                    $row['RRATE'], $row['RLUPDATE'], $row['RTANK'], $CID
                );
            }            
        }

        $UpgradeUnit=$this->_userUnitUpg(
            $UID, $USERID, $NewUnitLevel, $NewUnitWood, $NewUnitStone,
            $NewUnitGold, $NewUnitFood, $NewUnitTime, $NewEventEV, $CID
        );

        if ($GetUResorce && $UpgradeUnit && $UPG) {
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        } 

    }


    private function _buildingResorce(
        $ID, $USERID, $Unitlvl, $URW, $URS, $URG, $URF, $URT, $EN, $EV, $UFT, $CID
    ) {
        $now=time();

        $NewUnitLevel=$Unitlvl+1;
        $NewUnitWood=$URW*2;
        $NewUnitStone=$URS*2;
        $NewUnitGold=$URG*2;
        $NewUnitFood=$URF*2;
        $NewUnitTime=$URT*2;
        $NewEventEV=$EV*2;

        $this->conn->begin_transaction();
        $GetUResorce=$this->conn->prepare(
            'SELECT ID, USERID, COLONYID, RNAME, RVALUE, RRATE, RLUPDATE, RTANK
            FROM resources WHERE USERID=? AND RNAME=? AND COLONYID=?'
        );
        $GetUResorce->bind_param('isi', $USERID, $EN, $CID);
        $GetUResorce->execute();
        $GetUResorceData=$GetUResorce->get_result();
        $GetUResorce->close();
        if ($GetUResorceData->num_rows>0) {
            $row=$GetUResorceData->fetch_assoc();
            if ($this->_userFoodval($USERID, $CID)<0) {
                $NewReval=$row['RVALUE'];
            } else {
                if ($UFT==$now) {
                    $NewReval=$row['RVALUE'];
                } elseif ($UFT<$now) {
                    $NewReval
                        =$row['RVALUE'] + (($now-$UFT)*
                        ($EV-$row['RRATE']));
                } else {
                    $NewReval=$row['RVALUE'];
                }
                if ($NewReval>$row['RTANK']) {
                    $NewReval=$row['RTANK'];
                } 
            }
            
            $UpdateResource=$this->_userResourceUpg(
                $row['ID'], $row['USERID'], $row['RNAME'],
                $NewReval, $EV, $now, $row['RTANK'], $CID
            );
                  
        }

        $UpdateUnit=$this->_userUnitUpg(
            $ID, $USERID, $NewUnitLevel, $NewUnitWood, $NewUnitStone,
            $NewUnitGold, $NewUnitFood, $NewUnitTime, $NewEventEV, $CID
        );

        if ($GetUResorce && $UpdateResource && $UpdateUnit) {
            $this->conn->commit();
        } else {
            $this->conn->rollback();
        }
        
    }

    private function _userResourceUpg(
        $ID, $USERID, $RNAME, $RVALUE, $RRATE, $RLUPDATE, $RTANK, $CID
    ) {
        $this->conn->begin_transaction();
        $UPR=$this->conn->prepare(
            'UPDATE resources SET RNAME=?,RVALUE=?,RRATE=?,RLUPDATE=?,RTANK=? 
            WHERE ID=? AND USERID=? AND COLONYID=?'
        );
        $UPR->bind_param(
            'sddiiiii', $RNAME, $RVALUE, $RRATE, $RLUPDATE, $RTANK,
            $ID, $USERID, $CID
        );
        $UPR->execute();
        $UPR->close();
        if ($UPR) {
            $this->conn->commit();
            return true;
        } else {
            $this->conn->rollback();
            return false;
        }
    }

    private function _userUnitUpg(
        $UID, $USERID, $NewUnitLevel, $NewUnitWood, $NewUnitStone, $NewUnitGold, 
        $NewUnitFood, $NewUnitTime, $NewEventEV, $CID
    ) {

        $this->conn->begin_transaction();
        $UpdateUnit=$this->conn->prepare(
            'UPDATE userunits SET UNITLEVEL=?,RWOOD=?,RSTONE=?,
            RGOLD=?,RFOOD=?,RTIME=?,UPGRADE=0,UPGRADEFTIME=0,EVENTVAL=?
            WHERE USERID=? AND ID=? AND COLONYID=?'
        );
        $UpdateUnit->bind_param(
            'iddddidiii', $NewUnitLevel, $NewUnitWood, $NewUnitStone, $NewUnitGold,
            $NewUnitFood, $NewUnitTime, $NewEventEV, $USERID, $UID, $CID
        );
        $UpdateUnit->execute();
        $UpdateUnit->close();

        if ($UpdateUnit) {
            $this->conn->commit();
            return true;
        } else {
            $this->conn->rollback();
            return false;
        }

    }

    private function _userFoodval($UserID, $CID)
    {
        $GetFood=$this->conn->prepare(
            'SELECT USERID,COLONYID,RNAME,RVALUE 
            FROM resources WHERE USERID=? AND RNAME="FOOD" AND COLONYID=?'
        );
        $GetFood->bind_param('ii', $UserID, $CID);
        $GetFood->execute();
        $GetFoodD=$GetFood->get_result();
        $GetFood->close();
        $row=$GetFoodD->fetch_assoc();
        return $row['RVALUE'];

    }

    public function getuserBlacksmithLVL($UserID, $CID)
    {
        $getlvl=$this->conn->prepare(
            'SELECT USERID,COLONYID,UNITNAME,UNITLEVEL FROM userunits WHERE UNITNAME="Blacksmith" AND USERID=? AND COLONYID=?'
        );
        $getlvl->bind_param('ii', $UserID, $CID);
        $getlvl->execute();
        $getlvldat=$getlvl->get_result();
        $row=$getlvldat->fetch_assoc();
        return $row['UNITLEVEL'];

    }

    public function getanyUnitLvl($UID, $CID, $UNAME)
    {
        $getunit=$this->conn->prepare(
            'SELECT USERID,COLONYID,UNITNAME,UNITLEVEL 
            FROM userunits 
            WHERE USERID=? AND COLONYID=? AND UNITNAME=?'
        );
        $getunit->bind_param('iis', $UID, $CID, $UNAME);
        $getunit->execute();
        $getunitdat=$getunit->get_result();
        $getunit->close();
        if ($getunitdat->num_rows>0) {
            $row=$getunitdat->fetch_assoc();
            $return=$row['UNITLEVEL'];
            unset($row);
        } else {
            $return=0;
        }
        unset($getunitdat);
        return $return;
    }

}

?>