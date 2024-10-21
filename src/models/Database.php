<?php
require_once(__DIR__ . '/../utils/helpers.php');

class Database
{
    private $host;
    private $database;
    private $username;
    private $password;
    private $port;
    private $socket;
    private $conn;

    public function __construct($config, $logfile = __DIR__ . '../src/logs')
    {
        $this->host = $config['host'];
        $this->database = $config['database'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->port = $config['port'] ?? 3306;
        $this->socket = $config['socket'] ?? null;

        $this->connectDB();
    }

    private function connectDB()
    {
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port, $this->socket);
            if ($this->conn->connect_error) {
                throw new Exception('Database connection error: ' . $this->conn->connect_error);
            }
        } catch (Exception $exception) {
            // Log exception (implement a logging mechanism)
            logError($exception->getMessage());
        }
    }

    public function insert(string $table, array $data): bool
    {
        try {
            if (empty($data)) {
                throw new Exception('No data provided for insert operation.');
            }

            $columns = implode(", ", array_keys($data));
            $placeholders = rtrim(str_repeat("?, ", count($data)), ', ');
            $stmt = $this->conn->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");

            if (!$stmt) {
                throw new Exception('Prepare statement failed: ' . $this->conn->error);
            }

            // Prepare to bind parameters
            $types = str_repeat('s', count($data)); // Assume all values are strings; adjust as needed
            $params = array_values($data);
            $stmt->bind_param($types, ...$params);

            return $stmt->execute();
        } catch (Exception $exception) {
            // Log exception
            logError($exception->getMessage());
            return false;
        }
    }

    public function read(string $table, array $conditions): ?array
    {
        try {
            if (empty($conditions)) {
                throw new Exception('No conditions provided for read operation.');
            }

            $whereClause = implode(" AND ", array_map(fn($col) => "$col = ?", array_keys($conditions)));
            $stmt = $this->conn->prepare("SELECT * FROM $table WHERE $whereClause");

            if (!$stmt) {
                throw new Exception('Prepare statement failed: ' . $this->conn->error);
            }

            // Prepare to bind parameters
            $types = str_repeat('s', count($conditions)); // Assume all values are strings; adjust as needed
            $params = array_values($conditions);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);

            return !empty($data) ? $data[0] : null;
        } catch (Exception $exception) {
            // Log exception
            logError($exception->getMessage());
            return null;
        }
    }

    public function update(string $table, array $data, array $conditions): bool
    {
        try {
            if (empty($data) || empty($conditions)) {
                throw new Exception('No data or conditions provided for update operation.');
            }

            $setClause = implode(", ", array_map(fn($col) => "$col = ?", array_keys($data)));
            $whereClause = implode(" AND ", array_map(fn($col) => "$col = ?", array_keys($conditions)));
            $stmt = $this->conn->prepare("UPDATE $table SET $setClause WHERE $whereClause");

            if (!$stmt) {
                throw new Exception('Prepare statement failed: ' . $this->conn->error);
            }

            // Prepare to bind parameters
            $types = str_repeat('s', count($data) + count($conditions)); // Adjust as needed
            $params = array_merge(array_values($data), array_values($conditions));
            $stmt->bind_param($types, ...$params);

            return $stmt->execute();
        } catch (Exception $exception) {
            // Log exception
            logError($exception->getMessage());
            return false;
        }
    }

    public function delete(string $table, array $conditions): bool
    {
        try {
            if (empty($conditions)) {
                throw new Exception('No conditions provided for delete operation.');
            }

            $whereClause = implode(" AND ", array_map(fn($col) => "$col = ?", array_keys($conditions)));
            $stmt = $this->conn->prepare("DELETE FROM $table WHERE $whereClause");

            if (!$stmt) {
                throw new Exception('Prepare statement failed: ' . $this->conn->error);
            }

            // Prepare to bind parameters
            $types = str_repeat('s', count($conditions)); // Adjust as needed
            $params = array_values($conditions);
            $stmt->bind_param($types, ...$params);

            return $stmt->execute();
        } catch (Exception $exception) {
            // Log exception
            logError($exception->getMessage());
            return false;
        }
    }

    public function arrayDelete(string $table, array $conditions): bool
    {
        try {
            if (empty($conditions)) {
                throw new Exception('No conditions provided for array delete operation.');
            }

            foreach ($conditions as $key => $values) {
                if (!is_array($values)) {
                    throw new Exception('Values must be an array for array delete operation.');
                }

                $placeholders = implode(',', array_fill(0, count($values), '?'));
                $whereClause = "$key IN ($placeholders)";
                $stmt = $this->conn->prepare("DELETE FROM $table WHERE $whereClause");

                if (!$stmt) {
                    throw new Exception('Prepare statement failed: ' . $this->conn->error);
                }

                // Prepare to bind parameters
                $types = str_repeat('s', count($values)); // Adjust as needed
                $params = $values;
                $stmt->bind_param($types, ...$params);

                if (!$stmt->execute()) {
                    throw new Exception('Delete operation failed: ' . $stmt->error);
                }
            }
            return true;
        } catch (Exception $exception) {
            // Log exception
            logError($exception->getMessage());
            return false;
        }
    }
    public function prepare($query)
    {
        return $this->conn->prepare($query);
    }
}


$config = [
    'host' => 'localhost',
    'database' => 'meetwave',
    'username' => 'root',
    'password' => '',
];

$db = new Database($config);
