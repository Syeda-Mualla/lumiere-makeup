<?php
/**
 * Order Confirmation Page
 * LUMIÈRE - Luxury Makeup Brand
 */

$pageTitle = 'Order Confirmed';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Get order ID
$orderId = filter_input(INPUT_GET, 'order', FILTER_VALIDATE_INT);

if (!$orderId) {
    header('Location: index.php');
    exit;
}

// Verify order belongs to user
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$orderId, $_SESSION['user_id'] ?? 0]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: index.php');
    exit;
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Order Confirmed</h1>
</div>

<section class="confirmation-section">
    <div class="confirmation-icon">
        <i class="fas fa-check"></i>
    </div>
    
    <h1>Thank You for Your Order!</h1>
    <p>Your order has been placed successfully.</p>
    
    <p class="order-number">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
    
    <p>We've sent a confirmation email to <strong><?php echo h($_SESSION['user_email']); ?></strong></p>
    
    <p style="color: var(--color-gray); margin-top: 20px;">
        Estimated delivery: 5-7 business days
    </p>
    
    <div style="margin-top: 30px;">
        <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
