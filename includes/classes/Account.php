<?php

class Account { 
    
    private $con; 
    private $errorArray = array(); 
    
    public function __construct($con){ 
    
        $this->con = $con; 
    }
    
    // Check users login information
    public function login($un, $pw){
        $pw = hash("sha512", $pw);
        
        $query = $this->con->prepare("SELECT * FROM users WHERE username=:un AND password=:pw"); 
        $query->bindParam(":un", $un); 
        $query->bindParam(":pw", $pw);
        
        $query->execute(); 
        
        if ($query->rowCount() == 1){
            return true;     
        }else{ 
            array_push($this->errorArray, Constants::$loginFailed); 
            return false;
        }
        
    }
    
    
    public function register($fn, $ln, $un, $em, $em2, $pw, $pw2){ 
        
        $this->validateFirstName($fn);
        $this->validateLastName($ln);
        $this->validateUsername($un);
        $this->validateEmails($em,$em2);
        $this->validatePasswords($pw, $pw2); 
                
        
        if (empty($this->errorArray)){ 
    
            return $this->insertUserDetails($fn, $ln, $un, $em, $pw); 
            
        }else { 
            return false;
        }
    }
    
    public function updateDetails($fn, $ln, $em, $un){ 
        
        $this->validateFirstName($fn); 
        $this->validateLastName($ln); 
        $this->validateNewEmail($em, $un); 
        
        if (empty($this->errorArray)){
            
            $query = $this->con->prepare("UPDATE users SET firstName = :fn, lastName = :ln, email = :em  WHERE username =:un"); 
            $query->bindParam(":fn", $fn);
            $query->bindParam(":ln", $ln);
            $query->bindParam(":em", $em);
            $query->bindParam(":un", $un);
            
            return $query->execute(); 
            
        }else {
            return false;
        }
    }
    
    public function updatePassword($oldPw, $pw, $pw2, $un) {
        $this->validateOldPassword($oldPw, $un);
        $this->validatePasswords($pw, $pw2);
        
        if(empty($this->errorArray)) {
            $query = $this->con->prepare("UPDATE users SET password=:pw WHERE username=:un");
            $pw = hash("sha512", $pw);
            $query->bindParam(":pw", $pw);
            $query->bindParam(":un", $un);
            
            return $query->execute();
        }
        else {
            return false;
        }
    }
    
    private function validateOldPassword($oldPw, $un) {
        $pw = hash("sha512", $oldPw);
        
        $query = $this->con->prepare("SELECT * FROM users WHERE username=:un AND password=:pw");
        $query->bindParam(":un", $un);
        $query->bindParam(":pw", $pw);
        
        $query->execute();
        
        if($query->rowCount() == 0) {
            array_push($this->errorArray, Constants::$passwordIncorrect);
        }
    }
    // INSERTs into the new users info into the database
    public function insertUserDetails($fn, $ln, $un, $em, $pw){ 
     
        $pw = hash("sha512", $pw); 
        $proFilePic = "assets/images/profilePictures/default.png"; 
        
        $query = $this->con->prepare("INSERT INTO users (firstName, lastName, username, email, password, profilePic)
                                      VALUES(:fn,:ln,:un,:em,:pw,:pic)"); 
        $query->bindParam(":fn",$fn);
        $query->bindParam(":ln",$ln);
        $query->bindParam(":un",$un);
        $query->bindParam(":em",$em);
        $query->bindParam(":pw",$pw);
        $query->bindParam(":pic",$proFilePic);
        
        return $query->execute(); 
        
    }
    
    // Validate user fast name
    private function validateFirstName($fn){ 
        if (strlen($fn) > 25 || strlen($fn) < 2){ 
            array_push($this->errorArray, Constants::$firstNameCharacters);          
        }
    }
    
    // Validate user last name
    private function validateLastName($ln){
        if (strlen($ln) > 25 || strlen($ln) < 2){
            array_push($this->errorArray, Constants::$lastNameCharacters);
        }
    }
    
    // Validat if the username is not being used and meets the requirements
    private function validateUsername($un){

        if(strlen($un) > 25 || strlen($un) < 5) {
            array_push($this->errorArray, Constants::$usernameCharacters);
            return;
        }

        $query = $this->con->prepare("SELECT username FROM users WHERE username=:un");
        $query->bindParam(":un", $un); 
        $query->execute(); 
        
        if ($query->rowCount() != 0){ 
            array_push($this->errorArray, Constants::$usernameTaken);            
        }
    }
    
    // Validate Users emails are the same and not already being used
    private function validateEmails($em, $em2){
        if ($em != $em2){ 
            array_push($this->errorArray, Constants::$emailsDoNotMatch);
            return; 
        }
        
        if (!filter_var($em, FILTER_VALIDATE_EMAIL)){ 
            array_push($this->errorArray, Constants::$emailInvalid);
            return;
        }
        
        $query = $this->con->prepare("SELECT email FROM users WHERE email=:em");
        $query->bindParam(":em", $em);
        $query->execute();
        
        if ($query->rowCount() != 0){
            array_push($this->errorArray, Constants::$emailTaken);
        }
    }
    
    // Validate Users emails are the same and not already being used
    private function validateNewEmail($em, $un){

        
        if (!filter_var($em, FILTER_VALIDATE_EMAIL)){
            array_push($this->errorArray, Constants::$emailInvalid);
            return;
        }
        
        $query = $this->con->prepare("SELECT email FROM users WHERE email=:em AND username != :un");
        $query->bindParam(":em", $em);
        $query->bindParam(":un", $un);
        $query->execute();
        
        if ($query->rowCount() != 0){
            array_push($this->errorArray, Constants::$emailTaken);
        }
    }
    
    // Validate User Passwords
    private function validatePasswords($pw, $pw2){        
        
        if ($pw != $pw2){
            array_push($this->errorArray, Constants::$passwordsDoNotMatch);
            return;
        }
       
        // Check to make sure the password is letters and numbers only
        if (preg_match("/[^A-Za-z0-9]/", $pw)){
            array_push($this->errorArray, Constants::$passwordNotAlphanumeric);
            return;
        }
        
        // can onlt be between 2 and 30 chars 
        if (strlen($pw) > 30 || strlen($pw) < 5){
            array_push($this->errorArray, Constants::$passwordLength);           
        }              
    }
    
    // Array to hold all the errors to display to the form
    public function getError($error){ 
        if (in_array($error, $this->errorArray)){ 
            return "<span class='errorMessage'>$error</span>";
        }
    }
    
    public function getFirstError(){
        if (!empty($this->errorArray)){
            return $this->errorArray[0]; 
        }else { 
            return ""; 
        }
    }
}




?>