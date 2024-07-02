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
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Устанавливаем код ответа 204 (No Content) и завершаем выполнение скрипта
    http_response_code(204);
    exit;
}

// Обработка GET-запросов
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // --- Получение списка компаний ---

    // Получаем номер страницы и количество элементов на странице из параметров запроса
    // По умолчанию, страница 1, количество элементов - 10
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    try {
        // Получаем список компаний с помощью метода getCompanies
        $companies = $companyObj->getCompanies($page, $limit);

        // Проверяем, не возникла ли ошибка при получении списка компаний
        if ($companies === false) {
            // Если возникла ошибка, выбрасываем исключение с текстом ошибки
            throw new Exception($companyObj->getError()); 
        }

        // Получаем общее количество компаний и вычисляем количество страниц
        $totalCompanies = $companyObj->getTotalCompanies();
        $totalPages = ceil($totalCompanies / $limit);

        // Формируем массив ответа
        $response = [
            'success' => true,
            'message' => 'Список компаний',
            'data' => array_map(function ($company) {
                // Преобразуем каждый объект Company в массив
                return $company->toArray();
            }, $companies),
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalCompanies, 
            ]
        ];

        // Устанавливаем код ответа 200 (OK) и выводим массив ответа в формате JSON
        http_response_code(200); 
        echo json_encode($response);

    } catch (Exception $e) {
        // В случае возникновения исключения, устанавливаем код ответа 500 (Internal Server Error)
        // и выводим сообщение об ошибке в формате JSON
        http_response_code(500); 
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(), 
            'errorCode' => $companyObj->getErrorCode(),
        ]);
    }

// Обработка POST-запросов
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Создание новой компании ---

    // Получаем данные новой компании из тела запроса
    $data = json_decode(file_get_contents('php://input'), true);

    // Пытаемся создать новую компанию с помощью метода createCompany
    if ($companyObj->createCompany($data)) {
        // Если компания успешно создана, устанавливаем код ответа 201 (Created)
        // и выводим сообщение об успехе в формате JSON
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Компания успешно создана', 
            'data' => [
                'id' => $companyObj->getId()
            ]
        ]);
    } else {
        // Если произошла ошибка при создании компании, устанавливаем код ответа 400 (Bad Request)
        // и выводим сообщение об ошибке в формате JSON
        http_response_code(400); 
        echo json_encode([
            'success' => false,
            'error' => $companyObj->getError(),
            'errorCode' => $companyObj->getErrorCode(),
        ]);
    }

// Обработка запросов с неподдерживаемыми методами
} else {
    // Устанавливаем код ответа 405 (Method Not Allowed) и выводим сообщение об ошибке в формате JSON
    http_response_code(405);
    echo json_encode(['success' => false,  'error' => 'Метод не разрешен']); 
}