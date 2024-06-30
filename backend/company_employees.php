<?php
header('Content-Type: application/json');

require_once 'includes/database.php';
require_once 'includes/company.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';

$db = new Database();
$companyObj = new Company($db->getConnection());
$accountObj = new Account($db->getConnection());

// Обработка CORS для всех запросов
header("Access-Control-Allow-Origin: http://localhost:3000"); // Замени на адрес фронтенда
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // --- Получение списка сотрудников компании ---
    $companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    if ($companyId <= 0) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false,  'error' => 'Неверный ID компании']); 
        exit; 
    }

    try {
        $employees = $companyObj->getEmployees($companyId, $page, $limit);

        if ($employees === false) {
            throw new Exception($companyObj->getError()); 
        }

        $totalEmployees = $companyObj->getTotalAccountsByCompanyId($companyId); 
        $totalPages = ceil($totalEmployees / $limit);

        $response = [
            'success' => true,
            'message' => 'Список сотрудников',
            'data' => array_map(function ($account) {
                return $account->toArray();
            }, $employees),
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalEmployees, 
            ]
        ];

        http_response_code(200); 
        echo json_encode($response);

    } catch (Exception $e) {
        http_response_code(500); 
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(), 
            'errorCode' => $companyObj->getErrorCode(),
        ]);
    }

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false,  'error' => 'Метод не разрешен']); 
}