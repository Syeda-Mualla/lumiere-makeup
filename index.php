<?php
/**
 * Homepage
 * LUMIÈRE - Luxury Makeup Brand
 */

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch featured products
$stmt = $pdo->prepare("SELECT * FROM products WHERE featured = 1 ORDER BY created_at DESC LIMIT 4");
$stmt->execute();
$featuredProducts = $stmt->fetchAll();

// Fetch latest blog posts
$stmt = $pdo->prepare("SELECT * FROM blog_posts ORDER BY created_at DESC LIMIT 3");
$stmt->execute();
$latestPosts = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <p class="hero-subtitle">Discover Luxury Beauty</p>
        <h1>Where Beauty Meets Elegance</h1>
        <p class="hero-text">Experience the art of beauty with our exclusive collection of luxury cosmetics, crafted for those who dare to shine.</p>
        <a href="shop.php" class="btn btn-primary">Shop Collection</a>
    </div>
</section>

<!-- Trust Strip -->
<div class="trust-strip">
    <div class="trust-strip-inner">
        <div class="trust-item"><i class="fas fa-leaf"></i> 100% Cruelty Free</div>
        <div class="trust-item"><i class="fas fa-truck"></i> Free Delivery Over PKR 5,000</div>
        <div class="trust-item"><i class="fas fa-gem"></i> Luxury Ingredients</div>
        <div class="trust-item"><i class="fas fa-undo"></i> Easy Returns</div>
        <div class="trust-item"><i class="fas fa-shield-alt"></i> Authentic Products</div>
    </div>
</div>

<!-- Featured Products Section -->
<section class="section featured-products">
    <div class="container">
        <div class="section-title">
            <span class="subtitle">Handpicked For You</span>
            <h2>Featured Products</h2>
            <div class="line"></div>
        </div>

        <div class="products-grid">
            <?php foreach ($featuredProducts as $product): ?>
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

        <div style="text-align: center; margin-top: 50px;">
            <a href="shop.php" class="btn btn-outline">View All Products</a>
        </div>
    </div>
</section>

<!-- Beauty Tips Marquee -->
<section class="marquee-section">
    <div class="marquee">
        <div class="marquee-content">
            <span class="marquee-item"><i class="fas fa-circle"></i> Always apply primer before foundation for longer wear</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Blend your eyeshadow in circular motions for seamless colour</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Use a lip liner to prevent lipstick from bleeding</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Set your makeup with a setting spray for all-day freshness</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Clean your brushes weekly for flawless application</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Hydrate your skin before applying any foundation</span>
        </div>
        <div class="marquee-content" aria-hidden="true">
            <span class="marquee-item"><i class="fas fa-circle"></i> Always apply primer before foundation for longer wear</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Blend your eyeshadow in circular motions for seamless colour</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Use a lip liner to prevent lipstick from bleeding</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Set your makeup with a setting spray for all-day freshness</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Clean your brushes weekly for flawless application</span>
            <span class="marquee-item"><i class="fas fa-circle"></i> Hydrate your skin before applying any foundation</span>
        </div>
    </div>
</section>

<!-- Latest Blog Posts -->
<section class="section blog-preview">
    <div class="container">
        <div class="section-title">
            <span class="subtitle">Tips & Inspiration</span>
            <h2>Beauty Journal</h2>
            <div class="line"></div>
        </div>

        <div class="blog-grid">
            <?php foreach ($latestPosts as $post): ?>
            <article class="blog-card fade-in">
                <div class="blog-image">
                    <img src="uploads/blog/<?php echo h($post['image']); ?>"
                         alt="<?php echo h($post['title']); ?>"
                         onerror="this.src='https://placehold.co/600x400/F2E8E1/B8956A?text=LUMIERE+Journal'">
                </div>
                <div class="blog-content">
                    <span class="blog-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                    <h3 class="blog-title"><?php echo h($post['title']); ?></h3>
                    <p class="blog-excerpt"><?php echo h($post['excerpt'] ?? substr($post['content'], 0, 120) . '...'); ?></p>
                    <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more">
                        Read More <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 50px;">
            <a href="blog.php" class="btn btn-outline">View All Posts</a>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="container">
        <h2>Join the LUMIÈRE Family</h2>
        <p class="newsletter-text">Subscribe to receive exclusive offers, beauty tips, and be the first to know about new arrivals.</p>
        <form class="newsletter-form" id="newsletterForm">
            <input type="email" name="email" placeholder="Enter your email address" required>
            <button type="submit">Subscribe</button>
        </form>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>