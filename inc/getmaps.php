<?php

use GameEngine\Map;

$GetUserMap=new Map();
if (isset($_GET['co'])) {
    if (is_numeric($_GET['co'])) {
        $UserPosition=$GetUserMap->getUserposition($_SESSION['UID'], $_GET['co']);
    } else {
        $UserPosition=$GetUserMap->getUserposition($_SESSION['UID'], 0);
    }
} else {
    $UserPosition=$GetUserMap->getUserposition($_SESSION['UID'], 0);
}
define('UMLAT', $UserPosition[0]);
define('UMLONG', $UserPosition[1]);
define('CID', $UserPosition[2]);
define('UMRANGE', $UserPosition[3]);
define('MCOLONY', $UserPosition[4]);
?>