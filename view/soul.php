<?php

use GameEngine\Souls;

$usersouls=new Souls($_SESSION['UID'], CID);
?>
<div class="Soul p-2 h-100">
<?php
if (isset($_GET['use']) && isset($_GET['type'])) {
    $usersouls->soulRandom($_GET['use'], $_GET['type']);
}
?>
    <div class="row">
        <div class="col-sm" style="text-align:justify;">
            <h5>Dungeon of Souls - Sin and Innocence</h5>
            <p class="text-white">&nbsp; A woman gave birth to two boys, Innocence and Sin. Innocence was well behaved, while Sin was disobedient. When their mother broke the bread, she allowed Innocence to eat his fill, as a reward of his honest nature. Sin was cast the scraps to remind him of his bad behaviour. One day, Sin stole a fish, and beat his brother until he promised not to tell it to anyone. But Innocence could not keep his promise, and bore witness to the Mother of Two. It was decided between the mother and Innocence that sin was beyond redemption, and Sin was burned. While Sin became ash and smoke, he moved into the watching villagers. Sin forced the people turn on each other, and in the struggle the villagers formed a giant of Sin. Innocence realised that the village was lost and burned it to the ground. "As town and titan burned, the sky turned dark with the ash of Sin. There, amongst the raging ruins of his home, Innocence swore an oath. No matter where the ashes of Sin fell, his purifying flames would rise to meet them."</p>
            <div class="row">
                <div class="col-sm" style="text-align:justify;">
                    <h5>Catched Souls</h5>
                    <p>
                        <div class="carousel slide dragonic-carousel p-2 rounded">
                            <div class="row">
                                <div class="col-3">
                                    <?php
                                    if ($usersouls->userSoultypes(1)==1) {
                                        echo '<a href="?p=soul&co='.CID.'&use='.$usersouls->soulid.'&type=1" style="color:#ecd19a;">
                                            <img src="img/character-1.jpg" class="w-100 fimage" style="opacity: 0.8;">
                                        </a>';
                                    } else {
                                        echo '<img src="img/character-1.jpg" class="w-100 fimage" style="opacity: 0.2;"></a>';
                                    }
                                    ?>
                                </div>
                                <div class="col-3">
                                <?php
                                    if ($usersouls->userSoultypes(2)==2) {
                                        echo '<a href="?p=soul&co='.CID.'&use='.$usersouls->soulid.'&type=2" style="color:#ecd19a;">
                                            <img src="img/character-2.jpg" class="w-100 fimage" style="opacity: 0.8;">
                                        </a>';
                                    } else {
                                        echo '<img src="img/character-2.jpg" class="w-100 fimage" style="opacity: 0.2;"></a>';
                                    }
                                    ?>
                                </div>
                                <div class="col-3">
                                <?php
                                    if ($usersouls->userSoultypes(3)==3) {
                                        echo '<a href="?p=soul&co='.CID.'&use='.$usersouls->soulid.'&type=3" style="color:#ecd19a;">
                                            <img src="img/character-3.jpg" class="w-100 fimage" style="opacity: 0.8;">
                                        </a>';
                                    } else {
                                        echo '<img src="img/character-3.jpg" class="w-100 fimage" style="opacity: 0.2;"></a>';
                                    }
                                    ?>
                                </div>
                                <div class="col-3">
                                <?php
                                    if ($usersouls->userSoultypes(4)==4) {
                                        echo '<a href="?p=soul&co='.CID.'&use='.$usersouls->soulid.'&type=4" style="color:#ecd19a;">
                                            <img src="img/character-4.jpg" class="w-100 fimage" style="opacity: 0.8;">
                                        </a>';
                                    } else {
                                        echo '<img src="img/character-4.jpg" class="w-100 fimage" style="opacity: 0.2;"></a>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-sm-3 mr-2">
        <?php
            if ($usersouls->userSoultypes(5)==5) {
                echo '<a href="?p=soul&co='.CID.'&use='.$usersouls->soulid.'&type=5" style="color:#ecd19a;">
                    <img src="img/soul.jpg" class="w-100 fimage" style="opacity: 0.8;">
                </a>';
            } else {
                echo '<img src="img/soul.jpg" class="w-100 fimage" style="opacity: 0.2;"></a>';
            }
            ?>
        </div>
    </div>
<div class="row bottom-wrapper">
    <p></p>
</div>
</div>
