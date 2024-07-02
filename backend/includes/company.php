<?php
// includes/company.php

// Подключаем файл с кодами ошибок
require_once 'error_codes.php';

/**
 * Класс Company представляет собой модель для работы с компаниями.
 */
class Company {
    // Подключение к базе данных
    private $db;
    // ID компании
    private $id;
    // Название компании
    private $name;
    // Адрес компании
    private $address;

    // Текст последней ошибки
    private $lastError = "";
    // Код последней ошибки
    private $errorCode = 0;

    /**
     * Конструктор класса Company.
     *
     * @param PDO $db Подключение к базе данных.
     */
    public function __construct($db) {
        $this->db = $db;
    }

    // Геттеры для всех свойств класса
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getAddress() { return $this->address; }
    public function getError() { return $this->lastError; }
    public function getErrorCode() { return $this->errorCode; }

    // Сеттеры для всех свойств класса
    public function setId($id) { $this->id = (int)$id; }
    public function setName($name) { $this->name = $name; }
    public function setAddress($address) { $this->address = $address; }

    /**
     * Создает новую компанию.
     *
     * @param array $data Массив с данными компании (name, address).
     *
     * @return bool Возвращает true в случае успеха, false - в случае ошибки.
     */
    public function createCompany($data) {
        try {
            // Валидация данных 
            if (empty($data['name'])) {
                // Если название компании пустое, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
                $this->lastError = "Ошибка: Название компании обязательно.";
                $this->errorCode = ERROR_EMPTY_FIELDS;
                return false;
            }

            // Проверка на существование компании с таким же названием
            if ($this->isCompanyNameExists($data['name'])) { 
                // Если компания с таким названием уже существует, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
                $this->lastError = "Ошибка:  Компания  с  таким  названием  уже  существует."; 
                $this->errorCode = ERROR_COMPANY_NAME_EXISTS;
                return false; 
            }

            // Подготавливаем SQL-запрос на добавление новой компании в базу данных
            $sql = "INSERT INTO companies (name, address) VALUES (:name, :address)";
            $stmt = $this->db->prepare($sql);
            // Выполняем запрос, передавая название и адрес компании в качестве параметров
            $stmt->execute([
                ':name' => $data['name'],
                ':address' => $data['address'] ?? null,
            ]);
            // Устанавливаем ID созданной компании
            $this->setId($this->db->lastInsertId()); 
            // Возвращаем true в случае успешного создания компании
            return true;
        } catch (PDOException $e) {
            // В случае ошибки базы данных, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
            $this->lastError = "Ошибка базы данных при создании компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    /**
     * Возвращает компанию по ее ID.
     *
     * @param int $id ID компании.
     *
     * @return Company|bool Возвращает объект Company, если компания найдена, иначе false.
     */
    public function getCompanyById($id) {
        try {
            // Подготавливаем SQL-запрос для получения данных компании по ID
            $sql = "SELECT * FROM companies WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            // Выполняем запрос, передавая ID компании в качестве параметра
            $stmt->execute([':id' => $id]);
            // Получаем результат запроса в виде ассоциативного массива
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Если компания найдена, устанавливаем ее свойства и возвращаем объект Company
            if ($row) {
                $this->setId($row['id']); 
                $this->setName($row['name']); 
                $this->setAddress($row['address']); 
                return $this; 
            } else {
                // Если компания не найдена, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
                $this->lastError = "Компания не найдена.";
                $this->errorCode = ERROR_ACCOUNT_NOT_FOUND; 
                return false;
            }
        } catch (PDOException $e) {
            // В случае ошибки базы данных, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
            $this->lastError = "Ошибка базы данных при получении компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    /**
     * Возвращает список компаний с пагинацией.
     *
     * @param int $page Номер страницы.
     * @param int $limit Количество компаний на странице.
     *
     * @return array|bool Массив объектов Company или false в случае ошибки.
     */
    public function getCompanies($page, $limit) {
        // Вычисляем смещение для запроса с пагинацией
        $offset = ($page - 1) * $limit;
        try {
            // Подготавливаем SQL-запрос для получения списка компаний с учетом пагинации
            $sql = "SELECT * FROM companies LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            // Привязываем значения limit и offset к параметрам запроса
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            // Выполняем запрос
            $stmt->execute();

            // Создаем пустой массив для хранения объектов Company
            $companies = [];
            // Перебираем результаты запроса
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Создаем новый объект Company для каждой строки результата
                $company = new Company($this->db);
                $company->setId($row['id']);
                $company->setName($row['name']); 
                $company->setAddress($row['address']); 
                // Добавляем объект Company в массив
                $companies[] = $company;
            }
            // Возвращаем массив объектов Company
            return $companies;
        } catch (PDOException $e) {
            // В случае ошибки базы данных, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
            $this->lastError = "Ошибка базы данных при получении списка компаний: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }


    /**
     * Обновляет данные компании.
     *
     * @param array $data Массив с данными компании (name, address).
     *
     * @return bool Возвращает true в случае успеха, false - в случае ошибки.
     */
    public function updateCompany($data) {
        try {
            // Валидация данных 
            if (empty($data['name'])) {
                // Если название компании пустое, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
                $this->lastError = "Ошибка: Название компании обязательно.";
                $this->errorCode = ERROR_EMPTY_FIELDS;
                return false;
            }

            // Проверка на существование компании с таким же названием
            if ($this->isCompanyNameExists($data['name'], $this->id)) { 
                // Если компания с таким названием уже существует, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
                $this->lastError = "Ошибка:  Компания  с  таким  названием  уже  существует."; 
                $this->errorCode = ERROR_COMPANY_NAME_EXISTS;
                return false; 
            }

            // Подготавливаем SQL-запрос на обновление данных компании в базе данных
            $sql = "UPDATE companies SET name = :name, address = :address WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            // Выполняем запрос, передавая ID, название и адрес компании в качестве параметров
            $stmt->execute([
                ':name' => $data['name'],
                ':address' => $data['address'] ?? null,
                ':id' => $this->id,
            ]);
            // Возвращаем true в случае успешного обновления данных компании
            return true;
        } catch (PDOException $e) {
            // В случае ошибки базы данных, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
            $this->lastError = "Ошибка базы данных при обновлении компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    /**
     * Удаляет компанию.
     *
     * @param int $id ID компании.
     *
     * @return bool Возвращает true в случае успеха, false - в случае ошибки.
     */
    public function deleteCompany($id) {
        try {
            // Подготавливаем SQL-запрос на удаление компании из базы данных
            $sql = "DELETE FROM companies WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            // Выполняем запрос, передавая ID компании в качестве параметра
            $stmt->execute([':id' => $id]);
            // Возвращаем true в случае успешного удаления компании
            return true;
        } catch (PDOException $e) {
            // В случае ошибки базы данных, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
            $this->lastError = "Ошибка базы данных при удалении компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    /**
     * Возвращает список сотрудников компании с пагинацией.
     *
     * @param int $companyId ID компании.
     * @param int $page Номер страницы.
     * @param int $limit Количество сотрудников на странице.
     *
     * @return array|bool Массив объектов Account или false в случае ошибки.
     */
    public function getEmployees($companyId, $page, $limit) {
        // Вычисляем смещение для запроса с пагинацией
        $offset = ($page - 1) * $limit;
        try {
            // Подготавливаем SQL-запрос для получения списка сотрудников компании с учетом пагинации
            $sql = "SELECT * FROM accounts WHERE company_id = :companyId LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            // Привязываем значения companyId, limit и offset к параметрам запроса
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            // Выполняем запрос
            $stmt->execute();

            // Создаем пустой массив для хранения объектов Account
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
            // Возвращаем массив объектов Account
            return $accounts;
        } catch (PDOException $e) {
            // В случае ошибки базы данных, устанавливаем сообщение об ошибке, код ошибки и возвращаем false
            $this->lastError = "Ошибка базы данных при получении списка сотрудников: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false; 
        }
    }

    /**
     * Проверяет, существует ли компания с таким названием.
     *
     * @param string $name Название компании.
     *
     * @return bool Возвращает true, если компания существует, иначе false.
     */
    private function isCompanyNameExists($name, $excludeId = null): bool {
        try {
            // Подготавливаем SQL-запрос для проверки существования компании с таким названием
            $sql = "SELECT COUNT(*) FROM companies WHERE name = :name"; 

            //  Если  передан  $excludeId,  добавляем  его  в  условие  запроса
            if  (!is_null($excludeId))  {
                $sql  .=  " AND id != :excludeId"; 
            }
            $stmt = $this->db->prepare($sql); 
            $stmt->bindParam(':name',  $name,  PDO::PARAM_STR);

            //  Если  передан  $excludeId,  привязываем  его  к  запросу
            if  (!is_null($excludeId))  {
                $stmt->bindParam(':excludeId',  $excludeId,  PDO::PARAM_INT); 
            }
            $stmt->execute(); 
            // Возвращаем true, если компания с таким названием найдена, иначе false
            return $stmt->fetchColumn() > 0; 
        } catch (PDOException $e) {
            // В случае ошибки базы данных, записываем сообщение об ошибке в лог
            error_log("Ошибка  базы  данных  при  проверке  названия  компании:  " . $e->getMessage()); 
            // Устанавливаем код ошибки
            $this->errorCode = $e->getCode(); 
            // Возвращаем false, чтобы предотвратить создание компании
            return false;
        }
    }

    /**
     * Возвращает общее количество компаний.
     *
     * @return int|bool Количество компаний или false в случае ошибки.
     */
    public function getTotalCompanies() {
        try {
            // Подготавливаем SQL-запрос для получения общего количества компаний
            $sql = "SELECT COUNT(*) FROM companies";
            $stmt = $this->db->query($sql);
            // Возвращаем количество компаний
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            // В случае ошибки базы данных, устанавливаем сообщение об ошибке, код ошибки и возвращаем 0
            $this->lastError = "Ошибка базы данных при получении общего количества компаний: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return 0; 
        }
    }

    /**
     * Возвращает количество аккаунтов, привязанных к компании.
     *
     * @param int $companyId ID компании.
     *
     * @return int|bool Количество аккаунтов или false в случае ошибки.
     */
    public function getTotalAccountsByCompanyId($companyId) {
        try {
            // Подготавливаем SQL-запрос для получения количества аккаунтов, привязанных к компании
            $sql = "SELECT COUNT(*) FROM accounts WHERE company_id = :companyId"; 
            $stmt = $this->db->prepare($sql);
            // Привязываем значение companyId к параметру запроса
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT); 
            // Выполняем запрос
            $stmt->execute();
            // Возвращаем количество аккаунтов
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            // В случае ошибки базы данных, устанавливаем сообщение об ошибке, код ошибки и возвращаем 0
            $this->lastError = "Ошибка базы данных при получении количества аккаунтов компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return 0;
        }
    }

    /**
     * Возвращает массив с данными компании.
     *
     * @return array Массив с данными компании.
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address
        ];
    }
}