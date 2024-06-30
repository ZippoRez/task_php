<?php
header('Content-Type: application/json'); // Устанавливаем заголовок для JSON
header('Access-Control-Allow-Origin: http://localhost:3000'); // Разрешить запросы с этого источника
header('Access-Control-Allow-Methods: POST, OPTIONS'); // Разрешить эти HTTP-методы 
header('Access-Control-Allow-Headers: Content-Type'); // Разрешить этот заголовок
require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php'; // (файл с кодами ошибок)
require_once 'utils/helper.php'; 

$db = new Database();
$account = new Account($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Обработка preflight-запроса
    header('Access-Control-Allow-Origin: http://localhost:3000'); 
    header('Access-Control-Allow-Methods: POST'); // Разрешаем только POST
    header('Access-Control-Allow-Headers: Content-Type'); 
    http_response_code(200); // OK
    exit; // Завершаем обработку OPTIONS-запроса
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true); 
    $data = sanitizeInput($data);
    if ($account->createAccount($data)) {
        // Успешное создание аккаунта
        http_response_code(201); // Created
        echo json_encode([
            'success' => true,
            'message' => 'Аккаунт успешно создан',
            'data' => [
                'id' => $account->getId() 
            ]
        ]);
    } else {
        // Ошибка при создании аккаунта
        $errorCode = $account->getErrorCode(); 
        $httpCode = 400; // Bad Request 
        if ($errorCode == 0) {
            $httpCode = 409; // Conflict (для ошибки "email уже существует")
        }
        http_response_code($httpCode);
        echo json_encode([
            'success' => false,
            'error' => $account->getError(),
            'errorCode' => $errorCode 
        ]);
    }
} else {
    // Обработка других HTTP-методов (не POST)
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'error' => 'Метод не разрешен.'
    ]);
}