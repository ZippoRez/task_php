<?php
header('Content-Type: application/json');

require_once 'includes/database.php';
require_once 'includes/company.php';
require_once 'includes/error_codes.php';

$db = new Database();
$companyObj = new Company($db->getConnection());

// Обработка CORS для всех запросов
header("Access-Control-Allow-Origin: http://localhost:3000"); // Замени на адрес фронтенда
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // --- Получение списка компаний ---
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    try {
        $companies = $companyObj->getCompanies($page, $limit);

        if ($companies === false) {
            throw new Exception($companyObj->getError()); 
        }

        $totalCompanies = $companyObj->getTotalCompanies();
        $totalPages = ceil($totalCompanies / $limit);

        $response = [
            'success' => true,
            'message' => 'Список компаний',
            'data' => array_map(function ($company) {
                return $company->toArray();
            }, $companies),
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalCompanies, 
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

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Создание новой компании ---
    $data = json_decode(file_get_contents('php://input'), true);

    if ($companyObj->createCompany($data)) {
        http_response_code(201); // Created
        echo json_encode([
            'success' => true,
            'message' => 'Компания успешно создана', 
            'data' => [
                'id' => $companyObj->getId()
            ]
        ]);
    } else {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'error' => $companyObj->getError(),
            'errorCode' => $companyObj->getErrorCode(),
        ]);
    }

} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false,  'error' => 'Метод не разрешен']); 
}