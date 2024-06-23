<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000'); // Разрешить запросы с этого источника
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); // Разрешить эти HTTP-методы 
header('Access-Control-Allow-Headers: Content-Type'); // Разрешить этот заголовок
require_once 'includes/database.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';

$db = new Database();
$accountObj = new Account($db->getConnection());

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    //  Отправляем заголовки CORS для preflight-запроса
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    http_response_code(204); //  No Content
    exit; 
} 

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Получение ID аккаунта для удаления из данных запроса
    $data = json_decode(file_get_contents('php://input'), true);
    $accountId = isset($data['id']) ? (int)$data['id'] : 0; 


    if ($accountId > 0) {
        //  Проверяем,  нужно ли "пометить"  как удаленный или удалить окончательно
        $permanentDelete = isset($data['permanent']) && $data['permanent'] === true; 

        if ($permanentDelete) {
            //  Окончательное удаление аккаунта
            if ($accountObj->permanentDeleteAccount($accountId)) { 
                http_response_code(200); //  OK
                echo json_encode([
                    'success' => true,
                    'message' => 'Аккаунт успешно удален'
                ]);
            } else {
                http_response_code(500); //  Internal Server Error 
                echo json_encode([
                    'success' => false,
                    'error' => $accountObj->getError(),
                    'errorCode' => $accountObj->getErrorCode()
                ]);
            }
        } else { 
            //  "Мягкое"  удаление (пометка deleted_at)
            if ($accountObj->deleteAccount($accountId)) { 
                http_response_code(200); //  OK
                echo json_encode([
                    'success' => true,
                    'message' => 'Аккаунт помечен как удаленный' 
                ]);
            } else {
                http_response_code(500); //  Internal Server Error 
                echo json_encode([
                    'success' => false,
                    'error' => $accountObj->getError(),
                    'errorCode' => $accountObj->getErrorCode()
                ]);
            }
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