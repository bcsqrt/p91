<?php

use GameEngine\Units;
?>

<div class="row">
            <div class="col-sm w-100 text-center" style="text-align:justify;  letter-spacing: 8px; font-family: 'Cinzel Decorative', cursive;  color: #ecd19a;">
                <h5>Farm Buildings</h5>           
            </div>
        </div>
<div class="p-2">
<?php
require_once '../inc/autoloader.php';
$UnitQuery=new Units();
$UnitQuery->upUnit(WOODVAL, STONEVAL, GOLDVAL, UFOOD, CID, 'farmunits', 'units', 0);
echo $UnitQuery->getUnits($_SESSION['UID'], CID, 'farmunits', 'units', 0);
?>
</div>
<div class="row bottom-wrapper">
    <p></p>
</div>
