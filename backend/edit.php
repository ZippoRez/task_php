<?php
header('Content-Type: application/json');

require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';

$db = new Database();
$accountObj = new Account($db->getConnection());

// Обработка CORS для всех запросов
header("Access-Control-Allow-Origin: http://localhost:3000"); // Замени на адрес фронтенда
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

// Получение ID аккаунта из URL (для GET и PUT запросов)
$accountId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($accountId <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Неверный ID аккаунта']);
    exit;
}

if (!$accountObj->getAccountById($accountId)) {
    http_response_code(404); // Not Found
    echo json_encode([
        'success' => false,
        'error' => 'Аккаунт не найден',
        'errorCode' => ERROR_ACCOUNT_NOT_FOUND
    ]);
    exit;
}

// if ($_SERVER['REQUEST_METHOD'] === 'GET') {
//     // --- Обработка GET-запроса (получение данных аккаунта) ---

//     // Отправка данных аккаунта
//     http_response_code(200); // OK
//     echo json_encode([
//         'success' => true,
//         'message' => 'Данные аккаунта',
//         'data' => $accountObj->toArray()
//     ]);

// } 

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // --- Обработка PUT-запроса (обновление данных аккаунта) ---

    // Получение данных для обновления из тела запроса (JSON)
    $updateData = json_decode(file_get_contents('php://input'), true);

    // Проверка на корректность данных JSON
    if (is_null($updateData)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Некорректные данные в теле запроса']);
        exit;
    }

    // Получение аккаунта по ID
    

    // Объединение данных аккаунта с данными для обновления
    $data = array_merge($accountObj->toArray(), $updateData);

    // Обновление данных аккаунта
    if ($accountObj->updateAccount($data)) {
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Аккаунт успешно обновлен'
        ]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'success' => false,
            'error' => $accountObj->getError(),
            'errorCode' => $accountObj->getErrorCode()
        ]);
    }

} 
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','PUT', 'OPTIONS'])) {
    // --- Обработка других HTTP-методов ---
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен']);
}