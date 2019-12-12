<?php

namespace UserProcess;
include_once '../inc/autoloader.php';
// require_once 'Dbconnect.php';
// require_once 'Tools.php';

use Database\Dbconnect;
use Otherfunction\Tools;

class Userdb extends Dbconnect
{
    public function UserReg()
    {
        $Tools=new Tools();
        if ($_SERVER['REQUEST_METHOD']=='POST') {
            if (isset($_POST['uname']) && isset($_POST['upass']) 
                && isset($_POST['umail'])
            ) {                
                $uname=$Tools->Forminputcheck($_POST['uname']);                
                $upass=$Tools->Forminputcheck($_POST['upass']);
                $umail=$Tools->Forminputcheck($_POST['umail']);
                if (!empty($uname) && !empty($upass) && !empty($umail)) {
                    if (strlen($uname)>3 && strlen($uname)<16 && strlen($upass)>5 && strlen($upass)<16) {
                        if ($this->CheckUserNameandMail($uname, $umail)) {
                            $RegDate=time();
                            $upass=password_hash(
                                $upass, PASSWORD_DEFAULT
                            );
                            $this->conn->begin_transaction();
                            $RegUser=$this->conn->prepare(
                                'INSERT INTO users (USERID,USERPASS,USERMAIL,REGDATE)
                                VALUES (?,?,?,?)'
                            );
                            $RegUser->bind_param(
                                'sssi', $uname, $upass, $umail, $RegDate
                            );
                            $RegUser->execute();
                            $UID=$RegUser->insert_id;
                            $RegUser->close();
                            $RndmPosition=0;
                            $RndmPosition=rand(1, 10000);
                            $GetMap=$this->conn->prepare(
                                'SELECT ID,MLAT,MLONG,USER,NPC FROM map 
                                WHERE ID=? AND USER=0 AND NPC=0 AND USERID=0'
                            );
                            $GetMap->bind_param('i', $RndmPosition);
                            $GetMap->execute();
                            $GetMapD=$GetMap->get_result();
                            if ($GetMapD->num_rows>0) {
                                $row=$GetMapD->fetch_assoc();
                                $MapID=$RndmPosition;
                                $ColonyLat=$row['MLAT'];
                                $ColonyLong=$row['MLONG'];
                                $Config=true;
                            } else {
                                $Config=false;
                            } 
                            $GetMap->close();
                            $InsertColony=$this->conn->prepare(
                                'INSERT INTO usercolony (USERID,CLAT,CLONG) 
                                VALUES (?,?,?)'
                            );
                            $InsertColony->bind_param(
                                'iii', $UID, $ColonyLat, $ColonyLong
                            );
                            $InsertColony->execute();
                            $ColonyID=$InsertColony->insert_id;
                            $InsertColony->close();

                            $getTroops=$this->conn->prepare(
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
                            $getTroops->bind_param('ii', $UID, $ColonyID);
                            $getTroops->execute();
                            $getTroops->close();
                            
                            $InsertStat=$this->conn->prepare(
                                'INSERT INTO userstats (USERID,COLONYID)
                                VALUES (?,?)'
                            );
                            $InsertStat->bind_param('ii', $UID, $ColonyID);
                            $InsertStat->execute();
                            $InsertStat->close();
                            $InsertMap=$this->conn->prepare(
                                'UPDATE map SET USER=1,COLONYID=?,
                                NPC=0,UNAME=?,USERID=? 
                                WHERE ID=?'
                            );
                            $InsertMap->bind_param(
                                'isii', $ColonyID, $uname, $UID, $MapID
                            );
                            $InsertMap->execute();
                            $InsertMap->close();
                            $NewUserResources=$this->conn->prepare(
                                'INSERT INTO resources 
                                (USERID,COLONYID,RNAME,RVALUE,RLUPDATE)
                                 VALUES (?,?,?,?,?)'
                            );
                            $LastUpdate=time();
                            $NewUserResources->bind_param(
                                'iisdi', $UID, $ColonyID, 
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
                            $GetUnits->bind_param('ii', $UID, $ColonyID);
                            $GetUnits->execute();
                            $GetUnits->close();
                            if ($RegUser && $InsertColony && $NewUserResources 
                                && $GetUnits && $InsertMap && $GetMap && $InsertStat
                                && $getTroops && $Config
                            ) {
                                $this->conn->commit();
                                echo 'Your account has been activated successfully.';
                            } else {
                                $this->conn->rollback();
                                echo 'Some 
                                things got wrong';                                
                            }
                        } else {
                            echo 'Username already exist';
                        }
                    } else {
                        echo 'Length of username and password must 
                        be between 3 and 16';
                    }

                } else {
                    echo 'all fields must be filled';
                }
            } else {
                header ('location: login.php');
            }
        } else {
            header ('location: login.php');
        }

    }

    public function CheckLogin($uname, $upass, $upro)
    {
        $Tools=new Tools;
        $uname=$Tools->Forminputcheck($uname);                
        $upass=$Tools->Forminputcheck($upass);
        $upro=$Tools->Forminputcheck($upro);

        if (!empty($uname) && !empty($upass) && $upro=='login') {

            $CheckUser=$this->conn->prepare(
                'SELECT ID,USERID,USERPASS FROM users 
                WHERE USERID=?'
            );
            $CheckUser->bind_param('s', $uname);
            $CheckUser->execute();
            $userdata=$CheckUser->get_result();
            $CheckUser->close();
            echo $userdata->num_rows;
            if ($userdata->num_rows>0) {
                $row=$userdata->fetch_assoc();
                if (password_verify($upass, $row['USERPASS'])) {
                    ini_set('session.gc_maxlifetime', 31536000);
                    $_SESSION['LOGIN']=true;
                    $_SESSION['UID']=$row['ID'];
                    $_SESSION['UNAME']=$row['USERID'];
                    header('location:index.php');
                } else {
                    echo '<script>alert("Wrong Username or Password!");
                    window.location.href="login.php";</script>';
                }
            } else {
                echo '<script>alert("Wrong Username or Password!");
                window.location.href="login.php";</script>';

            }     
        } else {

            echo '<script>alert("All fields are required");
            window.location.href="login.php";</script>';

        }
    }

    public function CheckUserNameandMail($uname,$umail)
    {
        $checkuser=$this->conn->prepare(
            'SELECT USERID,USERMAIL FROM users 
            WHERE USERID=? OR USERMAIL=?'
        );
        $checkuser->bind_param('ss', $uname, $umail);
        $checkuser->execute();
        $checuserval=$checkuser->get_result();
        $checkuser->close();
        if ($checuserval->num_rows>0) {
            return false;
        } else {
            return true;
        }

    }

    public function userUpgcheck($UID, $CID, $unit)
    {
        $UnitUpCheck=$this->conn->prepare(
            'SELECT USERID,COLONYID,UPGRADE,STTYPE 
            FROM userunits 
            WHERE UPGRADE=1 AND USERID=? AND COLONYID=? AND STTYPE=?'
        );
        $UnitUpCheck->bind_param('iis', $UID, $CID, $unit);
        $UnitUpCheck->execute();
        $UnitUpCheckData=$UnitUpCheck->get_result();
        $UnitUpCheck->close();
        if ($UnitUpCheckData->num_rows>0) {
            return true;
        } else {
            return false;
        }
    }

}


?>