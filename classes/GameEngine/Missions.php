<?php
namespace GameEngine;

use Database\Dbconnect;
use Otherfunction\Tools;
use UserProcess\Message;
use GameEngine\Units;
use GameEngine\Resources;

class Missions
{
    private $_conn,$_now,$_tools;
    private $_mid,$_1NAME,$_1USERID,$_1COLONYID,$_1LAT,$_1LONG;
    private $_2NAME,$_2USERID,$_2COLONYID,$_2LAT,$_2LONG;
    private $_ETD,$_ETA,$_DISTANCE,$_COURSE,$_SPEED;
    private $_ARMYID,$_DO,$_CALLBACK;
    private $_reportsend,$_transferresourcereport,$_transferarmyreport;
    private $_units,$_resource;
    private $_battlewinner,$_reu1aval,$_reu2aval,$_reu1gval,$_reu2gval;
    private $_carrya,$_carryg;

    public function __construct()
    {
        $dbcon=new Dbconnect;
        $report=new Message;
        $this->_conn=$dbcon->conn;
        $this->_now=time();
        $this->_reportsend=$report;
        $this->_tools=new Tools;
        $this->_units=new Units;
        $this->_resource=new Resources;
        $this->_checkMissions($this->_now);
    }

    private function _checkMissions($now)
    {
        $checkmissions=$this->_conn->prepare(
            'SELECT ID,1NAME,1USERID,1COLONYID,1LAT,1LONG,2NAME,2USERID,
            2COLONYID,2LAT,2LONG,ETD,ETA,DISTANCE,COURSE,SPEED,ARMYID,DO,CALLBACK
            FROM usermissions WHERE ETA<=? ORDER BY ID ASC'
        );
        $checkmissions->bind_param(
            'i', $now
        );
        $checkmissions->execute();
        $checkmissionsdat=$checkmissions->get_result();
        $checkmissions->close();
        if ($checkmissionsdat->num_rows>0) {
            while ($row=$checkmissionsdat->fetch_assoc()) {
                $this->_mid=$row['ID'];
                $this->_1NAME=$row['1NAME'];
                $this->_1USERID=$row['1USERID'];
                $this->_1COLONYID=$row['1COLONYID'];
                $this->_1LAT=$row['1LAT'];
                $this->_1LONG=$row['1LONG'];

                $this->_2NAME=$row['2NAME'];
                $this->_2USERID=$row['2USERID'];
                $this->_2COLONYID=$row['2COLONYID'];
                $this->_2LAT=$row['2LAT'];
                $this->_2LONG=$row['2LONG'];

                $this->_ETD=$row['ETD'];
                $this->_ETA=$row['ETA'];
                $this->_DISTANCE=$row['DISTANCE'];
                $this->_COURSE=$row['COURSE'];
                $this->_SPEED=$row['SPEED'];

                $this->_ARMYID=$row['ARMYID'];
                $this->_DO=$row['DO'];
                $this->_CALLBACK=$row['CALLBACK'];

                // Contine
                $this->_doMission();

            }
        }

    }

    private function _doMission()
    {
        switch ($this->_DO) {
        case 'Spy':
            $this->_doSpy();
            break;
        case 'Attack';
            $this->_doAttack();
            break;
        case 'Trade':
            $this->_doTrade();
            break;
        case 'Colonization';
            $this->_doColonization();
            break;
        case 'Deployment':
            $this->_doDeployment();
            break;
        case 'Research';
            $this->_doResource();
            break;
        case 'Return';
            $this->_doReturn();
            break;
        default:
            $this->_deleteMission();
        }

    }

    private function _doResource()
    {
        $checkareatype=$this->_checkAreatype();
        if ($checkareatype['USER']==0 && $checkareatype['USERID']==0
            && $checkareatype['COLONYID']==0
            && $checkareatype['NPC']!=0
        ) {
            $getdungeon=$this->_conn->prepare(
                'SELECT DAP,DDEF,DGIFT,DGIFTTIYPE 
                FROM dungeon ORDER BY RAND() LIMIT 1'
            );
            $getdungeon->execute();
            $getdungeondat=$getdungeon->get_result();
            $getdungeon->close();
            if ($getdungeondat->num_rows>0) {
                $user1AP=array(0);
                $user1DEF=array(0);
                $user1TID=array(0);
                $user1VAL=array(0);
                $totalap=0;
                $totaldef=0;
                $getuserarmy=$this->_conn->prepare(
                    'SELECT (usermissionarmy.VALUE*usertroops.DAMAGE) as DAMAGE,
                    (usermissionarmy.VALUE*usertroops.ARMOR) as ARMOR,
                    usermissionarmy.TROOPID,usermissionarmy.VALUE 
                    FROM usermissionarmy
                    INNER JOIN usertroops 
                    ON usertroops.TROOPID=usermissionarmy.TROOPID
                    WHERE 
                    usermissionarmy.USERID=? AND 
                    usermissionarmy.COLONYID=? AND 
                    usermissionarmy.ARMY=? AND 
                    usertroops.USERID=? AND
                    usertroops.COLONYID=? '
                );
                $getuserarmy->bind_param(
                    'iiiii', $this->_1USERID, $this->_1COLONYID,
                    $this->_ARMYID, $this->_1USERID, $this->_1COLONYID
                );
                $getuserarmy->execute();
                $getuserarmydat=$getuserarmy->get_result();
                $getuserarmy->close();
                if ($getuserarmydat->num_rows>0) {
                    while ($row=$getuserarmydat->fetch_assoc()) {
                        array_push($user1AP, $row['DAMAGE']);
                        array_push($user1DEF, $row['ARMOR']);
                        array_push($user1TID, $row['TROOPID']);
                        array_push($user1VAL, $row['VALUE']);
                    }
                }
                $arraycount=count($user1AP);
                for ($i=0; $i<$arraycount; $i++) {
                    $totalap+=$user1AP[$i];
                    $totaldef+=$user1DEF[$i];
                }
                $row=$getdungeondat->fetch_assoc();
                $totalap-=($row['DAP']-($totaldef-$row['DDEF']));
                if ($totalap>0) {
                    for ($i=0; $i<$arraycount; $i++) {
                        $newval=rand(0, $user1VAL[$i]);
                        $updatearmy=$this->_conn->prepare(
                            'UPDATE usermissionarmy SET VALUE=? 
                            WHERE USERID=? AND COLONYID=? AND ARMY=? 
                            AND TROOPID=?'
                        );
                        $updatearmy->bind_param(
                            'iiiii', $newval, $this->_1USERID, 
                            $this->_1COLONYID, $this->_ARMYID,
                            $user1TID[$i]
                        );
                        $updatearmy->execute();
                        $updatearmy->close();                        
                    }
                    switch($row['DGIFTTIYPE']){
                    case 'Food':
                        $updatearmy=$this->_conn->prepare(
                            'UPDATE usermissionarmy SET FOOD=FOOD+? 
                            WHERE USERID=? AND COLONYID=? AND ARMY=? AND
                            VALUE>0'
                        );
                        $updatearmy->bind_param(
                            'iiii', $row['DGIFT'], $this->_1USERID, 
                            $this->_1COLONYID, $this->_ARMYID
                        );
                        $updatearmy->execute();
                        $updatearmy->close();     
                        break;
                    case 'Wood':
                        $updatearmy=$this->_conn->prepare(
                            'UPDATE usermissionarmy SET WOOD=WOOD+? 
                            WHERE USERID=? AND COLONYID=? AND ARMY=? AND
                            VALUE>0'
                        );
                        $updatearmy->bind_param(
                            'iiii', $row['DGIFT'], $this->_1USERID, 
                            $this->_1COLONYID, $this->_ARMYID
                        );
                        $updatearmy->execute();
                        $updatearmy->close();     
                        break;
                    case 'Stone':
                        $updatearmy=$this->_conn->prepare(
                            'UPDATE usermissionarmy SET STONE=STONE+? 
                            WHERE USERID=? AND COLONYID=? AND ARMY=? AND
                            VALUE>0'
                        );
                        $updatearmy->bind_param(
                            'iiii', $row['DGIFT'], $this->_1USERID, 
                            $this->_1COLONYID, $this->_ARMYID
                        );
                        $updatearmy->execute();
                        $updatearmy->close();     
                        break;
                    case 'Gold':
                        $updatearmy=$this->_conn->prepare(
                            'UPDATE usermissionarmy SET GOLD=GOLD+? 
                            WHERE USERID=? AND COLONYID=? AND ARMY=? AND
                            VALUE>0'
                        );
                        $updatearmy->bind_param(
                            'iiii', $row['DGIFT'], $this->_1USERID, 
                            $this->_1COLONYID, $this->_ARMYID
                        );
                        $updatearmy->execute();
                        $updatearmy->close();     
                        break;
                    case 'Soul':
                        for ($i=0; $i<$row['DGIFT']; $i++) {
                            $addsoul=$this->_conn->prepare(
                                'INSERT INTO userblueprints 
                                (USERID,COLONYID,BPRATE) VALUES (?,?,?)'
                            );
                            $addsoul->bind_param(
                                'iii', $this->_1USERID, $this->_1COLONYID, $row['DGIFT']
                            );
                            $addsoul->execute();
                            $addsoul->close();   
                        }                     
                        break;
                        
                    }
                    $this->_reportsend->createMessage(
                        0, $this->_1USERID, 'Resource Result', 
                        ''.$this->_2LAT.' - '.$this->_2LONG.' 
                        We found: '.$row['DGIFTTIYPE'].'('.$row['DGIFT'].') .'
                    ); 
                    $this->_makeMissionreturn();
                } else {
                    $this->_reportsend->createMessage(
                        0, $this->_1USERID, 'Resource Result', 
                        ''.$this->_2LAT.' - '.$this->_2LONG.' 
                        research failed, we lost everthing.'
                    ); 
                    $this->_deleteMission();
                } 
            } else {
                $this->_reportsend->createMessage(
                    0, $this->_1USERID, 'Resource Result', 
                    ''.$this->_2LAT.' - '.$this->_2LONG.' We found nothing!.'
                ); 
                $this->_makeMissionreturn();
            }       
        } else {
            $this->_reportsend->createMessage(
                0, $this->_1USERID, 'Resource failed', 
                ''.$this->_2LAT.' - '.$this->_2LONG.' this dungeon is abandoned'
            ); 
            $this->_makeMissionreturn();
        }

    }

    private function _doAttack()
    {
        $checkareatype=$this->_checkAreatype();
        if ($checkareatype['USER']!=0 && $checkareatype['USERID']==$this->_2USERID
            && $checkareatype['COLONYID']==$this->_2COLONYID
            && $checkareatype['NPC']==0
        ) {
            $user1GAP=array();
            $user1GDEF=array();
            $user1GVAL=array();
            $user1gtid=array();
            $user1AAP=array();
            $user1ADEF=array();
            $user1AVAL=array();
            $user1atid=array();
            $user2GAP=array();
            $user2GDEF=array();
            $user2GVAL=array();
            $user2gtid=array();
            $user2AAP=array();
            $user2ADEF=array();
            $user2AVAL=array();
            $user2atid=array();
            $user1acarry=array();
            $user1gcarry=array();
            $user1tname=array();
            $user1tnameval=array();
            $user2tname=array();
            $user2tnameval=array();
            $user2BGDEF=0;
            $user2BADEF=0;
            $getUser1AP=$this->_conn->prepare(
                'SELECT usersinfo.USERID,usersinfo.COLONYID,usersinfo.TROOPID,
                usersinfo.AIR,usersinfo.VALUE,usersinfo.DAMAGE,usersinfo.ARMOR,
                usersinfo.GROUNDDEF,usersinfo.AIRDEF,usersinfo.CARRY,usersinfo.TNAME
                FROM 
                (
                SELECT usermissionarmy.USERID,usermissionarmy.COLONYID,
                usermissionarmy.TROOPID,usermissionarmy.VALUE,
                usertroops.DAMAGE,usertroops.ARMOR,usertroops.CARRY,
                usertroops.TNAME,usertroops.AIR,userstats.GROUNDDEF,userstats.AIRDEF
                    FROM usermissionarmy 
                    INNER JOIN usertroops 
                    ON 
                    usermissionarmy.TROOPID=usertroops.TROOPID AND 
                    usermissionarmy.USERID=usertroops.USERID AND 
                    usermissionarmy.COLONYID=usertroops.COLONYID
                    INNER JOIN userstats
                    ON
                    userstats.USERID=usertroops.USERID AND 
                    userstats.COLONYID=usertroops.COLONYID
                    WHERE usermissionarmy.USERID=? AND 
                    usermissionarmy.COLONYID=? AND 
                    usermissionarmy.ARMY=?
                UNION
                SELECT usertroops.USERID,usertroops.COLONYID,usertroops.TROOPID,
                usertroops.VALUE,usertroops.DAMAGE,usertroops.ARMOR,usertroops.CARRY,
                usertroops.TNAME,usertroops.AIR,userstats.GROUNDDEF,userstats.AIRDEF
                    FROM usertroops
                    INNER JOIN userstats
                    ON 
                    userstats.USERID=usertroops.USERID AND
                    userstats.COLONYID=usertroops.COLONYID
                    WHERE 
                    usertroops.USERID=? AND 
                    usertroops.COLONYID=? AND 
                    usertroops.VALUE>0
                ) 
                as 
                usersinfo ORDER BY DAMAGE DESC'
            );
            $getUser1AP->bind_param(
                'iiiii', $this->_1USERID, $this->_1COLONYID, $this->_ARMYID,
                $this->_2USERID, $this->_2COLONYID
            );
            $getUser1AP->execute();
            $getUser1APDat=$getUser1AP->get_result();
            $getUser1AP->close();
            while ($row=$getUser1APDat->fetch_assoc()) {
                if ($row['USERID']==$this->_1USERID 
                    && $row['COLONYID']==$this->_1COLONYID
                ) {
                    if ($row['AIR']==0) {
                        array_push($user1GAP, $row['DAMAGE']);
                        array_push($user1GDEF, $row['ARMOR']);
                        array_push($user1GVAL, $row['VALUE']);
                        array_push($user1gtid, $row['TROOPID']);
                        array_push($user1gcarry, $row['CARRY']);
                    } else {
                        array_push($user1AAP, $row['DAMAGE']);
                        array_push($user1ADEF, $row['ARMOR']);
                        array_push($user1AVAL, $row['VALUE']);
                        array_push($user1atid, $row['TROOPID']);
                        array_push($user1acarry, $row['CARRY']);
                    }
                    array_push($user1tname, $row['TNAME']);
                    array_push($user1tnameval, $row['VALUE']);

                } elseif ($row['USERID']==$this->_2USERID 
                    && $row['COLONYID']==$this->_2COLONYID
                ) {
                    if ($row['AIR']==0) {
                        array_push($user2GAP, $row['DAMAGE']);
                        array_push($user2GDEF, $row['ARMOR']);
                        array_push($user2GVAL, $row['VALUE']);
                        array_push($user2gtid, $row['TROOPID']);
                    } else {
                        array_push($user2AAP, $row['DAMAGE']);
                        array_push($user2ADEF, $row['ARMOR']);
                        array_push($user2AVAL, $row['VALUE']);
                        array_push($user2atid, $row['TROOPID']);
                    }
                    array_push($user2tname, $row['TNAME']);
                    array_push($user2tnameval, $row['VALUE']);
                    if ($user2BGDEF==0) {
                        $user2BGDEF=$row['GROUNDDEF'];
                    }

                    if ($user2BADEF==0) {
                        $user2BADEF=$row['AIRDEF'];
                    }
                } 
            }
            $this->_battle(
                $user1GAP, $user1GDEF, $user1GVAL, $user1gtid,
                $user1AAP, $user1ADEF, $user1AVAL, $user1atid,
                $user2GAP, $user2GDEF, $user2GVAL, $user2gtid,
                $user2AAP, $user2ADEF, $user2AVAL, $user2atid,
                $user2BGDEF, $user2BADEF
            );
            $this->_carrya=$user1acarry;
            $this->_carryg=$user1gcarry;
            $updatetroopsafterbattle=$this->_updateTroops(
                $user1gtid, $this->_reu1gval,
                $user1atid, $this->_reu1aval,
                $user2gtid, $this->_reu2gval,
                $user2atid, $this->_reu2aval
            );

            switch($this->_battlewinner) {
            case 1:
                $this->_transferresource2to1(
                    $user1atid, $user1gtid
                );
                $this->_makeMissionreturn();
                $reportbattle='<div class="table-responsive">
                <table class="table table-bordered">
                <tr>
                    <td class="text-center">Atacker: 
                    <hr class="border border-warning"> '.$this->_1NAME.', 
                    '.$this->_1LAT.' - '.$this->_1LONG.' 
                    AREA-'.$this->_1COLONYID.'</td>
                    <td class="text-center">Defensive: 
                    <hr class="border border-warning">  '.$this->_2NAME.', 
                    '.$this->_2LAT.' - '.$this->_2LONG.' 
                    AREA-'.$this->_2COLONYID.'</td>
                <tr>
                <tr>
                    <td class="text-center">';
                $countname=count($user1tname);
                for ($i=0; $i<$countname; $i++) {
                    $reportbattle.=''.$user1tname[$i].' X '.$user1tnameval[$i].'
                    <br>';
                }
                $reportbattle.='</td>
                    <td class="text-center">';
                $countname=count($user2tname);
                for ($i=0; $i<$countname; $i++) {
                    $reportbattle.=''.$user2tname[$i].' X '.$user2tnameval[$i].'
                    <br>';
                }    
                $reportbattle.=' <br> Ground Defanse: 
                '.$user2BGDEF.' <br>
                Air Defanse: '.$user2BADEF.'</td>
                </tr>
                <tr>
                   <td class="text-center" colspan="2" > Resource: 
                   <br> '.$this->_transferresourcereport.' </td> 
                </tr>
                <tr>
                    <td class="text-center" colspan="2"> WINNER:
                    <br>
                     '.$this->_1NAME.' </td>
                </tr></table></div>';
                // transfer resources to 2-->1
                // makemission return
                break;
            case 2:
                $this->_transferResource(
                    $this->_2USERID, $this->_2COLONYID, 'Addandremove'
                );
                $this->_deleteMission();
                $reportbattle='<div class="table-responsive">
                <table class="table table-bordered">
                <tr>
                    <td class="text-center">Atacker: 
                    <hr class="border border-warning"> '.$this->_1NAME.', 
                    '.$this->_1LAT.' - '.$this->_1LONG.' 
                    AREA-'.$this->_1COLONYID.'</td>
                    <td class="text-center">Defensive: 
                    <hr class="border border-warning">  '.$this->_2NAME.', 
                    '.$this->_2LAT.' - '.$this->_2LONG.' 
                    AREA-'.$this->_2COLONYID.'</td>
                <tr>
                <tr>
                    <td class="text-center">';
                $countname=count($user1tname);
                for ($i=0; $i<$countname; $i++) {
                    $reportbattle.=''.$user1tname[$i].' X '.$user1tnameval[$i].'
                    <br>';
                }
                $reportbattle.='</td>
                    <td class="text-center">';
                $countname=count($user2tname);
                for ($i=0; $i<$countname; $i++) {
                    $reportbattle.=''.$user2tname[$i].' X '.$user2tnameval[$i].'
                    <br>';
                }    
                $reportbattle.=' <br> Ground Defanse: 
                '.$user2BGDEF.' <br>
                Air Defanse: '.$user2BADEF.'</td>
                </tr>
                <tr>
                   <td class="text-center" colspan="2" > Resource: 
                   <br> '.$this->_transferresourcereport.' </td> 
                </tr>
                <tr>
                    <td class="text-center" colspan="2"> WINNER:
                    <br>
                     '.$this->_2NAME.' </td>
                </tr></table></div>';
                // deletemission
                break;
            case 0:
                // Do not transfer resource
                // delete mission
                $reportbattle='<div class="table-responsive">
                <table class="table table-bordered">
                <tr>
                    <td class="text-center">Atacker: 
                    <hr class="border border-warning"> '.$this->_1NAME.', 
                    '.$this->_1LAT.' - '.$this->_1LONG.' 
                    AREA-'.$this->_1COLONYID.'</td>
                    <td class="text-center">Defensive: 
                    <hr class="border border-warning">  '.$this->_2NAME.', 
                    '.$this->_2LAT.' - '.$this->_2LONG.' 
                    AREA-'.$this->_2COLONYID.'</td>
                <tr>
                <tr>
                    <td class="text-center">';
                $countname=count($user1tname);
                for ($i=0; $i<$countname; $i++) {
                    $reportbattle.=''.$user1tname[$i].' X '.$user1tnameval[$i].'
                    <br>';
                }
                $reportbattle.='</td>
                    <td class="text-center">';
                $countname=count($user2tname);
                for ($i=0; $i<$countname; $i++) {
                    $reportbattle.=''.$user2tname[$i].' X '.$user2tnameval[$i].'
                    <br>';
                }    
                $reportbattle.=' <br> Ground Defanse: 
                '.$user2BGDEF.' <br>
                Air Defanse: '.$user2BADEF.'</td>
                </tr>
                <tr>
                    <td class="text-center" colspan="2"> WINNER:
                    <br>
                     No one </td>
                </tr></table></div>';
                $this->_deleteMission();
                break;
            default:
                break;
            }
            $this->_reportsend->createMessage(
                0, $this->_1USERID, 'Battle Report', $reportbattle
            ); 
            $this->_reportsend->createMessage(
                0, $this->_2USERID, 'Battle Report', $reportbattle
            ); 
        } else {
            $this->_reportsend->createMessage(
                0, $this->_1USERID, 'Battle failed', 
                ''.$this->_2LAT.' - '.$this->_2LONG.' this area is abandoned'
            ); 
            $this->_makeMissionreturn();
        }

    }

    private function _doSpy()
    {
        $checkareatype=$this->_checkAreatype();
        if ($checkareatype['USER']!=0 && $checkareatype['USERID']==$this->_2USERID
            && $checkareatype['COLONYID']==$this->_2COLONYID
            && $checkareatype['NPC']==0
        ) {
            $USER1CLVL=$this->_units->getanyUnitLvl(
                $this->_1USERID, $this->_1COLONYID, 'Church'
            );
            $USER2CLVL=$this->_units->getanyUnitLvl(
                $this->_2USERID, $this->_2COLONYID, 'Church'
            );
            $report='';
            if ($USER1CLVL>$USER2CLVL) {
                $totallvl=$USER1CLVL+$USER2CLVL;
                $diflvl=$USER1CLVL-$USER2CLVL;
                $rate=($diflvl*100)/$totallvl;
                if ($rate>=60) {
                    $reporttrops=$this->_conn->prepare(
                        'SELECT USERID,COLONYID,VALUE,TNAME 
                        FROM usertroops 
                        WHERE USERID=? AND COLONYID=? AND VALUE>0'
                    );
                    $reporttrops->bind_param(
                        'ii', $this->_2USERID, $this->_2COLONYID
                    );
                    $reporttrops->execute();
                    $reporttropsdat=$reporttrops->get_result();
                    $reporttrops->close();
                    if ($reporttropsdat->num_rows>0) {
                        while ($row=$reporttropsdat->fetch_assoc()) {
                            $report.=''.$row['TNAME'].' X '.$row['VALUE'].' <br>';
                        }
                        unset($row);
                    } else {
                        $report.='There is no any troop <br>';
                    }

                    $reportunits=$this->_conn->prepare(
                        'SELECT USERID,COLONYID,UNITLEVEL,UNITNAME 
                        FROM userunits 
                        WHERE USERID=? AND COLONYID=? AND UNITLEVEL>0'
                    );
                    $reportunits->bind_param(
                        'ii', $this->_2USERID, $this->_2COLONYID
                    );
                    $reportunits->execute();
                    $reportunitsdat=$reportunits->get_result();
                    $reportunits->close();
                    if ($reportunitsdat->num_rows>0) {
                        while ($row=$reportunitsdat->fetch_assoc()) {
                            $report.='
                            '.$row['UNITNAME'].' => Level'.$row['UNITLEVEL'].' <br>';
                        }
                        unset($row);
                    } else {
                        $report.='There is no any building. <br>';
                    }
                    $report.='Resources:<br> Wood:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Wood', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Stone:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Stone', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Gold:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Gold', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Food:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Food', $this->_2COLONYID
                    )[0].'<br>';
                    $this->_makeMissionreturn();
                } elseif ($rate>=30 && $rate<60) {
                    $reportunits=$this->_conn->prepare(
                        'SELECT USERID,COLONYID,UNITLEVEL,UNITNAME 
                        FROM userunits 
                        WHERE USERID=? AND COLONYID=? AND UNITLEVEL>0'
                    );
                    $reportunits->bind_param(
                        'ii', $this->_2USERID, $this->_2COLONYID
                    );
                    $reportunits->execute();
                    $reportunitsdat=$reportunits->get_result();
                    $reportunits->close();
                    if ($reportunitsdat->num_rows>0) {
                        while ($row=$reportunitsdat->fetch_assoc()) {
                            $report.='
                            '.$row['UNITNAME'].' => Level'.$row['UNITLEVEL'].' <br>';
                        }
                        unset($row);
                    } else {
                        $report.='There is no any building. <br>';
                    }
                    $report.='Resources:<br> Wood:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Wood', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Stone:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Stone', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Gold:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Gold', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Food:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Food', $this->_2COLONYID
                    )[0].'<br>';
                    $this->_makeMissionreturn();
                } elseif ($rate>=5 && $rate<30) {
                    $report.='Resources:<br> Wood:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Wood', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Stone:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Stone', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Gold:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Gold', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Food:'.
                    $this->_resource->GetUserResources(
                        $this->_2USERID, 'Food', $this->_2COLONYID
                    )[0].'<br>';
                    $report.='Spy captured...';
                    $this->_deleteMission();
                } else {
                    $report.='Spy captured...';
                    $this->_deleteMission();
                }

            } else {
                $report.='Spy captured...';
                $this->_deleteMission();
            }
            $this->_reportsend->createMessage(
                0, $this->_1USERID, 'Spy Report', 
                '<b>From:</b> '.$this->_1NAME.' Area 
                '.$this->_1LAT.' - '.$this->_1LONG.' 
                <b>To:</b> '.$this->_2NAME.' 
                Area '.$this->_2LAT.' - '.$this->_2LONG.' <br> 
                '.$report.''
            );
            if ($report=='Spy captured...') {
                $this->_reportsend->createMessage(
                    0, $this->_2USERID, 'Spy Report', 
                    '<b>From:</b> '.$this->_1NAME.' Area 
                    '.$this->_1LAT.' - '.$this->_1LONG.' 
                    <b>To:</b> '.$this->_2NAME.' 
                    Area '.$this->_2LAT.' - '.$this->_2LONG.' <br> 
                    '.$report.''
                );  
            }
        } else {
            $this->_makeMissionreturn();
        }
    }

    private function _doColonization()
    { 
        $checkareatype=$this->_checkAreatype();
        if ($checkareatype['USER']==0 && $checkareatype['USERID']==0 
            && $checkareatype['COLONYID']==0 && $checkareatype['NPC']==0 
        ) { 
            $ColonyCount=$this->_conn->prepare(
                'SELECT COUNT(usercolony.ID) AS TOTALCOLONY,usercolony.USERID,
                userstats.USERID,userstats.MCOLONY
                FROM usercolony INNER JOIN userstats 
                ON usercolony.USERID=userstats.USERID
                WHERE usercolony.USERID=? AND userstats.COLONYID=?'
            );
            $ColonyCount->bind_param('ii', $this->_1USERID, $this->_1COLONYID);
            $ColonyCount->execute();
            $ColonyCountData=$ColonyCount->get_result();
            $ColonyCount->close();
            if ($ColonyCountData->num_rows>0) {
                $row2=$ColonyCountData->fetch_assoc();
                if ($row2['MCOLONY']>$row2['TOTALCOLONY']) { 
                    $this->_conn->begin_transaction();
                    $AddColony=$this->_conn->prepare(
                        'INSERT INTO usercolony 
                        (USERID,CLAT,CLONG) 
                        VALUES (?,?,?)'
                    );
                    $AddColony->bind_param(
                        'iii', $this->_1USERID, $this->_2LAT, $this->_2LONG
                    );
                    $AddColony->execute();
                    $NewColonyid=$AddColony->insert_id;
                    $AddColony->close();
                    $getTroops=$this->_conn->prepare(
                        'INSERT INTO usertroops 
                        (USERID,COLONYID,TROOPID,TNAME,TIMG,TDESCP,
                        DAMAGE,ARMOR,SPEED,CARRY,AIR,WOOD,STONE,GOLD,
                        FOOD,TIME,BLEVEL,TROOPTYPE,ORDERT,SPY,ATTACK,
                        TRADE,COLONIZATION,DEPLOYMENT,RESEARCH) 
                        SELECT ?,?,ID,TNAME,TIMG,TDESCP,DAMAGE,ARMOR,
                        SPEED,CARRY,AIR,WOOD,STONE,GOLD,FOOD,TIME,
                        BLEVEL,TROOPTYPE,ORDERT,SPY,ATTACK,TRADE,
                        COLONIZATION,DEPLOYMENT,RESEARCH FROM troops
                        WHERE BLUEPRINT=0'
                    );
                    $getTroops->bind_param('ii', $this->_1USERID, $NewColonyid);
                    $getTroops->execute();
                    $getTroops->close();
    
                    $InsertStat=$this->_conn->prepare(
                        'INSERT INTO userstats (USERID,COLONYID,MCOLONY)
                        VALUES (?,?,?)'
                    );
                    $InsertStat->bind_param(
                        'iii', $this->_1USERID, $NewColonyid, $row2['MCOLONY']
                    );
                    $InsertStat->execute();
                    $InsertStat->close();
                    $UpdateMap=$this->_conn->prepare(
                        'UPDATE map SET USER=1,COLONYID=?,UNAME=?,NPC=0,USERID=?
                        WHERE MLAT=? AND MLONG=?'
                    );
                    $UpdateMap->bind_param(
                        'isiii', $NewColonyid, $this->_1NAME, $this->_1USERID,
                        $this->_2LAT, $this->_2LONG
                    );
                    $UpdateMap->execute();
                    $UpdateMap->close();
                    $NewUserResources=$this->_conn->prepare(
                        'INSERT INTO resources 
                        (USERID,COLONYID,RNAME,RVALUE,RLUPDATE)
                        VALUES (?,?,?,?,?)'
                    );
                    $NewUserResources->bind_param(
                        'iisdi', $this->_1USERID, $NewColonyid, 
                        $RNAME, $RVALUE, $this->_now
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
                    $NewUserResources->
                    execute();                                                         
                    $NewUserResources->close();
                    $GetUnits=$this->_conn->prepare(
                        'INSERT INTO userunits 
                        (USERID,COLONYID,UNITID,UNITNAME,UNITDESCP,UNITIMG,
                        UNITTYPE,RWOOD,RSTONE,RGOLD,RFOOD,RTIME,
                        EVENTNAME,EVENTVAL,ORDERU,STTYPE,RLVL) 
                        SELECT ?,?,ID,UNITNAME,UNITDESCP,UNITIMG,
                        UNITTYPE,RWOOD,RSTONE,RGOLD,
                        RFOOD,RTIME,EVENTNAME,EVENTVAL,ORDERU,STTYPE,RLVL
                        FROM units'
                    );
                    $GetUnits->bind_param('ii', $this->_1USERID, $NewColonyid);
                    $GetUnits->execute();
                    $GetUnits->close();
                    $transferresource=$this->_transferResource(
                        $this->_1USERID, $NewColonyid, 'Add'
                    );
                    $deletemission=$this->_deleteMission();
                    if ($AddColony && $UpdateMap && $NewUserResources
                        && $GetUnits && $InsertStat && $getTroops
                        && $transferresource && $deletemission
                    ) {
                        $this->_conn->commit();
                    } else {
                        $this->_conn->rollback();
                    }
                    $this->_reportsend->createMessage(
                        0, $this->_1USERID, 'Colonization was successful', 
                        'You have a colony in position 
                        '.$this->_2LAT.' - '.$this->_2LONG.''
                    );            
                } else {
                    $this->_makeMissionreturn();
                    $this->_reportsend->createMessage(
                        0, $this->_1USERID, 'Colonization failed', 
                        'You have already reached the maximum colony number'
                    );     
                } 
            } else {
                $this->_makeMissionreturn();
                $this->_reportsend->createMessage(
                    0, $this->_1USERID, 'Colonization failed', 
                    'You lost your main area'
                );     
            }
        } else {
            $this->_reportsend->createMessage(
                0, $this->_1USERID, 'Colonization failed', 
                ''.$this->_2LAT.' - '.$this->_2LONG.' this area is already colonized'
            );                 
            $this->_makeMissionreturn();
        }
    }

    private function _doTrade()
    {
        $checkareatype=$this->_checkAreatype();
        if ($checkareatype['USER']!=0 && $checkareatype['USERID']==$this->_2USERID
            && $checkareatype['COLONYID']==$this->_2COLONYID
            && $checkareatype['NPC']==0
        ) {
            $this->_conn->begin_transaction();
            $transferresource=$this->_transferResource(
                $this->_2USERID, $this->_2COLONYID, 'Addandremove'
            );
            $makereturn=$this->_makeMissionreturn();
            // SEND REPORT
            if ($transferresource && $makereturn) {    
                $this->_reportsend->createMessage(
                    0, $this->_1USERID, 'Trade Completed', 
                    '<b>From:</b> '.$this->_1NAME.' Area 
                    '.$this->_1LAT.' - '.$this->_1LONG.' 
                    <b>To:</b> '.$this->_2NAME.' 
                    Area '.$this->_2LAT.' - '.$this->_2LONG.' <br> 
                    '.$this->_transferresourcereport.''
                ); 
                $this->_reportsend->createMessage(
                    0, $this->_2USERID, 'Trade Completed', 
                    '<b>From:</b> '.$this->_1NAME.' Area 
                    '.$this->_1LAT.' - '.$this->_1LONG.' 
                    <b>To:</b> '.$this->_2NAME.' 
                    Area '.$this->_2LAT.' - '.$this->_2LONG.' <br> 
                    '.$this->_transferresourcereport.''
                );                          
                                    
                $this->_conn->commit();
            } else {
                $this->_conn->rollback();
            }
        } else {
            $this->_makeMissionreturn();
        }
    }

    private function _doDeployment()
    {
        $checkareatype=$this->_checkAreatype();
        if ($checkareatype['USER']!=0 && $checkareatype['USERID']==$this->_2USERID 
            && $checkareatype['COLONYID']==$this->_2COLONYID 
            && $checkareatype['NPC']==0 
        ) {
            $this->_conn->begin_transaction();
            $transferresource=$this->_transferResource(
                $this->_2USERID, $this->_2COLONYID, 'Add'
            );
            $transferarmy=$this->_transferArmy($this->_2USERID, $this->_2COLONYID);
            $deletemission=$this->_deleteMission();
            // SEND REPORT
            if ($transferresource && $transferarmy && $deletemission) {
                $this->_conn->commit();
                $this->_reportsend->createMessage(
                    0, $this->_1USERID, 'Deployment Completed', 
                    '<b>From:</b> '.$this->_1NAME.' Area '.$this->_1LAT.' - 
                    '.$this->_1LONG.' 
                    <b>To:</b> '.$this->_2NAME.'  
                    Area '.$this->_2LAT.' - '.$this->_2LONG.' <br> 
                    '.$this->_transferresourcereport.' <br> 
                    '.$this->_transferarmyreport.''
                );
                $this->_reportsend->createMessage(
                    0, $this->_2USERID, 'Deployment Completed', 
                    '<b>From:</b> '.$this->_1NAME.' Area '.$this->_1LAT.' - 
                    '.$this->_1LONG.' 
                    <b>To:</b> '.$this->_2NAME.'  
                    Area '.$this->_2LAT.' - '.$this->_2LONG.' <br> 
                    '.$this->_transferresourcereport.' <br> 
                    '.$this->_transferarmyreport.''
                );
            } else {
                $this->_conn->rollback();
            }
        } else {
            $this->_makeMissionreturn();
        }       
    }

    private function _doReturn()
    {
        $checkareatype=$this->_checkAreatype();
        if ($checkareatype['USER']!=0 && $checkareatype['USERID']==$this->_1USERID 
            && $checkareatype['COLONYID']==$this->_1COLONYID 
            && $checkareatype['NPC']==0 
        ) {
            $this->_conn->begin_transaction();
            $transfertroop=$this->_transferArmy($this->_1USERID, $this->_1COLONYID);
            $transferresource=$this->_transferResource(
                $this->_1USERID, $this->_1COLONYID, 'Add'
            );
            $delemission=$this->_deleteMission();
            // SEND REPORT
            if (!isset($this->_transferresourcereport)) {
                $this->_transferresourcereport='';
            }
            $this->_reportsend->createMessage(
                0, $this->_1USERID, 'Troops was returned', 
                '<b>To:</b> '.$this->_1NAME.' Area 
                '.$this->_1LAT.' - '.$this->_1LONG.' 
                <b>From:</b> '.$this->_2NAME.' 
                Area '.$this->_2LAT.' - '.$this->_2LONG.' <br> 
                '.$this->_transferresourcereport.' <br>
                '.$this->_transferarmyreport.''
            );
            unset($this->_transferresourcereport);      
            if ($transfertroop && $transferresource && $delemission) {
                $this->_conn->commit();
            } else {
                $this->_conn->rollback();
            }
        } else {
            $this->_deleteMission();
        }              
    }

    private function _transferArmy($ToUserID, $ToColonyID)
    {
        $this->_conn->begin_transaction();
        $getOnmissiontroops=$this->_conn->prepare(
            'SELECT usermissionarmy.ARMY,usermissionarmy.USERID,
            usermissionarmy.COLONYID,usermissionarmy.TROOPID,
            usermissionarmy.VALUE,troops.ID,troops.TNAME 
            FROM usermissionarmy INNER JOIN troops ON 
            troops.ID=usermissionarmy.TROOPID
            WHERE usermissionarmy.ARMY=? AND usermissionarmy.USERID=? 
            AND usermissionarmy.COLONYID=? AND usermissionarmy.VALUE>0'
        );
        $getOnmissiontroops->bind_param(
            'iii', $this->_ARMYID, $this->_1USERID, $this->_1COLONYID
        );
        $getOnmissiontroops->execute();
        $getOnmissiontroopsdat=$getOnmissiontroops->get_result();
        $getOnmissiontroops->close();
        $UpdateTroops=true;
        $this->_transferarmyreport='';
        if ($getOnmissiontroopsdat->num_rows>0) {
            $UpdateTroops=$this->_conn->prepare(
                'UPDATE usertroops SET VALUE=VALUE+? 
                WHERE USERID=? AND COLONYID=? AND TROOPID=?'
            );
            $UpdateTroops->bind_param(
                'diii', $tvalue, $ToUserID, $ToColonyID, $tid
            );
            while ($row=$getOnmissiontroopsdat->fetch_assoc()) {
                $this->_transferarmyreport.=''.$row['TNAME'].' X 
                '.$row['VALUE'].' <br>';
                $tvalue=$row['VALUE'];
                $tid=$row['TROOPID'];
                $UpdateTroops->execute();
            }
            $UpdateTroops->close();
        }
        if ($getOnmissiontroops &&  $UpdateTroops) {
            $this->_conn->commit();
            return true;
        } else {
            $this->_conn->rollback();
            return false;
        }
    }

    private function _transferResource($ToUserID, $ToColonyID, $Do)
    {
        $this->_conn->begin_transaction();
        $getCarryResource=$this->_conn->prepare(
            'SELECT ID,ARMY,USERID,COLONYID,WOOD,STONE,GOLD,FOOD 
            FROM usermissionarmy WHERE (ARMY=? AND USERID=? AND COLONYID=?) 
            AND (WOOD>0 OR STONE>0 OR GOLD>0 OR FOOD>0)'
        );
        $getCarryResource->bind_param(
            'iii', $this->_ARMYID, $this->_1USERID, $this->_1COLONYID
        );
        $getCarryResource->execute();
        $getCarryResourcedat=$getCarryResource->get_result();
        $getCarryResource->close();
        $updateresource=true;
        $updatearmyresource=true;
        if ($getCarryResourcedat->num_rows>0) {
            $row=$getCarryResourcedat->fetch_assoc();
            if ($Do=='Add' || $Do=='Addandremove') {
                $sql='UPDATE resources SET RVALUE=RVALUE+? 
                WHERE USERID=? AND COLONYID=? AND RNAME=?';
            } else {
                $sql='UPDATE resources SET RVALUE=RVALUE-? 
                WHERE USERID=? AND COLONYID=? AND RNAME=?';             
            }
            $updateresource=$this->_conn->prepare($sql);
            $updateresource->bind_param(
                'diis', $rvalue, $ToUserID, $ToColonyID, $rname
            );
            $rname='Food';
            $rvalue=$row['FOOD'];
            $updateresource->execute();
            $rname='Wood';
            $rvalue=$row['WOOD'];
            $updateresource->execute();
            $rname='Stone';
            $rvalue=$row['STONE'];
            $updateresource->execute();
            $rname='Gold';
            $rvalue=$row['GOLD'];
            $updateresource->execute();
            $updateresource->close();
            $this->_transferresourcereport='Wood: '.$row['WOOD'].' 
            Stone: '.$row['STONE'].' Gold: '.$row['GOLD'].' Food: '.$row['FOOD'].'';
            if ($Do=='Addandremove') {
                $updatearmyresource=$this->_conn->prepare(
                    'UPDATE usermissionarmy SET WOOD=0,STONE=0,GOLD=0,FOOD=0 
                    WHERE ID=?'
                );
                $updatearmyresource->bind_param('i', $row['ID']);
                $updatearmyresource->execute();
                $updatearmyresource->close();
            }
        }

        if ($getCarryResource && $updateresource && $updatearmyresource) {
            $this->_conn->commit();
            return true;
        } else {
            $this->_conn->rollback();
            return false;
        }

    }

    private function _deleteMission()
    {
        $this->_conn->begin_transaction();
        $delete=$this->_conn->prepare(
            'DELETE FROM usermissionarmy WHERE ARMY=?'
        );
        $delete->bind_param('i', $this->_ARMYID);
        $delete->execute();
        $delete->close();

        if ($delete) {
            $this->_conn->commit();
            return true;
        } else {
            $this->_conn->rollback();
            return false;
        }
    }

    private function _makeMissionreturn()
    {
        $this->_conn->begin_transaction();
        $voyagetime=$this->_now-$this->_ETD;
        $returndistance=round(($voyagetime/3600)*$this->_SPEED, 2);
        $course=($this->_COURSE+180)%360;
        $eta=$voyagetime+$this->_now;
        $updatemission=$this->_conn->prepare(
            'UPDATE usermissions 
            SET 1LAT=?,1LONG=?,2USERID=?,2COLONYID=?,2LAT=?,
            2LONG=?,ETD=?,ETA=?,DISTANCE=?,COURSE=?,
            DO="Return",CALLBACK=1
            WHERE ID=? AND 1USERID=?'
        );
        $updatemission->bind_param(
            'iiiiiiiiddii', $this->_2LAT, $this->_2LONG, 
            $this->_1USERID, $this->_1COLONYID, $this->_1LAT,
            $this->_1LONG, $this->_now, $eta,
            $returndistance, $course, $this->_mid, $this->_1USERID
        );
        $updatemission->execute();
        $updatemission->close();

        if ($updatemission) {
            $this->_conn->commit();
            return true;
        } else {
            $this->_conn->rollback();
            return false;
        }
    }

    private function _checkAreatype()
    {
        $return=array('USER'=>'0','USERID'=>'0','COLONYID'=>'0','NPC'=>'0');
        $CheckMap=$this->_conn->prepare(
            'SELECT ID,MLAT,MLONG,USER,COLONYID,UNAME,NPC,USERID
            FROM map WHERE MLAT=? AND MLONG=?'
        );
        $CheckMap->bind_param('ii', $this->_2LAT, $this->_2LONG);
        $CheckMap->execute();
        $CheckMapData=$CheckMap->get_result();
        $CheckMap->close();
        if ($CheckMapData->num_rows>0) {
            $row=$CheckMapData->fetch_assoc();
            $return=array(
                'USER'=>$row['USER'],
                'USERID'=>$row['USERID'],
                'COLONYID'=>$row['COLONYID'],
                'NPC'=>$row['NPC']
            ); 
        }
        return $return;

    }

    private function _battle(
        array $U1GAP, array $U1GDEF, array $U1GVAL, array $U1GTID,
        array $U1AAP, array $U1ADEF, array $U1AVAL, array $U1ATID,
        array $U2GAP, array $U2GDEF, array $U2GVAL, array $U2GTID,
        array $U2AAP, array $U2ADEF, array $U2AVAL, array $U2ATID,
        $U2BGDEF, $U2BADEF
    ) {
   
        //// AIR BATTLE
        $air1=count($U1AAP);
        $air2=count($U2AAP);
        if ($air1>0) {
            $tair1damage=0;
            $tair1armor=0;
            $tair2damage=0;
            $tair2armor=0;
            for ($i=0; $i<$air1; $i++) {
                $tair1damage+=$U1AAP[$i]*$U1AVAL[$i];
                $tair1armor+=$U1ADEF[$i]*$U1AVAL[$i];
            }
            if ($air2>0) {
                for ($b=0; $b<$air2; $b++) {
                    $tair2damage+=$U2AAP[$b]*$U2AVAL[$b];
                    $tair2armor+=$U2ADEF[$b]*$U2AVAL[$b];
                }
            }

            $tair1damage=$tair1damage-($tair2armor+$U2BADEF);
            $tair2damage-=$tair1armor;

            if (($tair1damage<0 && $tair2damage<0) || ($tair1damage==$tair2damage)) {
                $U1AVAL=array_fill(0, $air1, 0);
                $U2AVAL=array_fill(0, $air2, 0);
            } else {
                if ($tair1damage>$tair2damage) {
                    $tair1damage-=$tair2damage;
                    if ($tair1damage<0) {
                        $tair2damage=0;
                    }
                    if ($tair1damage>0) {
                        for ($i=0; $i<$air1; $i++) {
                            if (($U1AAP[$i]*$U1AVAL[$i])>$tair1damage) {
                                $newval=floor($tair1damage/$U1AAP[$i]);
                                if ($newval>$U1AVAL[$i]) {
                                    $newval=$U1AVAL[$i];
                                }
                                $U1AVAL[$i]=$newval;
                                // echo ''.$newval.'<br>';
                                $tair1damage=$tair1damage-
                                ($U1AAP[$i]*$U1AVAL[$i]);
                                // echo 'TA1DAMAGE:'.$tair1damage.' <br>';
                            } 
                        }
                    } else {
                        $U1AVAL=array_fill(
                            0, $air1, 0
                        );
                    }
                    $U2AVAL=array_fill(0, $air2, 0);
                } else {
                    if ($air2>0) {
                        $tair2damage-=$tair1damage;
                        if ($tair2damage<0) {
                            $tair2damage=0;
                        }
                        if ($tair2damage>0) {
                            for ($i=0; $i<$air2; $i++) {
                                if (($U2AAP[$i]*$U2AVAL[$i])>$tair2damage) {
                                    $newval=floor($tair2damage/$U2AAP[$i]);
                                    if ($newval>$U2AVAL[$i]) {
                                        $newval=$U2AVAL[$i];
                                    }
                                    $U2AVAL[$i]=$newval;
                                    $tair2damage=$tair2damage-
                                    ($U2AAP[$i]*$U2AVAL[$i]);
                                } 
                            }
                        } else {
                            $U2AVAL=array_fill(0, $air2, 0);
                        }
                
                    } 
                    $U1AVAL=array_fill(
                        0, $air1, 0
                    );                                           
                }
            }
        }
        $ground1=count($U1GAP);
        $ground2=count($U2GAP);
        if ($ground1>0) {
            $tg1damage=0;
            $tg1armor=0;
            $tg2damage=0;
            $tg2armor=0;
            for ($i=0; $i<$ground1; $i++) {
                $tg1damage+=$U1GAP[$i]*$U1GVAL[$i];
                $tg1armor+=$U1GDEF[$i]*$U1GVAL[$i];
            }
            if ($ground2>0) {
                for ($b=0; $b<$ground2; $b++) {
                    $tg2damage+=$U2GAP[$b]*$U2GVAL[$b];
                    $tg2armor+=$U2GDEF[$b]*$U2GDEF[$b];
                }
            }
            $tg1damage-=($tg2armor+$U2BGDEF);
            $tg2damage-=$tg1armor;
            if (($tg1damage<0 && $tg2damage<0) || ($tg1damage==$tg2damage)) {
                $U1GVAL=array_fill(0, $ground1, 0);
                $U2GVAL=array_fill(0, $ground2, 0);
            } else {
                if ($tg1damage>$tg2damage) {
                    $tg1damage-=$tg2damage;
                    if ($tg1damage<0) {
                        $tg1damage=0;
                    }
                    if ($tg1damage>0) {
                        for ($i=0; $i<$ground1; $i++) {
                            if (($U1GAP[$i]*$U1GVAL[$i])>$tg1damage) {
                                $newval=floor($tg1damage/$U1GAP[$i]);
                                if ($newval>$U1GVAL[$i]) {
                                    $newval=$U1GVAL[$i];
                                }
                                $U1GVAL[$i]=$newval;
                                $tg1damage-=$newval*$U1GAP[$i];
                            }
                        }
                    } else {
                        $U1GVAL=array_fill(0, $ground1, 0);
                    }
                    $U2GVAL=array_fill(0, $ground2, 0);
                } else {
                    $tg2damage-=$tg1damage;
                    if ($tg2damage<0) {
                        $tg2damage=0;
                    }
                    if ($tg2damage>0) {
                        for ($i=0; $i<$ground2; $i++) {
                            if (($U2GAP[$i]*$U2GVAL[$i])>$tg2damage) {
                                $newval=floor($tg2damage/$U2GAP[$i]);
                                if ($newval>$U2GVAL[$i]) {
                                    $newval=$U2GVAL[$i];
                                }
                                $U2GVAL[$i]=$newval;
                                $tg2damage-=$newval*$U2GAP[$i];
                            }
                        }

                    } else {
                        $U2GVAL=array_fill(0, $ground2, 0);
                    }
                    $U1GVAL=array_fill(0, $ground1, 0);
                }
            }

        }
        if (count($U1AVAL)>0) {
            $u1am=max($U1AVAL);
        } else {
            $u1am=0;
        }
        if (count($U2AVAL)>0) {
            $u2am=max($U2AVAL);
        } else {
            $u2am=0;
        }
        if (count($U1GVAL)>0) {
            $u1gm=max($U1GVAL);
        } else {
            $u1gm=0;
        }
        if (count($U2GVAL)>0) {
            $u2gm=max($U2GVAL);
        } else {
            $u2gm=0;
        }
        if ($u1am>0 && $u2am==0) {
            $this->_battlewinner=1;
            // echo '1 winner';
        } elseif ($u1am==0 && $u2am>0) {
            $this->_battlewinner=2;
            // echo '2 winner';
        } elseif ($u1gm>0 && $u2gm==0) {
            $this->_battlewinner=1;
            // echo 'g1 winner';
        } elseif ($u1gm==0 && $u2gm>0) {
            $this->_battlewinner=2;
            // echo 'g2 winner';
        } else {
            $this->_battlewinner=0;
            // echo '0 ';
        }
        $this->_reu1aval=$U1AVAL;
        $this->_reu2aval=$U2AVAL;
        $this->_reu1gval=$U1GVAL;
        $this->_reu2gval=$U2GVAL;      

    }

    private function _transferresource2to1($U1ATID, $U1GTID)
    {
        $tresource=0;
        $getwood=0;
        $getstone=0;
        $getgold=0;
        $getfood=0;
        $airtroopnum=count($U1ATID);
        $groundtroopnum=count($U1GTID);
        if ($airtroopnum>0) {
            $gettroopid=$U1ATID[0];
        } elseif ($groundtroopnum>0) {
            $gettroopid=$U1GTID[0];
        }
        for ($i=0; $i<$airtroopnum; $i++) {
            $tresource+=$this->_carrya[$i]*$this->_reu1aval[$i];
        }
        for ($i=0; $i<$groundtroopnum; $i++) {
            $tresource+=$this->_carryg[$i]*$this->_reu1gval[$i];
        }
        $aresource=floor($tresource/4);
        if ($aresource>0) {

            $enemyResource=$this->_conn->prepare(
                'SELECT RNAME,RVALUE FROM resources WHERE USERID=? AND COLONYID=?'
            );
            $enemyResource->bind_param('ii', $this->_2USERID, $this->_2COLONYID);
            $enemyResource->execute();
            $enemyResourcedat=$enemyResource->get_result();
            $enemyResource->close();
            if ($enemyResourcedat->num_rows>0) {
                while ($row=$enemyResourcedat->fetch_assoc()) {
                    if ($row['RNAME']=='Wood') {
                        if ($row['RVALUE']>$aresource) {
                            $getwood=$aresource;
                        } else {
                            $getwood=$row['RVALUE'];
                        }
                    }
                    if ($row['RNAME']=='Stone') {
                        if ($row['RVALUE']>$aresource) {
                            $getstone=$aresource;
                        } else {
                            $getstone=$row['RVALUE'];
                        }
                    }
                    if ($row['RNAME']=='Gold') {
                        if ($row['RVALUE']>$aresource) {
                            $getgold=$aresource;
                        } else {
                            $getgold=$row['RVALUE'];
                        }
                    }
                    if ($row['RNAME']=='Food') {
                        if ($row['RVALUE']>$aresource) {
                            $getfood=$aresource;
                        } else {
                            $getfood=$row['RVALUE'];
                        }
                    }
                }
                if ($gettroopid>0) {
                    $this->_conn->begin_transaction();
                    $updateenemyresource=$this->_conn->prepare(
                        'UPDATE resources SET RVALUE=RVALUE-? WHERE USERID=? AND COLONYID=? AND RNAME=?'
                    );
                    $updateenemyresource->bind_param(
                        'diis', $newval, $this->_2USERID, $this->_2COLONYID, $newname
                    );
                    $newname='Wood';
                    $newval=$getwood;
                    $updateenemyresource->execute();
                    $newname='Stone';
                    $newval=$getstone;
                    $updateenemyresource->execute();
                    $newname='Gold';
                    $newval=$getgold;
                    $updateenemyresource->execute();
                    $newname='Food';
                    $newval=$getfood;
                    $updateenemyresource->execute();
                    $updateenemyresource->close();
    
                    $addarmytroop1=$this->_conn->prepare(
                        'UPDATE usermissionarmy SET WOOD=WOOD+? 
                        WHERE USERID=? AND COLONYID=? AND ARMY=? AND TROOPID=?'
                    );
                    $addarmytroop1->bind_param(
                        'diiii', $getwood, $this->_1USERID, $this->_1COLONYID, 
                        $this->_ARMYID, $gettroopid
                    );
                    $addarmytroop1->execute();
                    $addarmytroop1->close();
    
                    $addarmytroop2=$this->_conn->prepare(
                        'UPDATE usermissionarmy SET STONE=STONE+? 
                        WHERE USERID=? AND COLONYID=? AND ARMY=? AND TROOPID=?'
                    );
                    $addarmytroop2->bind_param(
                        'diiii', $getstone, $this->_1USERID, $this->_1COLONYID, 
                        $this->_ARMYID, $gettroopid
                    );
                    $addarmytroop2->execute();
                    $addarmytroop2->close();
    
                    $addarmytroop3=$this->_conn->prepare(
                        'UPDATE usermissionarmy SET GOLD=GOLD+? 
                        WHERE USERID=? AND COLONYID=? AND ARMY=? AND TROOPID=?'
                    );
                    $addarmytroop3->bind_param(
                        'diiii', $getgold, $this->_1USERID, $this->_1COLONYID, 
                        $this->_ARMYID, $gettroopid
                    );
                    $addarmytroop3->execute();
                    $addarmytroop3->close();
    
                    $addarmytroop4=$this->_conn->prepare(
                        'UPDATE usermissionarmy SET FOOD=FOOD+? 
                        WHERE USERID=? AND COLONYID=? AND ARMY=? AND TROOPID=?'
                    );
                    $addarmytroop4->bind_param(
                        'diiii', $getfood, $this->_1USERID, $this->_1COLONYID, 
                        $this->_ARMYID, $gettroopid
                    );
                    $addarmytroop4->execute();
                    $addarmytroop4->close();

                    $this->_transferresourcereport='Wood: '.$getwood.' 
                    Stone: '.$getstone.' <br> Gold: '.$getgold.' Food: '.$getfood.'';
    
                    if ($updateenemyresource && $addarmytroop1 
                        && $addarmytroop2 && $addarmytroop3 && $addarmytroop4
                    ) {
                        $this->_conn->commit();
                    } else {
                        $this->_conn->rollback();
                    }

                }
               

            }


        }
       





    }

    private function _updateTroops(
        array $U1GTID, array $U1GVAL,
        array $U1ATID, array $U1AVAL,
        array $U2GTID, array $U2GVAL,
        array $U2ATID, array $U2AVAL
    ) {
        $u1ground=count($U1GTID);
        $u1air=count($U1ATID);
        $u2ground=count($U2GTID);
        $u2air=count($U2ATID);
        $uptroop=true;
        $this->_conn->begin_transaction();
        if ($u1ground>0) {
            $uptroop=$this->_conn->prepare(
                'UPDATE usermissionarmy SET VALUE=? 
                WHERE USERID=? AND COLONYID=? AND ARMY=? AND TROOPID=?'
            );
            $uptroop->bind_param(
                'iiiii', $newvalue, $this->_1USERID,
                $this->_1COLONYID, $this->_ARMYID, $newtid
            );
            for ($i=0; $i<$u1ground; $i++) {
                $newvalue=$U1GVAL[$i];
                $newtid=$U1GTID[$i];
                $uptroop->execute();
            }
            $uptroop->close();
        }

        if ($u1air>0) {
            $uptroop=$this->_conn->prepare(
                'UPDATE usermissionarmy SET VALUE=? 
                WHERE USERID=? AND COLONYID=? AND ARMY=? AND TROOPID=?'
            );
            $uptroop->bind_param(
                'iiiii', $newvalue, $this->_1USERID,
                $this->_1COLONYID, $this->_ARMYID, $newtid
            );
            for ($i=0; $i<$u1air; $i++) {
                $newvalue=$U1AVAL[$i];
                $newtid=$U1ATID[$i];
                $uptroop->execute();
            }
            $uptroop->close();
        }

        if ($u2ground>0) {
            $uptroop=$this->_conn->prepare(
                'UPDATE usertroops SET VALUE=? 
                WHERE USERID=? AND COLONYID=? AND TROOPID=?'
            );
            $uptroop->bind_param(
                'iiii', $newvalue, $this->_2USERID,
                $this->_2COLONYID, $newtid
            );
            for ($i=0; $i<$u2ground; $i++) {
                $newvalue=$U2GVAL[$i];
                $newtid=$U2GTID[$i];
                $uptroop->execute();
            }
            $uptroop->close();
        }

        if ($u2air>0) {
            $uptroop=$this->_conn->prepare(
                'UPDATE usertroops SET VALUE=? 
                WHERE USERID=? AND COLONYID=? AND TROOPID=?'
            );
            $uptroop->bind_param(
                'iiii', $newvalue, $this->_2USERID,
                $this->_2COLONYID, $newtid
            );
            for ($i=0; $i<$u2air; $i++) {
                $newvalue=$U2AVAL[$i];
                $newtid=$U2ATID[$i];
                $uptroop->execute();
            }
            $uptroop->close();
        }

        if ($uptroop) {
            $this->_conn->commit();
            return true;
        } else {
            $this->_conn->rollback();
            return false;
        }

    }

 

}
?>