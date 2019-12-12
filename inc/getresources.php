<?php
use GameEngine\Resources;
$UserResource=new Resources();
$UserResource->UsersResourceUpdater();
/*0:Value,1:Rate,2:Tank*/  
$UserResArWood=$UserResource->GetUserResources($_SESSION['UID'], 'Wood', CID);
$UserResArStone=$UserResource->GetUserResources($_SESSION['UID'], 'Stone', CID);
$UserResArGold=$UserResource->GetUserResources($_SESSION['UID'], 'Gold', CID);
$UserResArFood=$UserResource->GetUserResources($_SESSION['UID'], 'Food', CID);
define('UFOOD', $UserResArFood[0]);
define('WOODTANK', $UserResArWood[2]);
define('WOODVAL', $UserResArWood[0]);
define('STONETANK', $UserResArStone[2]);
define('STONEVAL', $UserResArStone[0]); 
define('GOLDTANK', $UserResArGold[2]);
define('GOLDVAL', $UserResArGold[0]);
?>