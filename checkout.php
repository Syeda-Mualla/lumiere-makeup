<?php
/**
 * Checkout Page
 * LUMIÈRE - Luxury Makeup Brand
 */

$pageTitle = 'Checkout';
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireLogin();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$cartItems  = [];
$subtotal   = 0;
$productIds = array_keys($_SESSION['cart']);
$placeholders = str_repeat('?,', count($productIds) - 1) . '?';

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($productIds);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $product) {
    $quantity  = $_SESSION['cart'][$product['id']];
    $lineTotal = $product['price'] * $quantity;
    $subtotal += $lineTotal;

    $cartItems[] = [
        'product'   => $product,
        'quantity'  => $quantity,
        'lineTotal' => $lineTotal
    ];
}

$shipping = ($subtotal < 5000) ? 350 : 0;
$total    = $subtotal + $shipping;

$errors = [];
$user   = getCurrentUser($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        $shippingName    = trim($_POST['shipping_name']    ?? '');
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $shippingCity    = trim($_POST['shipping_city']    ?? '');
        $shippingZip     = trim($_POST['shipping_zip']     ?? '');
        $shippingPhone   = trim($_POST['shipping_phone']   ?? '');

        if (empty($shippingName))    $errors['shipping_name']    = 'Full name is required';
        if (empty($shippingAddress)) $errors['shipping_address'] = 'Address is required';
        if (empty($shippingCity))    $errors['shipping_city']    = 'City is required';
        if (empty($shippingZip))     $errors['shipping_zip']     = 'Postal code is required';
        if (empty($shippingPhone))   $errors['shipping_phone']   = 'Phone number is required';

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_price, shipping_name, shipping_address, shipping_city, shipping_zip)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $total,
                    $shippingName,
                    $shippingAddress,
                    $shippingCity,
                    $shippingZip
                ]);

                $orderId = $pdo->lastInsertId();

                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

                foreach ($cartItems as $item) {
                    $stmt->execute([
                        $orderId,
                        $item['product']['id'],
                        $item['quantity'],
                        $item['product']['price']
                    ]);

                    $updateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $updateStock->execute([$item['quantity'], $item['product']['id']]);
                }

                $pdo->commit();
                $_SESSION['cart'] = [];

                setFlashMessage('success', 'Order placed successfully!');
                header('Location: confirmation.php?order=' . $orderId);
                exit;

            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Checkout error: " . $e->getMessage());
                $errors['general'] = 'An error occurred. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Checkout</h1>
    <p>Complete your order securely</p>
</div>

<section class="checkout-section">
    <div class="checkout-form">

        <?php if (isset($errors['general'])): ?>
        <div class="flash-message flash-error" style="position:static; transform:none; margin-bottom:20px;">
            <?php echo h($errors['general']); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="" id="checkoutForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <h3>Shipping Information</h3>

            <div class="form-group">
                <label for="shipping_name">Full Name</label>
                <input type="text" id="shipping_name" name="shipping_name"
                       value="<?php echo h($_POST['shipping_name'] ?? $user['name']); ?>"
                       placeholder="Your full name" required>
                <?php if (isset($errors['shipping_name'])): ?>
                <span class="form-error"><?php echo h($errors['shipping_name']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" value="<?php echo h($user['email']); ?>" disabled
                           style="background:var(--blush); color:var(--muted);">
                </div>
                <div class="form-group">
                    <label for="shipping_phone">Phone Number</label>
                    <input type="text" id="shipping_phone" name="shipping_phone"
                           value="<?php echo h($_POST['shipping_phone'] ?? ''); ?>"
                           placeholder="03XX-XXXXXXX" required>
                    <?php if (isset($errors['shipping_phone'])): ?>
                    <span class="form-error"><?php echo h($errors['shipping_phone']); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="shipping_address">Street Address</label>
                <input type="text" id="shipping_address" name="shipping_address"
                       value="<?php echo h($_POST['shipping_address'] ?? ''); ?>"
                       placeholder="House No., Street, Area" required>
                <?php if (isset($errors['shipping_address'])): ?>
                <span class="form-error"><?php echo h($errors['shipping_address']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="shipping_city">City</label>
                    <input type="text" id="shipping_city" name="shipping_city"
                           value="<?php echo h($_POST['shipping_city'] ?? ''); ?>"
                           placeholder="e.g. Karachi, Lahore, Peshawar" required>
                    <?php if (isset($errors['shipping_city'])): ?>
                    <span class="form-error"><?php echo h($errors['shipping_city']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="shipping_zip">Postal Code</label>
                    <input type="text" id="shipping_zip" name="shipping_zip"
                           value="<?php echo h($_POST['shipping_zip'] ?? ''); ?>"
                           placeholder="e.g. 25000" required>
                    <?php if (isset($errors['shipping_zip'])): ?>
                    <span class="form-error"><?php echo h($errors['shipping_zip']); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Payment Method -->
            <h3 style="margin-top:40px;">Payment Method</h3>

            <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:28px;">

                <label style="display:flex; align-items:center; gap:14px; padding:16px 20px;
                              border:1.5px solid var(--rose-gold); cursor:pointer; background:var(--blush);">
                    <input type="radio" name="payment_method" value="cod" checked>
                    <div>
                        <span style="font-weight:500; font-size:0.9rem;">Cash on Delivery</span>
                        <small style="display:block; color:var(--muted); font-size:0.78rem; margin-top:2px;">
                            Pay when your order arrives at your doorstep
                        </small>
                    </div>
                    <i class="fas fa-money-bill-wave" style="margin-left:auto; color:var(--rose-gold);"></i>
                </label>

                <label style="display:flex; align-items:center; gap:14px; padding:16px 20px;
                              border:1.5px solid var(--muted-light); cursor:pointer;">
                    <input type="radio" name="payment_method" value="bank">
                    <div>
                        <span style="font-weight:500; font-size:0.9rem;">Bank Transfer</span>
                        <small style="display:block; color:var(--muted); font-size:0.78rem; margin-top:2px;">
                            Transfer to our account before dispatch
                        </small>
                    </div>
                    <i class="fas fa-university" style="margin-left:auto; color:var(--muted);"></i>
                </label>

                <label style="display:flex; align-items:center; gap:14px; padding:16px 20px;
                              border:1.5px solid var(--muted-light); cursor:pointer;">
                    <input type="radio" name="payment_method" value="easypaisa">
                    <div>
                        <span style="font-weight:500; font-size:0.9rem;">EasyPaisa / JazzCash</span>
                        <small style="display:block; color:var(--muted); font-size:0.78rem; margin-top:2px;">
                            Send payment via mobile wallet
                        </small>
                    </div>
                    <i class="fas fa-mobile-alt" style="margin-left:auto; color:var(--muted);"></i>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; padding:18px; font-size:0.8rem; letter-spacing:2px;">
                <i class="fas fa-lock"></i> &nbsp;Place Order — PKR <?php echo number_format($total, 0); ?>
            </button>

            <p style="text-align:center; font-size:0.75rem; color:var(--muted); margin-top:14px;">
                <i class="fas fa-shield-alt"></i> &nbsp;Your information is secure and will never be shared
            </p>
        </form>
    </div>

    <!-- Order Summary -->
    <div class="order-summary">
        <h3>Order Summary</h3>

        <div class="order-items">
            <?php foreach ($cartItems as $item): ?>
            <div class="order-item">
                <div class="order-item-image">
                    <img src="uploads/products/<?php echo h($item['product']['image']); ?>"
                         alt="<?php echo h($item['product']['name']); ?>"
                         onerror="this.src='https://placehold.co/60x60/F2E8E1/B8956A?text=L'">
                </div>
                <div class="order-item-details">
                    <span class="order-item-name"><?php echo h($item['product']['name']); ?></span>
                    <span class="order-item-qty">Qty: <?php echo $item['quantity']; ?></span>
                </div>
                <span class="order-item-price">PKR <?php echo number_format($item['lineTotal'], 0); ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-totals-row">
            <span>Subtotal</span>
            <span>PKR <?php echo number_format($subtotal, 0); ?></span>
        </div>
        <div class="cart-totals-row">
            <span>Delivery</span>
            <span>
                <?php if ($shipping === 0): ?>
                    <span style="color:var(--success); font-weight:500;">Free</span>
                <?php else: ?>
                    PKR <?php echo number_format($shipping, 0); ?>
                <?php endif; ?>
            </span>
        </div>
        <div class="cart-totals-row total">
            <span>Total</span>
            <span>PKR <?php echo number_format($total, 0); ?></span>
        </div>

        <div style="margin-top:20px; padding:14px; background:var(--blush); font-size:0.78rem; color:var(--muted); text-align:center;">
            <i class="fas fa-truck"></i> &nbsp;
            <?php if ($shipping === 0): ?>
                You qualify for <strong>free delivery!</strong>
            <?php else: ?>
                Add PKR <?php echo number_format(5000 - $subtotal, 0); ?> more for free delivery
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>