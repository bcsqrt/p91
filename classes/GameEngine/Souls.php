<?php

namespace GameEngine;

use Database\Dbconnect;

class Souls
{
    private $_conn;
    private $_UID, $_CID;
    public $soulid;

    public function __construct($UID, $CID)
    {
        $this->_UID=$UID;
        $this->_CID=$CID;
    }

    public function userSoultypes($type=0, $soulid=0)
    {
        $dbcon=new Dbconnect;
        $this->_conn=$dbcon->conn;
        if ($soulid==0) {
            $gettype=$this->_conn->prepare(
                'SELECT ID,BPRATE FROM userblueprints 
                WHERE USERID=? 
                AND COLONYID=? AND BPRATE=?'
            );
            $gettype->bind_param('iii', $this->_UID, $this->_CID, $type);
        } else {
            $gettype=$this->_conn->prepare(
                'SELECT ID,BPRATE FROM userblueprints 
                WHERE USERID=? 
                AND COLONYID=? AND BPRATE=? AND ID=?'
            );
            $gettype->bind_param('iiii', $this->_UID, $this->_CID, $type, $soulid);
        }
        $gettype->execute();
        $gettypedat=$gettype->get_result();
        $gettype->close();
        if ($gettypedat->num_rows>0) {
            $row=$gettypedat->fetch_assoc();
            $this->soulid=$row['ID'];
            return $row['BPRATE'];
        } else {
            return 0;
        } 
    }

    public function soulRandom($soulid, $type)
    {
        if ($this->userSoultypes($type, $soulid)>0) {
            $dbcon=new Dbconnect;
            $this->_conn=$dbcon->conn;
            $tid=rand(1, 100);
            $getusertroop=$this->_conn->prepare(
                'SELECT ID,TNAME,TIMG FROM troops WHERE ID=?'
            );
            $getusertroop->bind_param('i', $tid);
            $getusertroop->execute();
            $getusertroopdat=$getusertroop->get_result();
            $getusertroop->close();
            if ($getusertroopdat->num_rows>0) {
                $userstroop=$this->_conn->prepare(
                    'SELECT USERID FROM usertroops
                    WHERE USERID=? AND COLONYID=? AND TROOPID=?'
                );
                $userstroop->bind_param('iii', $this->_UID, $this->_CID, $tid);
                $userstroop->execute();
                $userstroopdat=$userstroop->get_result();
                $userstroop->close();
                if ($userstroopdat->num_rows>0) {
                    echo ' <div class="row">

                    <div class="col-sm mb-2 text-center">
                        <i class="fas fa-times fa-4x"></i>
                    </div>
                </div>';
                } else {
                    $row=$getusertroopdat->fetch_assoc();
                    $getTroops=$this->_conn->prepare(
                        'INSERT INTO usertroops 
                        (USERID,COLONYID,TROOPID,TNAME,TIMG,TDESCP,
                        DAMAGE,ARMOR,SPEED,CARRY,AIR,WOOD,STONE,GOLD,
                        FOOD,TIME,BLEVEL,TROOPTYPE,ORDERT,SPY,ATTACK,
                        TRADE,COLONIZATION,DEPLOYMENT,RESEARCH) 
                        SELECT ?,?,ID,TNAME,TIMG,TDESCP,DAMAGE,ARMOR,
                        SPEED,CARRY,AIR,WOOD,STONE,GOLD,FOOD,TIME,
                        BLEVEL,TROOPTYPE,ORDERT,SPY,ATTACK,TRADE,
                        COLONIZATION,DEPLOYMENT,RESEARCH FROM troops WHERE ID=?'
                    );
                    $getTroops->bind_param('iii', $this->_UID, $this->_CID, $tid);
                    $getTroops->execute();
                    $getTroops->close();
                    echo '    <div class="row">

                    <div class="col-sm mb-2 text-center">
                            <img src="'.$row['TIMG'].'" title="'.$row['TNAME'].'">
                        </div>
                    </div>';
                }
            } else {
                echo ' <div class="row">

                <div class="col-sm mb-2 text-center">
                    <i class="fas fa-times fa-4x"></i>
                </div>
            </div>';
            }
            $delsould=$this->_conn->prepare(
                'DELETE FROM userblueprints 
                WHERE ID=? AND USERID=? AND COLONYID=?'
            );
            $delsould->bind_param('iii', $soulid, $this->_UID, $this->_CID);
            $delsould->execute();
            $delsould->close();
        }
    }
}

?>