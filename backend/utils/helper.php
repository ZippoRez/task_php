<?php

/**
 * Файл helper.php содержит набор вспомогательных функций для приложения.
 */

/**
 * Валидирует адрес электронной почты.
 *
 * @param string $email Адрес электронной почты для валидации.
 * @return bool Возвращает true, если email валидный, иначе false.
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Валидирует российский номер телефона.
 *
 * @param string $phone Номер телефона для валидации.
 * @return bool Возвращает true, если номер телефона валидный, иначе false.
 */
function validateRussianPhone($phone) {
    // Удаляем все символы, кроме цифр, +, (, ), -
    $phone = preg_replace('/[^0-9\+()-]/', '', $phone);
    // Проверяем на соответствие формату +7XXXXXXXXXX или 8XXXXXXXXXX
    return preg_match('/^(\+7|8)\d{10}$/', $phone);
}

/**
 * Очищает данные от потенциально опасных символов (XSS).
 *
 * @param mixed $data Данные для очистки. Может быть строкой или массивом.
 * @return mixed Возвращает очищенные данные.
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        // Если данные - массив, рекурсивно очищаем каждый элемент
        return array_map('sanitizeInput', $data); 
    } else {
        // Очистка строки: удаление тегов, спецсимволов HTML, пробелов по краям 
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8'); 
    }
}

/**
 * Генерирует случайный пароль.
 *
 * @param int $length Длина пароля (по умолчанию 12 символов).
 * @return string Сгенерированный пароль.
 */
function generatePassword($length = 12) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)]; 
    }
    return $password;
}

/**
 * Форматирует телефонный номер для вывода.
 *
 * @param string $phone Номер телефона для форматирования.
 * @return string Отформатированный номер телефона или исходный номер, 
 *                если форматирование не удалось.
 */
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
        substr($phone, 2, 3), 
        substr($phone, 5, 3),
        substr($phone, 8, 2),
        substr($phone, 10, 2)
    );
}

/**
 * Записывает сообщение об ошибке в лог-файл.
 *
 * @param string $message Сообщение об ошибке.
 */
function logError($message) {
    $logFilePath = __DIR__ . '/../logs/error.log'; // Путь к лог-файлу
    $logMessage = date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL;
    // Записываем сообщение в файл (режим дозаписи)
    file_put_contents($logFilePath, $logMessage, FILE_APPEND); 
}