<?php
/**
 * API Response Handler
 * Standardized JSON response format for all API endpoints
 */

class APIResponse {
    public static function success($data = null, $message = 'Success') {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }
    
    public static function error($message = 'Error', $code = 400, $errors = null) {
        http_response_code($code);
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
    }
    
    public static function paginated($data, $total, $page, $perPage) {
        return [
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'pages' => ceil($total / $perPage)
            ]
        ];
    }
}

/**
 * Validation Helper
 */
class Validator {
    private $errors = [];
    
    public function validate($data, $rules) {
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            
            foreach ($ruleList as $rule) {
                $this->validateField($field, $value, $rule);
            }
        }
        
        return count($this->errors) === 0;
    }
    
    private function validateField($field, $value, $rule) {
        if (strpos($rule, ':') !== false) {
            list($ruleName, $param) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $param = null;
        }
        
        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' is required';
                }
                break;
                
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be a valid email';
                }
                break;
                
            case 'min':
                if (!empty($value) && strlen($value) < intval($param)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be at least ' . $param . ' characters';
                }
                break;
                
            case 'max':
                if (!empty($value) && strlen($value) > intval($param)) {
                    $this->errors[$field][] = ucfirst($field) . ' must not exceed ' . $param . ' characters';
                }
                break;
                
            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be numeric';
                }
                break;
                
            case 'date':
                if (!empty($value) && !strtotime($value)) {
                    $this->errors[$field][] = ucfirst($field) . ' must be a valid date';
                }
                break;
        }
    }
    
    public function getErrors() {
        return $this->errors;
    }
}
?>
