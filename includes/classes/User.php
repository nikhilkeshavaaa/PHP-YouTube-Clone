<?php

class User { 
    
    private $con; 
    private $sqlData; 
    
    public function __construct($con, $username){         
        $this->con = $con;          
        
        $query = $this->con->prepare("SELECT * FROM users WHERE username =:un"); 
        $query->bindParam(":un", $username); 
        $query->execute(); 
        
        $this->sqlData = $query->fetch(PDO::FETCH_ASSOC); 
         
    }
    
    public static function isLoggedIn(){ 
        return isset($_SESSION["userLoggedIn"]) ? $_SESSION["userLoggedIn"] : "";
    }
    
    // Get the username for session
    public function getUsername(){         
        return $this->sqlData["username"]; 
    }
    
    // Get the First and Last name for session
    public function getName(){
        return $this->sqlData["firstName"] . " " . $this->sqlData["lastName"];
    }
    
    public function getFirstName() {
        return $this->sqlData["firstName"];
    }
    
    // Get the First name for session
    public function getLastName(){
        return $this->sqlData["lastName"] ;
    }
    
    // Get the email for session
    public function getEmail(){
        return $this->sqlData["email"];
    }
    
    // Get the Profile Pic path for session
    public function getProfilePic(){
        return $this->sqlData["profilePic"];
    }
    
    // Get the Sign Up date for session
    public function getSignUpDate(){
        return $this->sqlData["signUpDate"];
    }
    
    // check if use is subscribed
    public function isSubscribedTo($userTo){
       
        $query = $this->con->prepare("SELECT * FROM subscribers WHERE userTo = :userTo AND userFrom = :userFrom");
        $query->bindParam(":userTo", $userTo); 
        $query->bindParam(":userFrom", $username); 
        $username = $this->getUsername(); 
        
        $query->execute(); 
        
        return $query->rowCount() > 0; 
    }
    
    // return total number of subscribers 
    public function getSubscriberCount(){
        
        $query = $this->con->prepare("SELECT * FROM subscribers WHERE userTo = :userTo");
        $query->bindParam(":userTo", $username);       
        $username = $this->getUsername();
        
        $query->execute();
        
        return $query->rowCount();
    }
    
    public function getSubscriptions() {
        
        $query = $this->con->prepare("SELECT userTo FROM subscribers WHERE userFrom=:userFrom");
        $username = $this->getUsername();
        $query->bindParam(":userFrom", $username);
        $query->execute();
        
        $subs = array();
        while($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($this->con, $row["userTo"]);
            array_push($subs, $user);
        }
        return $subs;
    }
}





?>