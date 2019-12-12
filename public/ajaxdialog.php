<?php
session_start();
use UserProcess\User;
include_once '../inc/autoloader.php';
$SessionCheck=new User();
$SessionCheck->CheckSession();
echo ' Dialog';
?>