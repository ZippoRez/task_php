<?php
// Устанавливаем Content-Type для JSON ответа
header('Content-Type: application/json');

// Подключаем необходимые файлы
require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';

// Настраиваем CORS
// Разрешаем запросы только с этого источника
header("Access-Control-Allow-Origin: http://localhost:3000");
// Разрешаем только PUT и OPTIONS методы
header("Access-Control-Allow-Methods: PUT, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Устанавливаем уровень логирования ошибок
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log'); 

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

// Создаем объекты для работы с базой данных и аккаунтами
$db = new Database();
$accountObj = new Account($db->getConnection());

// Получение ID аккаунта из URL
$accountId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Обработка PUT-запроса (обновление данных аккаунта)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    
    // Проверка, передан ли ID аккаунта
    if ($accountId <= 0) {
        // Возвращаем ошибку, если ID не передан или некорректный
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Неверный ID аккаунта']);
        exit;
    }

    // Получаем данные аккаунта по ID
    if (!$accountObj->getAccountById($accountId)) {
        // Возвращаем ошибку, если аккаунт не найден
        http_response_code(404); // Not Found
        echo json_encode([
            'success' => false,
            'error' => 'Аккаунт не найден',
            'errorCode' => ERROR_ACCOUNT_NOT_FOUND
        ]);
        exit;
    }

    // Получение данных для обновления из тела запроса (JSON)
    $updateData = json_decode(file_get_contents('php://input'), true);

    // Проверка на корректность данных JSON
    if (json_last_error() !== JSON_ERROR_NONE) { 
        // Возвращаем ошибку, если JSON некорректный
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Некорректные данные JSON в теле запроса']);
        exit;
    }

    // Фильтрация данных перед обновлением (защита от XSS)
    $filteredData = [];
    foreach ($updateData as $key => $value) {
        // Используем метод filterString объекта $accountObj для фильтрации каждого значения
        $filteredData[$key] = $accountObj->filterString($value); 
    }

    // Объединение данных аккаунта с отфильтрованными данными для обновления
    // Получаем текущие данные аккаунта и объединяем их с новыми данными
    $data = array_merge($accountObj->toArray(), $filteredData); 

    // Обновление данных аккаунта
    if ($accountObj->updateAccount($data)) {
        // Возвращаем успешный ответ, если обновление прошло успешно
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Аккаунт успешно обновлен'
        ]);
    } else {
        // Логируем ошибку обновления
        error_log("Ошибка при обновлении аккаунта (ID: $accountId): " . $accountObj->getError());
        // Возвращаем ошибку, если при обновлении произошла ошибка
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'success' => false,
            'error' => $accountObj->getError(), 
            'errorCode' => $accountObj->getErrorCode()
        ]);
    }
// Обработка других HTTP-методов (не PUT и не OPTIONS)
} else {
    // Возвращаем ошибку, если используется неподдерживаемый метод
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен. Используйте PUT']);
}