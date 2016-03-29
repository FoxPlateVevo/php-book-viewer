<?php

class app_configs{
    private static $namespaces = [
        'ebook'
    ];
    
    public static function getNamespaces() {
        return self::$namespaces;
    }
}