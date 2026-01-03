<?php
require_once dirname(__DIR__) . '/includes/config.php';

try {
    // 1) Create or find test user
    $email = 'testuser@example.com';
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $password = password_hash('Test@1234', PASSWORD_DEFAULT, ['cost' => HASH_COST]);
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, role, credit) VALUES (?, ?, ?, ?, 'user', 0)");
        $stmt->execute(['testuser', $email, $password, 'Test User']);
        $userId = $db->lastInsertId();
        echo "Created user id=$userId\n";
    } else {
        $userId = $user['id'];
        echo "Found user id={$userId}\n";
    }

    // 2) Ensure user has credit
    $credit = 100.00;
    $stmt = $db->prepare("UPDATE users SET credit = ? WHERE id = ?");
    $stmt->execute([$credit, $userId]);
    echo "Set user credit to {$credit}\n";

    // 3) Pick or create a race
    $stmt = $db->query("SELECT id FROM races LIMIT 1");
    $race = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($race) {
        $raceId = $race['id'];
        echo "Using race id={$raceId}\n";
    } else {
        $stmt = $db->prepare("INSERT INTO races (name, race_date, race_time, prize_money) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Test Race', date('Y-m-d'), date('H:i:s'), 1000]);
        $raceId = $db->lastInsertId();
        echo "Created race id={$raceId}\n";
    }

    // 4) Ensure there's at least one race_entry for this race
    $stmt = $db->prepare("SELECT * FROM race_entries WHERE race_id = ? LIMIT 1");
    $stmt->execute([$raceId]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($entry) {
        $entryId = $entry['id'];
        echo "Found race_entry id={$entryId}\n";
    } else {
        // Pick a horse and jockey
        $horse = $db->query("SELECT id FROM horses LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $jockey = $db->query("SELECT id FROM jockeys LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $horseId = $horse['id'] ?? 1;
        $jockeyId = $jockey['id'] ?? null;

        $stmt = $db->prepare("INSERT INTO race_entries (race_id, horse_id, jockey_id, saddle_number, current_odds, odds_decimal, win_probability) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$raceId, $horseId, $jockeyId, 1, '5/1', 6.0, 10.0]);
        $entryId = $db->lastInsertId();
        echo "Created race_entry id={$entryId}\n";
    }

    // 5) Place a pending bet for the user (deduct credit)
    $betAmount = 10.00;
    $stmt = $db->prepare("SELECT credit FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (floatval($user['credit']) < $betAmount) {
        throw new Exception('Insufficient credit for test user');
    }

    // Deduct credit
    $stmt = $db->prepare("UPDATE users SET credit = credit - ? WHERE id = ?");
    $stmt->execute([$betAmount, $userId]);

    // Compute potential payout
    $oddsDecimal = 6.0; // as inserted above
    $potential = round($betAmount * $oddsDecimal, 2);

    $stmt = $db->prepare("INSERT INTO bets (user_id, race_id, race_entry_id, bet_type, amount, odds_value, odds_decimal, potential_payout, status) VALUES (?, ?, ?, 'win', ?, ?, ?, ?, 'pending')");
    $stmt->execute([$userId, $raceId, $entryId, $betAmount, '5/1', $oddsDecimal, $potential]);
    $betId = $db->lastInsertId();
    echo "Inserted bet id={$betId} for user={$userId} on entry={$entryId} amount={$betAmount} potential={$potential}\n";

    echo "Test bet setup complete\n";

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

?>