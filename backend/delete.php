<?php
// Устанавливаем Content-Type для JSON ответа
header('Content-Type: application/json');
// Настраиваем CORS
header('Access-Control-Allow-Origin: http://localhost:3000'); 
header('Access-Control-Allow-Methods: DELETE, OPTIONS'); // Разрешаем только DELETE и OPTIONS
header('Access-Control-Allow-Headers: Content-Type, Authorization'); 

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

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit; 
} 

// Обработка DELETE-запроса (удаление аккаунта)
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Получение данных из тела запроса (JSON)
    $data = json_decode(file_get_contents('php://input'), true);
    $accountId = isset($data['id']) ? (int)$data['id'] : 0; 
    // Проверка, передан ли ID аккаунта
    if ($accountId > 0) {
        // Определяем тип удаления (окончательное или "мягкое")
        $permanentDelete = isset($data['permanent']) && $data['permanent'] === true; 

        try { 
            if ($permanentDelete) {
                // Выполняем окончательное удаление аккаунта
                if (!$accountObj->permanentDeleteAccount($accountId)) {
                    throw new Exception($accountObj->getError(), $accountObj->getErrorCode()); 
                } 
                $message = 'Аккаунт успешно удален';
            } else {
                // Выполняем "мягкое" удаление аккаунта (пометка deleted_at)
                if (!$accountObj->deleteAccount($accountId)) {
                    throw new Exception($accountObj->getError(), $accountObj->getErrorCode()); 
                }
                $message = 'Аккаунт помечен как удаленный'; 
            }

            // Отправляем успешный ответ
            http_response_code(200); // OK
            echo json_encode([
                'success' => true,
                'message' => $message 
            ]);

        } catch (Exception $e) {
            // Логируем ошибку удаления
            error_log("Ошибка при удалении аккаунта (ID: $accountId): " . $e->getMessage()); 

            // Отправляем сообщение об ошибке
            http_response_code(500); // Internal Server Error 
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
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
    // Обработка других HTTP-методов (не DELETE и не OPTIONS)
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        'success' => false,
        'error' => 'Метод не разрешен. Используйте DELETE' 
    ]);
}