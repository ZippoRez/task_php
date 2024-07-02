<?php
// Устанавливаем заголовок Content-Type для ответа в формате JSON
header('Content-Type: application/json');

// Подключаем необходимые файлы
require_once 'includes/database.php';
require_once 'includes/company.php';
require_once 'includes/error_codes.php';

// Создаем экземпляры классов Database и Company
$db = new Database();
$companyObj = new Company($db->getConnection());

// Устанавливаем заголовки CORS для разрешения кросс-доменных запросов
// Важно: замените http://localhost:3000 на адрес вашего фронтенд-приложения
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Устанавливаем код ответа 204 (No Content) и завершаем выполнение скрипта
    http_response_code(204); 
    exit;
}

// Получаем ID компании из URL
$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0; 

// Обработка GET-запросов (получение компании)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // --- Получение компании по ID ---

    // Проверяем, передан ли ID компании
    if ($companyId <= 0) {
        // Если ID компании не передан или некорректный, 
        // устанавливаем код ответа 400 (Bad Request) и выводим сообщение об ошибке
        http_response_code(400); 
        echo json_encode(['success' => false, 'error' => 'Неверный ID компании']); 
        exit; 
    }

    // Пытаемся получить компанию по ID
    if (!$companyObj->getCompanyById($companyId)) {
        // Если компания не найдена, устанавливаем код ответа 404 (Not Found)
        // и выводим сообщение об ошибке
        http_response_code(404); 
        echo json_encode([
            'success' => false, 
            'error' => $companyObj->getError(), 
            'errorCode' => $companyObj->getErrorCode()
        ]);
        exit; 
    }

    // Если компания успешно найдена, устанавливаем код ответа 200 (OK)
    // и выводим данные компании в формате JSON
    http_response_code(200); 
    echo json_encode([
        'success' => true, 
        'message' => 'Данные компании', 
        'data' => $companyObj->toArray() 
    ]);

// Обработка PUT-запросов (обновление компании)
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // --- Обновление компании ---

    // Проверяем, передан ли ID компании
    if ($companyId <= 0) {
        // Если ID компании не передан или некорректный, 
        // устанавливаем код ответа 400 (Bad Request) и выводим сообщение об ошибке
        http_response_code(400); 
        echo json_encode(['success' => false, 'error' => 'Неверный ID компании']); 
        exit; 
    }

    // Получаем данные компании из тела запроса
    $data = json_decode(file_get_contents('php://input'), true);

    // Получаем компанию по ID, чтобы установить ID в объект
    if (!$companyObj->getCompanyById($companyId)) {
        // Если компания не найдена, устанавливаем код ответа 404 (Not Found)
        // и выводим сообщение об ошибке
        http_response_code(404); 
        echo json_encode([
            'success' => false, 
            'error' => $companyObj->getError(), 
            'errorCode' => $companyObj->getErrorCode()
        ]);
        exit; 
    }

    // Пытаемся обновить данные компании
    if ($companyObj->updateCompany($data)) {
        // Если компания успешно обновлена, устанавливаем код ответа 200 (OK)
        // и выводим сообщение об успехе
        http_response_code(200); 
        echo json_encode([
            'success' => true, 
            'message' => 'Компания успешно обновлена',
            'data' => [
                'id' => $companyObj->getId()
            ]
        ]);
    } else {
        // Если произошла ошибка при обновлении компании, устанавливаем код ответа 400 (Bad Request)
        // и выводим сообщение об ошибке
        http_response_code(400); 
        echo json_encode([
            'success' => false, 
            'error' => $companyObj->getError(), 
            'errorCode' => $companyObj->getErrorCode()
        ]);
    }

// Обработка DELETE-запросов (удаление компании)
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // --- Удаление компании ---

    // Проверяем, передан ли ID компании
    if ($companyId <= 0) {
        // Если ID компании не передан или некорректный, 
        // устанавливаем код ответа 400 (Bad Request) и выводим сообщение об ошибке
        http_response_code(400); 
        echo json_encode(['success' => false, 'error' => 'Неверный ID компании']); 
        exit; 
    }

    // Пытаемся удалить компанию
    if ($companyObj->deleteCompany($companyId)) {
        // Если компания успешно удалена, устанавливаем код ответа 200 (OK)
        // и выводим сообщение об успехе
        http_response_code(200); 
        echo json_encode([
            'success' => true, 
            'message' => 'Компания успешно удалена'
        ]);
    } else {
        // Если произошла ошибка при удалении компании, устанавливаем код ответа 500 (Internal Server Error)
        // и выводим сообщение об ошибке
        http_response_code(500); 
        echo json_encode([
            'success' => false, 
            'error' => $companyObj->getError(), 
            'errorCode' => $companyObj->getErrorCode()
        ]);
    }
// Обработка запросов с неподдерживаемыми методами
} else {
    // Устанавливаем код ответа 405 (Method Not Allowed) 
    // и выводим сообщение об ошибке
    http_response_code(405); 
    echo json_encode(['success' => false,  'error' => 'Метод не разрешен']); 
}