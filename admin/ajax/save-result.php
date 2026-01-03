<?php
/**
 * AJAX Handler: Save Race Result
 * Handles recording race results
 */

header('Content-Type: application/json');

require_once dirname(__DIR__, 2) . '/includes/config.php';

if (!isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// CSRF protection for admin actions
require_once dirname(__DIR__, 2) . '/includes/functions.php';
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$raceId = intval($_POST['race_id'] ?? 0);

if ($raceId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Race ID is required']);
    exit;
}

// Results array - format: results[entry_id] = position
$results = $_POST['results'] ?? [];
$times = $_POST['times'] ?? [];
$margins = $_POST['margins'] ?? [];

try {
    $db->beginTransaction();

    // Prepare models
    $raceResultModel = new RaceResult($db);
    $entryModel = new RaceEntry($db);
    $horseModel = new Horse($db);
    $jockeyModel = new Jockey($db);

    // Reverse any existing results for this race (adjust horse/jockey aggregates), then remove them
    $existingResults = $raceResultModel->getAll('race_id = ?', [$raceId]);
    foreach ($existingResults as $er) {
        $raceEntryId = $er['race_entry_id'] ?? null;
        if (!$raceEntryId) {
            $raceResultModel->delete($er['id']);
            continue;
        }

        $entry = $entryModel->getById($raceEntryId);
        $horseId = $entry['horse_id'] ?? null;
        $jockeyId = $entry['jockey_id'] ?? null;

        // Rollback horse aggregates
        if ($horseId) {
            $horse = $horseModel->getById($horseId);
            if ($horse) {
                $hUpdate = [];
                $hUpdate['career_starts'] = max(0, intval($horse['career_starts'] ?? 0) - 1);
                $pos = intval($er['finish_position'] ?? 0);
                if ($pos == 1) $hUpdate['career_wins'] = max(0, intval($horse['career_wins'] ?? 0) - 1);
                if ($pos == 2) $hUpdate['career_places'] = max(0, intval($horse['career_places'] ?? 0) - 1);
                if ($pos == 3) $hUpdate['career_shows'] = max(0, intval($horse['career_shows'] ?? 0) - 1);
                $hUpdate['career_earnings'] = max(0, floatval($horse['career_earnings'] ?? 0) - floatval($er['prize_won'] ?? 0));
                $horseModel->update($horseId, $hUpdate);
            }
        }

        // Rollback jockey aggregates
        if ($jockeyId) {
            $jockey = $jockeyModel->getById($jockeyId);
            if ($jockey) {
                $oldTotalRaces = intval($jockey['total_races'] ?? 0);
                $oldTotalWins = intval($jockey['total_wins'] ?? 0);
                $newTotalRaces = max(0, $oldTotalRaces - 1);
                $newTotalWins = $oldTotalWins - (intval($er['finish_position'] ?? 0) == 1 ? 1 : 0);
                $newTotalWins = max(0, $newTotalWins);
                $winPct = $newTotalRaces > 0 ? round(($newTotalWins / $newTotalRaces) * 100, 2) : 0;

                $jockeyModel->update($jockeyId, [
                    'total_races' => $newTotalRaces,
                    'total_wins' => $newTotalWins,
                    'win_percentage' => $winPct
                ]);
            }
        }

        // Finally remove the old result row
        $raceResultModel->delete($er['id']);
    }

    // Get race info and prize
    $raceModel = new Race($db);
    $raceInfo = $raceModel->getById($raceId);
    $totalPrize = $raceInfo['prize_money'] ?? 0;

    $prizeDistribution = [1 => 0.60, 2 => 0.20, 3 => 0.10, 4 => 0.05, 5 => 0.05];

    $entryModel = new RaceEntry($db);
    $horseModel = new Horse($db);

    foreach ($results as $entryId => $position) {
        $entryId = intval($entryId);
        $position = intval($position);
        if ($entryId <= 0 || $position <= 0) continue;

        // Get starting price and horse id from entry
        $entry = $entryModel->getById($entryId);
        $startingPrice = $entry['current_odds'] ?? '';
        $horseId = $entry['horse_id'] ?? null;

        $prizeWon = isset($prizeDistribution[$position]) ? $totalPrize * $prizeDistribution[$position] : 0;

        // Insert result using model
        $raceResultModel->create([
            'race_id' => $raceId,
            'race_entry_id' => $entryId,
            'finish_position' => $position,
            'finish_time' => $times[$entryId] ?? null,
            'margin' => $margins[$entryId] ?? null,
            'starting_price' => $startingPrice,
            'prize_won' => $prizeWon
        ]);

        // Update horse career statistics (increment starts and earnings; wins/places/shows when applicable)
        if ($horseId) {
            $horse = $horseModel->getById($horseId);
            if ($horse) {
                $update = [];
                $update['career_starts'] = ($horse['career_starts'] ?? 0) + 1;
                if ($position == 1) $update['career_wins'] = ($horse['career_wins'] ?? 0) + 1;
                if ($position == 2) $update['career_places'] = ($horse['career_places'] ?? 0) + 1;
                if ($position == 3) $update['career_shows'] = ($horse['career_shows'] ?? 0) + 1;
                $update['career_earnings'] = ($horse['career_earnings'] ?? 0) + $prizeWon;
                $horseModel->update($horseId, $update);
            }
        }

        // Update jockey statistics (total_races, total_wins, win_percentage)
        $jockeyId = $entry['jockey_id'] ?? null;
        if ($jockeyId) {
            $jockeyModel = new Jockey($db);
            $jockey = $jockeyModel->getById($jockeyId);
            if ($jockey) {
                $oldTotalRaces = intval($jockey['total_races'] ?? 0);
                $oldTotalWins = intval($jockey['total_wins'] ?? 0);
                $newTotalRaces = $oldTotalRaces + 1;
                $newTotalWins = $oldTotalWins + ($position == 1 ? 1 : 0);
                $winPct = $newTotalRaces > 0 ? round(($newTotalWins / $newTotalRaces) * 100, 2) : 0;

                $jockeyModel->update($jockeyId, [
                    'total_races' => $newTotalRaces,
                    'total_wins' => $newTotalWins,
                    'win_percentage' => $winPct
                ]);
            }
        }
    }

    // Update race status to finished
    $raceModel->update($raceId, ['status' => 'finished']);

    $db->commit();

    logActivity('Recorded race results', 'race', $raceId, null, $results);

    // Settle bets for this race: winners get payouts, losers marked lost
    try {
        // Ensure bets table exists
        $db->exec("CREATE TABLE IF NOT EXISTS bets (
            id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
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

        // Winners
        $winners = $raceResultModel->getAll('race_id = ? AND finish_position = ?', [$raceId, 1]);
        foreach ($winners as $w) {
            $entryId = $w['race_entry_id'];
            // Fetch pending bets for this entry
            $stmt = $db->prepare("SELECT * FROM bets WHERE race_entry_id = ? AND race_id = ? AND status = 'pending'");
            $stmt->execute([$entryId, $raceId]);
            $bets = $stmt->fetchAll();
            foreach ($bets as $bet) {
                $payout = round(floatval($bet['amount']) * floatval($bet['odds_decimal'] ?? 1), 2);
                // Update bet as won
                $updateBet = $db->prepare("UPDATE bets SET status = 'won', payout_amount = ?, settled_at = NOW() WHERE id = ?");
                $updateBet->execute([$payout, $bet['id']]);

                // Credit user
                $creditStmt = $db->prepare("UPDATE users SET credit = credit + ? WHERE id = ?");
                $creditStmt->execute([$payout, $bet['user_id']]);

                // Record payout
                $pstmt = $db->prepare("INSERT INTO payouts (race_id, bet_type, combination, payout_amount, pool_total, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $pstmt->execute([$raceId, $bet['bet_type'], $bet['race_entry_id'], $payout, $payout]);

                logActivity('settle_bet_won', 'bet', $bet['id'], null, ['payout' => $payout]);
                // Audit the credit change for the user
                logAudit('settle_bet_won', $bet['user_id'], $bet['id'], $payout, ['entry' => $bet['race_entry_id']]);
            }
        }

        // Mark remaining pending bets for this race as lost
        // Log lost bets individually so we have audit records
        $stmt = $db->prepare("SELECT * FROM bets WHERE race_id = ? AND status = 'pending'");
        $stmt->execute([$raceId]);
        $pending = $stmt->fetchAll();
        $updateLost = $db->prepare("UPDATE bets SET status = 'lost', settled_at = NOW() WHERE id = ?");
        foreach ($pending as $pb) {
            $updateLost->execute([$pb['id']]);
            logActivity('settle_bet_lost', 'bet', $pb['id'], null, null);
            logAudit('settle_bet_lost', $pb['user_id'], $pb['id'], 0, ['entry' => $pb['race_entry_id']]);
        }

    } catch (Exception $e) {
        error_log('Settle Bets Error: ' . $e->getMessage());
    }

    echo json_encode(['success' => true, 'message' => 'Results saved successfully']);

} catch (Exception $e) {
    $db->rollBack();
    error_log("Save Result Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>