<?php
session_start();
ob_start();
function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
   
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
   
    $bytes /= pow(1024, $pow); 
   
    return round($bytes, $precision) . ' ' . $units[$pow]; 
}
$start = microtime(true);

use UserProcess\User;
// require_once '../classes/User.php';
require_once '../inc/autoloader.php';
require_once '../inc/server.php';
$SessionCheck=new User();
$SessionCheck->CheckSession();
require_once '../inc/getmaps.php';
require_once '../inc/troopupdate.php';
require_once '../inc/getbuildings.php';
require_once '../inc/getresources.php';
require_once '../inc/getmissions.php';
require_once '../view/frame/head.php';
require_once '../view/frame/body.php';
require_once '../view/frame/footer.php';


ob_end_flush();
?>
