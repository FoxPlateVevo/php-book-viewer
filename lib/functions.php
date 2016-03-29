<?php

function crypt_password($string) {
    return crypt($string, '$2a$09$tARm1a9A9N7q1W9T9n5LqR$');
}

function get_conditional_string($alternateAttributes, $optionParams){
    $CONDITIONALS_PART = [];

    if($optionParams && is_array($optionParams)){
        foreach ($optionParams as $attribute => $value){
            if(array_key_exists($attribute, $alternateAttributes)){
                $columnName = $alternateAttributes[$attribute];

                if(is_array($value) && !empty($value)){
                    $valueData = array_map(function($item){
                        return "'{$item}'";
                    }, $value);

                    $valueDataString = implode(",", $valueData);

                    $CONDITIONALS_PART[]= "{$columnName} IN ({$valueDataString})";
                }else{
                    $CONDITIONALS_PART[]= "{$columnName} = '{$value}'";
                }
            }
        }
    }

    return ($CONDITIONALS_PART)? "WHERE " . implode(" AND ", $CONDITIONALS_PART) : null;
}

function get_date(){
    return date("Y-m-d");
}

function get_datetime(){
    return date("Y-m-d H:i:s");
}

function get_date_from_format($dateFormatString, $format = 'j F, Y'){
    $datetime = DateTime::createFromFormat($format, $dateFormatString);
    
    return $datetime ? $datetime->format("Y-m-d") : null;
}

function get_dateformated_from_date($dateString, $format = 'Y-m-d'){
    $datetime = DateTime::createFromFormat($format, $dateString);
    
    return $datetime ? $datetime->format("j F, Y") : null;
}

function get_domain() {
    return "//{$_SERVER["HTTP_HOST"]}";
}

function get_user_session(){
    $success = null;
    
    if(isset($_SESSION["user"])){
        $success = unserialize($_SESSION["user"]);
    }
    
    return $success;
}

/*
 * Possible values:
 * - windows
 * - cygwin
 * - darwin
 * - freebsd
 * - hp-ux
 * - irix64
 * - linux
 * - netbsd
 * - openbsd
 * - sunos
 * - unix
 * 
 * http://stackoverflow.com/questions/738823/possible-values-for-php-os
 */
function get_os(){
    $os = strtolower(PHP_OS);
    $return = $os;
    
    if(is_int(strpos($os, "win"))){
        $return = "windows";
    }else if(is_int(strpos($os, "cygwin"))){
        $return = "cygwin";
    }
    
    return $return;
}

function get_url_from_string($string){
    $matches = null;
    
    preg_match('/(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.\-?&=#%+]*)/', $string, $matches);
    
    $url = array_shift($matches);
    
    return $url ? $url : null;
}

function create_upload_token($path, $maxSize, $extensions){
    $cleandPath = file::cleanPath($path);
    
    //create a Upload Service
    //create constraints
    $constraints = new UploadService_Config_Constraints();
    $constraints->setMaxSize($maxSize);
    $constraints->setExtensions($extensions);

    //create configs
    $config = new UploadService_Config();
    $config->setPath($cleandPath);
    $config->setConstraints($constraints);

    $upload = new UploadService();
    $upload->setConfig($config);

    $token = $upload->token->generate();

    //save this in storage session
    $_SESSION["upload"][$token] = serialize($upload);
    
    return $token;
}

function has_extension($path, $extension){
    return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === strtolower($extension);
}

function substr_with_dots($string, $length){
    strlen($string) > $length && $string = substr($string, 0, $length) . "...";
    
    return $string;
}

function vd($expresion) {
    echo "<pre>";
    var_dump($expresion);
    echo "</pre>";
}

//lol
function __post(){
    echo "<pre>";
    foreach ($_POST as $key => $post){
        echo "
            \$var = \$request->param(\"{$key}\");";
    }
    echo "</pre>";
    exit;
}

function __class($object){
    $reflect = new ReflectionClass($object);
    $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

    echo "<pre>";
    foreach ($props as $prop) {
        echo "
        \"{$prop->getName()}\" => \$da,";
    }
    echo "</pre>";
    exit;
}
