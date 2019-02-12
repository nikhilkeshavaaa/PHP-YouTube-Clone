<?php 
    require_once 'includes/config.php';
    require_once 'includes/classes/Account.php';
    require_once 'includes/classes/Constants.php';
    require_once 'includes/classes/formSanitizer.php';
    
    $account = new Account($con);  
    
    if (isset($_POST["submitButton"])){
        
        $username = $userName = FormSanitizer::sanatizeFormUsername($_POST["username"]); 
        $password = $password = FormSanitizer::sanatizeFormPassword($_POST["password"]);
        
        $wasSuccessful = $account->login($username, $password); 
        
        if ($wasSuccessful){
            
            $_SESSION["userLoggedIn"] = $userName;
            header("Location: index.php");
            
        }else {
            
        }
    }
    
    function getInputValue($name){
        if (isset($_POST[$name])){
            echo $_POST[$name];
        }
    }
?>
<!DOCTYPE html>
<html>
	<head>
		<title>VideoTube</title>
		
		<!-- CSS -->
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
		<link rel="stylesheet" type="text/css" href="assets/css/style.css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
		<!-- JS scripts  -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
		
	</head>
	<body>
	
		<div class="signInContainer">
			<div class="column">
				<div class="header">
					<img src="assets/images/icons/VideoTubeLogo.png" title="logo" alt="siteLogo">
					<h3>Sign In</h3>
					<span>to continue to VideoTube</span>
				</div>
				
					<div class="loginForm">						
						<form action="signIn.php" method="POST">
							<?php echo $account->getError(Constants::$loginFailed); ?>
							<input type="text" name="username" placeholder="Username" autocomplete="off" value="<?php echo getInputValue('username'); ?>" required>
							<input type="password" name="password" placeholder="Password" required>
							<input type="submit" name="submitButton" value="SUBMIT">
						</form>
					</div>
					
					<a href="signUp.php" class="signInMessage">Need an account? Sign up here</a>
				
				
			</div>
		</div>
    </body>
    </html>