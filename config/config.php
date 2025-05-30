<?php
// Application configuration
define('APP_NAME', 'File Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost');

// File upload settings
define('UPLOAD_DIR', __DIR__ . '/../uploads/documents/');
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar']);

// Session settings
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Auto-include database
require_once __DIR__ . '/database.php';
?>