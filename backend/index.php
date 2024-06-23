<?php

header('Content-Type: application/json'); 
header('Access-Control-Allow-Origin: http://localhost:3000'); // Разрешить запросы с этого источника
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE'); // Разрешить эти HTTP-методы 
header('Access-Control-Allow-Headers: Content-Type'); // Разрешить этот заголовок
require_once 'includes/database.php';
require_once 'includes/account.php';

$db = new Database();
$accountObj = new Account($db->getConnection());
$accountId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// Обработка GET-запроса для получения списка аккаунтов (с пагинацией)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($accountId > 0) {
        // --- Получение одного аккаунта по ID ---

        if (!$accountObj->getAccountById($accountId)) {
            http_response_code(404); // Not Found
            echo json_encode([
                'success' => false,
                'error' => 'Аккаунт не найден',
                'errorCode' => ERROR_ACCOUNT_NOT_FOUND
            ]);
            exit;
        }

        // Отправка данных аккаунта
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Данные аккаунта',
            'data' => $accountObj->toArray()
        ]);

    } else {
        try {
            // Параметры пагинации 
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            $deleted = filter_var($_GET['deleted'] ?? false, FILTER_VALIDATE_BOOLEAN); 
            $totalAccounts = $accountObj->getTotalAccounts();
            if ($page <= 0 || $limit <= 0 || ($page-1) * $limit > $totalAccounts) {
                throw new Exception("Некорректные параметры пагинации."); 
            }

            $totalPages = ceil($totalAccounts / $limit);

            $accounts = $deleted ? $accountObj->getDeletedAccounts($page, $limit) : $accountObj->getAccounts($page, $limit);
            $accountData = [];
            foreach ($accounts as $account) {
                $accountData[] = [
                    'id' => $account->getId(),
                    'first_name' => $account->getFirstName(),
                    'last_name' => $account->getLastName(),
                    'email' => $account->getEmail(),
                    'company_name' => $account->getCompanyName(),
                    'position' => $account->getPosition(),
                    'phone_1' => $account->getPhone1(),
                    'phone_2' => $account->getPhone2(),
                    'phone_3' => $account->getPhone3(),
                    'deleted_at' => $account->getDeleted_At(),
                ];
            }
            // var_dump($accounts);
            // Формирование успешного ответа
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
            http_response_code(200); // OK
            // var_dump($accounts);
            echo json_encode($response); 

        } catch (Exception $e) {
            // Обработка ошибок
            http_response_code(400); // Bad Request 
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(), // Сообщение об ошибке
                'errorCode' => $accountObj->getErrorCode() // Код ошибки
            ]);
        }
    }

    } else {
        // Обработка других методов (не GET)
        http_response_code(405); // Method Not Allowed
        echo json_encode(['success' => false, 'error' => 'Метод не разрешен. Используйте GET' ]);
    }