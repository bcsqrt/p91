<?php
session_start();
// header('Content-type: application/json');

use GameEngine\Troopprocess;
use UserProcess\User;
// require_once '../classes/User.php';
require_once '../inc/autoloader.php';
require_once '../inc/server.php';
$SessionCheck=new User();
$SessionCheck->CheckSession();
$TroopPro=new Troopprocess();
if ($TroopPro->checkPostelement()) {
    $TroopPro->userData($_SESSION['UID'], $_SESSION['UNAME']);
    
} else {
    echo 'no';
} 

/* echo $_SESSION['UID'].'<br>';
print_r($_POST); */
// 
// $return_arry['result']='error';
// echo json_encode($return_arry);

?>