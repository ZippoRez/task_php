<?php
header('Content-Type: application/json');

require_once 'includes/database.php';
require_once 'includes/company.php';
require_once 'includes/error_codes.php';

$db = new Database();
$companyObj = new Company($db->getConnection());

// Обработка CORS для всех запросов 
header("Access-Control-Allow-Origin: http://localhost:3000"); // Замени на адрес фронтенда
header("Access-Control-Allow-Methods: GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка preflight-запросов (OPTIONS) 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit; 
}

// Получение ID компании из URL
$companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0; 

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // --- Получение компании по ID ---
    if ($companyId <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Неверный ID компании']); 
        exit; 
    }

    if (!$companyObj->getCompanyById($companyId)) {
        http_response_code(404); // Not Found
        echo json_encode([
            'success' => false, 
            'error' => $companyObj->getError(), 
            'errorCode' => $companyObj->getErrorCode()
        ]);
        exit; 
    }

    http_response_code(200); // OK
    echo json_encode([
        'success' => true, 
        'message' => 'Данные компании', 
        'data' => $companyObj->toArray() 
    ]);

} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // --- Обновление компании ---
    if ($companyId <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Неверный ID компании']); 
        exit; 
    }

    $data = json_decode(file_get_contents('php://input'), true);

    // Получаем компанию, чтобы установить ID в объект
    if (!$companyObj->getCompanyById($companyId)) {
        http_response_code(404); // Not Found
        echo json_encode([
            'success' => false, 
            'error' => $companyObj->getError(), 
            'errorCode' => $companyObj->getErrorCode()
        ]);
        exit; 
    }

    if ($companyObj->updateCompany($data)) {
        http_response_code(200); // OK
        echo json_encode([
            'success' => true, 
            'message' => 'Компания успешно обновлена',
            'data' => [
                'id' => $companyObj->getId()
            ]
        ]);
    } else {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false, 
            'error' => $companyObj->getError(), 
            'errorCode' => $companyObj->getErrorCode()
        ]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // --- Удаление компании ---
    if ($companyId <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'error' => 'Неверный ID компании']); 
        exit; 
    }

    if ($companyObj->deleteCompany($companyId)) {
        http_response_code(200); // OK
        echo json_encode([
            'success' => true, 
            'message' => 'Компания успешно удалена'
        ]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'success' => false, 
            'error' => $companyObj->getError(), 
            'errorCode' => $companyObj->getErrorCode()
        ]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false,  'error' => 'Метод не разрешен']); 
}