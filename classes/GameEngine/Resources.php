<?php
namespace GameEngine;

use Database\Dbconnect;

require_once '../inc/autoloader.php';

class Resources extends Dbconnect
{
    public function GetUserResources($UID, $RNAME, $CID)
    {
        $UserResource=$this->conn->prepare(
            'SELECT 
            USERID,COLONYID,RNAME,RVALUE,RRATE,RTANK 
            FROM resources WHERE USERID=? AND RNAME=? AND COLONYID=?'
        );
        $UserResource->bind_param('isi', $UID, $RNAME, $CID);
        $UserResource->execute();
        $UserResourceVal=$UserResource->get_result();
        $UserResource->close();
        $row=$UserResourceVal->fetch_assoc();
        return array(
            $row['RVALUE'], $row['RRATE'], $row['RTANK']
        ); 
    }

    public function UsersResourceUpdater()
    {
        $now=time();
        $Getresources=$this->conn->prepare(
            'SELECT ID,USERID,RNAME,RVALUE,RRATE,RLUPDATE,RTANK 
            FROM resources WHERE RLUPDATE<?'
        );
        $Getresources->bind_param('i', $now);
        $Getresources->execute();
        $GetresourcesAr=$Getresources->get_result();
        $Getresources->close();
        if ($GetresourcesAr->num_rows>0) {
            $row=$GetresourcesAr->fetch_assoc();
            $GetFood=$this->conn->prepare(
                'SELECT USERID,RNAME,RVALUE 
                FROM resources WHERE USERID=? AND RNAME="FOOD"'
            );
            $GetFood->bind_param('i', $row['USERID']);
            $GetFood->execute();
            $GetFoodD=$GetFood->get_result();
            $GetFood->close();
            $row2=$GetFoodD->fetch_assoc();
            while ($row=$GetresourcesAr->fetch_assoc()) {
                if ($row['RNAME']!='Food') {
                    if ($row['RVALUE']<$row['RTANK'] && $row2['RVALUE']>=0) {
                        $setvalue
                            =($row['RRATE']*($now-$row['RLUPDATE']))
                        +$row['RVALUE'];
                        if ($setvalue>$row['RTANK']) {
                            $setvalue=$row['RTANK'];
                        }            
                    } else {
                        $setvalue=$row['RVALUE'];
                    }                
                    $UpdateResource=$this->conn->prepare(
                        'UPDATE resources SET RVALUE=?,RLUPDATE=? 
                        WHERE ID=? AND RNAME!="Food"'
                    );
                    $UpdateResource->bind_param('dii', $setvalue, $now, $row['ID']);
                    $UpdateResource->execute();
                    $UpdateResource->close();
                }
            }
        }
    }
    
}


?>