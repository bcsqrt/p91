<?php
use UserProcess\Userdb;
use Server\Server;
use GameEngine\Colony;
// require_once '../classes/Userdb.php';
require_once '../inc/autoloader.php';
$CheckServer=new Server;
if (!$CheckServer->CheckServer(0)) {
    die('Server is offline');
}
if (isset($_GET['p'])) {
    $page=$_GET['p'];
    if ($page=='MapDialog') {
        // id':$id colonyid,'do':'details
        if ($_POST['do']=='details') {
            if (isset($_POST['id']) && is_numeric($_POST['id'])) {
                $getColonyDetail=new Colony;
                $getColonyDetail->getColonydetails($_POST['id']);

            }
        }
    }
} else {
    $user=new Userdb();
    $user->UserReg();
}
?>