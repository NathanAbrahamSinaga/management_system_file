<?php
require_once __DIR__ . '/../config/config.php';

class Auth {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login($email, $password) {
        try {
            $query = "SELECT * FROM User WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id_user'];
                    $_SESSION['user_name'] = $user['nama'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    return true;
                }
            }
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function register($nama, $email, $password, $role = 'User') {
        try {
            // Check if email already exists
            $query = "SELECT id_user FROM User WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return false; // Email already exists
            }
            
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO User (nama, email, password, role) VALUES (:nama, :email, :password, :role)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':role', $role);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function getUserRole() {
        return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
    }

    public function hasPermission($required_role) {
        $user_role = $this->getUserRole();
        $roles = ['Viewer' => 1, 'User' => 2, 'Admin' => 3];
        
        return isset($roles[$user_role]) && isset($roles[$required_role]) && 
               $roles[$user_role] >= $roles[$required_role];
    }

    public function logout() {
        session_destroy();
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: index.php?page=login');
            exit;
        }
    }
}
?>