<?php
use UserProcess\Userdb;
use UserProcess\User;
use Server\Server;

if (isset($_GET['p'])) {
    $page=$_GET['p'];
    include_once '../inc/autoloader.php';
    if ($page==='logcheck') {
        $CheckServer=new Server;
        if (!$CheckServer->CheckServer(0)) {
            die('Server is offline');
        }
        if (isset($_POST['Lusername']) && isset($_POST['Lpass'])) {
            session_start();
            // require_once '../classes/Userdb.php';
            $login=new Userdb();
            $login->CheckLogin(
                $_POST['Lusername'], $_POST['Lpass'], 'login'
            );          
        }
    }

    if ($page==='logout') {
        session_start();
        // require_once '../classes/User.php';
        $logout=new User();
        $logout->Logout();
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Planet91 || Login</title>
    <link rel="shortcut icon" href="img/Icon.1_07.png" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/439aff7575.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="  crossorigin="anonymous"></script>    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>
	<script src="js/loginpanel.js"></script>
	<link rel="stylesheet" href="css/jquery-ui.css" >
	<link rel="stylesheet" href="css/loginpanel.css">
</head>
<body>
    <div class="container h-100">
        <div class="d-flex justify-content-center h-100">
            <div class="user_card">
                <div class="d-flex justify-content-center">
                    <div class="brand_logo_container">
                        <img src="img/Icon.1_07.png" class="brand_logo" alt="Logo">
                    </div>
                </div>
                <div class="d-flex justify-content-center form_container">
                <form id="Loginform" method="post" action="login.php?p=logcheck">
                        <div class="input-group mb-3">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                                <input type="text" name="Lusername" id="Lusername" class="form-control" value="" placeholder="Username">
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-append">
                                <span class="input-group-text"><i class="fas fa-key"></i></span>
                            </div>
                            <input type="password" name="Lpass" id="Lpass" class="form-control" value="" placeholder="Password">
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="customControlInline">
                                <label class="custom-control-label" for="customControlInline">Remember me</label>
                            </div>
                        </div>
				</div>
					<div class="d-flex justify-content-center mt-3 login_container">
                        <input type="submit" name="button" id="Lbutton" class="btn login_btn" value="Login">
                    </div>
                </form>	
                <div class="mt-4">
                    <div class="d-flex justify-content-center links">
                        Don't have an account? <a href="#" class="ml-2" id="create-user">Sign Up</a>
                    </div>
                    <div class="d-flex justify-content-center links">
                        <a href="#">Forgot your password?</a>
                    </div>
                </div>
            </div>
        </div>
	</div>
	
	<div id="dialog-form" title="Create new user">
		<p class="validateTips"></p>
		<form>
			<div class="input-group mb-3">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="fas fa-user"></i></span>
				</div>
				<input type="text" name="name" id="name" class="form-control" value="" placeholder="Username">
			</div>
			<div class="input-group mb-3">
					<div class="input-group-prepend">
						<span class="input-group-text"><i class="fas fa-at"></i></span>
					</div>
					<input type="text" name="email" id="email" class="form-control input_mail" value="" placeholder="E-Mail">
			</div>
			<div class="input-group mb-2">
				<div class="input-group-prepend">
					<span class="input-group-text"><i class="fas fa-key"></i></span>
				</div>
				<input type="password" name="password" id="password" class="form-control" value="" placeholder="Password">
				<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
			</div>
		</form>
		   		
	</div>

	
</body>
</html>