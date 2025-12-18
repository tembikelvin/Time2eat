<?php

// Toggle the email_verification_required setting

ini_set('display_errors', '1');
error_reporting(E_ALL);

$projectRoot = dirname(__DIR__, 2);

require_once $projectRoot . '/src/models/SiteSetting.php';

use Time2Eat\Models\SiteSetting;

header('Content-Type: application/json');

try {
    if (!isset($_GET['enabled'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing enabled query param (0 or 1)']);
        exit;
    }

    $enabled = $_GET['enabled'] === '1' ? true : false;

    $model = new SiteSetting();

    // Use updateSetting if exists, otherwise set with type boolean
    if ($model->exists('email_verification_required')) {
        $ok = $model->updateSetting('email_verification_required', $enabled);
    } else {
        $ok = $model->set('email_verification_required', $enabled, 'boolean', 'system', 'Email verification required');
    }

    if (!$ok) {
        throw new Exception('Failed to persist setting');
    }

    echo json_encode([
        'success' => true,
        'email_verification_required' => $enabled,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}



