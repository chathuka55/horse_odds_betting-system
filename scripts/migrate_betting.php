<?php
require_once dirname(__DIR__) . '/includes/config.php';

try {
    // Add credit column if missing
    $col = $db->query("SHOW COLUMNS FROM users LIKE 'credit'")->fetch();
    if (!$col) {
        $db->exec("ALTER TABLE users ADD COLUMN credit DECIMAL(12,2) DEFAULT 0.00 AFTER phone");
        echo "Added credit column to users\n";
    } else {
        echo "credit column already exists\n";
    }

    // Create bets table if missing
    $tbl = $db->query("SHOW TABLES LIKE 'bets'")->fetch();
    if (!$tbl) {
        $db->exec("CREATE TABLE bets (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            race_id INT NOT NULL,
            race_entry_id INT NOT NULL,
            bet_type VARCHAR(50) DEFAULT 'win',
            amount DECIMAL(12,2) NOT NULL,
            odds_value VARCHAR(50) DEFAULT NULL,
            odds_decimal DECIMAL(10,2) DEFAULT NULL,
            potential_payout DECIMAL(12,2) DEFAULT NULL,
            payout_amount DECIMAL(12,2) DEFAULT NULL,
            status ENUM('pending','won','lost','refunded') DEFAULT 'pending',
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            settled_at DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        echo "Created bets table\n";
    } else {
        echo "bets table already exists\n";
    }

    // Create audit_logs table if missing
    $auditTbl = $db->query("SHOW TABLES LIKE 'audit_logs'")->fetch();
    if (!$auditTbl) {
        $db->exec("CREATE TABLE audit_logs (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            action VARCHAR(100) NOT NULL,
            user_id INT DEFAULT NULL,
            bet_id INT DEFAULT NULL,
            amount DECIMAL(12,2) DEFAULT NULL,
            before_credit DECIMAL(12,2) DEFAULT NULL,
            after_credit DECIMAL(12,2) DEFAULT NULL,
            details JSON DEFAULT NULL,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        echo "Created audit_logs table\n";
    } else {
        echo "audit_logs table already exists\n";
    }

    // Ensure foreign keys may be added if desired (skipped for safety)

    echo "Migration complete\n";
} catch (Exception $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
?>