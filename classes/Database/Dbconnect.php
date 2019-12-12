<?php

namespace Database;

include_once '../inc/definitions.php';


use mysqli;

class Dbconnect
{
    public $conn;

    public function __construct()
    {
        $conn=new mysqli(DBHOST, DBUSERNAME, DBPASS, DBNAME, DBPORT);
        $conn->set_charset('utf8');
        if ($conn->connect_error) {
            die($conn->connect_error);
        }
        $this->conn=$conn;
    }

    public function __destruct()
    {
        $this->conn->close();
    }


}

?>