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

/**
 * Get PDF information
 * 
 * @param string $filename It is the PDF source path to get information
 * @return stdClass The <b>stdClass</b> instance that contains information 
 * about PDF or <b>NULL</b> on failure
 */
function PDF_get_information($filename){
    /*
     * Get current operative system 
     */
    $os = get_os();
    
    if($os === "windows"){
        //For Windows
        $command = __PATH__ . "/lib/bin/windows/xpdf/pdfinfo.exe {$filename}";
    }else if($os === "linux"){
        //For Linux
        $command = __PATH__ . "/lib/bin/linux/xpdf/pdfinfo {$filename}";
    }

    $output = $return =  null;

    exec($command, $output, $return);
    
    if($return){
        return null;
    }
    
    $pdfInfo = (object) [
        "pages"     => null,
        "size"      => null,
        "version"   => null,
    ];

    foreach ($output as $op){
        $matches = null;

        if(preg_match('/Pages:\s*(\d+)/i', $op, $matches)){
            $pdfInfo->pages = (int) array_pop($matches);
        }else if(preg_match('/Page size:\s*(\d+\.?\d*)\s*x\s*(\d+\.?\d*)/i', $op, $matches)){
            $pdfInfo->size = new stdClass();

            $pdfInfo->size->height  = array_pop($matches);
            $pdfInfo->size->width   = array_pop($matches);
        }else if(preg_match('/PDF version:\s*(\d+\.?\d*)/i', $op, $matches)){
            $pdfInfo->version = array_pop($matches);
        }
    }
    
    return $pdfInfo;
}