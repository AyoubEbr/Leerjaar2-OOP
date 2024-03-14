<?php
namespace login\classes;

use login\classes\database;

session_start();

class User {
    private $db;

    public function __construct() {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=login2', 'root');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            exit("Error: " . $e->getMessage());
        }
    }

    public function registerUser($username, $password, $role = 'gebruiker') {
        $sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':username', $username);
        $stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT));
        $stmt->bindValue(':role', $role);

        try {
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function loginUser($username, $password) {
        $sql = "SELECT password, role FROM users WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $user['role'];
            return true;
        } else {
            return false;
        }
    }
    
    public function GetUser() {
        if (!$this->IsLoggedin()) {
            return false;
        }
    
        $query = "SELECT username FROM users WHERE username = ?";
        $stmt = $this->db->prepare($query);
        $params = array($_SESSION['username']);
        
        try {
            $stmt->execute($params);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user) {
                $this->username = $user['username'];
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return false;
        }
        
        return true;
    }
    
    public function IsLoggedin() {
        return isset($_SESSION['username']);
    }

    public function ShowUser() {
        echo "Username: " . $this->username;
    }
    
    public function Logout() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        
        $_SESSION = array();
        session_destroy();
    
        header('Location: index.php');
        exit;
    }  
}

?>