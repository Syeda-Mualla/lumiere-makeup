<?php
/**
 * Admin Users Management
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Admin role required
 */

$pageTitle = 'Manage Users';
$isAdmin = true;
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Handle role update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $role = $_POST['role'] ?? '';
        
        // Don't allow changing own role
        if ($userId && $userId != $_SESSION['user_id'] && in_array($role, ['user', 'admin'])) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$role, $userId]);
            setFlashMessage('success', 'User role updated');
        }
    }
    header('Location: users.php');
    exit;
}

// Handle community post moderation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_community'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        if ($postId) {
            // Get image
            $stmt = $pdo->prepare("SELECT image FROM community WHERE id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch();
            
            // Delete post
            $stmt = $pdo->prepare("DELETE FROM community WHERE id = ?");
            $stmt->execute([$postId]);
            
            // Delete image
            if ($post && file_exists('../uploads/community/' . $post['image'])) {
                unlink('../uploads/community/' . $post['image']);
            }
            
            setFlashMessage('success', 'Community post deleted');
        }
    }
    header('Location: users.php#community');
    exit;
}

// Fetch users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Fetch reported community posts
$stmt = $pdo->query("
    SELECT c.*, u.name as author_name 
    FROM community c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.reported = 1 
    ORDER BY c.created_at DESC
");
$reportedPosts = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <ul class="admin-sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="blogs.php"><i class="fas fa-newspaper"></i> Blog Posts</a></li>
            <li><a href="users.php" class="active"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
        </ul>
    </aside>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1>Users</h1>
        </div>
        
        <div class="admin-card">
            <h3>All Users</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Avatar</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <img src="../uploads/avatars/<?php echo h($user['avatar']); ?>" 
                                 alt="<?php echo h($user['name']); ?>"
                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                                 onerror="this.src='https://placehold.co/40x40/f5f0e8/c9a84c?text=U'">
                        </td>
                        <td><?php echo h($user['name']); ?></td>
                        <td><?php echo h($user['email']); ?></td>
                        <td>
                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                <span class="status-badge status-delivered"><?php echo ucfirst($user['role']); ?></span>
                                <small style="color: var(--color-gray);">(You)</small>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <select name="role" onchange="this.form.submit()" 
                                            style="padding: 5px; border: 1px solid var(--color-gray-light); cursor: pointer;">
                                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <input type="hidden" name="update_role" value="1">
                                </form>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Reported Community Posts -->
        <div class="admin-card" id="community">
            <h3>Reported Community Posts</h3>
            <?php if (empty($reportedPosts)): ?>
            <p style="color: var(--color-gray);">No reported posts.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>User</th>
                        <th>Caption</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportedPosts as $post): ?>
                    <tr>
                        <td>
                            <img src="../uploads/community/<?php echo h($post['image']); ?>" 
                                 alt="Community post"
                                 style="width: 60px; height: 60px; object-fit: cover;"
                                 onerror="this.src='https://placehold.co/60x60/f5f0e8/c9a84c?text=Img'">
                        </td>
                        <td><?php echo h($post['author_name']); ?></td>
                        <td><?php echo h(substr($post['caption'] ?? '', 0, 50)); ?></td>
                        <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this post?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="delete_community" class="action-btn action-btn-delete" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
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
