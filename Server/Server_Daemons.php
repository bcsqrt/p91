<?php

require_once '../inc/autoloader.php';

use GameEngine\Missions;
use GameEngine\Resources;
use GameEngine\Units;
use Server\Server;
use GameEngine\Barrack;

$UserResource=new Resources();
$checkbuildings=new Units();
$troopupdate=new Barrack;
$Server=new Server();

set_time_limit(0);

$sleep_time = 1;

$mem_limit = 52428800;

$iteration = 0;
$time=time();
$reloop=$time+250;

while (true) {
   
    $iteration++;
    if (time()==$reloop) {
        $komut = '/usr/local/bin/php Server/Server_Daemons.php';
        exec($komut, $return, $varre);
        die();
    } else {
        if ($Server->CheckServer(1)) {

            if (memory_get_usage(true) >= $mem_limit) {
                $Server->MakeServerOffline(
                    $iteration, memory_get_usage(true), 'Server is Offline'
                );
                die();
            } else {
                $troopupdate->usersTroopproduce();
                $checkbuildings->buildingUnits();
                $UserResource->UsersResourceUpdater();
                new Missions();
                sleep($sleep_time);
            }
    
        } else {
            die();
        }
    }
    echo $iteration;
}
$Server->MakeServerOffline(
    $iteration, memory_get_usage(true), 'Server is Offline'
);
?>