
<?php

use GameEngine\Troopprocess2;
use GameEngine\Troops;

if (isset($_GET['mid'])) {
    if (is_numeric($_GET['mid'])) {
        if ($_GET['mid']>0) {
            $returntroop=new Troopprocess2;
            $returntroop->callbackTroops($_SESSION['UID'], $_GET['mid']);
        }
    }
}
?>
<div class="p-2">
        <div class="row">
            <div class="col-sm w-100 text-center" style="text-align:justify;  letter-spacing: 8px; font-family: 'Cinzel Decorative', cursive;  color: #ecd19a;">
                <h5>Troop Management</h5>           
            </div>
        </div>
<?php
$Troop= new Troops();
$Troop->firstStep($_SESSION['UID'], CID, WOODVAL, STONEVAL, GOLDVAL, UFOOD);
$Troop->userTrooplist(CID);
$Troop->secondStep($_SESSION['UID'], CID, $_SESSION['UNAME'], UMLAT, UMLONG);
$Troop->thirdStep(CID);
?>
</div>
<div id="troopdialog" title="Result">
</div>

<div class="row bottom-wrapper">
    <p></p>
</div>



 