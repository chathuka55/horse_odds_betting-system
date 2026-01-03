<?php
    /**
    * Helper Functions
    */

// Sanitize input
 
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}


  // Validate email

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}


// Generate random string
 
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}


 // Generate slug from string
 
function generateSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}


 // Upload file
 
function uploadFile($file, $directory, $allowedTypes = null) {
    $allowedTypes = $allowedTypes ?? ALLOWED_IMAGE_TYPES;
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large'];
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = generateRandomString(16) . '.' . $extension;
    $targetPath = UPLOADS_PATH . $directory . '/' . $filename;
    
    // Create directory if not exists
    $dir = UPLOADS_PATH . $directory;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $directory . '/' . $filename];
    }
    
    return ['success' => false, 'error' => 'Failed to move file'];
}

/**
 * Delete file
 */
function deleteFile($filepath) {
    $fullPath = UPLOADS_PATH . $filepath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Calculate win probability
 */
function calculateWinProbability($horse, $raceConditions = []) {
    // Basic probability calculation (can be enhanced with ML)
    $factors = [
        'form_weight' => 0.25,
        'class_weight' => 0.15,
        'jockey_weight' => 0.15,
        'trainer_weight' => 0.10,
        'distance_weight' => 0.10,
        'going_weight' => 0.10,
        'recent_form_weight' => 0.15
    ];
    
    $probability = 0;
    
    // Form analysis
    $form = $horse['form'] ?? '';
    $formPositions = array_map('intval', explode('-', $form));
    $avgPosition = count($formPositions) > 0 ? array_sum($formPositions) / count($formPositions) : 5;
    $formScore = max(0, (6 - $avgPosition) / 5) * 100;
    $probability += $formScore * $factors['form_weight'];
    
    // Career win rate
    $starts = $horse['career_starts'] ?? 1;
    $wins = $horse['career_wins'] ?? 0;
    $winRate = ($wins / max(1, $starts)) * 100;
    $probability += $winRate * $factors['class_weight'];
    
    // Rating factor
    $rating = $horse['rating'] ?? 80;
    $ratingScore = min(100, ($rating / 100) * 100);
    $probability += $ratingScore * $factors['recent_form_weight'];
    
    // Normalize to percentage
    $probability = min(95, max(1, $probability));
    
    return round($probability, 1);
}

/**
 * Convert fractional odds to decimal
 */
function fractionalToDecimal($fractional) {
    $parts = explode('/', $fractional);
    if (count($parts) === 2) {
        return round(($parts[0] / $parts[1]) + 1, 2);
    }
    return floatval($fractional);
}


 // Compute a prediction for a race entry using available data
 // Returns ['win_prob' => float, 'confidence' => float]

function getPredictionForEntry($entry) {
    global $db;
    // entry can be an id or an associative array
    if (is_int($entry) || is_string($entry)) {
        $stmt = $db->prepare('SELECT re.*, h.*, j.name as jockey_name, tr.id as trainer_id FROM race_entries re LEFT JOIN horses h ON re.horse_id = h.id LEFT JOIN jockeys j ON re.jockey_id = j.id LEFT JOIN trainers tr ON h.trainer_id = tr.id WHERE re.id = ?');
        $stmt->execute([$entry]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$entry) return ['win_prob' => 0.0, 'confidence' => 0.0];

    // Build horse data for base probability
    $horseData = [
        'form' => $entry['form'] ?? $entry['horse_form'] ?? '',
        'career_starts' => $entry['career_starts'] ?? $entry['career_starts'] ?? 0,
        'career_wins' => $entry['career_wins'] ?? $entry['career_wins'] ?? 0,
        'rating' => $entry['horse_rating'] ?? $entry['rating'] ?? 80
    ];

    // Base probability from existing helper
    $baseProb = calculateWinProbability($horseData);

    //  Jockey recent form 
    $jWinPct = 0;
    $jockeyBoost = 0;
    if (!empty($entry['jockey_id'])) {
        $j = $db->prepare('SELECT total_races, total_wins, recent_wins_30d, recent_rides_30d FROM jockeys WHERE id = ?');
        $j->execute([$entry['jockey_id']]);
        $jr = $j->fetch(PDO::FETCH_ASSOC);
        if ($jr && intval($jr['total_races']) > 0) {
            $jWinPct = ($jr['total_wins'] / max(1, $jr['total_races'])) * 100;
            $jockeyBoost = min(12, ($jWinPct - 8) * 0.25);
            // recent form multiplier if there is recent data
            if (!empty($jr['recent_rides_30d']) && intval($jr['recent_rides_30d']) > 0) {
                $recentWinPct = ($jr['recent_wins_30d'] / max(1, $jr['recent_rides_30d'])) * 100;
                $jockeyBoost += min(6, ($recentWinPct - 8) * 0.2);
            }
        }
    }

    //  Trainer recent form 
    $tWinPct = 0;
    $trainerBoost = 0;
    if (!empty($entry['trainer_id'])) {
        $t = $db->prepare('SELECT wins, races, recent_wins_30d, recent_runs_30d FROM trainers WHERE id = ?');
        $t->execute([$entry['trainer_id']]);
        $tr = $t->fetch(PDO::FETCH_ASSOC);
        if ($tr && intval($tr['races']) > 0) {
            $tWinPct = ($tr['wins'] / max(1, $tr['races'])) * 100;
            $trainerBoost = min(10, ($tWinPct - 8) * 0.18);
            if (!empty($tr['recent_runs_30d']) && intval($tr['recent_runs_30d']) > 0) {
                $recentTrainerPct = ($tr['recent_wins_30d'] / max(1, $tr['recent_runs_30d'])) * 100;
                $trainerBoost += min(5, ($recentTrainerPct - 8) * 0.15);
            }
        }
    }

    //  Distance / Going match 
    $distanceBoost = 0;
    if (!empty($entry['race_distance']) && !empty($entry['best_distance'])) {
        // simple match: if race distance within 10% of horse best distance
        $raceDist = floatval($entry['race_distance']);
        $bestDist = floatval($entry['best_distance']);
        if ($bestDist > 0 && abs($raceDist - $bestDist) / $bestDist <= 0.10) {
            $distanceBoost = 3; // small boost for distance suitability
        }
    }

    //  Odds adjustment 
    $oddsDecimal = floatval($entry['odds_decimal'] ?? $entry['current_odds_decimal'] ?? 0);
    if ($oddsDecimal <= 0 && !empty($entry['current_odds'])) {
        $oddsDecimal = fractionalToDecimal($entry['current_odds']);
    }
    $oddsSignal = 0;
    if ($oddsDecimal > 1) {
        // Shorter odds (closer to 1) indicate market confidence
        // Use a smooth mapping: signal in range 0..15
        $oddsSignal = max(0, min(15, (3.5 - log(max(1.01, $oddsDecimal))) * 4));
    }

    //  Recent horse form (last 5 starts) 
    $recentFormBoost = 0;
    if (!empty($entry['form'])) {
        $form = preg_split('/[^0-9]+/', $entry['form']);
        $nums = array_filter(array_map('intval', $form));
        if (count($nums) > 0) {
            $avgPos = array_sum($nums) / count($nums);
            // better average position => higher boost
            $recentFormBoost = max(0, min(12, (6 - $avgPos) * 2.2));
        }
    }

    // Compose final probability with tuned weights
    // We weight base probability highest, then recentForm, jockey, trainer, odds, distance
    $winProb = (
        $baseProb * 0.55 +
        $recentFormBoost * 0.12 +
        $jockeyBoost * 0.12 +
        $trainerBoost * 0.10 +
        $oddsSignal * 0.07 +
        $distanceBoost * 0.04
    );

    // Clamp to sensible range
    $winProb = max(0.5, min(95, $winProb));

    // Confidence: composite score based on amount of data and agreement
    $confidence = 40;
    if (!empty($entry['career_starts']) && intval($entry['career_starts']) > 8) $confidence += 10;
    if ($recentFormBoost > 6) $confidence += 10;
    if ($jockeyBoost > 6) $confidence += 8;
    if ($trainerBoost > 6) $confidence += 6;
    if ($oddsSignal > 6) $confidence += 6;
    // More cap and normalization
    $confidence = max(10, min(99, $confidence));

    return ['win_prob' => round($winProb, 2), 'confidence' => round($confidence, 2)];
}

/**
 * Convert decimal odds to fractional
 */
function decimalToFractional($decimal) {
    $decimal = floatval($decimal);
    if ($decimal <= 1) return '1/1';
    
    $numerator = $decimal - 1;
    $denominator = 1;
    
    // Find a reasonable fraction
    $fractions = [
        '1/5' => 1.2, '1/4' => 1.25, '1/3' => 1.33, '2/5' => 1.4, '1/2' => 1.5,
        '4/7' => 1.57, '8/15' => 1.53, '4/6' => 1.67, '8/13' => 1.62, '4/5' => 1.8,
        '5/6' => 1.83, '10/11' => 1.91, '1/1' => 2, '11/10' => 2.1, '6/5' => 2.2,
        '5/4' => 2.25, '11/8' => 2.38, '6/4' => 2.5, '13/8' => 2.63, '7/4' => 2.75,
        '15/8' => 2.88, '2/1' => 3, '9/4' => 3.25, '5/2' => 3.5, '11/4' => 3.75,
        '3/1' => 4, '100/30' => 4.33, '7/2' => 4.5, '4/1' => 5, '9/2' => 5.5,
        '5/1' => 6, '11/2' => 6.5, '6/1' => 7, '13/2' => 7.5, '7/1' => 8,
        '15/2' => 8.5, '8/1' => 9, '9/1' => 10, '10/1' => 11, '12/1' => 13,
        '14/1' => 15, '16/1' => 17, '20/1' => 21, '25/1' => 26, '33/1' => 34,
        '40/1' => 41, '50/1' => 51, '66/1' => 67, '100/1' => 101
    ];
    
    $closestFraction = '1/1';
    $closestDiff = PHP_FLOAT_MAX;
    
    foreach ($fractions as $fraction => $value) {
        $diff = abs($decimal - $value);
        if ($diff < $closestDiff) {
            $closestDiff = $diff;
            $closestFraction = $fraction;
        }
    }
    
    return $closestFraction;
}

/**
 * Get time ago string
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}


  // Log activity
 
function logActivity($action, $entityType = null, $entityId = null, $oldValues = null, $newValues = null) {
    global $db;
    
    $userId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    try {
        $stmt = $db->prepare("
            INSERT INTO activity_log (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $action,
            $entityType,
            $entityId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ipAddress,
            $userAgent
        ]);
    } catch (Exception $e) {
        error_log("Activity Log Error: " . $e->getMessage());
    }
}


 // CSRF token helpers
 
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function get_csrf_token_input() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '" />';
}

function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($token) || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Audit logging for financial actions (bets, settlements, credit changes)
 */
function logAudit($action, $userId = null, $betId = null, $amount = null, $details = []) {
    global $db;
    try {
        // Try to capture user's credit before/after if available
        $before = null;
        $after = null;
        if ($userId) {
            $stmt = $db->prepare('SELECT credit FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            $before = $row ? floatval($row['credit']) : null;
        }

        $stmt = $db->prepare('INSERT INTO audit_logs (action, user_id, bet_id, amount, details, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $action,
            $userId,
            $betId,
            $amount,
            json_encode($details)
        ]);

        // Optionally update after snapshot
        if ($userId) {
            $stmt = $db->prepare('SELECT credit FROM users WHERE id = ?');
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            $after = $row ? floatval($row['credit']) : null;
            // update the last audit row with before/after if possible
            $auditId = $db->lastInsertId();
            $stmt = $db->prepare('UPDATE audit_logs SET before_credit = ?, after_credit = ? WHERE id = ?');
            $stmt->execute([$before, $after, $auditId]);
        }
    } catch (Exception $e) {
        error_log('Audit log error: ' . $e->getMessage());
    }
}


 // Get all races
 
function getRaces($filters = [], $limit = null, $offset = 0) {
    global $db;
    
    $where = ['1=1'];
    $params = [];
    
    if (!empty($filters['date'])) {
        $where[] = 'r.race_date = ?';
        $params[] = $filters['date'];
    }
    
    if (!empty($filters['track_id'])) {
        $where[] = 'r.track_id = ?';
        $params[] = $filters['track_id'];
    }
    
    if (!empty($filters['status'])) {
        $where[] = 'r.status = ?';
        $params[] = $filters['status'];
    }
    
    $whereString = implode(' AND ', $where);
    $limitString = $limit ? "LIMIT {$offset}, {$limit}" : '';
    
    $sql = "
        SELECT r.*, t.name as track_name, t.country as track_country,
               (SELECT COUNT(*) FROM race_entries WHERE race_id = r.id AND is_non_runner = 0) as runner_count
        FROM races r
        LEFT JOIN tracks t ON r.track_id = t.id
        WHERE {$whereString}
        ORDER BY r.race_date ASC, r.race_time ASC
        {$limitString}
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

/**
 * Get single race with entries
 */
function getRaceWithEntries($raceId) {
    global $db;
    
    // Get race details
    $stmt = $db->prepare("
        SELECT r.*, t.name as track_name, t.location as track_location, t.country as track_country
        FROM races r
        LEFT JOIN tracks t ON r.track_id = t.id
        WHERE r.id = ?
    ");
    $stmt->execute([$raceId]);
    $race = $stmt->fetch();
    
    if (!$race) return null;
    
    // Get entries
    $stmt = $db->prepare("
        SELECT re.*, 
               h.name as horse_name, h.age as horse_age, h.gender as horse_gender,
               h.form as horse_form, h.career_wins, h.career_places, h.career_starts,
               h.career_earnings, h.rating as horse_rating, h.best_distance,
               j.name as jockey_name,
               tr.name as trainer_name,
               o.name as owner_name
        FROM race_entries re
        LEFT JOIN horses h ON re.horse_id = h.id
        LEFT JOIN jockeys j ON re.jockey_id = j.id
        LEFT JOIN trainers tr ON h.trainer_id = tr.id
        LEFT JOIN owners o ON h.owner_id = o.id
        WHERE re.race_id = ?
        ORDER BY re.saddle_number ASC
    ");
    $stmt->execute([$raceId]);
    $race['entries'] = $stmt->fetchAll();
    
    return $race;
}

/**
 * Get horses
 */
function getHorses($limit = null, $offset = 0) {
    global $db;
    
    $limitString = $limit ? "LIMIT {$offset}, {$limit}" : '';
    
    $sql = "
        SELECT h.*, t.name as trainer_name, o.name as owner_name
        FROM horses h
        LEFT JOIN trainers t ON h.trainer_id = t.id
        LEFT JOIN owners o ON h.owner_id = o.id
        WHERE h.is_active = 1
        ORDER BY h.name ASC
        {$limitString}
    ";
    
    return $db->query($sql)->fetchAll();
}

/**
 * Get tracks
 */
function getTracks() {
    global $db;
    return $db->query("SELECT * FROM tracks WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
}

/**
 * Get jockeys
 */
function getJockeys() {
    global $db;
    return $db->query("SELECT * FROM jockeys WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
}

/**
 * Get trainers
 */
function getTrainers() {
    global $db;
    return $db->query("SELECT * FROM trainers WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
}

/**
 * Get owners
 */
function getOwners() {
    global $db;
    return $db->query("SELECT * FROM owners WHERE is_active = 1 ORDER BY name ASC")->fetchAll();
}

/**
 * Validate password strength
 * Returns array of errors (empty if valid)
 */
function validatePassword($password) {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?\":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    return $errors;
}

?>