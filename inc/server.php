<?php
use Server\Server;
$CheckServer=new Server();
if (!$CheckServer->CheckServer(1)) {
    die('Server is offline');
}
?>