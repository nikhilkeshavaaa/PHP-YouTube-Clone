<?php

    ob_start(); // Turns on output-buffering
    date_default_timezone_set("America/Chicago");
    session_start(); 
    
    try {
        $con = new PDO("mysql:dbname=VideoTube;host=localhost", "admin", "passWord");
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); 
    }catch (PDOException $e){
        echo "Connection failed: " . $e->getMessage();
    }
?>