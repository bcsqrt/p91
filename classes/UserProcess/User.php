<?php

namespace UserProcess;

class User
{
    public function CheckSession()
    {
        if (!isset($_SESSION['LOGIN'])) {
            header('location: login.php');
            exit();
        }

    }

    public function Logout()
    {
        session_destroy();
        header('location: index.php');
        exit();
    }

}

?>