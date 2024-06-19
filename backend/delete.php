<?php
header('Content-Type: application/json');

require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';

$db = new Database();
$accountObj = new Account($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Получение ID аккаунта для удаления из данных запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $accountId = isset($data['id']) ? (int)$data['id'] : 0; 

    if ($accountId > 0 && $accountId < $accountObj->getTotalAccounts()) {
        if ($accountObj->deleteAccount($accountId)) {
            // Успешное удаление
            http_response_code(200); // OK
            echo json_encode([
                'success' => true,
                'message' => 'Аккаунт успешно удален'
            ]);
        } else {
            // Ошибка при удалении
            http_response_code(500); // Internal Server Error (или другой подходящий код)
            echo json_encode([
                'success' => false,
                'error' => $accountObj->getError(),
                'errorCode' => $accountObj->getErrorCode()
            ]);
        }
    } else {
        // Неверный ID аккаунта
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'error' => 'Неверный ID аккаунта'
        ]);
    }
} else {
    // Обработка других HTTP-методов (не DELETE)
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'error' => 'Метод не разрешен'
    ]);
}