<?php
use GameEngine\Barrack;

$Troops=new Barrack;
$Troops->produceUserTroop(
    $_SESSION['UID'], CID, WOODVAL, STONEVAL, GOLDVAL, UFOOD, 'Church', 'Church'
);
?>
<div class="row">
            <div class="col-sm w-100 text-center" style="text-align:justify;  letter-spacing: 8px; font-family: 'Cinzel Decorative', cursive;  color: #ecd19a;">
                <h5>Church</h5>           
            </div>
        </div>
<div class="p-2">
<?php
$Troops->getUserTroopProduce($_SESSION['UID'], CID, 'Church');
$Troops->getUserTroops($_SESSION['UID'], CID, 'Church', 'Church');
?>
</div>
<div class="row bottom-wrapper">
    <p></p>
</div>