<?php
    require_once 'includes/header.php';
    require_once 'includes/classes/Account.php';
    require_once 'includes/classes/formSanitizer.php';
    require_once 'includes/classes/Constants.php';
    require_once 'includes/classes/SettingsFormProvider.php';
    
    if (!User::isLoggedIn()){
         header("Locations: signIn.php"); 
    }

        $detailsMessage = ""; 
        $passwordMessage = "";         
        $formProvider = new SettingsFormProvider();
        
        if (isset($_POST["saveDetailsButton"])){
            $account = new Account($con); 
            
            $firstName = FormSanitizer::sanatizeFormString($_POST["firstName"]); 
            $lastName = FormSanitizer::sanatizeFormString($_POST["lastName"]);
            $email = FormSanitizer::sanatizeFormString($_POST["email"]);
            
            if ($account->updateDetails($firstName, $lastName, $email, $userLoggedInObj->getUsername())){
                
               $detailsMessage = "<div class='alert alert-success'>
                                     <strong>SUCCESS!</strong> Details updated successfully!
                                  </div>";                    
                   
            }else { 
                $errorMessage = $account->getFirstError(); 
            
                if ($errorMessage == ""){
                    $errorMessage = "Something went worng"; 
                }
                
                $detailsMessage = "<div class='alert alert-danger'>
                                     <strong>ERROR!</strong> $errorMessage
                                  </div>"; 
            }
        }
        
        if (isset($_POST["savePasswordButton"])){
            
            $account = new Account($con);
            
            $oldPassword = FormSanitizer::sanatizeFormPassword($_POST["oldPassword"]);
            $newPassword = FormSanitizer::sanatizeFormPassword($_POST["newPassword"]);
            $newPassword2 = FormSanitizer::sanatizeFormPassword($_POST["newPassword2"]);
            
            
            if ($account->updatePassword($oldPassword, $newPassword, $newPassword2, $userLoggedInObj->getusername())){
                
                $passwordMessage = "<div class='alert alert-success'>
                                     <strong>SUCCESS!</strong> Password updated successfully!
                                  </div>";
                
            }else {
                $errorMessage = $account->getFirstError();
                
                if ($errorMessage == ""){
                    $errorMessage = "Something went worng";
                }
                
                $passwordMessage = "<div class='alert alert-danger'>
                                     <strong>ERROR!</strong> $errorMessage
                                  </div>";
            }
        }
        
    ?>


	<div class="settingContainer column">
		<div class="formSection">
			<div class="message"><?php echo $detailsMessage?></div>
			<?php 
			 
			  echo $formProvider->createUserDetailsForm(
    			  isset($_POST["fistName"]) ? $_POST["fistName"] : $userLoggedInObj->getFirstName(),
			      isset($_POST["lastName"]) ? $_POST["lastName"] : $userLoggedInObj->getLastName(),
			      isset($_POST["email"]) ? $_POST["email"] : $userLoggedInObj->getEmail()
			      ); 
			?>
		</div>
		
		<div class="formSection">
		<div class="message"><?php echo $passwordMessage?></div>
			<?php 
			 echo $formProvider->createPasswordForm(); 
			?>
		</div>
	</div>