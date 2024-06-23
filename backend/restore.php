<?php
// Устанавливаем Content-Type для JSON ответа
header('Content-Type: application/json');

// Подключаем необходимые файлы
require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';

// Устанавливаем уровень логирования ошибок
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log'); 

// Создаем объекты для работы с базой данных и аккаунтами
$db = new Database();
$accountObj = new Account($db->getConnection()); 

// Настройка CORS для разработки
header("Access-Control-Allow-Origin: http://localhost:3000"); // Разрешаем запросы с этого адреса
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Разрешаем методы POST и OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Разрешаем заголовки Content-Type и Authorization

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit; 
}

// Обработка POST запросов на восстановление аккаунта
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из тела запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $accountId = isset($data['id']) ? (int)$data['id'] : 0; 

    // Проверяем, передан ли ID аккаунта
    if ($accountId > 0) {
        try {
            // Восстановление аккаунта (установка deleted_at в NULL)
            $conn = $db->getConnection(); // Получаем объект PDO
            if ($conn !== null) {
                $sql = "UPDATE accounts SET deleted_at = NULL WHERE id = :id";
                $stmt = $conn->prepare($sql); // Подготавливаем запрос
                $stmt->execute([':id' => $accountId]); // Выполняем запрос

                // Отправляем успешный ответ
                http_response_code(200); // OK
                echo json_encode([
                    'success' => true,
                    'message' => 'Аккаунт успешно восстановлен'
                ]);
            } else {
                // Обработка ошибки подключения к базе данных 
                http_response_code(500); // Internal Server Error
                echo json_encode([
                    'success' => false,
                    'error' => 'Ошибка при подключении: ' . $e->getMessage(),
                    'errorCode' => $e->getCode()
                ]);
            }

        } catch (PDOException $e) {
            // Обработка ошибки SQL запроса
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
    // Обработка других HTTP-методов
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'error' => 'Метод не разрешен'
    ]);
}