<?php
header('Content-Type: application/json');

// Simulate API endpoint for live data
$action = $_GET['action'] ?? 'races';

switch($action) {
    case 'races':
        echo json_encode([
            'status' => 'success',
            'data' => getMockRaces()
        ]);
        break;
        
    case 'horses':
        echo json_encode([
            'status' => 'success',
            'data' => getMockHorses()
        ]);
        break;
        
    case 'odds':
        echo json_encode([
            'status' => 'success',
            'data' => generateRandomOdds()
        ]);
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

function generateRandomOdds() {
    $odds = [];
    for($i = 1; $i <= 8; $i++) {
        $odds[] = [
            'horse_id' => $i,
            'current_odds' => rand(2, 20) . '/' . rand(1, 5),
            'movement' => rand(-5, 5),
            'volume' => '$' . number_format(rand(10000, 500000))
        ];
    }
    return $odds;
}
?>