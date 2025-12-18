<?php

// Render the register view to probe whether verify UI is visible based on setting

ini_set('display_errors', '1');
error_reporting(E_ALL);

$projectRoot = dirname(__DIR__, 2);

// Minimal stubs for functions used in the view
if (!function_exists('url')) {
    function url($path = '/') { return $path; }
}
if (!function_exists('csrf_field')) {
    function csrf_field() { return '<input type="hidden" name="csrf_token" value="test">'; }
}
if (!function_exists('e')) {
    function e($str) { return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8'); }
}

// Include helpers to compute $emailVerificationRequired inside the view
require_once $projectRoot . '/src/helpers/auth_helpers.php';

ob_start();
require $projectRoot . '/src/views/auth/register.php';
$html = ob_get_clean();

// Probe for the verify elements
$hasVerifyBtn = strpos($html, 'id="verifyEmailBtn"') !== false;
$hasVerifySection = strpos($html, 'id="emailVerificationSection"') !== false;

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'has_verify_button' => $hasVerifyBtn,
    'has_verify_section' => $hasVerifySection,
]);



