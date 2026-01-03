<?php
/**
 * Base Model Class and Specific Models
 */

class Model {
    protected $table = '';
    protected $db;
    
    public function __construct($pdo = null) {
        // Use the passed PDO instance, otherwise fall back to the global $db
        global $db;
        $this->db = $pdo ?? $db;
    }
    
    /**
     * Get all records
     */
    public function getAll($where = null, $params = [], $limit = null, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        
        if ($limit) {
            $sql .= " LIMIT " . intval($offset) . ", " . intval($limit);
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params ?? []);
        return $stmt->fetchAll();
    }
    
    /**
     * Get record by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get one record by condition
     */
    public function getOne($where, $params = []) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE $where");
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "{$column} = :{$column}";
        }
        $setString = implode(', ', $set);
        
        $sql = "UPDATE {$this->table} SET {$setString} WHERE id = :id";
        $data['id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    /**
     * Delete record
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Count records
     */
    public function count($where = null, $params = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if ($where) {
            $sql .= " WHERE $where";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] ?? 0;
    }
}

/**
 * Race Model
 */
class Race extends Model {
    protected $table = 'races';
    
    public function getRaceWithDetails($id) {
        $sql = "
            SELECT r.*, t.name as track_name, t.location, t.country
            FROM {$this->table} r
            LEFT JOIN tracks t ON r.track_id = t.id
            WHERE r.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getRaceEntries($race_id) {
        $sql = "
            SELECT re.*, 
                   h.name as horse_name, h.age, h.form, h.rating,
                   j.name as jockey_name,
                   t.name as trainer_name,
                   o.name as owner_name,
                   p.win_probability, p.confidence_level
            FROM race_entries re
            LEFT JOIN horses h ON re.horse_id = h.id
            LEFT JOIN jockeys j ON re.jockey_id = j.id
            LEFT JOIN trainers t ON h.trainer_id = t.id
            LEFT JOIN owners o ON h.owner_id = o.id
            LEFT JOIN predictions p ON p.race_entry_id = re.id
            WHERE re.race_id = ? AND re.is_non_runner = 0
            ORDER BY re.saddle_number ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$race_id]);
        return $stmt->fetchAll();
    }
}

/**
 * Horse Model
 */
class Horse extends Model {
    protected $table = 'horses';
    
    public function getHorseWithDetails($id) {
        $sql = "
            SELECT h.*, t.name as trainer_name, o.name as owner_name
            FROM {$this->table} h
            LEFT JOIN trainers t ON h.trainer_id = t.id
            LEFT JOIN owners o ON h.owner_id = o.id
            WHERE h.id = ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}

/**
 * Jockey Model
 */
class Jockey extends Model {
    protected $table = 'jockeys';
}

/**
 * Trainer Model
 */
class Trainer extends Model {
    protected $table = 'trainers';
}

/**
 * Owner Model
 */
class Owner extends Model {
    protected $table = 'owners';
}

/**
 * Track Model
 */
class Track extends Model {
    protected $table = 'tracks';
}

/**
 * RaceEntry Model
 */
class RaceEntry extends Model {
    protected $table = 'race_entries';
}

/**
 * RaceResult Model
 */
class RaceResult extends Model {
    protected $table = 'race_results';
}

/**
 * Prediction Model
 */
class Prediction extends Model {
    protected $table = 'predictions';
}

/**
 * User Model
 */
class User extends Model {
    protected $table = 'users';
    
    public function getUserByUsername($username) {
        return $this->getOne('username = ?', [$username]);
    }
    
    public function getUserByEmail($email) {
        return $this->getOne('email = ?', [$email]);
    }
}

/**
 * API Settings Model
 */
class ApiSetting extends Model {
    protected $table = 'api_settings';
}

/**
 * OddsHistory Model
 */
class OddsHistory extends Model {
    protected $table = 'odds_history';
}

/**
 * Payouts Model
 */
class Payout extends Model {
    protected $table = 'payouts';
}

?>
