<?php
/**
 * Database Connection using PDO
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Uses PDO with prepared statements to prevent SQL injection
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === 'db.php') {
    http_response_code(403);
    exit('Direct access not allowed');
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'lumiere');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Create PDO connection
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Log error (in production, don't show detailed error)
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

/**
 * Helper function to sanitize output
 * Prevents XSS attacks when displaying user-generated content
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get base URL
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
}
