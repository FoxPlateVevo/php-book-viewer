<?php

class file{
    public static function cleanName($fileName){
        $search = [" "];
        $fileNameModified = str_replace($search, "_", $fileName);
        
        return $fileNameModified;
    }
    
    public static function cleanPath($path){
        $search = ["//", "../", "./"];
        $pathModified = str_replace($search, "", $path);

        while($pathModified[0] === "/"){
            $array = str_split($pathModified, 1);
            array_shift($array);
            $pathModified = implode("", $array);
        }

        while($pathModified[strlen($pathModified) - 1] === "/"){
            $array = str_split($pathModified, 1);
            array_pop($array);
            $pathModified = implode("", $array);
        }

        return $pathModified;
    }
    
    public static function isImage($fileName){
        $IMAGE_EXTENSIONS = ["jpg", "png", "gif"];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        return in_array($fileExtension, $IMAGE_EXTENSIONS);
    }

    public static function isVideo($fileName){
        $VIDEO_EXTENSIONS = [
            "3g2", "3gp", "aaf", "asf", "avchd", "avi", "drc", "flv", "m2v",
            "m4p", "m4v", "mkv", "mng", "mov", "mp2", "mp4", "mpe", "mpeg",
            "mpg", "mpv", "mxf", "nsv", "ogg", "ogv", "qt", "rm", "rmvb", "roq",
            "svi", "vob", "webm", "wmv", "yuv"
        ];

        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        return in_array($fileExtension, $VIDEO_EXTENSIONS);
    }
    
    public static function upload($inputName, $directoryDestinyPath){
        $file = $_FILES[$inputName];
        
        $fileError      = $file["error"];
        $fileName       = $file["name"];
        $fileSize       = $file["size"];
        $fileTempName   = $file["tmp_name"];
        $fileType       = $file["type"];
        
        $fileName = self::cleanName($fileName);
        
        !is_file($directoryDestinyPath) && !is_dir($directoryDestinyPath) && folder::create($directoryDestinyPath);
        
        $fullDestinyPath = "{$directoryDestinyPath}/{$fileName}";
        
        //return object
        $returnData = (object) [
          "file"    => null,
          "message" => null,
          "success" => false
        ];
        
        if($fileName && $fileTempName && $fileError === UPLOAD_ERR_OK){
            if(move_uploaded_file($fileTempName, $fullDestinyPath)){
                $file["path"] = $fullDestinyPath;
                
                $returnData->success = true;
                $returnData->file = (object) $file;
            }else{
                $returnData->message = "Destiny directory don't exists or isn't writable";
            }
        }else{
            $returnData->message = "Happened a error whit code {$fileError}";
        }
        
        return $returnData;
    }
}