<?php

header('Content-Type: application/json'); 

require_once 'includes/database.php';
require_once 'includes/account.php';

$db = new Database();
$accountObj = new Account($db->getConnection());

// Обработка GET-запроса для получения списка аккаунтов (с пагинацией)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Параметры пагинации 
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

        $totalAccounts = $accountObj->getTotalAccounts();
        if ($page <= 0 || $limit <= 0 || ($page-1) * $limit > $totalAccounts) {
            throw new Exception("Некорректные параметры пагинации."); 
        }

        $totalPages = ceil($totalAccounts / $limit);

        $accounts = $accountObj->getAccounts($page, $limit);
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
                'phone_3' => $account->getPhone3()
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
} else {
    // Обработка других методов (не GET)
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Метод не разрешен. Используйте GET' ]);
}