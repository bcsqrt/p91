<?php

use GameEngine\Colony;
$getColonyList=new Colony();
if (isset($_GET['gco'])) {
    if (is_numeric($_GET['gco'])) {
        if ($_GET['gco']>0) {
            $getColonyList->getNewColony(
                $_GET['gco'], $_SESSION['UID'], CID, MCOLONY, $_SESSION['UNAME']
            );
        }
    }
}
if (isset($_GET['ab']) && isset($_GET['co'])) {
    if ($_GET['ab']==1 && is_numeric($_GET['co'])) {
        if ($_GET['co']>0) {
            $getColonyList->abandonColony($_GET['co'], $_SESSION['UID']);
        }
    }
}
?>
<div class="row">
            <div class="col-sm w-100 text-center" style="text-align:justify;  letter-spacing: 8px; font-family: 'Cinzel Decorative', cursive;  color: #ecd19a;">
                <h5>Colonys</h5>           
            </div>
        </div>
<div class="p-2">
<?php
$getColonyList->getColony($_SESSION['UID'], CID);
?>
</div>
<div id="AbandonColony" colony="">
    Are you sure? You are about to leave this area.
</div>
<div class="row bottom-wrapper">
    <p></p>
</div>