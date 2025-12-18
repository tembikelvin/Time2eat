<?php
/**
 * Webhook Endpoint Test Script
 * 
 * This script tests if the Tranzak webhook endpoint is accessible
 * Run from command line or access via browser
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTML output for browser
$isCli = php_sapi_name() === 'cli';
if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Webhook Endpoint Test</title>';
    echo '<style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
        h2 { color: #34495e; margin-top: 30px; }
        .test-section { background: #ecf0f1; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 4px solid #3498db; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .info { color: #3498db; }
        .warning { color: #f39c12; }
        pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .config { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .config-item { margin: 5px 0; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
    </style></head><body><div class="container">';
}

function output($message, $type = 'info') {
    global $isCli;
    $prefix = '';
    $suffix = '';
    
    if (!$isCli) {
        $class = $type;
        $prefix = "<span class='$class'>";
        $suffix = '</span>';
    } else {
        switch ($type) {
            case 'success': $prefix = "\033[32m✓ "; $suffix = "\033[0m"; break;
            case 'error': $prefix = "\033[31m✗ "; $suffix = "\033[0m"; break;
            case 'warning': $prefix = "\033[33m⚠ "; $suffix = "\033[0m"; break;
            case 'info': $prefix = "\033[36mℹ "; $suffix = "\033[0m"; break;
        }
    }
    
    echo $prefix . $message . $suffix . ($isCli ? "\n" : "<br>");
}

function outputSection($title) {
    global $isCli;
    if (!$isCli) {
        echo "<div class='test-section'><h2>$title</h2>";
    } else {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "$title\n";
        echo str_repeat("=", 60) . "\n";
    }
}

function outputEndSection() {
    global $isCli;
    if (!$isCli) {
        echo "</div>";
    }
}

function outputPre($data) {
    global $isCli;
    if (!$isCli) {
        echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";
    } else {
        print_r($data);
    }
}

// Start test
outputSection("Tranzak Webhook Endpoint Test");
output("Testing webhook endpoint accessibility...", 'info');

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Get webhook URLs
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/eat';
$webhookUrl = rtrim($appUrl, '/') . '/api/payment/tranzak/notify';
$webhookAuthKey = $_ENV['TRANZAK_WEBHOOK_AUTH_KEY'] ?? '';

// Display configuration
outputSection("Configuration");
if (!$isCli) {
    echo "<div class='config'>";
    echo "<div class='config-item'><strong>App URL:</strong> " . htmlspecialchars($appUrl) . "</div>";
    echo "<div class='config-item'><strong>Webhook URL:</strong> <code>" . htmlspecialchars($webhookUrl) . "</code></div>";
    echo "<div class='config-item'><strong>Webhook Auth Key:</strong> " . htmlspecialchars(substr($webhookAuthKey, 0, 10) . '...' . substr($webhookAuthKey, -5)) . "</div>";
    echo "</div>";
} else {
    output("App URL: $appUrl", 'info');
    output("Webhook URL: $webhookUrl", 'info');
    output("Webhook Auth Key: " . substr($webhookAuthKey, 0, 10) . '...' . substr($webhookAuthKey, -5), 'info');
}

if (empty($webhookAuthKey)) {
    output("WARNING: TRANZAK_WEBHOOK_AUTH_KEY is not set in .env file", 'warning');
}
outputEndSection();

// Test 1: Check if endpoint file exists
outputSection("Test 1: Endpoint File Existence");
$endpointFiles = [
    __DIR__ . '/src/controllers/PaymentController.php',
    __DIR__ . '/api/payment-tranzak.php',
    __DIR__ . '/routes/web.php'
];

$allFilesExist = true;
foreach ($endpointFiles as $file) {
    if (file_exists($file)) {
        output("✓ File exists: " . basename($file), 'success');
    } else {
        output("✗ File missing: " . basename($file), 'error');
        $allFilesExist = false;
    }
}

if ($allFilesExist) {
    output("All required files are present", 'success');
} else {
    output("Some required files are missing", 'error');
}
outputEndSection();

// Test 2: Test webhook endpoint with local request
outputSection("Test 2: Local Webhook Endpoint Test");
output("Sending test webhook notification to local endpoint...", 'info');

// Create a test TPN (Tranzak Payment Notification) payload
$testTpn = [
    'name' => 'Tranzak Payment Notification (TPN)',
    'version' => '1.0',
    'eventType' => 'REQUEST.COMPLETED',
    'appId' => $_ENV['TRANZAK_APP_ID'] ?? 'test_app_id',
    'resourceId' => 'TEST-' . time(),
    'resource' => [
        'serviceId' => 'TEST-SERVICE-' . time(),
        'transactionId' => 'TEST-TX-' . time(),
        'amount' => 1000,
        'currencyCode' => 'XAF',
        'status' => 'SUCCESSFUL',
        'mchTransactionRef' => 'TEST-REF-' . time()
    ],
    'webhookId' => 'TEST-WH-' . time(),
    'creationDateTime' => date('Y-m-d H:i:s'),
    'authKey' => $webhookAuthKey
];

output("Test TPN Payload:", 'info');
outputPre($testTpn);

// Test the endpoint using cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testTpn));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json, */*',
    'User-Agent: Time2Eat-Webhook-Test/1.0'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
$curlInfo = curl_getinfo($ch);
curl_close($ch);

if ($curlError) {
    output("cURL Error: $curlError", 'error');
    output("This might mean the endpoint is not accessible from this location", 'warning');
    output("For production, the endpoint must be publicly accessible via HTTPS", 'warning');
} else {
    output("HTTP Status Code: $httpCode", 'info');
    output("Response: $response", 'info');
    
    if ($httpCode === 200 || $httpCode === 400) {
        // 200 = success, 400 = might be validation error but endpoint is accessible
        output("✓ Webhook endpoint is accessible and responding", 'success');
        
        $responseData = json_decode($response, true);
        if ($responseData) {
            output("Response Data:", 'info');
            outputPre($responseData);
        }
    } elseif ($httpCode === 404) {
        output("✗ Webhook endpoint returned 404 (Not Found)", 'error');
        output("Check your routing configuration", 'warning');
    } elseif ($httpCode === 405) {
        output("✗ Webhook endpoint returned 405 (Method Not Allowed)", 'error');
        output("The endpoint might not accept POST requests", 'warning');
    } elseif ($httpCode === 500) {
        output("⚠ Webhook endpoint returned 500 (Internal Server Error)", 'warning');
        output("The endpoint is accessible but has an error. Check server logs.", 'warning');
    } else {
        output("⚠ Unexpected HTTP status code: $httpCode", 'warning');
    }
}

output("Effective URL: " . ($curlInfo['url'] ?? $webhookUrl), 'info');
outputEndSection();

// Test 3: Check if endpoint is publicly accessible (for production)
outputSection("Test 3: Public Accessibility Check");
if (strpos($webhookUrl, 'https://') === 0 || strpos($webhookUrl, 'http://') === 0) {
    $host = parse_url($webhookUrl, PHP_URL_HOST);
    if ($host && $host !== 'localhost' && $host !== '127.0.0.1') {
        output("Testing public accessibility for: $host", 'info');
        
        // Simple connectivity test
        $pingResult = @gethostbyname($host);
        if ($pingResult === $host) {
            output("⚠ Could not resolve hostname: $host", 'warning');
            output("DNS resolution failed. Check your domain configuration.", 'warning');
        } else {
            output("✓ Hostname resolves to: $pingResult", 'success');
        }
        
        // Check if HTTPS is used for production
        if (strpos($webhookUrl, 'https://') === 0) {
            output("✓ Using HTTPS (secure connection)", 'success');
        } else {
            output("⚠ Using HTTP (not secure). Production webhooks should use HTTPS.", 'warning');
        }
    } else {
        output("Local endpoint detected. For production, use your public domain.", 'info');
    }
} else {
    output("Relative URL detected. Make sure your web server is configured correctly.", 'info');
}
outputEndSection();

// Test 4: Verify webhook handler code
outputSection("Test 4: Webhook Handler Verification");
$handlerFile = __DIR__ . '/src/controllers/PaymentController.php';
if (file_exists($handlerFile)) {
    $handlerContent = file_get_contents($handlerFile);
    
    $checks = [
        'webhook method exists' => strpos($handlerContent, 'function webhook') !== false || strpos($handlerContent, 'public function webhook') !== false,
        'TPN handling' => strpos($handlerContent, 'handlePaymentNotification') !== false || strpos($handlerContent, 'Tranzak Payment Notification') !== false,
        'JSON parsing' => strpos($handlerContent, 'json_decode') !== false,
        'authKey verification' => strpos($handlerContent, 'authKey') !== false || strpos($handlerContent, 'verifyNotificationSignature') !== false
    ];
    
    foreach ($checks as $check => $result) {
        if ($result) {
            output("✓ $check", 'success');
        } else {
            output("✗ $check", 'error');
        }
    }
} else {
    output("✗ PaymentController.php not found", 'error');
}
outputEndSection();

// Summary
outputSection("Test Summary");
output("Webhook URL: $webhookUrl", 'info');
output("", 'info');

if ($httpCode === 200 || $httpCode === 400) {
    output("✓ Webhook endpoint is accessible and functional", 'success');
    output("You can configure this URL in the Tranzak Developer Portal", 'info');
} else {
    output("⚠ Webhook endpoint may not be fully accessible", 'warning');
    output("Check the errors above and verify:", 'info');
    output("1. Your web server is running", 'info');
    output("2. The route is properly configured", 'info');
    output("3. For production: The endpoint is publicly accessible via HTTPS", 'info');
}

output("", 'info');
output("To configure in Tranzak Developer Portal:", 'info');
output("1. Log in to https://developer.tranzak.me", 'info');
output("2. Go to your app settings", 'info');
output("3. Add webhook URL: $webhookUrl", 'info');
output("4. Set authKey: " . substr($webhookAuthKey, 0, 10) . '...' . substr($webhookAuthKey, -5), 'info');
output("5. Subscribe to event: REQUEST.COMPLETED", 'info');

if (!$isCli) {
    echo "</div></body></html>";
}

