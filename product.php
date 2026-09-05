<?php
/**
 * Product Detail Page
 * LUMIÈRE - Luxury Makeup Brand
 */

require_once 'includes/db.php';
require_once 'includes/auth.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$productId) {
    header('Location: shop.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: shop.php');
    exit;
}

$pageTitle = $product['name'];

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?: 1;
    $quantity = max(1, min($quantity, $product['stock']));

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$productId])) {
        $_SESSION['cart'][$productId] += $quantity;
    } else {
        $_SESSION['cart'][$productId] = $quantity;
    }

    $_SESSION['cart'][$productId] = min($_SESSION['cart'][$productId], $product['stock']);

    setFlashMessage('success', $product['name'] . ' has been added to your cart!');
    header('Location: product.php?id=' . $productId);
    exit;
}

require_once 'includes/header.php';
?>

<section class="product-detail">
    <div class="product-gallery">
        <div class="product-main-image">
            <img src="uploads/products/<?php echo h($product['image']); ?>"
                 alt="<?php echo h($product['name']); ?>"
                 onerror="this.src='https://placehold.co/600x750/F2E8E1/B8956A?text=LUMIERE'">
        </div>
    </div>

    <div class="product-details">
        <span class="product-category"><?php echo h($product['category']); ?></span>
        <h1><?php echo h($product['name']); ?></h1>
        <span class="product-price">PKR <?php echo number_format($product['price'], 0); ?></span>

        <p class="product-description"><?php echo nl2br(h($product['description'])); ?></p>

        <?php if ($product['stock'] > 0): ?>
            <p class="stock-info <?php echo $product['stock'] <= 5 ? 'low-stock' : 'in-stock'; ?>">
                <i class="fas fa-check-circle"></i>
                <?php if ($product['stock'] <= 5): ?>
                    Only <?php echo $product['stock']; ?> left in stock — order soon
                <?php else: ?>
                    In Stock &amp; Ready to Ship
                <?php endif; ?>
            </p>

            <form method="POST" action="">
                <div class="quantity-selector">
                    <label>Quantity:</label>
                    <div class="quantity-input">
                        <button type="button"><i class="fas fa-minus"></i></button>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                        <button type="button"><i class="fas fa-plus"></i></button>
                    </div>
                </div>

                <button type="submit" name="add_to_cart" class="btn btn-primary" style="width:100%; margin-bottom:12px;">
                    <i class="fas fa-shopping-bag"></i> &nbsp;Add to Cart
                </button>
            </form>

            <p style="font-size:0.75rem; color:var(--muted); margin-top:14px; letter-spacing:0.5px;">
                <i class="fas fa-truck"></i> &nbsp;Free delivery on orders above PKR 5,000 &nbsp;|&nbsp;
                <i class="fas fa-undo"></i> &nbsp;Easy 7-day returns
            </p>

        <?php else: ?>
            <p class="stock-info out-of-stock">
                <i class="fas fa-times-circle"></i> Currently Out of Stock
            </p>
            <button class="btn btn-outline" disabled style="width:100%; opacity:0.5; cursor:not-allowed;">
                Notify Me When Available
            </button>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>