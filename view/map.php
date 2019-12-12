
<?php

use GameEngine\Map;

require_once '../inc/autoloader.php';

if (isset($_GET['lat']) && isset($_GET['long'])) {
    if (is_numeric($_GET['lat']) && is_numeric($_GET['long'])) {
        $Lat=$_GET['lat'];
        $Long=$_GET['long'];

    } else {
        $Lat=UMLAT;
        $Long=UMLONG;
    }
} else {
    $Lat=UMLAT;
    $Long=UMLONG;
}
$Getmap=new Map();
?>
<div class="row">
            <div class="col-sm w-100 text-center" style="text-align:justify;  letter-spacing: 8px; font-family: 'Cinzel Decorative', cursive;  color: #ecd19a;">
                <h5>Universe</h5>           
            </div>
        </div>
<div class="p-2">
<div class="d-flex justify-content-center">
<form method="GET" action="index.php?p=map" id="mapform" colony="<?php echo CID;?>">
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text" id="maphome" lat="<?php echo UMLAT; ?>" long="<?php echo UMLONG; ?>"><i class="fas fa-home"></i></span>
            <span class="input-group-text" id="mapup"><i class="fas fa-angle-double-up"></i></span>
        </div>
        <input type="text" class="form-control text-center font-weight-bold" id="maplat" name="lat" value="<?php echo $Lat; ?>">
        <div class="input-group-append">
            <span class="input-group-text" id="mapdown"><i class="fas fa-angle-double-down"></i></span>
        </div>  

        <div class="input-group-prepend">
            <span class="input-group-text" id="mapleft"><i class="fas fa-angle-double-left"></i></span>
        </div>
        <input type="text" class="form-control text-center font-weight-bold" id="maplong" name="long" value="<?php echo $Long; ?>">
        <div class="input-group-append">
            <span class="input-group-text" id="mapright"><i class="fas fa-angle-double-right"></i></span>
        </div>
        <div class="input-group-append">
            <input type="submit" class="btn btn-danger" value="Go">
        </div>  
    </div>
    <input type="hidden" name="p" value="map">
    <!-- <input type="hidden" name="co" value="<?php echo CID;?>"> -->
</form>
</div>
<div id="mapdialog" title="">
  
</div>

<?php
$Getmap->getMap($Lat, $Long, $_SESSION['UID']);
// $Getmap->createMap();
// $Getmap->createDungeon();
?>
</div>
<div class="row bottom-wrapper">
    <p></p>
</div>







