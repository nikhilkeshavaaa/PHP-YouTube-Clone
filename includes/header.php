<?php
    require_once 'includes/config.php';
    require_once 'includes/classes/ButtonProvider.php';
    require_once 'includes/classes/User.php';
    require_once 'includes/classes/Video.php';
    require_once 'includes/classes/VideoGrid.php';
    require_once 'includes/classes/VideoGridItem.php';
    require_once 'includes/classes/SubscriptionsProvider.php';
    require_once 'includes/classes/NavigationMenuProvider.php';
    
    $usernameLoggedIn = User::isLoggedIn();
    $userLoggedInObj = new User($con, $usernameLoggedIn);
    
    //session_destroy(); 
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
		<script src="assets/js/commonActions.js"></script>
		<script src="assets/js/userActions.js"></script>
	</head>
	<body>

		<div id="pageContainer">
			<!-- Master head container -->
			<div id="mastHeadContainer">
				<button class="navShowHide">					
					<img class="menuBars" src="assets/images/icons/menu.png">
				</button>
				
				<a class="logoContainer" href="index.php">
					<img src="assets/images/icons/VideoTubeLogo.png" title="logo" alt="siteLogo">
				</a>
				
				<div class="searchBarContainer">
					<form method="GET" action="search.php">
						<input type="text" class="searchBar" name="term" placeholder="Search" />
						<button class="searchButton">
							<img src="assets/images/icons/search.png" />
						</button>
					</form>
				</div>
				
				<div class="rightIcons">
					<a href="upload.php">
						<img class="uplaod" src="assets/images/icons/upload.png" />
					</a>
					<?php 
					   echo ButtonProvider::createUserProfileNavigationButton($con, $userLoggedInObj->getUsername()); 
					?>
				</div>
				
				
			</div>
			<!-- Side Nav container -->
			<div id="sideNavContainer" style="display: none;">
				<?php 
				    $navigationProvider = new NavigationMenuProvider($con, $userLoggedInObj); 
				    echo $navigationProvider->create(); 
				?>
			</div>
			
			<!-- Main Section container -->
			<div id="mainSectionContainer">
			
				<div id="mainContentContainer">