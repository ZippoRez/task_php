<?php

// Валидация 
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateRussianPhone($phone) {
    // Удаляем все символы, кроме цифр, +, (, ), -
    $phone = preg_replace('/[^0-9\+()-]/', '', $phone);
    // Проверяем на соответствие формату +7XXXXXXXXXX или 8XXXXXXXXXX
    return preg_match('/^(\+7|8)\d{10}$/', $phone);
}

// Очистка данных (для защиты от XSS)
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data); 
    } else {
        return htmlspecialchars(strip_tags(trim($data))); 
    }
}

// Генерация случайного пароля 
function generatePassword($length = 12) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

// Форматирование телефонного номера (для вывода)
function formatPhoneNumber($phone) {
    // Удаляем все символы, кроме цифр
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Проверяем длину номера
    if (strlen($phone) == 11 && $phone[0] == '8') {
        // Заменяем 8 на +7
        $phone = '+7' . substr($phone, 1);
    } elseif (strlen($phone) != 12 || $phone[0] != '+') {
        // Некорректный номер, возвращаем как есть
        return $phone; 
    }

    // Форматируем номер: +7 (XXX) XXX-XX-XX
    return sprintf('+7 (%s) %s-%s-%s',
        substr($phone, 2, 3), // Код оператора
        substr($phone, 5, 3),
        substr($phone, 8, 2),
        substr($phone, 10, 2)
    );
}

// Логирование ошибок
function logError($message) {
    $logFilePath = __DIR__ . '/../logs/error.log';
    $logMessage = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
    file_put_contents($logFilePath, $logMessage, FILE_APPEND); 
}