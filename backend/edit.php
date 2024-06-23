<?php
// Устанавливаем Content-Type для JSON ответа
header('Content-Type: application/json');

// Подключаем необходимые файлы
require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';

// Настраиваем CORS
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: PUT, OPTIONS"); // Разрешаем только PUT и OPTIONS 
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

// Проверка, передан ли ID аккаунта
if ($accountId <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Неверный ID аккаунта']);
    exit;
}

// Получаем данные аккаунта по ID
if (!$accountObj->getAccountById($accountId)) {
    http_response_code(404); // Not Found
    echo json_encode([
        'success' => false,
        'error' => 'Аккаунт не найден',
        'errorCode' => ERROR_ACCOUNT_NOT_FOUND
    ]);
    exit;
}

// Обработка PUT-запроса (обновление данных аккаунта)
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Получение данных для обновления из тела запроса (JSON)
    $updateData = json_decode(file_get_contents('php://input'), true);

    // Проверка на корректность данных JSON
    if (json_last_error() !== JSON_ERROR_NONE) { 
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Некорректные данные JSON в теле запроса']);
        exit;
    }

    // Фильтрация данных перед обновлением (защита от XSS)
    $filteredData = [];
    foreach ($updateData as $key => $value) {
        $filteredData[$key] = $accountObj->filterString($value); 
    }

    // Объединение данных аккаунта с отфильтрованными данными для обновления
    $data = array_merge($accountObj->toArray(), $filteredData); 

    // Обновление данных аккаунта
    if ($accountObj->updateAccount($data)) {
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Аккаунт успешно обновлен'
        ]);
    } else {
        // Логируем ошибку обновления
        error_log("Ошибка при обновлении аккаунта (ID: $accountId): " . $accountObj->getError());

        http_response_code(500); // Internal Server Error
        echo json_encode([
            'success' => false,
            'error' => $accountObj->getError(), 
            'errorCode' => $accountObj->getErrorCode()
        ]);
    }
} else {
    // Обработка других HTTP-методов (не PUT и не OPTIONS)
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен. Используйте PUT']);
}