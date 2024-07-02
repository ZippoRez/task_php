<?php
// includes/account.php

// Подключаем файл с кодами ошибок
require_once 'error_codes.php';
// Подключаем файл с вспомогательными функциями
require_once '/var/www/site/task_php/backend/utils/helper.php';

// Включаем отображение всех ошибок
error_reporting(E_ALL);
// Включаем логирование ошибок
ini_set('log_errors', 1);
// Устанавливаем путь к файлу лога ошибок
ini_set('error_log', __DIR__ . '/error.log');

/**
 * Класс Account представляет собой модель для работы с аккаунтами пользователей.
 */
class Account
{
    // Подключение к базе данных
    private $db;
    // ID аккаунта
    private $id;
    // Имя пользователя
    private $firstName;
    // Фамилия пользователя
    private $lastName;
    // Email пользователя
    private $email;
    // Должность пользователя
    private $position;
    // Номер телефона 1
    private $phone1;
    // Номер телефона 2
    private $phone2;
    // Номер телефона 3
    private $phone3;
    // ID компании, к которой принадлежит пользователь
    private $companyId; 
    // Дата и время "мягкого" удаления аккаунта
    private $deleted_at;

    // Текст последней ошибки
    private $lastError = "";
    // Код последней ошибки
    private $errorCode;

    /**
     * Конструктор класса Account.
     *
     * @param PDO $db Подключение к базе данных.
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    // Геттеры для всех свойств класса
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

    // Сеттеры для всех свойств класса
    public function setId($id)
    {
        // Приводим ID к целочисленному типу
        $this->id = (int)$id; 
    }

    public function setFirstName($firstName)
    {
        // Фильтруем строку имени от опасных символов
        $this->firstName = $this->filterString($firstName); 
    }

    public function setLastName($lastName)
    {
        // Фильтруем строку фамилии от опасных символов
        $this->lastName = $this->filterString($lastName); 
    }

    public function setEmail($email)
    {
        // Фильтруем строку email от опасных символов
        $this->email = $this->filterString($email); 
    }

    public function setCompanyId($companyId)
    {
        // Приводим ID компании к целочисленному типу
        $this->companyId = (int)$companyId; 
    }

    public function setPosition($position)
    {
        // Фильтруем строку должности от опасных символов
        $this->position = $this->filterString($position); 
    }

    public function setPhone1($phone1)
    {
        // Нормализуем номер телефона 1
        $this->phone1 = $this->normalizePhoneNumber($phone1); 
    }

    public function setPhone2($phone2)
    {
        // Нормализуем номер телефона 2
        $this->phone2 = $this->normalizePhoneNumber($phone2); 
    }

    public function setPhone3($phone3)
    {
        // Нормализуем номер телефона 3
        $this->phone3 = $this->normalizePhoneNumber($phone3); 
    }

    public function setDelete_At($deleted_at)
    {
        // Фильтруем строку даты и времени "мягкого" удаления от опасных символов
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
                
                // Создание нового объекта Account с данными из переданного массива
                $account = new Account($this->db); 
                $account->setFirstName($data['first_name']);
                $account->setLastName($data['last_name']);
                $account->setEmail($data['email']);
                $account->setCompanyId($data['company_id']);
                $account->setPosition($data['position']);
                $account->setPhone1($data['phone_1']); 
                $account->setPhone2($data['phone_2']); 
                $account->setPhone3($data['phone_3']);

                // Подготовка SQL-запроса на добавление данных в таблицу accounts
                $sql = "INSERT INTO accounts (first_name, last_name, email, position, phone_1, phone_2, phone_3, company_id)
                        VALUES (:first_name, :last_name, :email, :position, :phone_1, :phone_2, :phone_3, :company_id)";
                $stmt = $this->db->prepare($sql);
                // Привязка значений к параметрам запроса с использованием геттеров объекта $account
                $stmt->bindValue(':first_name',  $account->getFirstName(),  PDO::PARAM_STR); 
                $stmt->bindValue(':last_name',  $account->getLastName(),  PDO::PARAM_STR); 
                $stmt->bindValue(':email',  $account->getEmail(),  PDO::PARAM_STR);
                $stmt->bindValue(':position',  $account->getPosition(),  PDO::PARAM_STR);
                $stmt->bindValue(':phone_1',  $account->getPhone1(),  PDO::PARAM_STR); 
                $stmt->bindValue(':phone_2',  $account->getPhone2(),  PDO::PARAM_STR); 
                $stmt->bindValue(':phone_3',  $account->getPhone3(),  PDO::PARAM_STR); 
                // Проверка на пустое значение company_id перед привязкой
                if ($account->getCompanyId() === 0 || $account->getCompanyId() === null) { 
                    $stmt->bindValue(':company_id', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindValue(':company_id', $account->getCompanyId(), PDO::PARAM_INT); 
                }

                // Выполнение подготовленного запроса
                $result = $stmt->execute();

                // Обработка результата выполнения запроса
                if ($result) {
                    // В случае успешного выполнения запроса сохраняем ID созданного аккаунта
                    $this->id = $this->db->lastInsertId();
                    return true; 
                } else {
                    // В случае ошибки выполнения запроса записываем сообщение об ошибке в лог и свойство lastError
                    $this->lastError = "Ошибка при выполнении запроса на создание аккаунта.";
                    error_log($this->lastError . ": " . json_encode($stmt->errorInfo()));
                    return false;
                }

            } else {
                // В случае ошибки валидации данных записываем сообщение об ошибке в лог
                error_log("Ошибка валидации данных при создании аккаунта: " . $this->lastError);
                return false; 
            }

        } catch (PDOException $e) {
            // В случае исключения PDOException записываем сообщение об ошибке и код ошибки в соответствующие свойства и лог
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
            // Подготавливаем SQL-запрос для выборки данных аккаунта по ID
            $sql = "SELECT * FROM accounts WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            // Привязываем значение ID к параметру запроса
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            // Выполняем подготовленный запрос
            $stmt->execute();

            // Получаем результат запроса в виде ассоциативного массива
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Если данные аккаунта найдены, заполняем свойства объекта Account
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
                // Если данные аккаунта не найдены, устанавливаем сообщение об ошибке и код ошибки
                $this->lastError = 'Аккаунт не найден';
                $this->errorCode = ERROR_ACCOUNT_NOT_FOUND;
                return false;
            }
        } catch (PDOException $e) {
            // Обработка ошибки базы данных - устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = 'Ошибка при обращении к базе данных:' . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения списка "мягко" удаленных аккаунтов с пагинацией
    public function getDeletedAccounts($page, $limit)
    {
        // Вычисляем смещение для запроса с пагинацией
        $offset = ($page - 1) * $limit;
        try {
            // Подготавливаем SQL-запрос для выборки "мягко" удаленных аккаунтов с учетом пагинации
            $sql = "SELECT * FROM accounts WHERE deleted_at IS NOT NULL LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            // Привязываем значения limit и offset к параметрам запроса
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            // Выполняем подготовленный запрос
            $stmt->execute();

            // Создаем пустой массив для хранения объектов аккаунтов
            $accounts = [];
            // Перебираем результаты запроса
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Создаем новый объект Account для каждой строки результата
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
                // Добавляем объект Account в массив
                $accounts[] = $account;
            }
            // Возвращаем массив объектов аккаунтов
            return $accounts;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных - устанавливаем сообщение об ошибке и код ошибки
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
                // Если валидация не пройдена, записываем сообщение об ошибке в лог
                error_log("Ошибка валидации данных при обновлении аккаунта: " . $this->lastError);
                return false;
            }

            // Устанавливаем значения свойств объекта Account из переданного массива данных
            $this->setFirstName($data['first_name']);
            $this->setLastName($data['last_name']);
            $this->setEmail($data['email']);
            $this->setCompanyId($data['company_id']);
            $this->setPosition($data['position']);
            $this->setPhone1($data['phone_1']);
            $this->setPhone2($data['phone_2']);
            $this->setPhone3($data['phone_3']);

            // Подготавливаем SQL-запрос на обновление данных аккаунта
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

            // Привязываем значения свойств объекта Account к параметрам запроса
            $stmt->bindValue(':first_name', $this->getFirstName(), PDO::PARAM_STR);
            $stmt->bindValue(':last_name', $this->getLastName(), PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->getEmail(), PDO::PARAM_STR);
            $stmt->bindValue(':position', $this->getPosition(), PDO::PARAM_STR);
            $stmt->bindValue(':phone_1', $this->getPhone1(), PDO::PARAM_STR);
            $stmt->bindValue(':phone_2', $this->getPhone2(), PDO::PARAM_STR);
            $stmt->bindValue(':phone_3', $this->getPhone3(), PDO::PARAM_STR);
            $stmt->bindValue(':id', (int)$data['id'], PDO::PARAM_INT);

            // Проверка на пустое значение company_id перед привязкой
            if ($this->getCompanyId() === 0 || $this->getCompanyId() === null) { 
                $stmt->bindValue(':company_id', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':company_id', $this->getCompanyId(), PDO::PARAM_INT); 
            }

            // Выполняем подготовленный запрос
            $result = $stmt->execute();

            // Обработка результата выполнения запроса
            if ($result) {
                return true;
            } else {
                // Если произошла ошибка при выполнении запроса, записываем сообщение об ошибке в лог
                $this->lastError = "Ошибка при выполнении запроса на обновление аккаунта.";
                error_log($this->lastError . ": " . json_encode($stmt->errorInfo()));
                return false;
            }

        } catch (PDOException $e) {
            // Обработка ошибки базы данных - устанавливаем сообщение об ошибке и код ошибки
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
            // Подготавливаем SQL-запрос на обновление поля deleted_at у аккаунта с заданным ID
            $sql = "UPDATE accounts SET deleted_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            // Выполняем подготовленный запрос, передавая ID аккаунта в качестве параметра
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных - устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка базы данных при удалении аккаунта: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для окончательного удаления аккаунта из базы данных
    public function permanentDeleteAccount($id)
    {
        try {
            // Подготавливаем SQL-запрос для проверки, был ли аккаунт предварительно "мягко" удален
            $sql = "SELECT deleted_at FROM accounts WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            // Выполняем подготовленный запрос, передавая ID аккаунта в качестве параметра
            $stmt->execute([':id' => $id]);
            // Получаем значение поля deleted_at
            $deletedAt = $stmt->fetchColumn();

            // Если аккаунт не был "мягко" удален, устанавливаем сообщение об ошибке и код ошибки
            if (is_null($deletedAt)) {
                $this->lastError = "Ошибка: Нельзя окончательно удалить аккаунт, который не был помечен как удаленный.";
                $this->errorCode = ERROR_DB_DELETE;
                return false;
            }

            // Если аккаунт был "мягко" удален, выполняем окончательное удаление
            $sql = "DELETE FROM accounts WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            // Выполняем подготовленный запрос, передавая ID аккаунта в качестве параметра
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных - устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка базы данных при удалении аккаунта: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения списка аккаунтов с пагинацией
    public function getAccounts($page, $limit, $deleted = false)
    {
        // Вычисляем смещение для запроса с пагинацией
        $offset = ($page - 1) * $limit;
        try {
            // Формируем SQL-запрос для выборки аккаунтов с учетом пагинации
            $sql = "SELECT * FROM accounts";
            
            // Добавляем условие WHERE для выборки удаленных или неудаленных аккаунтов
            if ($deleted) {
                $sql .= " WHERE deleted_at IS NOT NULL";
            } else {
                $sql .= " WHERE deleted_at IS NULL";
            }

            // Добавляем LIMIT и OFFSET для пагинации
            $sql .= " LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            // Привязываем значения limit и offset к параметрам запроса
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            // Выполняем подготовленный запрос
            $stmt->execute();

            // Создаем пустой массив для хранения объектов аккаунтов
            $accounts = [];
            // Перебираем результаты запроса
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Создаем новый объект Account для каждой строки результата
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
                // Добавляем объект Account в массив
                $accounts[] = $account;
            }
            // Возвращаем массив объектов аккаунтов
            return $accounts;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных - устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка базы данных при получении списка аккаунтов: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения общего количества аккаунтов
    public function getTotalAccounts()
    {
        try {
            // Подготавливаем SQL-запрос для получения количества аккаунтов, у которых deleted_at IS NULL
            $sql = "SELECT COUNT(*) FROM accounts WHERE deleted_at IS NULL";
            $stmt = $this->db->query($sql);
            // Возвращаем количество аккаунтов
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            // Обработка ошибки базы данных - устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка базы данных при получении количества аккаунтов: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            // В случае ошибки возвращаем 0
            return 0;
        }
    }
    
    // Метод для получения объекта Company, связанного с аккаунтом
    public function getCompany(): ?Company {
        // Проверяем, установлен ли ID компании
        if ($this->companyId !== null) {
            // Создаем объект Company и получаем данные компании по ID
            $company = new Company($this->db);
            return $company->getCompanyById($this->companyId);
        }
        // Если ID компании не установлен, возвращаем null
        return null; 
    }

    // Приватный метод для валидации данных аккаунта
    private function validateData($data)
    {
        // Проверка на пустые обязательные поля (имя, фамилия, email)
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            // Если найдено пустое обязательное поле, устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка: Имя, Фамилия и Email являются обязательными полями.";
            $this->errorCode = ERROR_EMPTY_FIELDS;
            return false;
        }

        // Валидация email с использованием функции validateEmail из файла helper.php
        if (!validateEmail($data['email'])) {
            // Если email не прошел валидацию, устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка: Неверный формат email.";
            $this->errorCode = ERROR_INVALID_EMAIL;
            return false;
        }

        // Проверка на уникальность email (кроме случая обновления собственного email)
        if ($this->isEmailExists($data['email'], $this->id)) {
            // Если email уже существует, устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка: Пользователь с таким email уже существует.";
            $this->errorCode = ERROR_EMAIL_EXISTS;
            return false;
        }

        // Проверка на дублирование номеров телефонов среди других аккаунтов
        if ($this->isPhoneDuplicate($data, $this->id)) {
            // Если найден дубликат номера телефона, устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка: Один из номеров телефонов уже используется другим аккаунтом.";
            $this->errorCode = ERROR_INVALID_PHONE;
            return false;
        }

        // Проверка на дублирование номеров телефонов внутри аккаунта
        if ($this->hasDuplicatePhones($data)) {
            // Если найдены одинаковые номера телефонов внутри аккаунта, устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка: У пользователя не может быть одинаковых номеров телефонов.";
            $this->errorCode = ERROR_INVALID_PHONE;
            return false;
        }

        // Валидация формата номеров телефонов с использованием функции validateRussianPhone из файла helper.php
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($data['phone_' . $i])) {
                if (!validateRussianPhone($data['phone_' . $i])) {
                    // Если номер телефона не прошел валидацию, устанавливаем сообщение об ошибке и код ошибки
                    $this->lastError = "Ошибка: Неверный формат телефона в поле 'Телефон $i'.";
                    $this->errorCode = ERROR_INVALID_PHONE;
                    return false;
                }
            }
        }

        // Если все проверки пройдены, возвращаем true
        return true;
    }

    // Приватный метод для проверки существования email в базе данных
    private function isEmailExists($email, $excludeId = null): bool
    {
        try {
            // Формируем SQL-запрос для проверки существования email
            $sql = "SELECT COUNT(*) FROM accounts WHERE email = :email";
            // Если передан ID аккаунта, который нужно исключить из проверки, добавляем условие AND id != :excludeId
            if (!is_null($excludeId)) {
                $sql .= " AND id != :excludeId";
            }

            // Подготавливаем SQL-запрос
            $stmt = $this->db->prepare($sql);
            // Привязываем значение email к параметру запроса
            $stmt->bindParam(':email', $email);

            // Если передан ID аккаунта для исключения, привязываем его к параметру запроса
            if (!is_null($excludeId)) {
                $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
            }

            // Выполняем подготовленный запрос
            $stmt->execute();

            // Возвращаем true, если email найден, иначе false
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            // Обработка ошибки базы данных - устанавливаем сообщение об ошибке и код ошибки
            $this->lastError = "Ошибка базы данных при проверке email: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            // В случае ошибки считаем, что email уже существует
            return true; 
        }
    }

    // Приватный метод для проверки дублирования номеров телефонов среди аккаунтов
    private function isPhoneDuplicate($data, $excludeId = null): bool
    {
        // Массив с названиями полей номеров телефонов
        $phoneFields = ['phone_1', 'phone_2', 'phone_3'];

        // Перебираем все поля с номерами телефонов
        foreach ($phoneFields as $field) {
            // Проверяем, заполнено ли поле с номером телефона
            if (!empty($data[$field])) {
                // Нормализуем номер телефона
                $phoneNumber = $this->normalizePhoneNumber($data[$field]);

                // Формируем SQL-запрос для проверки на дублирование номера телефона
                $sql = "SELECT COUNT(*) FROM accounts WHERE $field = :phoneNumber";
                // Если передан ID аккаунта, который нужно исключить из проверки, добавляем условие AND id != :excludeId
                if (!is_null($excludeId)) {
                    $sql .= " AND id != :excludeId";
                }

                // Подготавливаем SQL-запрос
                $stmt = $this->db->prepare($sql);
                // Привязываем значение номера телефона к параметру запроса
                $stmt->bindParam(':phoneNumber', $phoneNumber);

                // Если передан ID аккаунта для исключения, привязываем его к параметру запроса
                if (!is_null($excludeId)) {
                    $stmt->bindParam(':excludeId', $excludeId, PDO::PARAM_INT);
                }
                // Выполняем подготовленный запрос
                $stmt->execute();

                // Если найден дубликат номера телефона, возвращаем true
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
        // Если номер телефона пустой, возвращаем пустую строку
        if (empty($phone)) {
            return '';
        }

        // Удаляем все символы, кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Нормализуем номер телефона в зависимости от его длины
        if (strlen($phone) == 11 && ($phone[0] == '8' || $phone[0] == '7')) {
            // Если номер начинается с 8 или 7 и его длина 11, заменяем первую цифру на +7
            $phone = '+7' . substr($phone, 1);
        } elseif (strlen($phone) == 10) {
            // Если длина номера 10 цифр, добавляем +7 в начало
            $phone = '+7' . $phone;
        } else if (strlen($phone) !== 12 || $phone[0] . $phone[1] !== '+7') {
            // Если номер не соответствует формату +7XXXXXXXXXX, считаем его некорректным и возвращаем false
            return false;
        }

        // Возвращаем нормализованный номер телефона
        return $phone;
    }
}