<?php

use GameEngine\Units;
?>
<div class="row">
            <div class="col-sm w-100 text-center" style="text-align:justify;  letter-spacing: 8px; font-family: 'Cinzel Decorative', cursive;  color: #ecd19a;">
                <h5>Defansive Buildings</h5>           
            </div>
        </div>
<div class="p-2">
<?php
require_once '../inc/autoloader.php';
$UnitQuery=new Units();
$blcksmthlvl=$UnitQuery->getuserBlacksmithLVL($_SESSION['UID'], CID);
if ($blcksmthlvl>0) {
    $UnitQuery->upUnit(
        WOODVAL, STONEVAL, GOLDVAL, UFOOD, CID, 
        'defansiveunits', 'defansiveunits', $blcksmthlvl
    );
    echo $UnitQuery->getUnits(
        $_SESSION['UID'], CID, 'defansiveunits', 'defansiveunits', $blcksmthlvl
    ); 
} else {
    echo '<div class="row p-0 m-0">
            <div class="col-sm p-0 m-0">
                <div class="bg-danger rounded p-2 m-0 d-flex justify-content-center">
                    <div class="text-white">
                        <i class="fas fa-exclamation-circle fa-2x"> </i>
                          You have to build a Blacksmith  on your area.
                    </div>
                </div>
            </div>
        </div>';
}
?>
</div>
<div class="row bottom-wrapper">
    <p></p>
</div>
