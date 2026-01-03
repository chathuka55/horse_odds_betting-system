<?php
/**
 * Data Fixtures Generator
 * Populates sample data for testing and demo purposes
 * 
 * Usage: php admin/fixtures-generator.php
 */

// Prevent direct web access
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from command line");
}

require_once dirname(__DIR__) . '/includes/config.php';

echo "🏇 RacingPro Analytics - Data Fixtures Generator\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Generate Sample Races
    echo "Generating sample races...\n";
    
    $sampleRaces = [
        [
            'name' => 'Kentucky Derby 2025',
            'track_id' => 1,
            'race_date' => date('Y-m-d', strtotime('+7 days')),
            'race_time' => '14:05',
            'distance' => '1 1/4 miles',
            'race_class' => 'Grade 1',
            'race_type' => 'flat',
            'prize_money' => 5000000,
            'currency' => 'USD',
            'going' => 'Good',
            'is_featured' => 1,
            'status' => 'scheduled'
        ],
        [
            'name' => 'Royal Ascot Gold Cup',
            'track_id' => 2,
            'race_date' => date('Y-m-d', strtotime('+3 days')),
            'race_time' => '15:30',
            'distance' => '2 1/2 miles',
            'race_class' => 'Group 1',
            'race_type' => 'flat',
            'prize_money' => 7500000,
            'currency' => 'GBP',
            'going' => 'Good to Firm',
            'is_featured' => 1,
            'status' => 'scheduled'
        ],
        [
            'name' => 'Melbourne Cup 2025',
            'track_id' => 3,
            'race_date' => date('Y-m-d', strtotime('+14 days')),
            'race_time' => '16:00',
            'distance' => '3200m',
            'race_class' => 'Group 1',
            'race_type' => 'flat',
            'prize_money' => 10000000,
            'currency' => 'AUD',
            'going' => 'Good',
            'is_featured' => 1,
            'status' => 'scheduled'
        ]
    ];
    
    foreach ($sampleRaces as $race) {
        try {
            $stmt = $db->prepare("
                INSERT INTO races 
                (name, track_id, race_date, race_time, distance, race_class, race_type, 
                 prize_money, currency, going, is_featured, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $race['name'], $race['track_id'], $race['race_date'], $race['race_time'],
                $race['distance'], $race['race_class'], $race['race_type'],
                $race['prize_money'], $race['currency'], $race['going'],
                $race['is_featured'], $race['status']
            ]);
            
            echo "  ✓ Created race: {$race['name']}\n";
        } catch (Exception $e) {
            echo "  ✗ Error creating race: {$e->getMessage()}\n";
        }
    }
    
    echo "\n✅ Fixtures generated successfully!\n";
    echo "\nYou can now:\n";
    echo "  1. Visit http://localhost/horse-racing-platform/races.php to see races\n";
    echo "  2. Login to admin at /auth/login.php\n";
    echo "  3. Manage races, horses, and entries from the admin panel\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
