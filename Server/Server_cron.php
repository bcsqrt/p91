<?php

require_once '../inc/autoloader.php';

use GameEngine\Missions;
use GameEngine\Resources;
use GameEngine\Units;
use GameEngine\Barrack;

$UserResource=new Resources();
$checkbuildings=new Units();
$troopupdate=new Barrack;

$troopupdate->usersTroopproduce();
$checkbuildings->buildingUnits();
$UserResource->UsersResourceUpdater();
new Missions();

?>
