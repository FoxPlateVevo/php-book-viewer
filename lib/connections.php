<?php
class Connection{
    //define connection properties
    const DB_SERVER     = "localhost";
    const DB_NAME       = "ebook";
    const DB_USER       = "root";
    const DB_PASSWORD   = "";
    
    public function getConnection(){
        try {
            $dbserver   = self::DB_SERVER;
            $dbname     = self::DB_NAME;
            
            $pdo = new PDO("mysql:host={$dbserver};dbname={$dbname}", self::DB_USER, self::DB_PASSWORD);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}