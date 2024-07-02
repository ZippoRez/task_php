<?php
// Устанавливаем заголовок для JSON ответа
header('Content-Type: application/json'); 
// Разрешить запросы с этого источника
header('Access-Control-Allow-Origin: http://localhost:3000'); 
// Разрешить эти HTTP-методы 
header('Access-Control-Allow-Methods: POST, OPTIONS'); 
// Разрешить этот заголовок
header('Access-Control-Allow-Headers: Content-Type'); 
// Подключаем необходимые файлы
require_once 'includes/database.php';
require_once 'includes/account.php';
// Файл с кодами ошибок
require_once 'includes/error_codes.php'; 
// Файл с вспомогательными функциями
require_once 'utils/helper.php'; 

// Создаем экземпляр класса Database
$db = new Database();
// Создаем экземпляр класса Account, передавая ему подключение к базе данных
$account = new Account($db->getConnection());

// Обработка preflight-запроса (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Устанавливаем заголовки для ответа на preflight-запрос
    header('Access-Control-Allow-Origin: http://localhost:3000'); 
    // Разрешаем только POST для основного запроса
    header('Access-Control-Allow-Methods: POST'); 
    header('Access-Control-Allow-Headers: Content-Type'); 
    // Код ответа - 200 OK
    http_response_code(200);
    // Завершаем обработку OPTIONS-запроса
    exit; 
} 

// Обработка POST-запроса на создание аккаунта
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из тела запроса и декодируем их из JSON
    $data = json_decode(file_get_contents('php://input'), true); 
    // Очищаем полученные данные от потенциально опасных символов (защита от XSS)
    $data = sanitizeInput($data);
    
    // Пытаемся создать аккаунт с использованием метода createAccount объекта $account
    if ($account->createAccount($data)) {
        // Успешное создание аккаунта
        // Устанавливаем код ответа 201 (Created)
        http_response_code(201); 
        // Формируем JSON ответ с сообщением об успешном создании и ID созданного аккаунта
        echo json_encode([
            'success' => true,
            'message' => 'Аккаунт успешно создан',
            'data' => [
                'id' => $account->getId() 
            ]
        ]);
    } else {
        // Ошибка при создании аккаунта
        // Получаем код ошибки из объекта $account
        $errorCode = $account->getErrorCode(); 
        // Устанавливаем код ответа 400 (Bad Request) по умолчанию
        $httpCode = 400; 
        // Если код ошибки равен 0 (ошибка, связанная с email), меняем код ответа на 409 (Conflict)
        if ($errorCode == 0) {
            $httpCode = 409; 
        }
        // Устанавливаем HTTP код ответа 
        http_response_code($httpCode);
        // Формируем JSON ответ с информацией об ошибке
        echo json_encode([
            'success' => false,
            'error' => $account->getError(),
            'errorCode' => $errorCode 
        ]);
    }
// Обработка других HTTP-методов (не POST и не OPTIONS)
} else {
    // Устанавливаем код ответа 405 (Method Not Allowed)
    http_response_code(405); 
    // Возвращаем JSON ответ с сообщением о том, что метод не разрешен
    echo json_encode([
        'success' => false,
        'error' => 'Метод не разрешен.'
    ]);
}