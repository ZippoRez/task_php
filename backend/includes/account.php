<?php
    require_once 'error_codes.php';
    require_once '/var/www/site/task_php/backend/utils/helper.php'; 

    class Account{
        private $db;
        private $id;
        private $firstName;
        private $lastName;
        private $email;
        private $companyName;
        private $position;
        private $phone1;
        private $phone2;
        private $phone3;

        private $lastError = "";
        private $errorCode;

        public function __construct($db){
            $this->db = $db;
        }
        public function getId(){ return $this->id;}
        public function getFirstName(){ return $this->firstName;}
        public function getLastName(){ return $this->lastName;}
        public function getEmail(){ return $this->email;}
        public function getCompanyName(){ return $this->companyName;}
        public function getPosition(){ return $this->position;}
        public function getPhone1(){ return $this->phone1;}
        public function getPhone2(){ return $this->phone2;}
        public function getPhone3(){ return $this->phone3;}
        public function getError(){return $this->lastError;}
        public function getErrorCode(){return $this->errorCode;}

        public function setId($id){$this->id=(int) $id;}

        public function setFirstName($firstName){$this->firstName=(string) $firstName;}
        public function setLastName($lastName){$this->lastName=(string) $lastName;}
        public function setEmail($email){$
            $this->email=(string) $email;
        }
        public function setCompanyName($companyName){$this->companyName=(string) $companyName;}
        public function setPosition($position){$this->position=(int) $position;}
        public function setPhone1($phone1){$this->phone1=(string) $phone1;}
        public function setPhone2($phone2){$this->phone2=(string) $phone2;}
        public function setPhone3($phone3){$this->phone3=(string) $phone3;}
        
        public function createAccount($data){
            try{
                // var_dump($data);
                if(!$this->validateData($data)){
                    return false;
                }
                $sql = "INSERT INTO accounts (first_name, last_name, email, company_name, position, phone_1, phone_2, phone_3)
                VALUES (:first_name, :last_name, :email, :company_name, :position, :phone_1, :phone_2, :phone_3)";
                $stmt = $this->db->prepare($sql);
                // error_log("SQL-запрос: " . $sql); // Вывод запроса в лог
                // error_log("Параметры запроса: " . var_export([
                //     ':first_name' => $data['first_name'],
                //     // ... (другие параметры) ...
                // ], true)); 
                $stmt->execute([
                    ':first_name' => $data['first_name'],
                    ':last_name'=> $data['last_name'],
                    ':email'=> $data['email'],
                    ':company_name'=> $data['company_name'],
                    ':position'=> $data['position'],
                    ':phone_1'=> $data['phone_1'],
                    ':phone_2'=> $data['phone_2'],
                    ':phone_3'=> $data['phone_3'],
                ]);
                $this->id = $this->db->lastInsertId();
                // var_dump($sql);
                
                return true;
            }
            catch(PDOException $e){
                $this->lastError="Ошибка в базе при создании аккаунта: " . $e->getMessage();
                $this->errorCode= $e->getCode();
                return false;
            }
        } 
        public function getAccountById($id){
            try{
                $sql = "SELECT * FROM accounts WHERE id = :id";
                $stml = $this->db->prepare($sql);
                $stml->execute([':id' => $id]);
                $row = $stml->fetch(PDO::FETCH_ASSOC);

                if($row){
                    $this->id=$row['id'];
                    $this->firstName=$row['first_name'];
                    $this->lastName=$row['last_name'];
                    $this->email=$row['email'];
                    $this->companyName=$row['company_name'];
                    $this->position=$row['position'];
                    $this->phone1=$row['phone_1'];
                    $this->phone2=$row['phone_2'];
                    $this->phone3=$row['phone_3'];
                    return $this;
                }else{
                    $this->lastError= 'Аккаунт не найден';
                    $this->errorCode= ERROR_ACCOUNT_NOT_FOUND;
                    return false;
                }
            }catch(PDOException $e){
                $this->lastError= 'Ошибка при обращении к базе данных:'. $e->getMessage();
                $this->errorCode= $e->getCode();
                return false;
            }
        }

        public function updateAccount($data){
            try{
                if(!$this->validateData($data)){
                    return false;
                }
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
                $stml = $this->db->prepare($sql);

                $stml->execute([
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
            }catch(PDOException $e){
                $this->lastError= 'Ошибка при обращении к базе данных'. $e->getMessage();
                $this->errorCode= $e->getCode();
                return false;
            }
        }

        public function deleteAccount($id){
            try{
                $sql = "DELETE FROM accounts WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':id' => $id]);
                return true;
            } catch (PDOException $e) {
                $this->lastError = "Ошибка базы данных при удалении аккаунта: " . $e->getMessage();
                $this->errorCode = $e->getCode();
                return false;
            }
        }

            // Метод для получения списка аккаунтов (с пагинацией)
    public function getAccounts($page, $limit) {
        $offset = ($page - 1) * $limit;
        try {
            $sql = "SELECT * FROM accounts LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $accounts = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
                $accounts[] = $account;
            }
            // $testStmt = $this->db->query("SELECT * FROM accounts");
            // var_dump("Данные в таблице accounts: " . var_export($testStmt->fetchAll(PDO::FETCH_ASSOC), true));
            return $accounts;
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при получении списка аккаунтов: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false;
        }
    }

    // Метод для получения общего количества аккаунтов
    public function getTotalAccounts() {
        try {
            $sql = "SELECT COUNT(*) FROM accounts";
            $stmt = $this->db->query($sql);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при получении количества аккаунтов: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return 0; 
        }
    }

    // Приватный метод для валидации данных аккаунта
    private function validateData($data) {
        // Проверка на пустые обязательные поля
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            $this->lastError = "Ошибка: Имя, Фамилия и Email являются обязательными полями.";
            $this->errorCode = ERROR_EMPTY_FIELDS;
            return false;
        }

        // Валидация email
        if (!validateEmail($data['email'])) { // Используем функцию из helper.php
            $this->lastError = "Ошибка: Неверный формат email.";
            $this->errorCode = ERROR_INVALID_EMAIL;
            return false;
        }

        // Проверка на уникальность email
        if ($this->isEmailExists($data['email'])) {
            $this->lastError = "Ошибка: Пользователь с таким email уже существует.";
            $this->errorCode = ERROR_EMAIL_EXISTS;
            return false;
        }

        // Валидация телефонов
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

    // Приватный метод для проверки существования email
    private function isEmailExists($email) {
        try {
            $sql = "SELECT COUNT(*) FROM accounts WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            $this->lastError = "Ошибка базы данных при проверке email: " . $e->getMessage();
            $this->errorCode = $e->getCode();
            return false; // В случае ошибки считаем, что email уже существует 
        }
    }

    // Приватный метод для валидации российского номера телефона
    }
?>