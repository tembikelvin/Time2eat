<?php

declare(strict_types=1);

namespace traits;

require_once __DIR__ . '/../helpers/environment.php';

/**
 * Response Trait
 * Provides methods for handling different response types
 */
trait ResponseTrait
{
    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200, array $headers = []): void
    {
        $this->setJsonHeaders($headers);
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    
    /**
     * Return JSON success response
     */
    protected function jsonSuccess(string $message = 'Success', array $data = [], int $statusCode = 200): void
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
        
        $this->json($response, $statusCode);
    }
    
    /**
     * Return JSON error response
     */
    protected function jsonError(string $message = 'Error', int $statusCode = 400, array $errors = []): void
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
     * Return JSON response (alias for json() for backward compatibility)
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        $this->json($data, $statusCode);
    }
    
    /**
     * Return paginated JSON response
     */
    protected function jsonPaginated(array $data, array $pagination, string $message = 'Success'): void
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => $pagination
        ];
        
        $this->json($response);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        // If URL doesn't start with http or https, treat it as an application path
        if (!preg_match('/^https?:\/\//', $url)) {
            // Normalize: remove duplicate base application path if present (e.g., '/eat/...')
            $appPath = function_exists('getApplicationPath') ? rtrim(getApplicationPath(), '/') : '';
            if (!empty($appPath)) {
                // Ensure appPath starts with '/'
                if ($appPath[0] !== '/') {
                    $appPath = '/' . $appPath;
                }
                $withSlash = $appPath . '/';
                if (strpos($url, $withSlash) === 0) {
                    $url = substr($url, strlen($appPath));
                    if ($url === '') {
                        $url = '/';
                    }
                }
            }

            // Build full URL using environment-aware helper (adds base path exactly once)
            $url = url($url);
        }

        header("Location: {$url}", true, $statusCode);
        exit;
    }
    
    /**
     * Redirect back with input and errors
     */
    protected function redirectBack(array $errors = [], array $input = []): void
    {
        $this->startSession();
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
        }
        
        if (!empty($input)) {
            $_SESSION['old_input'] = $input;
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    /**
     * Redirect with flash message
     */
    protected function redirectWithMessage(string $url, string $type, string $message): void
    {
        $this->flash($type, $message);
        $this->redirect($url);
    }
    
    /**
     * Return XML response
     */
    protected function xml(string $xml, int $statusCode = 200): void
    {
        header('Content-Type: application/xml; charset=utf-8');
        http_response_code($statusCode);
        echo $xml;
        exit;
    }
    
    /**
     * Return CSV response
     */
    protected function csv(array $data, string $filename = 'export.csv', array $headers = []): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers if provided
        if (!empty($headers)) {
            fputcsv($output, $headers);
        }
        
        // Add data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Return file download
     */
    protected function download(string $filePath, string $filename = null, string $mimeType = null): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die('File not found');
        }
        
        $filename = $filename ?? basename($filePath);
        $mimeType = $mimeType ?? $this->getMimeType($filePath);
        
        header("Content-Type: {$mimeType}");
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($filePath);
        exit;
    }
    
    /**
     * Stream file content
     */
    protected function stream(string $filePath, string $mimeType = null): void
    {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die('File not found');
        }
        
        $mimeType = $mimeType ?? $this->getMimeType($filePath);
        
        header("Content-Type: {$mimeType}");
        header('Content-Length: ' . filesize($filePath));
        header('Accept-Ranges: bytes');
        
        // Handle range requests for video/audio streaming
        if (isset($_SERVER['HTTP_RANGE'])) {
            $this->handleRangeRequest($filePath);
        } else {
            readfile($filePath);
        }
        
        exit;
    }
    
    /**
     * Return image response with caching
     */
    protected function image(string $imagePath, int $cacheDuration = 3600): void
    {
        if (!file_exists($imagePath)) {
            http_response_code(404);
            die('Image not found');
        }
        
        $mimeType = $this->getMimeType($imagePath);
        $lastModified = filemtime($imagePath);
        $etag = md5_file($imagePath);
        
        // Set caching headers
        header("Content-Type: {$mimeType}");
        header('Cache-Control: public, max-age=' . $cacheDuration);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        header("ETag: \"{$etag}\"");
        
        // Check if client has cached version
        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '';
        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        
        if (($ifModifiedSince && strtotime($ifModifiedSince) >= $lastModified) ||
            ($ifNoneMatch && $ifNoneMatch === "\"{$etag}\"")) {
            http_response_code(304);
            exit;
        }
        
        readfile($imagePath);
        exit;
    }
    
    /**
     * Return API response with standard format
     */
    protected function apiResponse(mixed $data = null, string $message = 'Success', int $statusCode = 200, array $meta = []): void
    {
        $response = [
            'success' => $statusCode < 400,
            'message' => $message,
            'status_code' => $statusCode,
            'timestamp' => date('c')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        
        $this->json($response, $statusCode);
    }
    
    /**
     * Return Server-Sent Events response
     */
    protected function sse(callable $callback, int $interval = 1): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('Access-Control-Allow-Origin: *');
        
        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        while (true) {
            $data = $callback();
            
            if ($data !== null) {
                echo "data: " . json_encode($data) . "\n\n";
                flush();
            }
            
            sleep($interval);
            
            // Check if client disconnected
            if (connection_aborted()) {
                break;
            }
        }
    }
    
    /**
     * Set JSON headers
     */
    private function setJsonHeaders(array $additionalHeaders = []): void
    {
        // CRITICAL: Clear output buffer before setting headers
        if (ob_get_level()) {
            ob_clean();
        }
        
        // CRITICAL: Only set headers if they haven't been sent yet
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, no-store, must-revalidate, private');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            foreach ($additionalHeaders as $name => $value) {
                header("{$name}: {$value}");
            }
        }
    }
    
    /**
     * Get MIME type for file
     */
    private function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
    
    /**
     * Handle HTTP range requests for streaming
     */
    private function handleRangeRequest(string $filePath): void
    {
        $fileSize = filesize($filePath);
        $range = $_SERVER['HTTP_RANGE'];
        
        if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
            $start = (int)$matches[1];
            $end = $matches[2] ? (int)$matches[2] : $fileSize - 1;
            
            if ($start > $end || $start >= $fileSize) {
                http_response_code(416);
                header("Content-Range: bytes */{$fileSize}");
                return;
            }
            
            $length = $end - $start + 1;
            
            http_response_code(206);
            header("Content-Range: bytes {$start}-{$end}/{$fileSize}");
            header("Content-Length: {$length}");
            
            $file = fopen($filePath, 'rb');
            fseek($file, $start);
            
            $buffer = 8192;
            while (!feof($file) && $length > 0) {
                $read = min($buffer, $length);
                echo fread($file, $read);
                $length -= $read;
                flush();
            }
            
            fclose($file);
        }
    }
    
    /**
     * Set CORS headers
     */
    protected function setCorsHeaders(array $allowedOrigins = ['*'], array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE'], array $allowedHeaders = ['Content-Type', 'Authorization']): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: {$origin}");
        }
        
        header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
    
    /**
     * Return no content response
     */
    protected function noContent(): void
    {
        http_response_code(204);
        exit;
    }
    
    /**
     * Return created response
     */
    protected function created(array $data = [], string $location = null): void
    {
        if ($location) {
            header("Location: {$location}");
        }
        
        $this->json($data, 201);
    }
    
    /**
     * Return accepted response
     */
    protected function accepted(array $data = []): void
    {
        $this->json($data, 202);
    }
}
