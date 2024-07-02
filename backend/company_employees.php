<?php
// Устанавливаем заголовок Content-Type для ответа в формате JSON
header('Content-Type: application/json');

// Подключаем необходимые файлы
require_once 'includes/database.php';
require_once 'includes/company.php';
require_once 'includes/account.php';
require_once 'includes/error_codes.php';

// Создаем экземпляры классов Database, Company и Account
$db = new Database();
$companyObj = new Company($db->getConnection());
$accountObj = new Account($db->getConnection());

// Устанавливаем заголовки CORS для разрешения кросс-доменных запросов
// Важно: замените http://localhost:3000 на адрес вашего фронтенд-приложения
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Обработка preflight-запросов (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Устанавливаем код ответа 204 (No Content) и завершаем выполнение скрипта
    http_response_code(204); 
    exit;
}

// Обработка GET-запросов
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // --- Получение списка сотрудников компании ---

    // Получаем ID компании, номер страницы и количество элементов на странице из параметров запроса
    $companyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    // Проверяем, передан ли ID компании
    if ($companyId <= 0) {
        // Если ID компании не передан или некорректный, 
        // устанавливаем код ответа 400 (Bad Request) и выводим сообщение об ошибке
        http_response_code(400); 
        echo json_encode(['success' => false, 'error' => 'Неверный ID компании']); 
        exit; 
    }

    try {
        // Получаем список сотрудников компании с помощью метода getEmployees
        $employees = $companyObj->getEmployees($companyId, $page, $limit);

        // Проверяем, не возникла ли ошибка при получении списка сотрудников
        if ($employees === false) {
            // Если возникла ошибка, выбрасываем исключение с текстом ошибки
            throw new Exception($companyObj->getError()); 
        }

        // Получаем общее количество сотрудников компании и вычисляем количество страниц
        $totalEmployees = $companyObj->getTotalAccountsByCompanyId($companyId);
        $totalPages = ceil($totalEmployees / $limit);

        // Формируем массив ответа
        $response = [
            'success' => true,
            'message' => 'Список сотрудников',
            'data' => array_map(function ($account) {
                // Преобразуем каждый объект Account в массив
                return $account->toArray();
            }, $employees),
            'pagination' => [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalItems' => $totalEmployees, 
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

// Обработка запросов с неподдерживаемыми методами
} else {
    // Устанавливаем код ответа 405 (Method Not Allowed) и выводим сообщение об ошибке в формате JSON
    http_response_code(405); 
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен']); 
}