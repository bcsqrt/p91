<?php
namespace UserProcess;

use Database\Dbconnect;

class Message extends Dbconnect
{
    public $totalmsg;

    public function totalmsg($UID)
    {
        $totalmsg=$this->conn->prepare(
            'SELECT COUNT(ID) AS TOTALMSG,UTO FROM message WHERE UTO=?'
        );
        $totalmsg->bind_param('i', $UID);
        $totalmsg->execute();
        $totalmsgdat=$totalmsg->get_result();
        $totalmsg->close();
        $row=$totalmsgdat->fetch_assoc();
        $this->totalmsg=$row['TOTALMSG'];
    }

    public function messageList($UID)
    {
        $getList=$this->conn->prepare(
            'SELECT * FROM message WHERE UTO=? ORDER BY ID DESC lIMIT 10'
        );
        $getList->bind_param('i', $UID);
        $getList->execute();
        $getListdat=$getList->get_result();
        if ($getListdat->num_rows>0) {
            while ($row=$getListdat->fetch_assoc()) {
                if ($row['UFROM']==0) {
                    $sender='Report';
                } else {
                    $sender=$row['UFROM'];
                }
                echo ' 
            <div class="row m-2 p-2 text-light border rounded" style="background-image: linear-gradient(rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.55)), url("img/dark_wall.png");">
                <div class="col-2  m-0 mr-2 p-0 " 
                style="border-width:2px !important;">
                    <b>'.$sender.'</b>
                    <hr class="m-0 mt-1 mr-2 p-0 border ">
                    '.$row['SUBJECT'].'
                </div>
                <div class="col mr-2 p-2 border-left border-right 
                " style="border-width:2px !important;">
                '.$row['MESSAGE'].'
                </div>
                <div class="col-sm-1 m-auto p-auto text-center ">
                    <div class="btn-group">
                        <button type="button" 
                        class="btn btn-warning btn-sm">
                        <i class="fas fa-trash-alt fa-sm"></i></button>
                        <button type="button" 
                        class="btn btn-warning btn-sm">
                        <i class="fas fa-reply fa-sm"></i>
                        </button>               
                    </div> 
                </div>
            </div>    ';
            }
            
        } 
    }

    public function createMessage($from, $to, $subject, $message)
    {
        $now=time();
        $send=$this->conn->prepare(
            'INSERT INTO message (UFROM,UTO,SUBJECT,MESSAGE,DATE) VALUES (?,?,?,?,?)'
        );
        $send->bind_param('iissi', $from, $to, $subject, $message, $now);
        $send->execute();
        $send->close();
    }
}
?>