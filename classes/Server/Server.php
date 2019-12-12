<?php

namespace Server;

use Database\Dbconnect;

require_once '../inc/autoloader.php';

class Server extends Dbconnect
{
    public function CheckServer($Deamon)
    {
       /*  $Check=$this->conn->prepare(
            'SELECT ID,SERVERSTATUS,DEAMONSTATUS 
             FROM server 
             WHERE ID=1'
        );
        $Check->execute();
        $Data=$Check->get_result();
        $Check->close();
        if ($Data->num_rows>0) {
            $row=$Data->fetch_assoc();
            if ($Deamon==1) {
                if ($row['SERVERSTATUS']==1 && $row['DEAMONSTATUS']==1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if ($row['SERVERSTATUS']==1) {
                    return true;
                } else {
                    return false;
                }               
            }

        } else {
            return false;
        } */
        return true;
    }

    public function MakeServerOffline($ITERATION, $MEMORYUSAGE, $OFFLINEMSG)
    {
        $Offline=$this->conn->prepare(
            'UPDATE server SET SERVERSTATUS=0,DEAMONSTATUS=0,
            ITERATION=?,MEMORYUSAGE=?,OFFLINEMSG=? WHERE ID=1'
        );
        $Offline->bind_param(
            'ids', $ITERATION, $MEMORYUSAGE, $OFFLINEMSG
        );
        $Offline->execute();
        $Offline->close();

    }

    public function MakeServerOnline()
    {
        $command = '/usr/local/bin/php /Server/Server_Daemons.php';
        exec($command, $return, $varre);
    }
}




?>