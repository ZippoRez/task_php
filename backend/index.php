<?php
// Устанавливаем Content-Type для JSON ответа
header('Content-Type: application/json');
// Настраиваем CORS
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type'); 

// Подключаем необходимые файлы
require_once 'includes/database.php';
require_once 'includes/account.php';

// Устанавливаем уровень логирования ошибок
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log'); 

// Создаем объекты для работы с базой данных и аккаунтами
$db = new Database();
$accountObj = new Account($db->getConnection());

// Получаем ID аккаунта из параметров запроса (если он передан)
$accountId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Обработка GET-запроса (получение списка аккаунтов или одного аккаунта по ID)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Если передан ID аккаунта, получаем информацию о нем
    if ($accountId > 0) {
        if (!$accountObj->getAccountById($accountId)) {
            // Если аккаунт не найден, отправляем ошибку 404 Not Found
            http_response_code(404); 
            echo json_encode([
                'success' => false,
                'error' => 'Аккаунт не найден',
                'errorCode' => ERROR_ACCOUNT_NOT_FOUND 
            ]);
            exit; 
        }

        // Отправляем данные аккаунта
        http_response_code(200); 
        echo json_encode([
            'success' => true,
            'message' => 'Данные аккаунта',
            'data' => $accountObj->toArray() 
        ]);
    } else {
        // Если ID аккаунта не передан, получаем список аккаунтов с пагинацией
        try {
            // Получаем параметры пагинации из запроса
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1; // Проверка на минимальное значение
            $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10; // Проверка на минимальное значение
            $deleted = filter_var($_GET['deleted'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Получаем общее количество аккаунтов (для пагинации)
            $totalAccounts = $accountObj->getTotalAccounts();

            // Проверка на корректность параметров пагинации
            if (($page - 1) * $limit > $totalAccounts) {
                throw new Exception("Некорректные параметры пагинации."); 
            }

            // Вычисляем количество страниц
            $totalPages = ceil($totalAccounts / $limit);

            // Получаем список аккаунтов (с учетом deleted)
            $accounts = $deleted ? $accountObj->getDeletedAccounts($page, $limit) : $accountObj->getAccounts($page, $limit);

            // Формируем массив данных аккаунтов для ответа
            $accountData = [];
            foreach ($accounts as $account) {
                $accountData[] = $account->toArray(); 
            }

            // Формирование ответа
            $response = [
                'success' => true,
                'message' => 'Список аккаунтов',
                'data' => $accountData,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages'  => $totalPages,
                    'totalItems'  => $totalAccounts,
                ]
            ];

            // Отправка ответа
            http_response_code(200); 
            echo json_encode($response);

        } catch (Exception $e) {
            // Логируем ошибку
            error_log("Ошибка при получении списка аккаунтов: " . $e->getMessage());

            // Отправляем сообщение об ошибке
            http_response_code(400); 
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'errorCode' => $accountObj->getErrorCode() 
            ]);
        }
    }
} else {
    // Обработка других HTTP-методов (не GET)
    http_response_code(405); 
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен. Используйте GET']);
}