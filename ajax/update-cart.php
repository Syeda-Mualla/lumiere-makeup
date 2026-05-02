<?php
/**
 * AJAX: Update Cart Quantity
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Input validation, session-based cart
 */

header('Content-Type: application/json');

session_start();
require_once '../includes/db.php';

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Get product info
$stmt = $pdo->prepare("SELECT id, price, stock FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Initialize cart if needed
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle quantity
if ($quantity <= 0) {
    // Remove from cart
    unset($_SESSION['cart'][$productId]);
} else {
    // Ensure quantity doesn't exceed stock
    $quantity = min($quantity, $product['stock']);
    $_SESSION['cart'][$productId] = $quantity;
}

// Calculate totals
$subtotal = 0;
$cartCount = 0;

if (!empty($_SESSION['cart'])) {
    $productIds = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
    
    $stmt = $pdo->prepare("SELECT id, price FROM products WHERE id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($products as $p) {
        $qty = $_SESSION['cart'][$p['id']];
        $subtotal += $p['price'] * $qty;
        $cartCount += $qty;
    }
}

$shipping = $subtotal > 0 ? 10.00 : 0;
$total = $subtotal + $shipping;
$lineTotal = isset($_SESSION['cart'][$productId]) ? $product['price'] * $_SESSION['cart'][$productId] : 0;

echo json_encode([
    'success' => true,
    'cartCount' => $cartCount,
    'subtotal' => $subtotal,
    'shipping' => $shipping,
    'total' => $total,
    'lineTotal' => $lineTotal
]);
