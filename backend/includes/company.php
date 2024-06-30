<?php
// includes/company.php

require_once 'error_codes.php';

class Company {
    private $db;
    private $id;
    private $name;
    private $address;

    private $lastError = "";
    private $errorCode = 0;

    public function __construct($db) {
        $this->db = $db;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getAddress() { return $this->address; }
    public function getError() { return $this->lastError; }
    public function getErrorCode() { return $this->errorCode; }

    // Setters
    public function setId($id) { $this->id = (int)$id; }
    public function setName($name) { $this->name = $name; }
    public function setAddress($address) { $this->address = $address; }

    // Метод для создания компании
    public function createCompany($data) {
        try {
            // Валидация данных (добавь свою логику)
            if (empty($data['name'])) {
                $this->lastError = "Ошибка: Название компании обязательно.";
                $this->errorCode = ERROR_EMPTY_FIELDS;
                return false;
            }

            $sql = "INSERT INTO companies (name, address) VALUES (:name, :address)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':address' => $data['address'] ?? null,
            ]);
            $this->setId($this->db->lastInsertId()); 
            return true;
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при создании компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения компании по ID
    public function getCompanyById($id) {
        try {
            $sql = "SELECT * FROM companies WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $this->setId($row['id']); 
                $this->setName($row['name']); 
                $this->setAddress($row['address']); 
                return $this; // Возвращаем объект Company
            } else {
                $this->lastError = "Компания не найдена.";
                $this->errorCode = ERROR_ACCOUNT_NOT_FOUND; // Можно использовать тот же код, что и для аккаунта
                return false;
            }
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при получении компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения списка компаний (с пагинацией)
    public function getCompanies($page, $limit) {
        $offset = ($page - 1) * $limit;
        try {
            $sql = "SELECT * FROM companies LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $companies = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $company = new Company($this->db);
                $company->setId($row['id']);
                $company->setName($row['name']); 
                $company->setAddress($row['address']); 
                $companies[] = $company;
            }
            return $companies;
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при получении списка компаний: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }


    // Метод для обновления компании
    public function updateCompany($data) {
        try {
            // Валидация данных (добавь свою логику)
            if (empty($data['name'])) {
                $this->lastError = "Ошибка: Название компании обязательно.";
                $this->errorCode = ERROR_EMPTY_FIELDS;
                return false;
            }

            $sql = "UPDATE companies SET name = :name, address = :address WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':name' => $data['name'],
                ':address' => $data['address'] ?? null,
                ':id' => $this->id,
            ]);
            return true;
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при обновлении компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для удаления компании
    public function deleteCompany($id) {
        try {
            $sql = "DELETE FROM companies WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id]);
            return true;
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при удалении компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения списка сотрудников компании (с пагинацией)
    public function getEmployees($companyId, $page, $limit) {
        $offset = ($page - 1) * $limit;
        try {
            $sql = "SELECT * FROM accounts WHERE company_id = :companyId LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $accounts = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
                $accounts[] = $account; 
            }
            return $accounts;
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при получении списка сотрудников: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false; 
        }
    }

    // Метод для получения общего количества компаний
    public function getTotalCompanies() {
        try {
            $sql = "SELECT COUNT(*) FROM companies";
            $stmt = $this->db->query($sql);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при получении общего количества компаний: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return 0; 
        }
    }

    // Метод для получения количества аккаунтов,  привязанных к компании
    public function getTotalAccountsByCompanyId($companyId) {
        try {
            $sql = "SELECT COUNT(*) FROM accounts WHERE company_id = :companyId"; 
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT); 
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при получении количества аккаунтов компании: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return 0;
        }
    }

    // Метод для преобразования объекта Company в массив
    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address
        ];
    }
}