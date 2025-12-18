<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../security/SecurityManager.php';

/**
 * CAPTCHA Controller
 * Handles CAPTCHA generation and validation
 */
class CaptchaController extends BaseController
{
    private SecurityManager $security;
    
    public function __construct()
    {
        parent::__construct();
        $this->security = SecurityManager::getInstance();
    }
    
    /**
     * Generate new CAPTCHA
     */
    public function generate(): void
    {
        try {
            $captcha = $this->security->generateCaptcha();
            
            $this->jsonSuccess('CAPTCHA generated successfully', [
                'token' => $captcha['token'],
                'image' => $captcha['image']
            ]);
        } catch (Exception $e) {
            $this->security->logSecurityEvent('captcha_generation_failed', [
                'error' => $e->getMessage()
            ]);
            
            $this->jsonError('Failed to generate CAPTCHA', 500);
        }
    }
    
    /**
     * Validate CAPTCHA
     */
    public function validate(): void
    {
        $data = $this->validateRequest([
            'captcha' => 'required',
            'token' => 'required'
        ]);
        
        if (!$data) {
            $this->jsonError('Invalid request data', 400);
            return;
        }
        
        try {
            $isValid = $this->security->validateCaptcha($data['captcha'], $data['token']);
            
            if ($isValid) {
                $this->jsonSuccess('CAPTCHA validation successful');
            } else {
                $this->security->logSecurityEvent('captcha_validation_failed', [
                    'ip' => $this->getClientIp(),
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
                
                $this->jsonError('Invalid CAPTCHA', 400);
            }
        } catch (Exception $e) {
            $this->security->logSecurityEvent('captcha_validation_error', [
                'error' => $e->getMessage()
            ]);
            
            $this->jsonError('CAPTCHA validation failed', 500);
        }
    }
    
    /**
     * Refresh CAPTCHA (generate new one)
     */
    public function refresh(): void
    {
        $this->generate();
    }
    
    /**
     * Get CAPTCHA image directly
     */
    public function image(): void
    {
        try {
            $token = $_GET['token'] ?? '';
            
            if (empty($token)) {
                http_response_code(400);
                exit('Invalid token');
            }
            
            // Validate token exists in session
            if (!isset($_SESSION)) {
                session_start();
            }
            
            $captcha = $_SESSION['captcha'] ?? null;
            
            if (!$captcha || $captcha['token'] !== $token) {
                http_response_code(404);
                exit('CAPTCHA not found');
            }
            
            if (time() > $captcha['expires']) {
                unset($_SESSION['captcha']);
                http_response_code(410);
                exit('CAPTCHA expired');
            }
            
            // Generate new image for the same code
            $image = $this->createCaptchaImage($captcha['code']);
            
            // Set headers for image
            header('Content-Type: image/png');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Output image
            echo base64_decode(str_replace('data:image/png;base64,', '', $image));
            
        } catch (Exception $e) {
            $this->security->logSecurityEvent('captcha_image_error', [
                'error' => $e->getMessage()
            ]);
            
            http_response_code(500);
            exit('Failed to generate CAPTCHA image');
        }
    }
    
    /**
     * Create CAPTCHA image
     */
    private function createCaptchaImage(string $code): string
    {
        $width = 120;
        $height = 40;
        
        $image = imagecreate($width, $height);
        
        // Colors
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $lineColor = imagecolorallocate($image, 200, 200, 200);
        $noiseColor = imagecolorallocate($image, 150, 150, 150);
        
        // Add background noise
        for ($i = 0; $i < 100; $i++) {
            imagesetpixel($image, random_int(0, $width), random_int(0, $height), $noiseColor);
        }
        
        // Add noise lines
        for ($i = 0; $i < 5; $i++) {
            imageline($image, 0, random_int(0, $height), $width, random_int(0, $height), $lineColor);
        }
        
        // Add text with slight rotation and positioning variation
        $fontSize = 5;
        $codeLength = strlen($code);
        $charWidth = $width / $codeLength;
        
        for ($i = 0; $i < $codeLength; $i++) {
            $char = $code[$i];
            $x = $i * $charWidth + random_int(-5, 5);
            $y = random_int(5, 15);
            
            imagestring($image, $fontSize, (int)$x, (int)$y, $char, $textColor);
        }
        
        // Convert to base64
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                return trim($ips[0]);
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
