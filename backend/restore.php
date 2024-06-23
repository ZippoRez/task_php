<?php
header('Content-Type: application/json');

require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';

$db = new Database();
$accountObj = new Account($db->getConnection());

// Обработка CORS для всех запросов
header("Access-Control-Allow-Origin: http://localhost:3000"); // Замени на адрес фронтенда
header("Access-Control-Allow-Methods: POST, OPTIONS"); 
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение данных из тела запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $accountId = isset($data['id']) ? (int)$data['id'] : 0;

    if ($accountId > 0) {
        try {
            // Восстановление аккаунта (установка deleted_at в NULL)
            $conn = $db->getConnection(); // Получаем объект PDO
            if ($conn !== null) {
                $sql = "UPDATE accounts SET deleted_at = NULL WHERE id = :id";
                $stmt = $conn->prepare($sql); //  Используем $conn 
                $stmt->execute([':id' => $accountId]);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'Ошибка при подключении: ' . $e->getMessage(),
                    'errorCode' => $e->getCode()
                ]);
            }
            

            http_response_code(200); // OK
            echo json_encode([
                'success' => true,
                'message' => 'Аккаунт успешно восстановлен'
            ]);
        } catch (PDOException $e) {
            http_response_code(500); // Internal Server Error
            echo json_encode([
                'success' => false,
                'error' => 'Ошибка при восстановлении аккаунта: ' . $e->getMessage(),
                'errorCode' => $e->getCode()
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
    // Обработка других HTTP-методов (не POST)
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'error' => 'Метод не разрешен'
    ]);
}