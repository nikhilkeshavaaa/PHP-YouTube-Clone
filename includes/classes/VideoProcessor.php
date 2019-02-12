<?php

class VideoProcessor { 
    
    private $con;
    private $sizeLimit = 500000000; //half gig
    private $allowedTypes = array("mp4","flv","webm","mkv","vob","ogv","ogg","avi","wmv","mov","mpeg","mpg"); // Supported file types
    private $ffmpegPath;
    private $ffprobePath;
    
    
    
    public function __construct($con){ 
        $this->con = $con; 
        $this->ffmpegPath = realpath("ffmpeg/bin/ffmpeg.exe"); 
        $this->ffprobePath = realpath("ffmpeg/bin/ffprobe.exe"); 
    }
    
     
    
    public function upload($videoUploadData){
        
        $targetDir = "uploads/videos/"; 
        $videoDate = $videoUploadData->videoDataArray;
        
                        //uplaods/videos/5aa3e9343c9ffdogs_play.flv
        $tempFilePath = $targetDir . uniqid() . basename($videoDate["name"]);
                        
        // Temp file path
        $tempFilePath = str_replace(" ", "_", $tempFilePath); 
        
        $isValidData = $this->processData($videoDate, $tempFilePath); 
        
        if (!$isValidData){ 
            return false; 
        }
        
        // Checks to see is the file uploaded is valid and if so moves it to the correct path
        if (move_uploaded_file($videoDate["tmp_name"], $tempFilePath)){
            
            $finalFilePath = $targetDir . uniqid() . ".mp4"; 

            // Insert Video data into DB
            if (!$this->insertVideoData($videoUploadData, $finalFilePath)){ 
                echo "Insert query failed"; 
                return false; 
            }
                       
            
            // Convert Video to MP4 with ffmpeg 
            if (!$this->convertVideoToMp4($tempFilePath, $finalFilePath)){ 
                echo "Upload Failed\n"; 
                return false; 
            }
            
            // Delete tmp file in Video folder
            if (!$this->deleteFile($tempFilePath)){
                echo "Upload Failed\n";
                return false;
            }

            // Generate thumbnails
            if (!$this->generateThumbnails($finalFilePath)){
                echo "Upload Failed - could not generate thumbnails\n";
                return false;
            }
            
            return true; 
        }
    }
    
    // Check to see if the file is what it says it is
    private function processData($videoData, $filePath){ 
        
        $videoType = pathinfo($filePath, PATHINFO_EXTENSION); 
        
        if (!$this->isValidSize($videoData)){ 
            echo "File to large. Cant be more than " . $this->sizeLimit. "bytes" ; 
            return false; 
        }else if (!$this->isValidType($videoType)){
            echo "Invalid file type"; 
            return false; 
        }else if ($this->hasError($videoData)){
          echo "Error code: " . $videoData["error"]; 
          return false; 
        }               
        
        return true; 
    }
     
    // Check to see is the vide is of size
    private function isValidSize($data){
        return $data["size"] <= $this->sizeLimit;  
    }
    
    // Change all to lowercase
    private function isValidType($type){ 
        $lowercase = strtolower($type); 
        return in_array($lowercase, $this->allowedTypes); 
        
    }
    
    // Checks to see if there are anyother errors
    private function hasError($data){ 
        return $data["error"] != 0; 
    }
    
    // INSERT File data into DB
    private function insertVideoData($uploadData, $filePath){
     
        $query = $this->con->prepare("INSERT INTO videos(title, uploadedBy, description, privacy, category, filePath)
                                        VALUES(:title, :uploadedBy, :description, :privacy, :category, :filePath)");
        
        $query->bindParam(":title", $uploadData->title);
        $query->bindParam(":uploadedBy", $uploadData->uploadedBy);
        $query->bindParam(":description", $uploadData->description);
        $query->bindParam(":privacy", $uploadData->privacy);
        $query->bindParam(":category", $uploadData->category);
        $query->bindParam(":filePath", $filePath);
        
        return $query->execute();
    }
    
    // Call to ffmpeg.exe to convert file to mp4 with errors
    public function convertVideoToMp4($tempFilePath, $finalFilePath){ 
        
        // Call to the ffmpeg file converter 
        $cmd = "$this->ffmpegPath -i $tempFilePath $finalFilePath 2>&1";
        
        // If there are errors add them to the array
        $outputLog = array();
        
        // Execute the commandline 
        exec($cmd, $outputLog, $returnCode);
        
        // If there are errors return false 
        if ($returnCode != 0){ 
            
            // Command Failed
            foreach ($outputLog as $line){
                echo $line . "<br>"; 
            }
            return false;
        }
        
        return true; 
    }
    
    // Delete tmp file from folder
    private function deleteFile($filePath){ 
        if (!unlink($filePath)){ 
            echo "Could not delete file\n";
            return false;         
        }
        
        return true; 
    }
    
    // Generate Thumbnails for each uploaded video
    public function generateThumbnails($filePath){
        
        $thumbnailSize = "210x118"; 
        $numThumbnails = 3; 
        $pathToThumbnail = "uploads/videos/thumbnails"; 
                
        $duration = $this->getVideoDuration($filePath); 
        
        $videoId = $this->con->lastInsertId(); 
        $this->updateDuration($duration, $videoId); 
                
        for ($num = 1; $num <= $numThumbnails; $num++){
            $imageName = uniqid() . ".jpg";
            
            // Take a screen shot of 3 diff spot in the video
            $interval = ($duration * 0.8) / $numThumbnails * $num; 
            $fullThumbnailPath = "$pathToThumbnail/$videoId-$imageName"; 
            
            // Get into video into seconds(-ss)
            $cmd = "$this->ffmpegPath -i $filePath -ss $interval -s $thumbnailSize -vframes 1 $fullThumbnailPath 2>&1";
            
            // If there are errors add them to the array
            $outputLog = array();
            
            // Execute the commandline
            exec($cmd, $outputLog, $returnCode);
            
            // If there are errors return false
            if ($returnCode != 0){
                
                // Command Failed
                foreach ($outputLog as $line){
                    echo $line . "<br>";
                }
                    // Maybe insert default thumbnail                   
            }    
            
            $query = $this->con->prepare("INSERT INTO thumbnails(videoId, filePath, selected)
                                        VALUES(:videoId, :filePath, :selected)");
            $query->bindParam(":videoId", $videoId);
            $query->bindParam(":filePath", $fullThumbnailPath);
            $query->bindParam(":selected", $selected);
            
            $selected = $num == 1 ? 1 : 0; 
            
            $success = $query->execute(); 
            
            if (!$success) { 
                echo "Error inserting thumbnail\n"; 
                return false; 
            }
        }
        
        return true; 
    }
    
    private function getVideoDuration($filePath){        
        return (int)shell_exec("$this->ffprobePath -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $filePath"); 
    }
    
    // Update duration formate to save in DB 
    private function updateDuration($duration, $videoId){ 
        
        // Number of seconds in one hour 3600 
        $hours = floor($duration / 3600); 
        // Get the Min less than an hour
        $mins = floor(($duration - ($hours * 3600)) / 60);
        // Get the Seconds
        $secs = floor($duration % 60 ); 
        
        $hours = ($hours < 1) ? "" : $hours . ":";
        $mins = ($mins < 10) ? "0" . $mins . ":" : $mins . ":";
        $secs = ($secs < 10) ? "0" . $secs : $secs;
        
        // Add the time together
        $duration = $hours . $mins . $secs; 
        
        $query = $this->con->prepare("UPDATE videos SET duration=:duration WHERE id=:videoId"); 
        $query->bindParam(":duration", $duration);
        $query->bindParam(":videoId", $videoId);
        
        $query->execute(); 
        
        
    }
    
    
}