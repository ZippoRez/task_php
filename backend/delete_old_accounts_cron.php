<?php
// Подключаем файл для работы с базой данных
require_once 'includes/database.php';

// Устанавливаем уровень логирования ошибок
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/cron.log');

// Создаем объект для работы с базой данных
$db = new Database();
$conn = $db->getConnection();

// SQL-запрос для удаления аккаунтов, помеченных как удаленные более 1 часа назад
$sql = "DELETE FROM accounts WHERE deleted_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)";

try {
    if ($conn) {
        // Выполняем запрос
        $deletedRows = $conn->exec($sql);
        // Логируем информацию об удаленных аккаунтах
        error_log("CRON: Удалено {$deletedRows} аккаунтов.");
    } else {
        // Логируем ошибку подключения к базе данных
        throw new PDOException("Ошибка подключения к базе данных.");
    }
} catch (PDOException $e) {
    // Логируем ошибку выполнения запроса или подключения
    error_log("CRON Ошибка: " . $e->getMessage());
}