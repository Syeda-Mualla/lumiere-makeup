<?php
/**
 * Shopping Cart Page
 * LUMIÈRE - Luxury Makeup Brand
 */

$pageTitle = 'Shopping Cart';
require_once 'includes/db.php';
require_once 'includes/auth.php';

$cartItems = [];
$subtotal  = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $productIds   = array_keys($_SESSION['cart']);
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
}

// Handle remove item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if ($productId && isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
        setFlashMessage('success', 'Item removed from cart');
        header('Location: cart.php');
        exit;
    }
}

// PKR shipping — free above 5000
$shipping = ($subtotal > 0 && $subtotal < 5000) ? 350 : 0;
$total    = $subtotal + $shipping;

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Shopping Cart</h1>
    <p>Review your items before checkout</p>
</div>

<section class="cart-section">
    <?php if (empty($cartItems)): ?>
    <div class="empty-cart">
        <i class="fas fa-shopping-bag"></i>
        <h2>Your Cart is Empty</h2>
        <p>Looks like you haven't added anything yet. Explore our collection!</p>
        <a href="shop.php" class="btn btn-primary" style="margin-top:16px;">Continue Shopping</a>
    </div>
    <?php else: ?>

    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item): ?>
            <tr id="cartRow-<?php echo $item['product']['id']; ?>">
                <td>
                    <div class="cart-product">
                        <div class="cart-product-image">
                            <img src="uploads/products/<?php echo h($item['product']['image']); ?>"
                                 alt="<?php echo h($item['product']['name']); ?>"
                                 onerror="this.src='https://placehold.co/80x80/F2E8E1/B8956A?text=L'">
                        </div>
                        <div>
                            <span class="cart-product-name"><?php echo h($item['product']['name']); ?></span>
                            <small style="display:block; color:var(--muted); font-size:0.75rem; letter-spacing:1px; text-transform:uppercase; margin-top:3px;">
                                <?php echo h($item['product']['category']); ?>
                            </small>
                        </div>
                    </div>
                </td>
                <td>PKR <?php echo number_format($item['product']['price'], 0); ?></td>
                <td>
                    <div class="cart-quantity">
                        <button type="button" onclick="updateCart(<?php echo $item['product']['id']; ?>, <?php echo $item['quantity'] - 1; ?>)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" value="<?php echo $item['quantity']; ?>"
                               min="1" max="<?php echo $item['product']['stock']; ?>"
                               onchange="updateCart(<?php echo $item['product']['id']; ?>, this.value)">
                        <button type="button" onclick="updateCart(<?php echo $item['product']['id']; ?>, <?php echo $item['quantity'] + 1; ?>)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </td>
                <td id="lineTotal-<?php echo $item['product']['id']; ?>">
                    PKR <?php echo number_format($item['lineTotal'], 0); ?>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                        <button type="submit" name="remove_item" class="cart-remove" title="Remove item">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="cart-summary">
        <div class="cart-totals">
            <h3>Order Summary</h3>
            <div class="cart-totals-row">
                <span>Subtotal</span>
                <span id="cartSubtotal">PKR <?php echo number_format($subtotal, 0); ?></span>
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
                <span id="cartTotal">PKR <?php echo number_format($total, 0); ?></span>
            </div>

            <?php if ($subtotal < 5000 && $subtotal > 0): ?>
            <p style="font-size:0.75rem; color:var(--muted); margin-top:10px; text-align:center;">
                Add PKR <?php echo number_format(5000 - $subtotal, 0); ?> more for free delivery!
            </p>
            <?php endif; ?>

            <a href="checkout.php" class="btn btn-primary" style="width:100%; text-align:center; margin-top:20px; display:block;">
                Proceed to Checkout
            </a>
            <a href="shop.php" class="btn btn-outline" style="width:100%; text-align:center; margin-top:10px; display:block;">
                Continue Shopping
            </a>
        </div>
    </div>

    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>