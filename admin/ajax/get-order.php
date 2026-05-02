<?php
/**
 * AJAX: Get Order Details
 * LUMIÈRE - Luxury Makeup Brand
 */

header('Content-Type: application/json');

session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';

// Require admin
if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'Invalid order']);
    exit;
}

try {
    // Get order
    $stmt = $pdo->prepare("
        SELECT o.*, u.name as customer_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'order' => $order,
        'items' => $items
    ]);
    
} catch (PDOException $e) {
    error_log("Get order error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading order']);
}
