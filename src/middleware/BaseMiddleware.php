<?php

declare(strict_types=1);

require_once __DIR__ . '/../traits/DatabaseTrait.php';
require_once __DIR__ . '/../traits/AuthTrait.php';

use traits\DatabaseTrait;
use traits\AuthTrait;

/**
 * Base Middleware Class
 * Provides database access and authentication functionality to middleware
 */
abstract class BaseMiddleware
{
    use DatabaseTrait, AuthTrait;
    
    protected ?\PDO $db = null;
    
    /**
     * Handle the middleware
     */
    abstract public function handle(array $parameters = []): bool;
    
    /**
     * Check if request is API request
     */
    protected function isApiRequest(): bool
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($requestUri, '/api/') !== false;
    }
    
    /**
     * Check if request is POST request
     */
    protected function isPostRequest(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
    }
    
    /**
     * Send JSON error response
     */
    protected function jsonError(string $message, int $code = 400, array $data = []): void
    {
        http_response_code($code);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code
        ];

        if (!empty($data)) {
            $response['data'] = $data;
        }

        echo json_encode($response);
        exit;
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Add flash message
     */
    protected function flash(string $type, string $message): void
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][$type] = $message;
    }
}
