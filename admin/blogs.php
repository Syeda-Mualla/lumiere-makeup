<?php
/**
 * Admin Blog Posts Management
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Admin role required, CSRF protection
 */

$pageTitle = 'Manage Blog Posts';
$isAdmin = true;
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        if ($postId) {
            // Get post image
            $stmt = $pdo->prepare("SELECT image FROM blog_posts WHERE id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch();
            
            // Delete post
            $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
            $stmt->execute([$postId]);
            
            // Delete comments
            $stmt = $pdo->prepare("DELETE FROM comments WHERE post_id = ? AND post_type = 'blog'");
            $stmt->execute([$postId]);
            
            // Delete image file
            if ($post && $post['image'] && file_exists('../uploads/blog/' . $post['image'])) {
                unlink('../uploads/blog/' . $post['image']);
            }
            
            setFlashMessage('success', 'Blog post deleted successfully');
        }
    }
    header('Location: blogs.php');
    exit;
}

// Fetch posts
$stmt = $pdo->query("SELECT * FROM blog_posts ORDER BY created_at DESC");
$posts = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <ul class="admin-sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="blogs.php" class="active"><i class="fas fa-newspaper"></i> Blog Posts</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
        </ul>
    </aside>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1>Blog Posts</h1>
            <a href="add-blog.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Post
            </a>
        </div>
        
        <div class="admin-card">
            <?php if (empty($posts)): ?>
            <p style="color: var(--color-gray);">No blog posts yet. <a href="add-blog.php">Write your first post</a></p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <img src="../uploads/blog/<?php echo h($post['image']); ?>" 
                                 alt="<?php echo h($post['title']); ?>"
                                 onerror="this.src='https://placehold.co/50x50/f5f0e8/c9a84c?text=B'"
                                 style="width: 80px; height: 50px; object-fit: cover;">
                        </td>
                        <td><?php echo h($post['title']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="../post.php?id=<?php echo $post['id']; ?>" target="_blank" class="action-btn" style="background: var(--color-gray);" title="View">
                                    <i class="fas fa-eye" style="color: white;"></i>
                                </a>
                                <a href="edit-blog.php?id=<?php echo $post['id']; ?>" class="action-btn action-btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this post?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" name="delete_post" class="action-btn action-btn-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
