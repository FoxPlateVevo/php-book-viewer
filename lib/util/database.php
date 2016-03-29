<?php

class db{
    public static function fetchOne($query){
        $connection = new Connection();
        $pdo = $connection->getConnection();
        
        try {
            $pdoStatement = $pdo->query($query);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        
        return $pdoStatement->fetchObject();
    }
    
    public static function fetchAll($query){
        $connection = new Connection();
        $pdo = $connection->getConnection();
        
        try {
            $pdoStatement = $pdo->query($query);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
        
        $data = [];
        while ($object = $pdoStatement->fetchObject()) {
            $data [] = $object;
        }

        return $data;
    }
    
    public static function getConnection(){
        $connection = new Connection();
        
        return $connection->getConnection();
    }
    
    public static function insert($table, array $data){
        $connection = new Connection();
        $pdo = $connection->getConnection();
        
        try {
            $keys = $values = $keyParams = array();
            
            foreach ($data as $key => $value) {
                $keyParam           = ":{$key}";
                $keyParams[]        = $keyParam;
                $keys[]             = $key;
                $values[$keyParam]  = $value;
            }
            
            $sql = "INSERT INTO {$table} (" . implode(",", $keys) . ") VALUES (" . implode(",", $keyParams) . ")";
            
            $pdoStatement = $pdo->prepare($sql);
            
            if($pdoStatement->execute($values)){
                return $pdo->lastInsertId();
            }else{
                return null;
            }
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    
    public static function delete($table, array $conditions){
        $connection = new Connection();
        $pdo = $connection->getConnection();
        
        try {
            $values = $whereString = array();
            
            foreach ($conditions as $key => $value) {
                $keyParam           = ":{$key}";
                $whereString[]      = "{$key} = {$keyParam}";
                $values[$keyParam]  = $value;
            }
            
            $sql = "DELETE FROM {$table} WHERE " . implode(" AND ", $whereString);
            
            $pdoStatement = $pdo->prepare($sql);
            
            return $pdoStatement->execute($values);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    
    public static function update($table, array $data, array $conditions){
        $connection = new Connection();
        $pdo = $connection->getConnection();
        
        try {
            $setString = $whereString = array();
            
            foreach ($data as $key => $value) {
                $keyParam           = ":{$key}";
                $setString[]        = "{$key} = {$keyParam}";
                $values[$keyParam]  = $value;
            }
            
            foreach ($conditions as $key => $value) {
                $keyParam           = ":{$key}";
                $whereString[]      = "{$key} = {$keyParam}";
                $values[$keyParam]  = $value;
            }
            
            $sql = "UPDATE {$table} SET " . implode(",", $setString) . " WHERE " . implode(" AND ", $whereString);
            
            $pdoStatement = $pdo->prepare($sql);
            
            return $pdoStatement->execute($values);
        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
}