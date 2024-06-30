<?php
// includes/account.php

require_once 'error_codes.php';
require_once '/var/www/site/task_php/backend/utils/helper.php';

error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

class Account
{
    private $db;
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    private $position;
    private $phone1;
    private $phone2;
    private $phone3;
    private $companyId; 
    private $deleted_at;

    private $lastError = "";
    private $errorCode;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // Getters 
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

    public function getCompanyId()
    {
        return $this->companyId;
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

    // Setters
    public function setId($id)
    {
        $this->id = (int)$id; 
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $this->filterString($firstName); 
    }

    public function setLastName($lastName)
    {
        $this->lastName = $this->filterString($lastName); 
    }

    public function setEmail($email)
    {
        $this->email = $this->filterString($email); 
    }

    public function setCompanyId($companyId)
    {
        $this->companyId = (int)$companyId; 
    }

    public function setPosition($position)
    {
        $this->position = (int)$position; 
    }

    public function setPhone1($phone1)
    {
        $this->phone1 = $this->normalizePhoneNumber($phone1); 
    }

    public function setPhone2($phone2)
    {
        $this->phone2 = $this->normalizePhoneNumber($phone2); 
    }

    public function setPhone3($phone3)
    {
        $this->phone3 = $this->normalizePhoneNumber($phone3); 
    }

    public function setDelete_At($deleted_at)
    {
        $this->deleted_at = $this->filterString($deleted_at); 
    }

    /**
     * Создает новый аккаунт или обновляет существующий.
     *
     * @return bool Возвращает true в случае успеха, false - в случае ошибки.
     */
    public function save()
    {
        if ($this->getId()) {
            // Если у объекта есть ID, значит это обновление
            return $this->updateAccount($this->toArray());
        } else {
            // Иначе - создание нового аккаунта
            return $this->createAccount($this);
        }
    }


    /**
     * Создает новый аккаунт пользователя.
     *
     * @param Account $account Объект Account с данными для создания.
     *
     * @return bool Возвращает true в случае успеха, false - в случае ошибки.
     */
    public function createAccount($data)
    {
        try {
            // Валидация данных перед созданием
            if ($this->validateData($data)) {
                $account = new Account($this->db); 
                $account->setFirstName($data['first_name']);
                $account->setLastName($data['last_name']);
                $account->setEmail($data['email']);
                $account->setCompanyId($data['company_id']);
                $account->setPosition($data['position']);
                $account->setPhone1($data['phone_1']); 
                $account->setPhone2($data['phone_2']); 
                $account->setPhone3($data['phone_3']);
                $sql = "INSERT INTO accounts (first_name, last_name, email, position, phone_1, phone_2, phone_3, company_id)
                        VALUES (:first_name, :last_name, :email, :position, :phone_1, :phone_2, :phone_3, :company_id)";
                $stmt = $this->db->prepare($sql); 

                $stmt->bindValue(':first_name',  $account->getFirstName(),  PDO::PARAM_STR); 
                $stmt->bindValue(':last_name',  $account->getLastName(),  PDO::PARAM_STR); 
                $stmt->bindValue(':email',  $account->getEmail(),  PDO::PARAM_STR);
                $stmt->bindValue(':position',  $account->getPosition(),  PDO::PARAM_INT);
                $stmt->bindValue(':phone_1',  $account->getPhone1(),  PDO::PARAM_STR); 
                $stmt->bindValue(':phone_2',  $account->getPhone2(),  PDO::PARAM_STR); 
                $stmt->bindValue(':phone_3',  $account->getPhone3(),  PDO::PARAM_STR); 
                if ($account->getCompanyId() === 0) { 
                    $stmt->bindValue(':company_id', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':company_id', $account->getCompanyId(), PDO::PARAM_INT); 
                }
                // $stmt->bindValue(':company_id', $account->getCompanyId(), PDO::PARAM_INT);

                $result = $stmt->execute();

                if ($result) {
                    $this->id = $this->db->lastInsertId();
                    return true; 
                } else {
                    $this->lastError = "Ошибка при выполнении запроса на создание аккаунта.";
                    error_log($this->lastError . ": " . json_encode($stmt->errorInfo()));
                    return false;
                }

            } else {
                error_log("Ошибка валидации данных при создании аккаунта: " . $this->lastError);
                return false; 
            }

        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при создании аккаунта: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            error_log($this->lastError);
            return false; 
        }
    }

    /**
     * Возвращает аккаунт по его ID.
     *
     * @param int $id ID аккаунта.
     *
     * @return Account|bool Возвращает объект Account, если аккаунт найден, иначе false.
     */
    public function getAccountById($id)
    {
        try {
            // Используем подготовленный запрос
            $sql = "SELECT * FROM accounts WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->id = $row['id'];
                $this->setFirstName($row['first_name']);
                $this->setLastName($row['last_name']);
                $this->setEmail($row['email']);
                $this->setCompanyId($row['company_id']);
                $this->setPosition($row['position']);
                $this->setPhone1($row['phone_1']);
                $this->setPhone2($row['phone_2']);
                $this->setPhone3($row['phone_3']);
                $this->setDelete_At($row['deleted_at']);
                return $this;
            } else {
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
                $account->setId($row['id']);
                $account->setFirstName($row['first_name']);
                $account->setLastName($row['last_name']);
                $account->setEmail($row['email']);
                $account->setCompanyId($row['company_id']);
                $account->setPosition($row['position']);
                $account->setPhone1($row['phone_1']);
                $account->setPhone2($row['phone_2']);
                $account->setPhone3($row['phone_3']);
                $account->setDelete_At($row['deleted_at']);
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

    /**
     * Обновляет данные аккаунта.
     *
     * @param array $data Массив с данными аккаунта (id, first_name, last_name, email, company_name, position, phone_1, phone_2, phone_3).
     *
     * @return bool Возвращает true в случае успеха, false - в случае ошибки.
     */
    public function updateAccount($data)
    {
        try {
            // Валидация данных перед обновлением
            if (!$this->validateData($data)) {
                error_log("Ошибка валидации данных при обновлении аккаунта: " . $this->lastError);
                return false;
            }

            // Используем сеттеры для установки значений свойств:
            $this->setFirstName($data['first_name']);
            $this->setLastName($data['last_name']);
            $this->setEmail($data['email']);
            $this->setCompanyId($data['company_id']);
            $this->setPosition($data['position']);
            $this->setPhone1($data['phone_1']);
            $this->setPhone2($data['phone_2']);
            $this->setPhone3($data['phone_3']);

            // Подготовка SQL запроса на обновление данных аккаунта
            $sql = "UPDATE accounts SET
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        company_id = :company_id,
                        position = :position,
                        phone_1 = :phone_1,
                        phone_2 = :phone_2,
                        phone_3 = :phone_3
                    WHERE id = :id";
            $stmt = $this->db->prepare($sql);

            // Связывание параметров, используя геттеры:
            $stmt->bindValue(':first_name', $this->getFirstName(), PDO::PARAM_STR);
            $stmt->bindValue(':last_name', $this->getLastName(), PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->getEmail(), PDO::PARAM_STR);
            $stmt->bindValue(':company_id', $this->getCompanyId(), PDO::PARAM_STR);
            $stmt->bindValue(':position', $this->getPosition(), PDO::PARAM_INT);
            $stmt->bindValue(':phone_1', $this->getPhone1(), PDO::PARAM_STR);
            $stmt->bindValue(':phone_2', $this->getPhone2(), PDO::PARAM_STR);
            $stmt->bindValue(':phone_3', $this->getPhone3(), PDO::PARAM_STR);
            $stmt->bindValue(':id', (int)$data['id'], PDO::PARAM_INT);

            // Выполнение запроса
            $result = $stmt->execute();

            if ($result) {
                return true;
            } else {
                $this->lastError = "Ошибка при выполнении запроса на обновление аккаунта.";
                error_log($this->lastError . ": " . json_encode($stmt->errorInfo()));
                return false;
            }

        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = 'Ошибка при обращении к базе данных' . $e->getMessage();
            $this->errorCode = $e->getCode();
            error_log($this->lastError);
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
    public function getAccounts($page, $limit, $deleted = false)
    {
        $offset = ($page - 1) * $limit;
        try {
            // Подготовка SQL запроса на получение аккаунтов с учетом пагинации
            $sql = "SELECT * FROM accounts";
            
            if ($deleted) {
                $sql .= " WHERE deleted_at IS NOT NULL";
            } else {
                $sql .= " WHERE deleted_at IS NULL";
            }

            $sql .= " LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            // Создание массива для хранения объектов аккаунтов
            $accounts = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Создание объекта Account для каждой строки результата
                $account = new Account($this->db);
                $account->setId($row['id']);
                $account->setFirstName($row['first_name']);
                $account->setLastName($row['last_name']);
                $account->setEmail($row['email']);
                $account->setCompanyId($row['company_id']);
                $account->setPosition($row['position']);
                $account->setPhone1($row['phone_1']);
                $account->setPhone2($row['phone_2']);
                $account->setPhone3($row['phone_3']);
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
            $sql = "SELECT COUNT(*) FROM accounts WHERE deleted_at IS NULL";
            $stmt = $this->db->query($sql);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            // Обработка ошибки базы данных
            $this->lastError = "Ошибка базы данных при получении количества аккаунтов: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return 0;
        }
    }
    
    // Метод для получения объекта Company, связанного с аккаунтом
    public function getCompany(): ?Company {
        if ($this->companyId !== null) {
            $company = new Company($this->db);
            return $company->getCompanyById($this->companyId);
        }
        return null; 
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

        // Перебираем все поля с номерами телефонов
        foreach ($phoneFields as $field) {
            if (!empty($data[$field])) {
                // Приводим номер к единому формату (например, с +7):
                $phoneNumber = $this->normalizePhoneNumber($data[$field]);

                // Подготовка SQL запроса для проверки на дублирование номера
                $sql = "SELECT COUNT(*) FROM accounts WHERE $field = :phoneNumber";
                if (!is_null($excludeId)) {
                    $sql .= " AND id != :excludeId";
                }

                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':phoneNumber', $phoneNumber);

                // Если передан $excludeId, добавляем его в параметры запроса
                if (!is_null($excludeId)) {
                    $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
                }
                $stmt->execute();

                // Если найден дубликат, возвращаем true
                if ($stmt->fetchColumn() > 0) {
                    return true;
                }
            }
        }
        // Если дубликатов не найдено, возвращаем false
        return false;
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

    // Метод для преобразования объекта Account в массив
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'company_id' => $this->companyId, 
            'position' => $this->position,
            'phone_1' => $this->phone1,
            'phone_2' => $this->phone2,
            'phone_3' => $this->phone3,
            'deleted_at' => $this->deleted_at,
        ];
    }

    /**
     * Фильтрует строку, удаляя потенциально опасные символы.
     *
     * @param string $string Строка для фильтрации.
     *
     * @return string Отфильтрованная строка.
     */
    public function filterString($string)
    {
        return htmlspecialchars(strip_tags($string), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Нормализует номер телефона к формату +7XXXXXXXXXX.
     *
     * @param string $phone Номер телефона.
     * @return string|false Нормализованный номер телефона или false, если номер некорректный.
     */
    private function normalizePhoneNumber($phone)
    {
        // Удаляем все символы, кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Если номер начинается с 8 или 7, заменяем на +7
        if (strlen($phone) == 11 && ($phone[0] == '8' || $phone[0] == '7')) {
            $phone = '+7' . substr($phone, 1);
        } elseif (strlen($phone) == 10) {
            // Если номер из 10 цифр (без кода страны), добавляем +7
            $phone = '+7' . $phone;
        } else if (strlen($phone) !== 12 || $phone[0] . $phone[1] !== '+7') {
            // Если номер не в формате +7XXXXXXXXXX, считаем его некорректным (можно вернуть false или пустую строку)
            return false;
        }

        return $phone;
    }
}