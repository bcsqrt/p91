<?php

namespace Controller;



class DefaultController
{

    public function __construct()
    {
        if (isset($_GET['p'])) {

            $page=trim($_GET['p']);

            if (file_exists('../view/'.$page.'.php')) {

                include_once '../view/'.$page.'.php';

            }    
                
        } else {
            include_once '../view/main.php';
        }
    }
    
}

?>