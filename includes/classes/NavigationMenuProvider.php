<?php

class NavigationMenuProvider{ 
    
    private $con; 
    private $userLoggedInObj;
    
    
    public function __construct($con, $userLoggedInObj){ 
        $this->con = $con; 
        $this->userLoggedInObj = $userLoggedInObj; 
    }
    
    public function create(){ 
        
        $menuHTML = $this->createNavItem("Home", "assets/images/icons/home.png", "index.php"); 
        $menuHTML .= $this->createNavItem("Trending", "assets/images/icons/trending.png", "trending.php"); 
        $menuHTML .= $this->createNavItem("Subscriptions", "assets/images/icons/subscriptions.png", "subscriptions.php");
        $menuHTML .= $this->createNavItem("Liked Video", "assets/images/icons/thumb-up.png", "likedVideos.php");
        
        if (User::isLoggedIn()){
            $menuHTML .= $this->createNavItem("Settings", "assets/images/icons/settings.png", "settings.php");
            $menuHTML .= $this->createNavItem("Log Out", "assets/images/icons/logout.png", "logout.php");
            
            $menuHTML .= $this->createSubscriptionsSection();
        }               
        
        return "<div class='navigationItems'>
                    $menuHTML
                </div>"; 
    }
    
    private function createNavItem($text, $icon, $link){ 
        
        return "<div class='navigationItem'>
                    <a href='$link'>
                        <img src='$icon'>
                        <span>$text</span>
                    </a>
                </div>"; 
    }
    
    private function createSubscriptionsSection(){ 
        $subscriptions = $this->userLoggedInObj->getSubscriptions();
        
        $html = "<span class='heading'>Subscriptions</span>";
        
        foreach ($subscriptions as $sub){ 
            $subUsername = $sub->getUsername();  
            $html .= $this->createNavItem($sub->getUsername(), $sub->getProfilePic(), "profile.php?username=$subUsername");
        }
        
        return $html;
    }
    
}
 


?>