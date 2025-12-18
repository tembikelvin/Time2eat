<?php
/**
 * Time2Eat Image Optimizer
 * Advanced image compression and optimization service
 */

declare(strict_types=1);

class ImageOptimizer
{
    private array $config;
    private string $imageDir;
    private string $cacheDir;
    private array $supportedFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    public function __construct()
    {
        $this->imageDir = ROOT_PATH . '/public/images';
        $this->cacheDir = ROOT_PATH . '/storage/cache/images';
        $this->config = [
            'quality' => [
                'jpeg' => 85,
                'webp' => 80,
                'png' => 6,
                'gif' => 256
            ],
            'sizes' => [
                'thumbnail' => [150, 150],
                'small' => [300, 300],
                'medium' => [600, 600],
                'large' => [1200, 1200],
                'xlarge' => [1920, 1920]
            ],
            'progressive' => true,
            'strip_metadata' => true,
            'auto_orient' => true
        ];
        
        $this->ensureDirectories();
    }
    
    /**
     * Optimize uploaded image
     */
    public function optimizeUpload(string $sourcePath, string $targetPath, array $options = []): array
    {
        $result = [
            'success' => false,
            'original_size' => 0,
            'optimized_size' => 0,
            'compression_ratio' => 0,
            'formats_created' => [],
            'sizes_created' => [],
            'error' => null
        ];
        
        try {
            if (!file_exists($sourcePath)) {
                throw new Exception('Source image not found');
            }
            
            $result['original_size'] = filesize($sourcePath);
            
            // Get image info
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                throw new Exception('Invalid image format');
            }
            
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            $mimeType = $imageInfo['mime'];
            
            // Create optimized versions
            $formats = $options['formats'] ?? ['original', 'webp'];
            $sizes = $options['sizes'] ?? ['original', 'medium', 'small', 'thumbnail'];
            
            foreach ($formats as $format) {
                foreach ($sizes as $size) {
                    $optimizedPath = $this->generateOptimizedPath($targetPath, $format, $size);
                    
                    if ($this->createOptimizedVersion($sourcePath, $optimizedPath, $format, $size, $originalWidth, $originalHeight)) {
                        $result['formats_created'][] = $format;
                        $result['sizes_created'][] = $size;
                    }
                }
            }
            
            // Calculate final size (using the main optimized image)
            $mainOptimizedPath = $this->generateOptimizedPath($targetPath, 'original', 'original');
            if (file_exists($mainOptimizedPath)) {
                $result['optimized_size'] = filesize($mainOptimizedPath);
                $result['compression_ratio'] = round((1 - ($result['optimized_size'] / $result['original_size'])) * 100, 2);
            }
            
            $result['success'] = true;
            
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Create optimized version of image
     */
    private function createOptimizedVersion(string $sourcePath, string $targetPath, string $format, string $size, int $originalWidth, int $originalHeight): bool
    {
        try {
            // Calculate target dimensions
            list($targetWidth, $targetHeight) = $this->calculateDimensions($originalWidth, $originalHeight, $size);
            
            // Create image resource from source
            $sourceImage = $this->createImageFromFile($sourcePath);
            if (!$sourceImage) {
                return false;
            }
            
            // Create target image
            $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
            
            // Preserve transparency for PNG and GIF
            if ($format === 'png' || $format === 'gif') {
                imagealphablending($targetImage, false);
                imagesavealpha($targetImage, true);
                $transparent = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
                imagefill($targetImage, 0, 0, $transparent);
            }
            
            // Resize image
            imagecopyresampled(
                $targetImage, $sourceImage,
                0, 0, 0, 0,
                $targetWidth, $targetHeight,
                $originalWidth, $originalHeight
            );
            
            // Apply auto-orientation if enabled
            if ($this->config['auto_orient']) {
                $targetImage = $this->autoOrient($targetImage, $sourcePath);
            }
            
            // Save optimized image
            $saved = $this->saveOptimizedImage($targetImage, $targetPath, $format);
            
            // Clean up
            imagedestroy($sourceImage);
            imagedestroy($targetImage);
            
            return $saved;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Create image resource from file
     */
    private function createImageFromFile(string $filePath)
    {
        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }
        
        switch ($imageInfo['mime']) {
            case 'image/jpeg':
                return imagecreatefromjpeg($filePath);
            case 'image/png':
                return imagecreatefrompng($filePath);
            case 'image/gif':
                return imagecreatefromgif($filePath);
            case 'image/webp':
                return function_exists('imagecreatefromwebp') ? imagecreatefromwebp($filePath) : false;
            default:
                return false;
        }
    }
    
    /**
     * Save optimized image
     */
    private function saveOptimizedImage($image, string $targetPath, string $format): bool
    {
        // Ensure target directory exists
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        switch ($format) {
            case 'jpeg':
            case 'jpg':
            case 'original':
                return imagejpeg($image, $targetPath, $this->config['quality']['jpeg']);
                
            case 'png':
                return imagepng($image, $targetPath, $this->config['quality']['png']);
                
            case 'gif':
                return imagegif($image, $targetPath);
                
            case 'webp':
                if (function_exists('imagewebp')) {
                    return imagewebp($image, $targetPath, $this->config['quality']['webp']);
                }
                return false;
                
            default:
                return false;
        }
    }
    
    /**
     * Calculate target dimensions based on size preset
     */
    private function calculateDimensions(int $originalWidth, int $originalHeight, string $size): array
    {
        if ($size === 'original') {
            return [$originalWidth, $originalHeight];
        }
        
        if (!isset($this->config['sizes'][$size])) {
            return [$originalWidth, $originalHeight];
        }
        
        list($maxWidth, $maxHeight) = $this->config['sizes'][$size];
        
        // Calculate aspect ratio
        $aspectRatio = $originalWidth / $originalHeight;
        
        if ($originalWidth > $originalHeight) {
            $targetWidth = min($maxWidth, $originalWidth);
            $targetHeight = round($targetWidth / $aspectRatio);
        } else {
            $targetHeight = min($maxHeight, $originalHeight);
            $targetWidth = round($targetHeight * $aspectRatio);
        }
        
        return [(int)$targetWidth, (int)$targetHeight];
    }
    
    /**
     * Generate optimized image path
     */
    private function generateOptimizedPath(string $originalPath, string $format, string $size): string
    {
        $pathInfo = pathinfo($originalPath);
        $directory = $pathInfo['dirname'];
        $filename = $pathInfo['filename'];
        
        // Create size suffix
        $sizeSuffix = $size === 'original' ? '' : "_{$size}";
        
        // Create format extension
        $extension = $format === 'original' ? $pathInfo['extension'] : $format;
        
        return "{$directory}/{$filename}{$sizeSuffix}.{$extension}";
    }
    
    /**
     * Auto-orient image based on EXIF data
     */
    private function autoOrient($image, string $filePath)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }
        
        $exif = @exif_read_data($filePath);
        if (!$exif || !isset($exif['Orientation'])) {
            return $image;
        }
        
        switch ($exif['Orientation']) {
            case 2:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                break;
            case 3:
                $image = imagerotate($image, 180, 0);
                break;
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                break;
            case 5:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $image = imagerotate($image, -90, 0);
                break;
            case 6:
                $image = imagerotate($image, -90, 0);
                break;
            case 7:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                $image = imagerotate($image, 90, 0);
                break;
            case 8:
                $image = imagerotate($image, 90, 0);
                break;
        }
        
        return $image;
    }
    
    /**
     * Get optimized image URL
     */
    public function getOptimizedUrl(string $originalPath, string $format = 'webp', string $size = 'medium'): string
    {
        $optimizedPath = $this->generateOptimizedPath($originalPath, $format, $size);
        
        // Check if optimized version exists
        if (file_exists($optimizedPath)) {
            return str_replace(ROOT_PATH . '/public', '', $optimizedPath);
        }
        
        // Fallback to original
        return str_replace(ROOT_PATH . '/public', '', $originalPath);
    }
    
    /**
     * Generate responsive image HTML
     */
    public function generateResponsiveImage(string $imagePath, string $alt = '', array $options = []): string
    {
        $pathInfo = pathinfo($imagePath);
        $baseUrl = str_replace(ROOT_PATH . '/public', '', $imagePath);
        
        // Generate srcset for different sizes
        $srcset = [];
        $sizes = $options['sizes'] ?? ['small', 'medium', 'large'];
        
        foreach ($sizes as $size) {
            $optimizedUrl = $this->getOptimizedUrl($imagePath, 'webp', $size);
            $dimensions = $this->config['sizes'][$size] ?? [600, 600];
            $srcset[] = "{$optimizedUrl} {$dimensions[0]}w";
        }
        
        // Generate fallback srcset for browsers without WebP support
        $fallbackSrcset = [];
        foreach ($sizes as $size) {
            $optimizedUrl = $this->getOptimizedUrl($imagePath, 'original', $size);
            $dimensions = $this->config['sizes'][$size] ?? [600, 600];
            $fallbackSrcset[] = "{$optimizedUrl} {$dimensions[0]}w";
        }
        
        $class = $options['class'] ?? 'tw-w-full tw-h-auto';
        $loading = $options['loading'] ?? 'lazy';
        $sizesAttr = $options['sizes_attr'] ?? '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw';
        
        return "
        <picture>
            <source 
                srcset=\"" . implode(', ', $srcset) . "\" 
                sizes=\"{$sizesAttr}\" 
                type=\"image/webp\">
            <img 
                src=\"{$baseUrl}\" 
                srcset=\"" . implode(', ', $fallbackSrcset) . "\" 
                sizes=\"{$sizesAttr}\" 
                alt=\"{$alt}\" 
                class=\"{$class}\" 
                loading=\"{$loading}\">
        </picture>";
    }
    
    /**
     * Batch optimize existing images
     */
    public function batchOptimize(string $directory = null): array
    {
        $directory = $directory ?? $this->imageDir;
        $results = [
            'processed' => 0,
            'optimized' => 0,
            'errors' => 0,
            'space_saved' => 0
        ];
        
        $images = $this->findImages($directory);
        
        foreach ($images as $imagePath) {
            try {
                $originalSize = filesize($imagePath);
                $optimizeResult = $this->optimizeUpload($imagePath, $imagePath);
                
                if ($optimizeResult['success']) {
                    $results['optimized']++;
                    $results['space_saved'] += ($originalSize - $optimizeResult['optimized_size']);
                } else {
                    $results['errors']++;
                }
                
                $results['processed']++;
                
            } catch (Exception $e) {
                $results['errors']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Find all images in directory
     */
    private function findImages(string $directory): array
    {
        $images = [];
        $extensions = implode(',', $this->supportedFormats);
        
        // Find images recursively
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower($file->getExtension());
                if (in_array($extension, $this->supportedFormats)) {
                    $images[] = $file->getPathname();
                }
            }
        }
        
        return $images;
    }
    
    /**
     * Clean up old optimized images
     */
    public function cleanupOptimizedImages(int $maxAge = 2592000): int // 30 days
    {
        $cleaned = 0;
        $cutoffTime = time() - $maxAge;
        
        $optimizedImages = $this->findImages($this->cacheDir);
        
        foreach ($optimizedImages as $imagePath) {
            if (filemtime($imagePath) < $cutoffTime) {
                unlink($imagePath);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Get image optimization statistics
     */
    public function getOptimizationStats(): array
    {
        $originalImages = $this->findImages($this->imageDir);
        $optimizedImages = $this->findImages($this->cacheDir);
        
        $originalSize = 0;
        $optimizedSize = 0;
        
        foreach ($originalImages as $image) {
            $originalSize += filesize($image);
        }
        
        foreach ($optimizedImages as $image) {
            $optimizedSize += filesize($image);
        }
        
        return [
            'original_count' => count($originalImages),
            'optimized_count' => count($optimizedImages),
            'original_size' => $originalSize,
            'optimized_size' => $optimizedSize,
            'space_saved' => $originalSize - $optimizedSize,
            'compression_ratio' => $originalSize > 0 ? round((1 - ($optimizedSize / $originalSize)) * 100, 2) : 0
        ];
    }
    
    /**
     * Ensure required directories exist
     */
    private function ensureDirectories(): void
    {
        $directories = [
            $this->imageDir,
            $this->cacheDir,
            $this->imageDir . '/uploads',
            $this->imageDir . '/restaurants',
            $this->imageDir . '/menu',
            $this->imageDir . '/users'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
}
