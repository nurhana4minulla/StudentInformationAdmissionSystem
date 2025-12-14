<?php

class Database {
    private $host = "127.0.0.1";
    private $username = "root";
    private $password = "";
    private $dbname = "admission"; 

    protected $conn;

    public function connect() {
        $this->conn = null;

        if ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1') {
            $this->host = "localhost";
            $this->username = "u123456789_admin"; 
            $this->password = "YourStrongPassword123!"; 
            $this->dbname = "u123456789_admission"; 
        }

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            if ($_SERVER['SERVER_NAME'] === 'localhost') {
                echo "Connection Error: " . $e->getMessage();
            } else {
                echo "System Error: Unable to connect to database. Please try again later.";
            }
        }

        return $this->conn;
    }
}

