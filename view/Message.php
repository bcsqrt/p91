<?php

use UserProcess\Message;

$message=new Message();
?>
<div class="container-fluid m-0 p-0">
<div class="row">
            <div class="col-sm w-100 text-center" style="text-align:justify;  letter-spacing: 8px; font-family: 'Cinzel Decorative', cursive;  color: #ecd19a;">
                <h5>Messages</h5>           
            </div>
        </div>
<div class="p-2">
    <div class="row m-2 p-0">
        <button type="button" class="btn btn-warning btn-sm"><i class="fas fa-envelope-open-text"></i></button>
    </div>
    <?php $message->messageList($_SESSION['UID']);?>
</div>
<div class="row bottom-wrapper">
    <p></p>
</div>

</div>
