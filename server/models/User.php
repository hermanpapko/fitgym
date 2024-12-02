<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function create($name, $email, $password) {
        $query = "INSERT INTO " . $this->table_name . " 
                (name, email, password, role, membership) 
                VALUES 
                (:name, :email, :password, 'user', 'standard')";

        $stmt = $this->conn->prepare($query);
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password_hash);

        return $stmt->execute();
    }

    public function emailExists($email) {
        $query = "SELECT id, password FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}
?> 