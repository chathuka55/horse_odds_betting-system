<?php
/**
 * API Handler Class
 * Manages external API integrations
 */

class APIHandler {
    private $db;
    private $apiConfig;
    private $apiName;
    
    public function __construct($apiId = null, $apiName = null) {
        global $db;
        $this->db = $db;
        
        if ($apiId) {
            $stmt = $this->db->prepare("SELECT * FROM api_settings WHERE id = ? AND is_active = 1");
            $stmt->execute([$apiId]);
            $this->apiConfig = $stmt->fetch();
        } elseif ($apiName) {
            $stmt = $this->db->prepare("SELECT * FROM api_settings WHERE api_name LIKE ? AND is_active = 1");
            $stmt->execute(["%{$apiName}%"]);
            $this->apiConfig = $stmt->fetch();
        }
        
        if (!$this->apiConfig) {
            throw new Exception('API not configured or inactive');
        }
        
        $this->apiName = $this->apiConfig['api_name'];
    }
    
    /**
     * Make API request
     */
    private function makeRequest($endpoint, $params = []) {
        $url = rtrim($this->apiConfig['base_url'], '/') . '/' . ltrim($endpoint, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiConfig['api_key'],
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true);
        } else {
            throw new Exception("API Error: HTTP {$httpCode} - {$error}");
        }
    }
    
    /**
     * Get races from API
     */
    public function getRaces($date = null) {
        $date = $date ?? date('Y-m-d');
        
        try {
            $data = $this->makeRequest('/races', ['date' => $date]);
            return ['success' => true, 'data' => $data];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get race details
     */
    public function getRaceDetails($raceId) {
        try {
            $data = $this->makeRequest("/races/{$raceId}");
            return ['success' => true, 'data' => $data];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Get odds
     */
    public function getOdds($raceId) {
        try {
            $data = $this->makeRequest("/races/{$raceId}/odds");
            return ['success' => true, 'data' => $data];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Fetch and sync data to database
     */
    public function fetchAndSync($dataType = 'races') {
        try {
            switch ($dataType) {
                case 'races':
                    return $this->syncRaces();
                case 'odds':
                    return $this->syncOdds();
                case 'results':
                    return $this->syncResults();
                default:
                    return ['success' => false, 'error' => 'Unknown data type'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Sync races from API
     */
    private function syncRaces() {
        $result = $this->getRaces();
        
        if (!$result['success']) {
            return $result;
        }
        
        $count = 0;
        $races = $result['data']['races'] ?? $result['data'] ?? [];
        
        foreach ($races as $raceData) {
            // Check if race already exists
            $stmt = $this->db->prepare("SELECT id FROM races WHERE name = ? AND race_date = ?");
            $stmt->execute([$raceData['name'], $raceData['date']]);
            
            if (!$stmt->fetch()) {
                // Insert new race
                try {
                    $raceModel = new Race($this->db);
                    $raceModel->create([
                        'name' => $raceData['name'],
                        'race_date' => $raceData['date'],
                        'race_time' => $raceData['time'],
                        'distance' => $raceData['distance'] ?? '',
                        'status' => 'scheduled'
                    ]);
                    $count++;
                } catch (Exception $e) {
                    error_log("Error inserting race: " . $e->getMessage());
                }
            }
        }
        
        return ['success' => true, 'count' => $count];
    }
    
    /**
     * Sync odds
     */
    private function syncOdds() {
        // Implementation for odds sync
        return ['success' => true, 'count' => 0];
    }
    
    /**
     * Sync results
     */
    private function syncResults() {
        // Implementation for results sync
        return ['success' => true, 'count' => 0];
    }
}
?>