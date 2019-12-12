<?php
use Controller\DefaultController;
use GameEngine\Barrack;
use UserProcess\Userdb;
use GameEngine\Map;
use GameEngine\Troopprocess2;
use UserProcess\Message;

include_once '../inc/autoloader.php';
$notification=new Troopprocess2;
$notification->userNotification($_SESSION['UID'], CID);
$totalmsg=new Message;
$totalmsg->totalmsg($_SESSION['UID']);
?>
<!-- Body Tag -->
<body class="m-2">
    <div id="user-dialog-panel">
                     
    </div>
    <div class="container p-0 m-auto ">

        <div class="row " >

            <div class="col-sm-3 bg-dark p-0 " style="border-top-left-radius: 2px; ">
                <!-- Logo -->
                <div class="row">
                    <div class="col m-2 p-2 text-center"><img src="img/logo.png" class="w-75 bb "></div>
                </div>
                <!-- User Control Menu -->
                <div class="row">
                    <div class="col">
                        <div class="p-2 btn-group float-right">
                            <button class="btn btn-warning" id="btnprofile"><i class="fas fa-user"></i></button>
                            <button class="btn btn-warning" id="btnmessage" colony="<?php echo CID; ?>"><i class="fas fa-envelope fa-lg"></i><?php if ($totalmsg->totalmsg>0)  echo '<sup><span class="badge badge-danger badge-pill">'.$totalmsg->totalmsg.'</span></sup>'; ?></button>
                            <button class="btn btn-warning" id="btnsettings"><i class="fas fa-cog"></i></button>
                            <button class="btn btn-warning" id="btnlogout"><i class="fas fa-sign-out-alt"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col mt-2 mb-2 mr-2 ">
                        <div class="float-right">
                            <form>
                                <select class="custom-select" id="colonyselect" name="co">
                                    <?php
                                    $MapSelect = new Map();
                                    $MapSelect->colonyselectmenu(CID, $_SESSION['UID']);
                                    ?>
                                </select>
                                <input type="hidden" id="pagesec" value="<?php
                                if (isset($_GET['p'])) {
                                    $p=$_GET['p'];
                                } else { 
                                    $p='main';
                                }
                                echo $p;
                                ?>
                                ">
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Notification Area -->
                <?php
                if ($notification->notificationnumber>0) {
                    echo '<div class="row m-0 p-1">
                    <div class="col text-white m-0 p-2 text-center">
                        <button class=" w-100 notbutton" data-toggle="collapse" data-target="#notification">
                        <i class="fas fa-exclamation-triangle fa-2x" ></i></button> 
                    </div> 
                </div>';
                }
                ?>
                <!-- Main Menu -->
                <div class="row">
                    <div class="col">
                        <nav class=" navbar navbar-expand-md navbar-dark">
                            <button class="navbar-toggler" data-toggle="collapse" data-target="#menu"><span class=" navbar-toggler-icon"></span></button>
                            <div class="collapse navbar-collapse " id="menu">
                                    <div class="list-group list-group-flush text-left w-100" style="font-family: 'Cinzel Decorative', cursive; letter-spacing: 3px;color: #ecd19a;">                    
                                            <a href="#" id="Btmain" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white ">Main Page </a>
                                            <a href="#" id="Btmanagement" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Troops Management </a>
                                            <a href="#" id="Btunits" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Farm Buildings
                                            <?php
                                            $buildcheck=new Userdb();
                                            if ($buildcheck->userUpgcheck($_SESSION['UID'], CID, 'farmunits')) {
                                                echo '<span title="Troop Number" class="badge badge-danger badge-pill">1</span>';
                                            }
                                            ?>
                                            </a>
                                            <a href="#" id="Btmilitaryunits" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Military Buildings
                                            <?php
                                            $buildcheck=new Userdb();
                                            if ($buildcheck->userUpgcheck($_SESSION['UID'], CID, 'militaryunits')) {
                                                echo '<span title="Troop Number" class="badge badge-danger badge-pill">1</span>';
                                            }
                                            ?>                                            
                                            </a>
                                            <a href="#" id="Btdefansiveunits" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Defansive Buildings 
                                            <?php
                                            $buildcheck=new Userdb();
                                            if ($buildcheck->userUpgcheck($_SESSION['UID'], CID, 'defansiveunits')) {
                                                echo '<span title="Troop Number" class="badge badge-danger badge-pill">1</span>';
                                            }
                                            ?>     
                                            </a>
                                            <a href="#" id="Btcivilunits" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Civil Buildings 
                                            <?php
                                            $buildcheck=new Userdb();
                                            if ($buildcheck->userUpgcheck($_SESSION['UID'], CID, 'civilunits')) {
                                                echo '<span title="Troop Number" class="badge badge-danger badge-pill">1</span>';
                                            }
                                            ?>     
                                            </a>
                                            <a href="#" id="Btmap" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Universe <span title="Range" class="badge badge-danger badge-pill"><?php echo UMRANGE; ?></span></a>
                                            <a href="#" id="Btcolony" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Colony <span title="Colony Number" class="badge badge-danger badge-pill"><?php echo MCOLONY; ?></span></a>
                                            <a href="#" id="Btbarrack" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Barrack 
                                                <?php
                                                $tropcheck=new Barrack();
                                                $count=$tropcheck->usercheckPro(
                                                    $_SESSION['UID'], CID, 'Barracks'
                                                );
                                                if ($count>0) {
                                                    echo '<span title="Troop Number" class="badge badge-danger badge-pill">'.$count.'</span>';
                                                }
                                                ?>
                                            </a>
                                            <a href="#" id="Btchurch" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Church
                                                <?php
                                                $tropcheck=new Barrack();
                                                $count=$tropcheck->usercheckPro(
                                                    $_SESSION['UID'], CID, 'Church'
                                                );
                                                if ($count>0) {
                                                    echo '<span title="Troop Number" class="badge badge-danger badge-pill">'.$count.'</span>';
                                                }
                                                ?>
                                            </a>
                                            <a href="#" id="Btsoul" colony="<?php echo CID; ?>" class="list-group-item list-group-item-action bg-dark align-items-center text-white">Souls</a>

                    
                                    </div>

                            </div>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-sm-9 p-0 ">
                <div class=" bg-dark align-self-center rounded-right p-1">
                    <!-- Resources -->
                    <table class=" m-auto  text-center text-white" style="font-size:11px;">
                        <tr>
                            <td class="pl-5"><img src="img/Icons/INV_TradeskillItem_03.png" class="w-50" title="Wood"></td>
                            <td class="pl-5"><img src="img/Icons/INV_Stone_07.png" class="w-50" title="Stone"></td>
                            <td class="pl-5"><img src="img/Icons/Gold_01.png" class="w-50" title="Gold"></td>
                            <td class="pl-5"><img src="img/Icons/INV_Misc_Food_71.png" class="w-50" title="Food"></td>
                        </tr>
                        <tr>
                            <td class="pl-5"><?php echo ''.WOODVAL.' / '.WOODTANK.''; ?></td>
                            <td class="pl-5"><?php echo ''.STONEVAL.' / '.STONETANK.''; ?></td>
                            <td class="pl-5"><?php echo ''.GOLDVAL.' / '.GOLDTANK.''; ?></td>
                            <td class="pl-5"><?php echo UFOOD; ?></td>
                        </tr>
                    </table>                           
                </div>
                <!-- Notification Div -->
        
                <div class="p-1 m-0 collapse text-dark " id="notification" style=" background-image: linear-gradient(rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.55)), url(img/dark_wall.png);">
                    <div class="bg-white m-4 p-2 notificationarea p-2 rounded">
                        <div class="table-responsive-sm">
                            <table class="table table-striped table-bordered m-0 text-center w-100">
                                <thead >
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Area</th>
                                        <th scope="col">Position</th>
                                        <th scope="col"></th>
                                        <th scope="col">Target Name</th>
                                        <th scope="col">Target Area</th>
                                        <th scope="col">Target Position</th>
                                        <th scope="col">Mission</th>
                                        <th scope="col">Distance</th>
                                        <th scope="col">Course</th>
                                        <th scope="col">Arrival</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (is_array($notification->notificationdata)) {
                                        $countnotification=count($notification->notificationdata);
                                        for ($i=0; $i<$countnotification; $i++) {
                                            echo $notification->notificationdata[$i];
                                        } 
                                    }

                                    ?>
                                </tbody>    
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Page Div -->
                <div class="m-0 p-0 h-100" id="view" style=" background-image: linear-gradient(rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.55)), url(img/dark_wall.png);">
                    <div class="Divider m-0">
                        <hr class="Divider-rule">
                    </div>
                    <div class="m-0">
                    <?php
                        new DefaultController();
                    ?>
                    </div>
                </div>
            </div>
        </div> 