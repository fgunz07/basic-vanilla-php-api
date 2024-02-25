<?php

class Database {
    private $host   = 'database';
    private $db     = 'challenge';
    private $user   = 'user';
    private $pass   = 'password';

    // Hold connection
    private $conn   = null;
    
    public function connect() {
        try {
            $this->conn = new PDO('mysql:host='.$this->host.';dbname='.$this->db, $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        }
        catch(PDOException $e)
        {
            echo "PDOException: ".$e->getMessage();
        }
    }
}