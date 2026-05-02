<?php
/**
 * Shop Page
 * LUMIÈRE - Luxury Makeup Brand
 */

$pageTitle = 'Shop';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch all products
$stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC");
$stmt->execute();
$products = $stmt->fetchAll();

// Updated categories
$categories = ['All', 'Lip Shades', 'Eyes', 'Face', 'Sets'];

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Shop Collection</h1>
    <p>Discover our exclusive range of luxury cosmetics</p>
</div>

<!-- Category Filter Buttons -->
<div class="filter-buttons">
    <?php foreach ($categories as $category): ?>
    <button class="filter-btn <?php echo $category === 'All' ? 'active' : ''; ?>"
            data-category="<?php echo h($category); ?>">
        <?php echo h($category); ?>
    </button>
    <?php endforeach; ?>
</div>

<!-- Products Grid -->
<section class="shop-grid">
    <div class="products-grid" id="productsGrid">
        <?php foreach ($products as $product): ?>
        <article class="product-card fade-in">
            <div class="product-image">
                <img src="uploads/products/<?php echo h($product['image']); ?>"
                     alt="<?php echo h($product['name']); ?>"
                     onerror="this.src='https://placehold.co/400x500/F2E8E1/B8956A?text=LUMIERE'">
                <div class="product-overlay">
                    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
                </div>
            </div>
            <div class="product-info">
                <span class="product-category"><?php echo h($product['category']); ?></span>
                <h3 class="product-name"><?php echo h($product['name']); ?></h3>
                <span class="product-price">PKR <?php echo number_format($product['price'], 0); ?></span>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>