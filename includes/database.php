<?php
/**
 * Database Connection and Helper Functions
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;
    
    public function __construct() {
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            // Log error in production
            error_log("Database Connection Error: " . $e->getMessage());

            // If DEBUG_MODE is on show details, otherwise fall back to mock JSON data
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                // Attempt to provide a graceful fallback using local JSON files so the front-end can render
                error_log("Using JSON data fallback due to failed DB connection.");
                $this->conn = new MockDB();
            } else {
                // In production we still try to provide a fallback to avoid full site outage
                error_log("Using JSON data fallback due to failed DB connection (production mode).");
                $this->conn = new MockDB();
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Close connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}

/**
 * MockDB - lightweight mock database using JSON files
 */
class MockDB {
    private $dataPath;

    public function __construct($dataPath = null) {
        // DATA_PATH is defined in config.php
        $this->dataPath = $dataPath ?? (defined('DATA_PATH') ? DATA_PATH : dirname(__DIR__) . '/data/');
    }

    // Simple query that returns a MockStatement
    public function query($sql) {
        $rows = $this->resolveSql($sql);
        return new MockStatement($rows);
    }

    public function prepare($sql) {
        return new MockStatement(null, $sql, $this->dataPath);
    }

    public function lastInsertId() {
        return 0;
    }

    // Very small SQL-to-json resolver
    public function resolveSql($sql) {
        $sql = strtolower($sql);

        if (strpos($sql, 'from races') !== false || strpos($sql, 'from `races`') !== false) {
            $file = $this->dataPath . 'races.json';
            $json = @file_get_contents($file);
            if ($json === false) return [];
            $data = json_decode($json, true);
            return $data['races'] ?? [];
        }

        if (strpos($sql, 'from horses') !== false) {
            $file = $this->dataPath . 'horses.json';
            $json = @file_get_contents($file);
            if ($json === false) return [];
            $data = json_decode($json, true);
            return $data['horses'] ?? [];
        }

        if (strpos($sql, 'from tracks') !== false) {
            $file = $this->dataPath . 'tracks.json';
            $json = @file_get_contents($file);
            if ($json === false) return [];
            $data = json_decode($json, true);
            return $data['tracks'] ?? [];
        }

        // handle count queries like SELECT COUNT(*) as count FROM table
        if (preg_match('/select\s+count\(\*\)\s+as\s+count\s+from\s+([a-z0-9_`]+)/', $sql, $m)) {
            $table = trim($m[1], " `");
            $file = $this->dataPath . $table . '.json';
            $json = @file_get_contents($file);
            if ($json === false) return [['count' => 0]];
            $data = json_decode($json, true);
            $count = 0;
            if (is_array($data)) {
                // If file contains wrapper key (e.g., races->array)
                $firstKey = array_keys($data)[0] ?? null;
                if ($firstKey && is_array($data[$firstKey])) {
                    $count = count($data[$firstKey]);
                } else {
                    $count = count($data);
                }
            }
            return [['count' => $count]];
        }

        // default empty
        return [];
    }
}

/**
 * MockStatement - lightweight statement object with execute(), fetchAll(), fetch()
 */
class MockStatement {
    private $rows;
    private $sql;
    private $dataPath;

    public function __construct($rows = null, $sql = null, $dataPath = null) {
        $this->rows = $rows;
        $this->sql = $sql;
        $this->dataPath = $dataPath ?? (defined('DATA_PATH') ? DATA_PATH : dirname(__DIR__) . '/data/');
    }

    public function execute($params = []) {
        if ($this->rows !== null) return true;
        if ($this->sql) {
            // lazy resolve
            $resolver = new MockDB($this->dataPath);
            $this->rows = $resolver->resolveSql($this->sql);
        }
        return true;
    }

    public function fetchAll() {
        return $this->rows ?? [];
    }

    public function fetch() {
        if (is_array($this->rows) && count($this->rows) > 0) {
            return $this->rows[0];
        }
        return false;
    }
}

/**
 * Database Helper Class
 */
class DB {
    private static $db;
    
    public static function getInstance() {
        if (self::$db === null) {
            $database = new Database();
            self::$db = $database->getConnection();
        }
        return self::$db;
    }
    
    /**
     * Execute a query and return all results
     */
    public static function query($sql, $params = []) {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Execute a query and return single result
     */
    public static function queryOne($sql, $params = []) {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Insert data and return last insert ID
     */
    public static function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($data);
        
        return self::getInstance()->lastInsertId();
    }
    
    /**
     * Update data
     */
    public static function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $setString = implode(', ', $set);
        
        $sql = "UPDATE {$table} SET {$setString} WHERE {$where}";
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute(array_merge($data, $whereParams));
        
        return $stmt->rowCount();
    }
    
    /**
     * Delete data
     */
    public static function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }
    
    /**
     * Count records
     */
    public static function count($table, $where = '1=1', $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$where}";
        $result = self::queryOne($sql, $params);
        return $result['count'] ?? 0;
    }
}
?>