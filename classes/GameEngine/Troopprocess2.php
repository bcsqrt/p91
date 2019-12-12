<?php
namespace GameEngine;

use Database\Dbconnect;
use Otherfunction\Tools;

class Troopprocess2 extends Dbconnect
{
    public $notificationnumber,$notificationdata;

    public function userNotification($UID, $CID)
    {
        $checkarmyactivity=$this->conn->prepare(
            'SELECT ID AS MID,1NAME,1USERID,1COLONYID,CALLBACK,
            1LAT,1LONG,2NAME,2USERID,2USERID,2COLONYID,
            2LAT,2LONG,ETA,DO,DISTANCE,COURSE
            FROM usermissions WHERE 1USERID=? OR 2USERID=? 
            ORDER BY ID ASC'
        );
        $checkarmyactivity->bind_param('ii', $UID, $UID);
        $checkarmyactivity->execute();
        $checkarmyactivitydat=$checkarmyactivity->get_result();
        $checkarmyactivity->close();
        if ($checkarmyactivitydat->num_rows>0) {
            $this->notificationdata=array();
            $this->notificationnumber=1;
            while ($row=$checkarmyactivitydat->fetch_assoc()) {
                $eta=$row['ETA']-time();
                $now=time()-50;
                if ($row['1USERID']==$UID 
                    && $row['ETA']>=$now && $row['CALLBACK']==0
                ) {
                    $callback='<a 
                    href="?p=tmanagement&co='.$CID.'&mid='.$row['MID'].'">
                    <button type=\'button\' 
                    class=\'btn btn-dark btn-sm pl-1 pr-1 pt-0 pb-0\'>
                    <i class=\'fas fa-exchange-alt\'></i></button></a>';
                } else {
                    $callback='';
                }

                if ($row['CALLBACK']==0) {
                    $directionicon='<i class=\'fas fa-angle-double-right\'></i>';
                    $lat1=$row['1LAT'];
                    $long1=$row['1LONG'];
                    $lat2=$row['2LAT'];
                    $long2=$row['2LONG'];
                } else {
                    $directionicon='<i class=\'fas fa-angle-double-left\'></i>';
                    $lat1=$row['2LAT'];
                    $long1=$row['2LONG'];
                    $lat2=$row['1LAT'];
                    $long2=$row['1LONG'];
                }

                array_push(
                    $this->notificationdata, 
                    '<tr>
                        <th scope=\'row\'>'.$callback.' 
                        <i class=\'fas fa-envelope fa-xs\'></i>
                        </th>
                        <td><span class=\'text-Secondary\'>
                        '.$row['1NAME'].'</span></td>
                        <td><span class=\'text-Secondary\'>
                        '.$row['1COLONYID'].'</span></td>
                        <td><span class=\'text-Secondary\'>
                        '.$lat1.'-'.$long1.'</span></td>
                        <td>'.$directionicon.'</td>
                        <td><span class=\'text-danger\'>
                        '.$row['2NAME'].'</span></td>
                        <td><span class=\'text-danger\'>
                        '.$row['2COLONYID'].'</span></td>
                        <td><span class=\'text-danger\'>
                        '.$lat2.'-'.$long2.'</span></td>
                        <td class=\'text-center\'><b>'.$row['DO'].'</b></td>
                        <td class=\'text-center\'>'.$row['DISTANCE'].' nm</td>
                        <td class=\'text-center\'>'.$row['COURSE'].'<sup>0</sup></td>
                        <td class=\'text-center\'><b>'.$eta.'</b></td>
                    </tr>
                    '
                );
            }

        } else {
            $this->notificationnumber=0;
        }
    }

    public function callbackTroops($UID, $MID)
    {
        $time=time()-50;
        $Tools=new Tools();
        $checmission=$this->conn->prepare(
            'SELECT * FROM usermissions WHERE ID=? AND 1USERID=? AND ETA>=? AND CALLBACK=0'
        );
        $checmission->bind_param('iii', $MID, $UID, $time);
        $checmission->execute();
        $checmissiondat=$checmission->get_result();
        $checmission->close();
        if ($checmissiondat->num_rows>0) {
            $now=time();
            $row=$checmissiondat->fetch_assoc();
            $voyagetime=time()-$row['ETD'];
            $returndistance=round(($voyagetime/3600)*$row['SPEED'], 2);
            $returnposition=$Tools->calculatePositiontogo(
                $row['1LAT'], $row['1LONG'], $returndistance, $row['COURSE']
            );
            $course=($row['COURSE']+180)%360;
            $eta=$voyagetime+$now;
            $updatemission=$this->conn->prepare(
                'UPDATE usermissions 
                SET 1LAT=?,1LONG=?,2USERID=?,2COLONYID=?,2LAT=?,
                2LONG=?,ETD=?,ETA=?,DISTANCE=?,COURSE=?,
                DO="Return",CALLBACK=1
                WHERE ID=? AND 1USERID=? AND ETA>=? AND CALLBACK=0'
            );
            $updatemission->bind_param(
                'iiiiiiiiddiii', $returnposition[0], $returnposition[1], 
                $row['1USERID'], $row['1COLONYID'], $row['1LAT'],
                $row['1LONG'], $now, $eta,
                $returndistance, $course, $MID, $UID, $time
            );
            $updatemission->execute();
            $updatemission->close();
        } 

        header('location: index.php');

    }
}
?>