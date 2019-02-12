<?php

class FormSanitizer { 
    
    public static function sanatizeFormString($inputText){
        $inputText = strip_tags($inputText);
        $inputText = str_replace(" ", "", $inputText);
        $inputText = ucfirst(strtolower($inputText));
        return $inputText;
        
    }
    
    public static function sanatizeFormUsername($inputText){
        $inputText = strip_tags($inputText);
        $inputText = str_replace(" ", "", $inputText); 
        return $inputText;
        
    }
    
    public static function sanatizeFormPassword($inputText){
        $inputText = strip_tags($inputText);
        return $inputText;
        
    }
    
    public static function sanatizeFormEmail($inputText){
        $inputText = strip_tags($inputText);
        $inputText = str_replace(" ", "", $inputText);
        return $inputText;
        // There is a PHP method that will check for vaild email 
    }
}

?>