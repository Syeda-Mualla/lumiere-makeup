<?php
/**
 * AJAX: Newsletter Subscription
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Email validation, prepared statements
 */

header('Content-Type: application/json');

require_once '../includes/db.php';

$email = trim($_POST['email'] ?? '');

// Validate email
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please enter a valid email address'
    ]);
    exit;
}

try {
    // Check if already subscribed
    $stmt = $pdo->prepare("SELECT id FROM newsletter WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'This email is already subscribed'
        ]);
        exit;
    }
    
    // Insert new subscriber
    $stmt = $pdo->prepare("INSERT INTO newsletter (email) VALUES (?)");
    $stmt->execute([$email]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for subscribing!'
    ]);
    
} catch (PDOException $e) {
    error_log("Newsletter error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
