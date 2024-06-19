<?php
header('Content-Type: application/json');

require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';
require_once 'utils/helper.php'; 


$db = new Database();
$accountObj = new Account($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Получение JSON-данных из тела запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $data = sanitizeInput($data);
    // Проверка наличия ID аккаунта
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'error' => 'Не указан ID аккаунта'
        ]);
        exit;
    }

    // Получение аккаунта по ID
    $accountId = (int) $data['id'];
    if (!$accountObj->getAccountById($accountId)) {
        http_response_code(404); // Not Found
        echo json_encode([
            'success' => false,
            'error' => 'Аккаунт не найден',
            'errorCode' => ERROR_ACCOUNT_NOT_FOUND
        ]);
        exit;
    }

    // Обновление данных аккаунта
    if ($accountObj->updateAccount($data)) {
        // Успешное обновление
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Аккаунт успешно обновлен'
        ]);
    } else {
        // Ошибка при обновлении
        http_response_code(500); // Internal Server Error (или другой подходящий код)
        echo json_encode([
            'success' => false,
            'error' => $accountObj->getError(),
            'errorCode' => $accountObj->getErrorCode()
        ]);
    }
} else {
    // Обработка других HTTP-методов (не PUT)
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'error' => 'Метод не разрешен'
    ]);
}