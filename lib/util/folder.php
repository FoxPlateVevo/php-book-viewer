<?php

class folder{
    public static function getComponents($path){
        return array_diff(scandir($path), [".", ".."]);
    }
    
    public static function create($path){
        return mkdir($path, 0777, true);
    }
    
    public static function delete($path){
        if(is_dir($path)){ 
          $components = self::getComponents($path);
          
          foreach ($components as $component) {
              if(is_dir("{$path}/{$component}")){
                  self::delete("{$path}/{$component}");
              }else{
                  unlink("{$path}/{$component}"); 
              } 
          }
          
          rmdir($path);
        } 
    }
}

