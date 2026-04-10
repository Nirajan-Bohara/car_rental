<?php
class Database {
    private $host = "localhost";
    private $db_name = "car_rental"; // ✅ Correct database name
    private $username = "root";      // Your MySQL username
    private $password = "";          // Your MySQL password
    private $port = 3309;            // Your MySQL port (if MySQL runs on 3309)
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // DSN string includes host, port, and db name
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}";

            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Debug message (optional)
            error_log("✅ Connected to database: {$this->db_name} on port {$this->port}");
        } catch (PDOException $e) {
            error_log("❌ Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }

        return $this->conn;
    }
}
?>
