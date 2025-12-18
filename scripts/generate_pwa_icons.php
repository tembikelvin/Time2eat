<?php
/**
 * Generate PWA Icons from Base Image
 * 
 * This script generates all required PWA icon sizes from a base image
 * 
 * USAGE: php scripts/generate_pwa_icons.php
 */

// Configuration
$baseImagePath = __DIR__ . '/../public/favicon.png';
$outputDir = __DIR__ . '/../public/images/icons/';
$iconSizes = [72, 96, 128, 144, 152, 180, 192, 384, 512];
$backgroundColor = [255, 255, 255]; // White background for maskable icons

// Icon colors for Time2Eat brand
$brandColor = [234, 88, 12]; // #ea580c (orange)

echo "🎨 Time2Eat PWA Icon Generator\n";
echo "================================\n\n";

// Check if GD extension is available
if (!extension_loaded('gd')) {
    die("❌ Error: GD extension is not installed. Please install php-gd.\n");
}

// Check if base image exists
if (!file_exists($baseImagePath)) {
    echo "⚠️  Base image not found at: {$baseImagePath}\n";
    echo "📝 Creating a simple branded icon...\n\n";
    
    // Create a simple branded icon
    $baseImage = createBrandedIcon(512, 512, $brandColor);
    imagepng($baseImage, $baseImagePath);
    imagedestroy($baseImage);
    
    echo "✅ Created base icon at: {$baseImagePath}\n\n";
}

// Create output directory if it doesn't exist
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "✅ Created output directory: {$outputDir}\n\n";
}

// Load base image
$baseImage = @imagecreatefrompng($baseImagePath);

if (!$baseImage) {
    // Try JPEG
    $baseImage = @imagecreatefromjpeg($baseImagePath);
}

if (!$baseImage) {
    die("❌ Error: Could not load base image from {$baseImagePath}\n");
}

$baseWidth = imagesx($baseImage);
$baseHeight = imagesy($baseImage);

echo "📐 Base image size: {$baseWidth}x{$baseHeight}\n\n";
echo "🔄 Generating icons...\n\n";

// Generate each icon size
foreach ($iconSizes as $size) {
    $outputPath = $outputDir . "icon-{$size}x{$size}.png";
    
    // Create new image with transparency
    $newImage = imagecreatetruecolor($size, $size);
    
    // Enable alpha blending
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    
    // Fill with transparent background
    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
    imagefill($newImage, 0, 0, $transparent);
    
    // Enable alpha blending for resampling
    imagealphablending($newImage, true);
    
    // Resample base image to new size
    imagecopyresampled(
        $newImage, $baseImage,
        0, 0, 0, 0,
        $size, $size,
        $baseWidth, $baseHeight
    );
    
    // Save icon
    imagepng($newImage, $outputPath, 9);
    imagedestroy($newImage);
    
    echo "  ✅ Generated: icon-{$size}x{$size}.png\n";
}

// Generate badge icon (72x72 for notification badge)
$badgeSize = 72;
$badgePath = $outputDir . "badge-{$badgeSize}x{$badgeSize}.png";
$badgeImage = imagecreatetruecolor($badgeSize, $badgeSize);

// Make badge with brand color background
$bgColor = imagecolorallocate($badgeImage, $brandColor[0], $brandColor[1], $brandColor[2]);
imagefill($badgeImage, 0, 0, $bgColor);

// Add white "T2E" text
$white = imagecolorallocate($badgeImage, 255, 255, 255);
$fontSize = 20;
$text = "T2E";
$bbox = imagettfbbox($fontSize, 0, __DIR__ . '/../public/fonts/Inter-Bold.ttf', $text);

if ($bbox) {
    $textWidth = abs($bbox[4] - $bbox[0]);
    $textHeight = abs($bbox[5] - $bbox[1]);
    $x = ($badgeSize - $textWidth) / 2;
    $y = ($badgeSize + $textHeight) / 2;
    imagettftext($badgeImage, $fontSize, 0, $x, $y, $white, __DIR__ . '/../public/fonts/Inter-Bold.ttf', $text);
} else {
    // Fallback to built-in font if TTF not available
    imagestring($badgeImage, 5, 15, 28, "T2E", $white);
}

imagepng($badgeImage, $badgePath, 9);
imagedestroy($badgeImage);

echo "\n  ✅ Generated: badge-{$badgeSize}x{$badgeSize}.png\n";

// Generate action icons for notifications
$actionIcons = [
    'view' => '👁',
    'close' => '✕',
    'order' => '🍔',
    'message' => '💬',
    'track' => '📍'
];

echo "\n🔔 Generating notification action icons...\n\n";

foreach ($actionIcons as $action => $emoji) {
    $actionPath = $outputDir . "action-{$action}.png";
    $actionImage = imagecreatetruecolor(96, 96);
    
    // Transparent background
    imagealphablending($actionImage, false);
    imagesavealpha($actionImage, true);
    $transparent = imagecolorallocatealpha($actionImage, 0, 0, 0, 127);
    imagefill($actionImage, 0, 0, $transparent);
    imagealphablending($actionImage, true);
    
    // Add colored circle background
    $circleColor = imagecolorallocate($actionImage, $brandColor[0], $brandColor[1], $brandColor[2]);
    imagefilledellipse($actionImage, 48, 48, 80, 80, $circleColor);
    
    // Add white text/emoji
    $white = imagecolorallocate($actionImage, 255, 255, 255);
    imagestring($actionImage, 5, 38, 40, $emoji, $white);
    
    imagepng($actionImage, $actionPath, 9);
    imagedestroy($actionImage);
    
    echo "  ✅ Generated: action-{$action}.png\n";
}

// Generate notification type icons
$notificationTypes = [
    'order' => ['🍔', 'Order Update'],
    'delivery' => ['🚚', 'Delivery'],
    'message' => ['💬', 'Message']
];

echo "\n📬 Generating notification type icons...\n\n";

foreach ($notificationTypes as $type => $data) {
    list($emoji, $label) = $data;
    
    // Icon
    $iconPath = $outputDir . "{$type}-icon.png";
    $icon = imagecreatetruecolor(192, 192);
    imagealphablending($icon, false);
    imagesavealpha($icon, true);
    $transparent = imagecolorallocatealpha($icon, 0, 0, 0, 127);
    imagefill($icon, 0, 0, $transparent);
    imagealphablending($icon, true);
    
    $circleColor = imagecolorallocate($icon, $brandColor[0], $brandColor[1], $brandColor[2]);
    imagefilledellipse($icon, 96, 96, 160, 160, $circleColor);
    
    $white = imagecolorallocate($icon, 255, 255, 255);
    imagestring($icon, 5, 86, 88, $emoji, $white);
    
    imagepng($icon, $iconPath, 9);
    imagedestroy($icon);
    
    // Badge
    $badgePath = $outputDir . "{$type}-badge.png";
    $badge = imagecreatetruecolor(72, 72);
    $bgColor = imagecolorallocate($badge, $brandColor[0], $brandColor[1], $brandColor[2]);
    imagefill($badge, 0, 0, $bgColor);
    
    $white = imagecolorallocate($badge, 255, 255, 255);
    imagestring($badge, 5, 26, 28, $emoji, $white);
    
    imagepng($badge, $badgePath, 9);
    imagedestroy($badge);
    
    echo "  ✅ Generated: {$type}-icon.png and {$type}-badge.png\n";
}

// Clean up
imagedestroy($baseImage);

echo "\n✅ All icons generated successfully!\n";
echo "\n📁 Icons saved to: {$outputDir}\n";
echo "\n📋 Generated files:\n";
echo "   - " . count($iconSizes) . " app icons (72x72 to 512x512)\n";
echo "   - 1 notification badge (72x72)\n";
echo "   - " . count($actionIcons) . " action icons\n";
echo "   - " . (count($notificationTypes) * 2) . " notification type icons\n";
echo "\n🎉 PWA icons are ready!\n\n";

/**
 * Create a simple branded icon
 */
function createBrandedIcon($width, $height, $brandColor) {
    $image = imagecreatetruecolor($width, $height);
    
    // Enable alpha blending
    imagealphablending($image, false);
    imagesavealpha($image, true);
    
    // Create gradient background
    $bgColor = imagecolorallocate($image, $brandColor[0], $brandColor[1], $brandColor[2]);
    imagefill($image, 0, 0, $bgColor);
    
    // Add white circle in center
    $white = imagecolorallocate($image, 255, 255, 255);
    $centerX = $width / 2;
    $centerY = $height / 2;
    $radius = min($width, $height) * 0.35;
    
    imagefilledellipse($image, $centerX, $centerY, $radius * 2, $radius * 2, $white);
    
    // Add "T2E" text
    $fontSize = $width * 0.15;
    $text = "T2E";
    
    // Use built-in font for simplicity
    $textColor = imagecolorallocate($image, $brandColor[0], $brandColor[1], $brandColor[2]);
    imagestring($image, 5, $centerX - 30, $centerY - 10, $text, $textColor);
    
    return $image;
}

