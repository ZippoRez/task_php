<?php

// Ошибки валидации данных (100-199)
define('ERROR_EMPTY_FIELDS', 101);       // Обязательные поля не заполнены
define('ERROR_INVALID_EMAIL', 102);      // Неверный формат email
define('ERROR_EMAIL_EXISTS', 103);       // Email уже существует
define('ERROR_INVALID_PHONE', 104);      // Неверный формат телефона 

// Ошибки базы данных (200-299)
define('ERROR_DB_CONNECT', 201);         // Ошибка подключения к БД
define('ERROR_DB_QUERY', 202);           // Ошибка выполнения SQL-запроса 
define('ERROR_DB_INSERT', 203);         // Ошибка при вставке данных
define('ERROR_DB_UPDATE', 204);         // Ошибка при обновлении данных 
define('ERROR_DB_DELETE', 205);         // Ошибка при удалении данных
define('ERROR_ACCOUNT_NOT_FOUND', 206);  // Аккаунт не найден

// Другие ошибки (300-399)
define('ERROR_UNKNOWN', 301);            // Неизвестная ошибка