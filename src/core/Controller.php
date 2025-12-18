<?php

namespace core;

/**
 * Base Controller Class for Time2Eat
 * Provides common functionality for all controllers
 */
abstract class Controller {
    protected $db;
    protected $user;
    
    public function __construct() {
        // Load database connection
        require_once CONFIG_PATH . '/database.php';
        $this->db = \Database::getInstance();
        
        // Load current user if logged in
        $this->loadCurrentUser();
    }
    
    /**
     * Load view with data
     */
    protected function view($viewName, $data = []) {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = APP_PATH . '/views/' . $viewName . '.php';
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View not found: {$viewName} (looked in: {$viewFile})");
        }
        
        // Get the content
        $content = ob_get_clean();
        
        // If it's a partial view (no layout), return content directly
        if (strpos($viewName, 'partials/') === 0 || strpos($viewName, 'errors/') === 0) {
            echo $content;
            return;
        }
        
        // Load layout
        $this->loadLayout($content, $data);
    }
    
    /**
     * Load layout with content
     */
    private function loadLayout($content, $data = []) {
        // Extract data for layout
        extract($data);
        
        // Default layout
        $layout = $data['layout'] ?? 'app';
        
        // Include layout file
        $layoutFile = APP_PATH . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            // Fallback to content only
            echo $content;
        }
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Return JSON success response
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        $this->json($data, $statusCode);
    }

    /**
     * Return JSON error response
     */
    protected function jsonError(string $message, int $statusCode = 400, array $errors = []): void
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        $this->json($response, $statusCode);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($path, $statusCode = 302) {
        http_response_code($statusCode);
        header("Location: {$path}");
        exit;
    }
    
    /**
     * Get POST data with validation
     */
    protected function input($key, $default = null) {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * Validate required fields
     */
    protected function validate($rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            $ruleArray = explode('|', $rule);
            
            foreach ($ruleArray as $singleRule) {
                if ($singleRule === 'required' && empty($value)) {
                    $errors[$field] = ucfirst($field) . ' is required';
                    break;
                }
                
                if (strpos($singleRule, 'min:') === 0 && strlen($value) < (int)substr($singleRule, 4)) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . substr($singleRule, 4) . ' characters';
                    break;
                }
                
                if (strpos($singleRule, 'max:') === 0 && strlen($value) > (int)substr($singleRule, 4)) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . substr($singleRule, 4) . ' characters';
                    break;
                }
                
                if ($singleRule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid email address';
                    break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
        }
    }
    
    /**
     * Check if user has specific role
     */
    protected function requireRole($role) {
        $this->requireAuth();
        
        if ($this->user['role'] !== $role) {
            http_response_code(403);
            $this->view('errors/403');
            exit;
        }
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']) && $this->user !== null;
    }
    
    /**
     * Load current user from session
     */
    private function loadCurrentUser() {
        if (isset($_SESSION['user_id'])) {
            try {
                $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
                $stmt->execute([$_SESSION['user_id']]);
                $this->user = $stmt->fetch();
                
                if (!$this->user) {
                    // User not found, clear session
                    session_destroy();
                    $this->user = null;
                }
            } catch (\Exception $e) {
                error_log("Error loading user: " . $e->getMessage());
                $this->user = null;
            }
        }
    }
    
    /**
     * Set flash message
     */
    protected function setFlash($type, $message) {
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get and clear flash messages
     */
    protected function getFlash($type = null) {
        if ($type) {
            $message = $_SESSION['flash'][$type] ?? null;
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
