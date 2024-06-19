<?php 
class Database{
    private $conn;

    public function __construct(){
        $config = require_once '/var/www/site/task_php/backend/config/database.php';
        try{
            $this->conn = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
                    $config['username'], 
                    $config['password']);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
        catch(PDOException $e){
            $this->conn = null;
            die("Ошибка подключения в базе данных: ". $e->getMessage());
        }
    }
    public function getConnection(){
        return $this->conn;
    }
    public function lastInsertId(){
        return $this->conn->lastInsertId();
    }
}
