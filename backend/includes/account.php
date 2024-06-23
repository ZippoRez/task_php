<?php
// Подключаем файл с кодами ошибок
require_once 'error_codes.php';
// Подключаем файл с вспомогательными функциями (предположительно, валидация)
require_once '/var/www/site/task_php/backend/utils/helper.php';

// Устанавливаем уровень логирования ошибок
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Класс для работы с аккаунтами пользователей
class Account
{
    // Приватные свойства для хранения данных аккаунта
    private $db; // Подключение к базе данных
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $companyName;
    private $position;
    private $phone1;
    private $phone2;
    private $phone3;
    private $deleted_at;

    // Свойства для хранения информации об ошибках
    private $lastError = "";
    private $errorCode;

    // Конструктор класса. Устанавливает подключение к базе данных
    public function __construct($db)
    {
        $this->db = $db;
    }

    // Геттеры для свойств класса
    public function getId()
    {
        return $this->id;
    }
    public function getFirstName()
    {
        return $this->firstName;
    }
    public function getLastName()
    {
        return $this->lastName;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getCompanyName()
    {
        return $this->companyName;
    }
    public function getPosition()
    {
        return $this->position;
    }
    public function getPhone1()
    {
        return $this->phone1;
    }
    public function getPhone2()
    {
        return $this->phone2;
    }
    public function getPhone3()
    {
        return $this->phone3;
    }
    public function getDeleted_At()
    {
        return $this->deleted_at;
    }
    public function getError()
    {
        return $this->lastError;
    }
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    // Сеттеры для свойств класса
    public function setId($id)
    {
        $this->id = (int) $id;
    }
    public function setFirstName($firstName)
    {
        $this->firstName = (string) $firstName;
    }
    public function setLastName($lastName)
    {
        $this->lastName = (string) $lastName;
    }
    public function setEmail($email)
    {
        $this->email = (string) $email;
    }
    public function setCompanyName($companyName)
    {
        $this->companyName = (string) $companyName;
    }
    public function setPosition($position)
    {
        $this->position = (int) $position;
    }
    public function setPhone1($phone1)
    {
        $this->phone1 = (string) $phone1;
    }
    public function setPhone2($phone2)
    {
        $this->phone2 = (string) $phone2;
    }
    public function setPhone3($phone3)
    {
        $this->phone3 = (string) $phone3;
    }
    public function setDelete_At($deleted_at)
    {
        $this->deleted_at = (string) $deleted_at;
    }

    // Метод для создания нового аккаунта
    public function createAccount($data)
    {
        try {
            // Валидация данных перед созданием
            if (!$this->validateData($data)) {
                return false;
            }

            // Подготовка SQL запроса на добавление данных в таблицу accounts
            $sql = "INSERT INTO accounts (first_name, last_name, email, company_name, position, phone_1, phone_2, phone_3)
                VALUES (:first_name, :last_name, :email, :company_name, :position, :phone_1, :phone_2, :phone_3)";
            $stmt = $this->db->prepare($sql);

            // Фильтрация и связывание параметров:
            $stmt->bindValue(':first_name', $this->filterString($data['first_name']), PDO::PARAM_STR);
            $stmt->bindValue(':last_name', $this->filterString($data['last_name']), PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->filterString($data['email']), PDO::PARAM_STR);
            $stmt->bindValue(':company_name', $this->filterString($data['company_name']), PDO::PARAM_STR);
            $stmt->bindValue(':position', $this->filterString($data['position']), PDO::PARAM_INT);
            $stmt->bindValue(':phone_1', $this->filterString($data['phone_1']), PDO::PARAM_STR);
            $stmt->bindValue(':phone_2', $this->filterString($data['phone_2']), PDO::PARAM_STR);
            $stmt->bindValue(':phone_3', $this->filterString($data['phone_3']), PDO::PARAM_STR);

            // Выполнение запроса с переданными данными
            $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email'],
                ':company_name' => $data['company_name'],
                ':position' => $data['position'],
                ':phone_1' => $data['phone_1'],
                ':phone_2' => $data['phone_2'],
                ':phone_3' => $data['phone_3'],
            ]);

            // Получение ID созданного аккаунта
            $this->id = $this->db->lastInsertId();

            return true;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = "Ошибка в базе при создании аккаунта: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения аккаунта по ID
    public function getAccountById($id)
    {
        try {
            // Подготовка SQL запроса для получения данных аккаунта по ID
            $sql = "SELECT * FROM accounts WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);

            // Получение результата запроса
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Если аккаунт найден, устанавливаем значения свойств объекта
            if ($row) {
                $this->id = $row['id'];
                $this->firstName = $row['first_name'];
                $this->lastName = $row['last_name'];
                $this->email = $row['email'];
                $this->companyName = $row['company_name'];
                $this->position = $row['position'];
                $this->phone1 = $row['phone_1'];
                $this->phone2 = $row['phone_2'];
                $this->phone3 = $row['phone_3'];
                $this->deleted_at = $row['deleted_at'];
                return $this;
            } else {
                // Если аккаунт не найден, устанавливаем сообщение об ошибке
                $this->lastError = 'Аккаунт не найден';
                $this->errorCode = ERROR_ACCOUNT_NOT_FOUND;
                return false;
            }
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = 'Ошибка при обращении к базе данных:' . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения списка "мягко" удаленных аккаунтов с пагинацией
    public function getDeletedAccounts($page, $limit)
    {
        $offset = ($page - 1) * $limit;
        try {
            // Подготовка SQL запроса для получения "мягко" удаленных аккаунтов с учетом пагинации
            $sql = "SELECT * FROM accounts WHERE deleted_at IS NOT NULL LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            // Создание массива для хранения объектов аккаунтов
            $accounts = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Создание объекта Account для каждой строки результата
                $account = new Account($this->db);
                $account->id = $row['id'];
                $account->firstName = $row['first_name'];
                $account->lastName = $row['last_name'];
                $account->email = $row['email'];
                $account->companyName = $row['company_name'];
                $account->position = $row['position'];
                $account->phone1 = $row['phone_1'];
                $account->phone2 = $row['phone_2'];
                $account->phone3 = $row['phone_3'];
                $account->deleted_at = $row['deleted_at'];

                // Добавление объекта Account в массив
                $accounts[] = $account;
            }
            return $accounts;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = "Ошибка базы данных при получении списка аккаунтов: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для обновления данных аккаунта
    public function updateAccount($data)
    {
        try {
            // Валидация данных перед обновлением
            if (!$this->validateData($data)) {
                return false;
            }

            // Подготовка SQL запроса на обновление данных аккаунта
            $sql = "UPDATE accounts SET
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        company_name = :company_name,
                        position = :position,
                        phone_1 = :phone_1,
                        phone_2 = :phone_2,
                        phone_3 = :phone_3
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            // Фильтрация и связывание параметров (необязательные поля могут быть NULL)
            $stmt->bindValue(':first_name', $this->filterString($data['first_name']), PDO::PARAM_STR);
            $stmt->bindValue(':last_name', $this->filterString($data['last_name']), PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->filterString($data['email']), PDO::PARAM_STR);
            $stmt->bindValue(':company_name', $this->filterString($data['company_name'] ?? null), PDO::PARAM_STR);
            $stmt->bindValue(':position', (int)($data['position'] ?? null), PDO::PARAM_INT);
            $stmt->bindValue(':phone_1', $this->filterString($data['phone_1'] ?? null), PDO::PARAM_STR);
            $stmt->bindValue(':phone_2', $this->filterString($data['phone_2'] ?? null), PDO::PARAM_STR);
            $stmt->bindValue(':phone_3', $this->filterString($data['phone_3'] ?? null), PDO::PARAM_STR);
            $stmt->bindValue(':id', (int)$data['id'], PDO::PARAM_INT);
            // Выполнение запроса с переданными данными
            $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name'  => $data['last_name'],
                ':email'      => $data['email'],
                ':company_name' => $data['company_name'] ?? null,
                ':position'   => $data['position'] ?? null,
                ':phone_1'    => $data['phone_1'] ?? null,
                ':phone_2'    => $data['phone_2'] ?? null,
                ':phone_3'    => $data['phone_3'] ?? null,
                ':id'         => $data['id']
            ]);
            return true;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = 'Ошибка при обращении к базе данных' . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для "мягкого" удаления аккаунта (установка deleted_at)
    public function deleteAccount($id)
    {
        try {
            // Подготовка SQL запроса на установку deleted_at для аккаунта
            $sql = "UPDATE accounts SET deleted_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = "Ошибка базы данных при удалении аккаунта: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для окончательного удаления аккаунта из базы данных
    public function permanentDeleteAccount($id)
    {
        try {
            // Проверка, был ли аккаунт предварительно "мягко" удален
            $sql = "SELECT deleted_at FROM accounts WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $deletedAt = $stmt->fetchColumn();

            // Если аккаунт не был "мягко" удален, возвращаем ошибку
            if (is_null($deletedAt)) {
                $this->lastError = "Ошибка: Нельзя окончательно удалить аккаунт, который не был помечен как удаленный.";
                $this->errorCode = ERROR_DB_DELETE;
                return false;
            }

            // Если аккаунт был "мягко" удален, выполняем окончательное удаление
            $sql = "DELETE FROM accounts WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = "Ошибка базы данных при удалении аккаунта: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения списка аккаунтов с пагинацией
    public function getAccounts($page, $limit)
    {
        $offset = ($page - 1) * $limit;
        try {
            // Подготовка SQL запроса на получение аккаунтов с учетом пагинации
            $sql = "SELECT * FROM accounts WHERE deleted_at IS NULL LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            // Создание массива для хранения объектов аккаунтов
            $accounts = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Создание объекта Account для каждой строки результата
                $account = new Account($this->db);
                $account->id = $row['id'];
                $account->firstName = $row['first_name'];
                $account->lastName = $row['last_name'];
                $account->email = $row['email'];
                $account->companyName = $row['company_name'];
                $account->position = $row['position'];
                $account->phone1 = $row['phone_1'];
                $account->phone2 = $row['phone_2'];
                $account->phone3 = $row['phone_3'];

                // Добавление объекта Account в массив
                $accounts[] = $account;
            }
            return $accounts;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = "Ошибка базы данных при получении списка аккаунтов: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения общего количества аккаунтов
    public function getTotalAccounts()
    {
        try {
            // Подготовка SQL запроса для получения количества аккаунтов
            $sql = "SELECT COUNT(*) FROM accounts";
            $stmt = $this->db->query($sql);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = "Ошибка базы данных при получении количества аккаунтов: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return 0;
        }
    }

    // Приватный метод для валидации данных аккаунта
    private function validateData($data)
    {
        // Проверка на пустые обязательные поля
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            $this->lastError = "Ошибка: Имя, Фамилия и Email являются обязательными полями.";
            $this->errorCode = ERROR_EMPTY_FIELDS;
            return false;
        }

        // Валидация email с использованием функции из helper.php
        if (!validateEmail($data['email'])) {
            $this->lastError = "Ошибка: Неверный формат email.";
            $this->errorCode = ERROR_INVALID_EMAIL;
            return false;
        }

        // Проверка на уникальность email (кроме случая обновления собственного email)
        if ($this->isEmailExists($data['email'], $this->id)) {
            $this->lastError = "Ошибка: Пользователь с таким email уже существует.";
            $this->errorCode = ERROR_EMAIL_EXISTS;
            return false;
        }

        // Проверка на дублирование номеров телефонов среди других аккаунтов
        if ($this->isPhoneDuplicate($data, $this->id)) {
            $this->lastError = "Ошибка: Один из номеров телефонов уже используется другим аккаунтом.";
            $this->errorCode = ERROR_INVALID_PHONE;
            return false;
        }

        // Проверка на дублирование номеров телефонов внутри аккаунта
        if ($this->hasDuplicatePhones($data)) {
            $this->lastError = "Ошибка: У пользователя не может быть одинаковых номеров телефонов.";
            $this->errorCode = ERROR_INVALID_PHONE;
            return false;
        }

        // Валидация формата номеров телефонов с использованием функции из helper.php
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($data['phone_' . $i])) {
                if (!validateRussianPhone($data['phone_' . $i])) {
                    $this->lastError = "Ошибка: Неверный формат телефона в поле 'Телефон $i'.";
                    $this->errorCode = ERROR_INVALID_PHONE;
                    return false;
                }
            }
        }

        return true; // Все проверки пройдены
    }

    // Приватный метод для проверки существования email в базе данных
    private function isEmailExists($email, $excludeId = null): bool
    {
        try {
            // Подготовка SQL запроса для проверки существования email
            $sql = "SELECT COUNT(*) FROM accounts WHERE email = :email";
            if (!is_null($excludeId)) {
                $sql .= " AND id != :excludeId";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);

            // Если передан $excludeId, добавляем его в параметры запроса
            if (!is_null($excludeId)) {
                $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            }

            $stmt->execute();

            // Возвращаем true, если email найден, иначе false
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = "Ошибка базы данных при проверке email: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false; // В случае ошибки считаем, что email уже существует 
        }
    }

    // Приватный метод для проверки дублирования номеров телефонов среди аккаунтов
    private function isPhoneDuplicate($data, $excludeId = null): bool
{
    $phoneFields = ['phone_1', 'phone_2', 'phone_3'];

    foreach ($phoneFields as $field) {
        if (!empty($data[$field])) {
            $phoneNumber = $this->normalizePhoneNumber($data[$field]);
            $sql = "SELECT COUNT(*) FROM accounts WHERE $field = :phoneNumber";
            
            // Исправленное условие:
            if (!is_null($excludeId)) {
                $sql .= " AND id != :excludeId"; // AND должно быть внутри условия
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':phoneNumber', $phoneNumber);
            
            if (!is_null($excludeId)) {
                $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            }
            $stmt->execute();

            if ($stmt->fetchColumn() > 0) {
                return true; // Найден дубликат
            }
        }
    }
    return false; // Дубликатов нет
}

    // Приватный метод для проверки на дубликаты номеров телефонов внутри аккаунта
    private function hasDuplicatePhones($data): bool
    {
        // Создаем массив с номерами телефонов, исключая пустые значения
        $phoneNumbers = array_filter([
            $data['phone_1'],
            $data['phone_2'],
            $data['phone_3']
        ], function ($phone) {
            return !empty($phone);
        });

        // Сравниваем количество элементов в массиве с количеством уникальных элементов
        // Если они не равны, значит есть дубликаты
        return count($phoneNumbers) !== count(array_unique($phoneNumbers));
    }
    // Нормализация номера для исключения случаев дублирования с разными началами +7/8
    private function normalizePhoneNumber($phone) {
        // Удаляем все символы, кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);
    
        // Если номер начинается с 8 или 7, заменяем на +7
        if (strlen($phone) == 11 && ($phone[0] == '8' || $phone[0] == '7')) {
            $phone = '+7' . substr($phone, 1);
        } elseif (strlen($phone) == 10) {
            // Если номер из 10 цифр (без кода страны), добавляем +7
            $phone = '+7' . $phone; 
        } else if (strlen($phone) !== 12 ||  $phone[0] . $phone[1] !== '+7') {
            // Если номер не в формате +7XXXXXXXXXX, считаем его некорректным (можно вернуть false или пустую строку)
            return false;
        }
    
        return $phone;
    }

    // Метод для преобразования объекта Account в массив
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'company_name' => $this->companyName,
            'position' => $this->position,
            'phone_1' => $this->phone1,
            'phone_2' => $this->phone2,
            'phone_3' => $this->phone3,
            'deleted_at' => $this->deleted_at,
        ];
    }

    //Фильтрует строку, удаляя потенциально опасные символы.
    public function filterString($string)
    {
        return htmlspecialchars(strip_tags($string), ENT_QUOTES, 'UTF-8');
    }
}
