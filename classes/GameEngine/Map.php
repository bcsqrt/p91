<?php

namespace GameEngine;

use Database\Dbconnect;

class Map extends Dbconnect 
{

    public function getMap($Lat, $Long, $UID)
    {
        if ($Lat<=3) {
            $Latmin=1;
            $Latmax=7;                   
        } elseif ($Lat>97) {
            $Latmin=94;
            $Latmax=100;                                      
        } else {
            $Latmin=$Lat-3;
            $Latmax=$Lat+3;  
        }

        if ($Long<=3) {
            $Longmin=1;
            $Longmax=7;
        } elseif ($Long>97) {
            $Longmin=94;
            $Longmax=100;                    
        } else {
            $Longmin=$Long-3;
            $Longmax=$Long+3;
        }

        $ShowMaxLat=UMLAT+UMRANGE;
        $ShowMaxLong=UMLONG+UMRANGE;

        $ShowMinLat=UMLAT-UMRANGE;
        $ShowMinLong=UMLONG-UMRANGE;


        $GetMap=$this->conn->prepare(
            'SELECT * FROM map WHERE MLAT BETWEEN ? AND ? AND MLONG BETWEEN ? AND ?'
        );
        $GetMap->bind_param(
            'iiii', $Latmin, $Latmax, $Longmin, $Longmax
        );
        $GetMap->execute();
        $GetMapData=$GetMap->get_result();
        $GetMap->close();
        $MapRow=1;
        while ($row=$GetMapData->fetch_assoc()) {  
            $Title='';
            $Target='';         
            if ($MapRow==1) {
                echo '<div class="row p-0 m-0 d-flex justify-content-center">';
            }

            if (($row['MLAT']<=$ShowMaxLat && $row['MLONG']<=$ShowMaxLong 
                && $row['MLAT']>=$ShowMinLat && $row['MLONG']>=$ShowMinLong)
                || $row['USERID']==$UID
            ) {
                if ($row['USER']==1) {
                    $Title=$row['UNAME'];
                    $Target='<img src="img/units/townhallmap.png">';
                } elseif ($row['NPC']==1) {
                    $Title='NPC';
                    $Target='<img src="img/units/npc.png">';
                } else {
                    $Title='Empty Area';
                }
                if ($row['MLAT']==UMLAT && $row['MLONG']==UMLONG) {
                    echo '<div id="'.$row['ID'].'" 
                    title="'.$Title.'" area="'.$row['ID'].'"
                     class="col p-1 m-0 mapuser maptarget">
                    <img src="img/units/townhallmap.png">
                    <span class="badge badge-pill float-right">
                    '.$row['MLAT'].'-'.$row['MLONG'].'</span>
                    </div>';

                } elseif ($Lat==$row['MLAT'] && $Long==$row['MLONG']) {
                    echo '<div id="'.$row['ID'].'" 
                    title="'.$Title.'" area="'.$row['ID'].'" 
                    class="col p-1 m-0 mapsec maptarget">
                    '.$Target.'
                    <span class="badge badge-pill float-right">
                    '.$row['MLAT'].'-'.$row['MLONG'].'</span></div>';

                } else {
                    echo '<div id="'.$row['ID'].'" 
                    title="'.$Title.'" area="'.$row['ID'].'" 
                    class="col p-1 m-0 map maptarget">
                    '.$Target.'
                    <span class="badge badge-pill float-right">
                    '.$row['MLAT'].'-'.$row['MLONG'].'</span></div>';
                }

            } else {
                // out of range
                echo '<div id="'.$row['ID'].'" 
                title="Unidentified" class="col p-1 m-0 mapun">
                <span class="badge badge-pill float-right">
                '.$row['MLAT'].'-'.$row['MLONG'].'</span></div>';
            }
    
            if ($MapRow==7) {
                echo '</div>';
                $MapRow=0;
            }
            $MapRow++;
        }
     
    }

    public function createMap() 
    {
        // Max Latitude=9 (Each Page)
        // Max Longitude=8 (Each Page)
        $Lat=1;
        $CreateMap=$this->conn->prepare(
            'INSERT INTO map (MLAT,MLONG,DESCP) VALUES (?,?,?)'
        );
        $CreateMap->bind_param('iis', $Lat, $Long, $Desc);
        for ($Long=1; $Long<101; $Long++) {
                                               
            $Desc=''.$Lat.' '.$Long.'';
            $CreateMap->execute();
            if ($Long==100 && $Lat<100) {
                $Lat=$Lat+1;
                $Long=0;
            }
        }
        $CreateMap->close();
    }

    public function createDungeon()
    {
        for ($i=0; $i<1001; $i++) {
            $dungeongenerate=$this->conn->prepare(
                'UPDATE map SET NPC=1 
                WHERE USER=0 AND USERID=0 AND COLONYID=0 
                AND NPC=0 ORDER BY RAND() LIMIT 1'
            );
            $dungeongenerate->execute();
            $dungeongenerate->close();
        }
    }

    public function getUserposition($UID, $CID) 
    {
        if ($CID>=1) {
            $Getposition=$this->conn->prepare(
                'SELECT usercolony.ID AS COLONYID,usercolony.USERID,usercolony.CLAT,
                usercolony.CLONG,userstats.USERID,userstats.MRANGE,
                userstats.MCOLONY 
                FROM usercolony INNER JOIN userstats ON 
                userstats.USERID=usercolony.USERID AND 
                userstats.COLONYID=usercolony.ID
                WHERE userstats.USERID=? AND usercolony.ID=?'
            );
            $Getposition->bind_param('ii', $UID, $CID);
        } else {
            $Getposition=$this->conn->prepare(
                'SELECT usercolony.ID AS COLONYID,usercolony.USERID,usercolony.CLAT,
                usercolony.CLONG,userstats.USERID,userstats.MRANGE,
                userstats.MCOLONY 
                FROM usercolony INNER JOIN userstats ON 
                userstats.USERID=usercolony.USERID 
                WHERE userstats.USERID=?'
            );
            $Getposition->bind_param('i', $UID);
        }
        $Getposition->execute();
        $Data=$Getposition->get_result();
        if ($Data->num_rows<=0) {
            header ('location: index.php');
        }
        $Getposition->close();
        $row=$Data->fetch_assoc();
        return array(
            $row['CLAT'], $row['CLONG'], $row['COLONYID'],
            $row['MRANGE'], $row['MCOLONY']
        );
    }

    public function colonyselectmenu($CID, $UID)
    {
        $menu=$this->conn->prepare(
            'SELECT ID,USERID,CLAT,CLONG FROM usercolony WHERE USERID=?'
        );
        $menu->bind_param('i', $UID);
        $menu->execute();
        $menud=$menu->get_result();
        $menu->close();
        while ($row=$menud->fetch_assoc()) {
            if ($row['ID']==$CID) {
                echo '<option value="'.$row['ID'].'" selected>
                Area-'.$row['ID'].' '.$row['CLAT'].'-'.$row['CLONG'].'</option>';
            } else {
                echo '<option value="'.$row['ID'].'">
                Area-'.$row['ID'].' '.$row['CLAT'].'-'.$row['CLONG'].'</option>';
            }
        }
    }

    public function checkMapinfo($lat, $long)
    {
        $checkmap=$this->conn->prepare(
            'SELECT MLAT,MLONG,USER,UNAME,NPC FROM map WHERE MLAT=? AND MLONG=?'
        );
        $checkmap->bind_param('ii', $lat, $long);
        $checkmap->execute();
        $checkmapdat=$checkmap->get_result();
        $checkmap->close();
        if ($checkmapdat->num_rows>0) {
            $row=$checkmapdat->fetch_assoc();
            if ($row['USER']==1) {
                return $row['UNAME'];
            } elseif ($row['NPC']==1) {
                return 'NPC';
            } else {
                return 'Empty Area';
            }
        } else {
            return '---';
        }
    }

}

?>