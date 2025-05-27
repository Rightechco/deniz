<?php
// app/core/Database.php

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $charset = 'utf8mb4';

    private $dbh; 
    private $stmt; 
    private $error;
    private $lastSql = ''; // برای نگهداری آخرین SQL

    public function __construct() {
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
            error_log("Database Error: DB connection constants are not defined.");
            die("خطای پیکربندی پایگاه داده.");
        }
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        $options = [
            PDO::ATTR_PERSISTENT => true, 
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci"
        ];
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Connection Error: " . $this->error); 
            die("خطا در اتصال به پایگاه داده: " . $this->error);
        }
    }

    public function query($sql) {
        $this->lastSql = $sql; // ذخیره SQL برای دیباگ
        error_log("Database::query() Preparing SQL: " . $sql);
        try {
            $this->stmt = $this->dbh->prepare($sql);
            if (!$this->stmt) {
                 error_log("Database Prepare Error: PDO::prepare failed for SQL: " . $sql . " DBH Error: " . implode(" | ", $this->dbh->errorInfo()));
                 throw new PDOException("PDO::prepare failed for SQL: " . $sql);
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Prepare Exception: " . $this->error . " | SQL: " . $sql);
            throw $e; 
        }
    }

    public function bind($param, $value, $type = null) {
        if (!$this->stmt) {
            error_log("Database Bind Error: Statement is not prepared. Last SQL attempted: " . $this->lastSql);
            // برای جلوگیری از خطای بعدی، یک Exception ایجاد می‌کنیم
            throw new Exception("Database Bind Error: Statement is not prepared for param {$param}. Last SQL: " . $this->lastSql);
        }
        if (is_null($type)) {
            switch (true) {
                case is_int($value): $type = PDO::PARAM_INT; break;
                case is_bool($value): $type = PDO::PARAM_BOOL; break;
                case is_null($value): $type = PDO::PARAM_NULL; break;
                default: $type = PDO::PARAM_STR; break;
            }
        }
        error_log("Database::bind() - Binding param: [{$param}] with Value: [" . (is_scalar($value) ? $value : gettype($value)) . "] PDO_TYPE: {$type} to SQL: " . $this->stmt->queryString);
        try {
            return $this->stmt->bindValue($param, $value, $type);
        } catch (PDOException $e) {
            // این خطا دقیقاً خطایی است که شما دریافت می‌کنید
            error_log("Database::bind() - PDOException during bindValue for param [{$param}]. Message: " . $e->getMessage() . " | SQL: " . $this->stmt->queryString);
            throw $e; // پرتاب مجدد Exception تا در لایه‌های بالاتر قابل دریافت باشد
        }
    }

    public function execute($params = null) { 
        if (!$this->stmt) {
            error_log("Database Execute Error: Statement is not prepared. Last SQL attempted: " . $this->lastSql);
            return false;
        }
        $sqlQueryForLog = $this->stmt->queryString; // گرفتن SQL قبل از اجرای احتمالی
        try {
            $executeResult = $params === null ? $this->stmt->execute() : $this->stmt->execute($params);
            error_log("Database::execute() - SQL: [{$sqlQueryForLog}] - Params: [" . print_r($params, true) . "] - Result: " . ($executeResult ? 'Success' : 'Fail'));
            return $executeResult;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            error_log("Database Execute PDOException: " . $this->error . " | SQL: " . $sqlQueryForLog . " | Params Array: " . print_r($params, true));
            return false;
        }
    }

    public function resultSet($params = null) { 
        if ($this->execute($params)) {
             return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
    }

    public function single($params = null) { 
         if ($this->execute($params)) {
            return $this->stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false; 
    }

    public function rowCount() {
        return $this->stmt ? $this->stmt->rowCount() : 0;
    }

    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
    
    public function getErrorInfo(){
        $stmtError = $this->stmt ? $this->stmt->errorInfo() : ['00000', null, null];
        $dbhError = $this->dbh ? $this->dbh->errorInfo() : ['00000', null, null];
        
        if ($stmtError[0] !== '00000' && $stmtError[1] !== null) { 
            return $stmtError;
        } elseif ($dbhError[0] !== '00000' && $dbhError[1] !== null) { 
            return $dbhError;
        }
        return ['00000', null, null]; 
    }

    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }
    public function commit() {
        return $this->dbh->commit();
    }
    public function rollBack() {
        return $this->dbh->rollBack();
    }
}
?>
