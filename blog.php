<?php
/**
 * Blog Listing Page
 * LUMIÈRE - Luxury Makeup Brand
 */

$pageTitle = 'Beauty Journal';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch all blog posts
$stmt = $pdo->prepare("SELECT * FROM blog_posts ORDER BY created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Beauty Journal</h1>
    <p>Tips, trends, and beauty inspiration</p>
</div>

<section class="blog-page">
    <?php if (empty($posts)): ?>
    <p style="text-align: center; padding: 60px;">No blog posts yet. Check back soon!</p>
    <?php else: ?>
    <div class="blog-grid">
        <?php foreach ($posts as $post): ?>
        <article class="blog-card fade-in">
            <div class="blog-image">
                <a href="post.php?id=<?php echo $post['id']; ?>">
                    <img src="uploads/blog/<?php echo h($post['image']); ?>" 
                         alt="<?php echo h($post['title']); ?>"
                         onerror="this.src='https://placehold.co/600x400/f5f0e8/c9a84c?text=Blog'">
                </a>
            </div>
            <div class="blog-content">
                <span class="blog-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
                <h3 class="blog-title">
                    <a href="post.php?id=<?php echo $post['id']; ?>"><?php echo h($post['title']); ?></a>
                </h3>
                <p class="blog-excerpt">
                    <?php echo h($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 150) . '...'); ?>
                </p>
                <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more">
                    Read More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
