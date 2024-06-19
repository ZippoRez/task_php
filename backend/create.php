<?php
header('Content-Type: application/json'); // Устанавливаем заголовок для JSON

require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php'; // (файл с кодами ошибок)
require_once 'utils/helper.php'; 

$db = new Database();
$account = new Account($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение JSON-данных из тела запроса
    $data = json_decode(file_get_contents('php://input'), true); 
    $data = sanitizeInput($data);

    // Создание аккаунта
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