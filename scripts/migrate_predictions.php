<?php
require_once dirname(__DIR__) . '/includes/config.php';

try {
    $tbl = $db->query("SHOW TABLES LIKE 'predictions'")->fetch();
    if (!$tbl) {
        $db->exec("CREATE TABLE predictions (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            race_id INT NOT NULL,
            race_entry_id INT NOT NULL,
            win_prob DECIMAL(5,2) NOT NULL,
            confidence DECIMAL(5,2) DEFAULT NULL,
            model_version VARCHAR(50) DEFAULT 'v1',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY idx_race_entry (race_id, race_entry_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        echo "Created predictions table\n";
    } else {
        echo "predictions table already exists\n";
        // Ensure required columns exist, add if missing
        try {
            $db->exec("ALTER TABLE predictions ADD COLUMN IF NOT EXISTS win_prob DECIMAL(5,2) NOT NULL DEFAULT 0");
            $db->exec("ALTER TABLE predictions ADD COLUMN IF NOT EXISTS confidence DECIMAL(5,2) DEFAULT NULL");
            $db->exec("ALTER TABLE predictions ADD COLUMN IF NOT EXISTS model_version VARCHAR(50) DEFAULT 'v1'");
            $db->exec("ALTER TABLE predictions ADD COLUMN IF NOT EXISTS created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
            $db->exec("ALTER TABLE predictions ADD UNIQUE KEY IF NOT EXISTS idx_race_entry (race_id, race_entry_id)");
            echo "Ensured predictions columns exist\n";
        } catch (Exception $e) {
            echo "Warning ensuring columns: " . $e->getMessage() . "\n";
        }
    }

    echo "Prediction migration complete\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}

?>
