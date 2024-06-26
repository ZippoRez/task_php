<?php
// backup.php

// Устанавливаем уровень логирования ошибок
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/cron.log');

// Загружаем настройки базы данных из конфигурационного файла
$dbConfig = require_once 'config/database.php';

// Имя базы данных
$dbName = $dbConfig['dbname'];

// Имя файла для резервной копии
$backupFile = $dbName . '_' . date('Y-m-d_H-i-s') . '.sql';

// Команда mysqldump (убедитесь, что mysqldump доступен в системе)
$command = "mysqldump -h {$dbConfig['host']} -u {$dbConfig['username']} -p'{$dbConfig['password']}' {$dbName} > {$backupFile}";

try {
    // Выполняем команду
    exec($command, $output, $returnVar);

    // Проверяем, была ли ошибка
    if ($returnVar !== 0) {
        throw new Exception("Ошибка при создании резервной копии: " . implode("\n", $output));
    }

    // Логируем успешное создание резервной копии
    error_log("Резервная копия успешно создана: {$backupFile}");

} catch (Exception $e) {
    // Логируем ошибку
    error_log("Ошибка при создании резервной копии: " . $e->getMessage());
}
?>