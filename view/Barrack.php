<?php

use GameEngine\Barrack;

$Troops=new Barrack;
$Troops->produceUserTroop(
    $_SESSION['UID'], CID, WOODVAL, STONEVAL, GOLDVAL, UFOOD, 'Barracks', 'Barrack'
);
?>
<div class="row">
            <div class="col-sm w-100 text-center" style="text-align:justify;  letter-spacing: 8px; font-family: 'Cinzel Decorative', cursive;  color: #ecd19a;">
                <h5>Barrack</h5>           
            </div>
        </div>
<div class="p-2">
<?php
$Troops->getUserTroopProduce($_SESSION['UID'], CID, 'Barracks');
$Troops->getUserTroops($_SESSION['UID'], CID, 'Barracks', 'Barrack');
?>
</div>
<div class="row bottom-wrapper">
    <p></p>
</div>