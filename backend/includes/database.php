<?php 

/**
 * Класс Database обеспечивает подключение к базе данных и предоставляет методы для взаимодействия с ней.
 */
class Database {
    // Подключение к базе данных
    private $conn;

    /**
     * Конструктор класса Database. 
     * При создании объекта класса происходит попытка подключения к базе данных с использованием данных из конфигурационного файла.
     */
    public function __construct() {
        // Подключение конфигурационного файла с данными для подключения к базе данных
        $config = require_once '/var/www/site/task_php/backend/config/database.php';
        try {
            // Попытка подключения к базе данных с использованием PDO
            $this->conn = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}", 
                    $config['username'], 
                    $config['password']);
            // Установка PDO в режим выброса исключений при возникновении ошибок
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // В случае ошибки подключения к базе данных, сбрасываем подключение и выводим сообщение об ошибке
            $this->conn = null;
            die("Ошибка подключения в базе данных: ". $e->getMessage());
        }
    }

    /**
     * Возвращает текущее подключение к базе данных.
     *
     * @return PDO|null Возвращает объект PDO, если подключение установлено, иначе null.
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * Возвращает ID последней вставленной строки.
     *
     * @return string ID последней вставленной строки.
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}