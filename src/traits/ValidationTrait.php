<?php

declare(strict_types=1);

namespace traits;

/**
 * Validation Trait
 * Provides comprehensive input validation methods
 */
trait ValidationTrait
{
    /**
     * Validate data against rules
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        $validatedData = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $fieldErrors = [];
            
            // Parse rules
            $parsedRules = $this->parseRules($fieldRules);
            
            foreach ($parsedRules as $rule) {
                $result = $this->validateField($value, $rule, $data);
                
                if ($result !== true) {
                    $fieldErrors[] = $result;
                }
            }
            
            if (empty($fieldErrors)) {
                $validatedData[$field] = $this->sanitizeValue($value, $parsedRules);
            } else {
                $errors[$field] = $fieldErrors;
            }
        }
        
        return [
            'valid' => empty($errors),
            'data' => $validatedData,
            'errors' => $errors
        ];
    }
    
    /**
     * Parse validation rules
     */
    private function parseRules(string|array $rules): array
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        
        $parsed = [];
        foreach ($rules as $rule) {
            if (strpos($rule, ':') !== false) {
                [$name, $params] = explode(':', $rule, 2);
                $parsed[] = [
                    'name' => $name,
                    'params' => explode(',', $params)
                ];
            } else {
                $parsed[] = [
                    'name' => $rule,
                    'params' => []
                ];
            }
        }
        
        return $parsed;
    }
    
    /**
     * Validate individual field
     */
    private function validateField(mixed $value, array $rule, array $allData): string|bool
    {
        $ruleName = $rule['name'];
        $params = $rule['params'];
        
        return match($ruleName) {
            'required' => $this->validateRequired($value),
            'email' => $this->validateEmail($value),
            'min' => $this->validateMin($value, (int)$params[0]),
            'max' => $this->validateMax($value, (int)$params[0]),
            'minlength' => $this->validateMinLength($value, (int)$params[0]),
            'maxlength' => $this->validateMaxLength($value, (int)$params[0]),
            'numeric' => $this->validateNumeric($value),
            'integer' => $this->validateInteger($value),
            'alpha' => $this->validateAlpha($value),
            'alphanumeric' => $this->validateAlphanumeric($value),
            'phone' => $this->validatePhone($value),
            'url' => $this->validateUrl($value),
            'date' => $this->validateDate($value),
            'datetime' => $this->validateDateTime($value),
            'in' => $this->validateIn($value, $params),
            'not_in' => $this->validateNotIn($value, $params),
            'regex' => $this->validateRegex($value, $params[0]),
            'confirmed' => $this->validateConfirmed($value, $params[0], $allData),
            'unique' => $this->validateUnique($value, $params[0], $params[1] ?? null),
            'exists' => $this->validateExists($value, $params[0], $params[1] ?? 'id'),
            default => true
        };
    }
    
    /**
     * Validation methods
     */
    private function validateRequired(mixed $value): string|bool
    {
        if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
            return 'This field is required';
        }
        return true;
    }
    
    private function validateEmail(mixed $value): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'Please enter a valid email address';
        }
        return true;
    }
    
    private function validateMin(mixed $value, int $min): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (is_numeric($value) && (float)$value < $min) {
            return "Value must be at least {$min}";
        }
        return true;
    }
    
    private function validateMax(mixed $value, int $max): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (is_numeric($value) && (float)$value > $max) {
            return "Value must not exceed {$max}";
        }
        return true;
    }
    
    private function validateMinLength(mixed $value, int $min): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (strlen((string)$value) < $min) {
            return "Must be at least {$min} characters";
        }
        return true;
    }
    
    private function validateMaxLength(mixed $value, int $max): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (strlen((string)$value) > $max) {
            return "Must not exceed {$max} characters";
        }
        return true;
    }
    
    private function validateNumeric(mixed $value): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (!is_numeric($value)) {
            return 'Must be a number';
        }
        return true;
    }
    
    private function validateInteger(mixed $value): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return 'Must be an integer';
        }
        return true;
    }
    
    private function validateAlpha(mixed $value): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (!preg_match('/^[a-zA-Z]+$/', (string)$value)) {
            return 'Must contain only letters';
        }
        return true;
    }
    
    private function validateAlphanumeric(mixed $value): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (!preg_match('/^[a-zA-Z0-9]+$/', (string)$value)) {
            return 'Must contain only letters and numbers';
        }
        return true;
    }
    
    private function validatePhone(mixed $value): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        // Cameroon phone number format
        $pattern = '/^(\+237|237)?[2368]\d{8}$/';
        if (!preg_match($pattern, (string)$value)) {
            return 'Please enter a valid Cameroon phone number';
        }
        return true;
    }
    
    private function validateUrl(mixed $value): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return 'Please enter a valid URL';
        }
        return true;
    }
    
    private function validateDate(mixed $value): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        $date = \DateTime::createFromFormat('Y-m-d', (string)$value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            return 'Please enter a valid date (YYYY-MM-DD)';
        }
        return true;
    }
    
    private function validateDateTime(mixed $value): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', (string)$value);
        if (!$date || $date->format('Y-m-d H:i:s') !== $value) {
            return 'Please enter a valid datetime (YYYY-MM-DD HH:MM:SS)';
        }
        return true;
    }
    
    private function validateIn(mixed $value, array $allowed): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (!in_array($value, $allowed, true)) {
            $allowedStr = implode(', ', $allowed);
            return "Value must be one of: {$allowedStr}";
        }
        return true;
    }
    
    private function validateNotIn(mixed $value, array $forbidden): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (in_array($value, $forbidden, true)) {
            return 'This value is not allowed';
        }
        return true;
    }
    
    private function validateRegex(mixed $value, string $pattern): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (!preg_match($pattern, (string)$value)) {
            return 'Invalid format';
        }
        return true;
    }
    
    private function validateConfirmed(mixed $value, string $field, array $allData): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        $confirmValue = $allData[$field] ?? null;
        if ($value !== $confirmValue) {
            return 'Confirmation does not match';
        }
        return true;
    }
    
    private function validateUnique(mixed $value, string $table, ?string $column = null): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        $column = $column ?? 'id';
        
        if (method_exists($this, 'fetchOne')) {
            $result = $this->fetchOne("SELECT {$column} FROM {$table} WHERE {$column} = ?", [$value]);
            if ($result) {
                return 'This value already exists';
            }
        }
        
        return true;
    }
    
    private function validateExists(mixed $value, string $table, string $column = 'id'): string|bool
    {
        if (is_null($value) || $value === '') return true;
        
        if (method_exists($this, 'fetchOne')) {
            $result = $this->fetchOne("SELECT {$column} FROM {$table} WHERE {$column} = ?", [$value]);
            if (!$result) {
                return 'This value does not exist';
            }
        }
        
        return true;
    }
    
    
    /**
     * Sanitize value based on rules
     */
    private function sanitizeValue(mixed $value, array $rules): mixed
    {
        if (is_null($value) || $value === '') {
            return $value;
        }
        
        foreach ($rules as $rule) {
            $value = match($rule['name']) {
                'email' => filter_var($value, FILTER_SANITIZE_EMAIL),
                'url' => filter_var($value, FILTER_SANITIZE_URL),
                'integer' => (int)$value,
                'numeric' => (float)$value,
                'alpha', 'alphanumeric' => preg_replace('/[^a-zA-Z0-9]/', '', $value),
                default => is_string($value) ? trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8')) : $value
            };
        }
        
        return $value;
    }
    
    /**
     * Custom validation rule
     */
    protected function addValidationRule(string $name, callable $callback): void
    {
        // This would be implemented with a registry pattern in a full framework
        // For now, custom rules can be added by extending the validateField method
    }
}
