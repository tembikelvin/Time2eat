<?php
/**
 * WebSocket Server for Real-time Tracking
 * 
 * This script starts the WebSocket server for real-time order tracking.
 * Run this script in the background to enable real-time features.
 * 
 * Usage:
 * php scripts/websocket-server.php [port]
 * 
 * Default port: 8080
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use Time2Eat\WebSocket\TrackingServer;

// Get port from command line argument or use default
$port = isset($argv[1]) ? (int)$argv[1] : 8080;

// Validate port
if ($port < 1024 || $port > 65535) {
    echo "Error: Port must be between 1024 and 65535\n";
    exit(1);
}

// Check if port is already in use
$socket = @fsockopen('localhost', $port, $errno, $errstr, 1);
if ($socket) {
    fclose($socket);
    echo "Error: Port {$port} is already in use\n";
    exit(1);
}

// Display startup information
echo "===========================================\n";
echo "Time2Eat WebSocket Server\n";
echo "===========================================\n";
echo "Starting WebSocket server on port {$port}...\n";
echo "Server will handle real-time tracking updates\n";
echo "Press Ctrl+C to stop the server\n";
echo "===========================================\n\n";

// Set up signal handlers for graceful shutdown
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGTERM, function() {
        echo "\nReceived SIGTERM, shutting down gracefully...\n";
        exit(0);
    });
    
    pcntl_signal(SIGINT, function() {
        echo "\nReceived SIGINT, shutting down gracefully...\n";
        exit(0);
    });
}

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set memory limit for long-running process
ini_set('memory_limit', '256M');

// Disable time limit
set_time_limit(0);

try {
    // Start the WebSocket server
    TrackingServer::start($port);
} catch (Exception $e) {
    echo "Error starting WebSocket server: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
