<?php
/**
 * Admin Add Blog Post
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Admin role required, file upload validation, CSRF protection
 */

$pageTitle = 'Add Blog Post';
$isAdmin = true;
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$errors = [];
$formData = [
    'title' => '',
    'content' => '',
    'excerpt' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $formData['title'] = trim($_POST['title'] ?? '');
        $formData['content'] = trim($_POST['content'] ?? '');
        $formData['excerpt'] = trim($_POST['excerpt'] ?? '');
        
        // Validate
        if (empty($formData['title'])) {
            $errors['title'] = 'Title is required';
        }
        if (empty($formData['content'])) {
            $errors['content'] = 'Content is required';
        }
        
        // Handle image upload (optional)
        $filename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors['image'] = 'Only JPEG, PNG, GIF, and WebP images are allowed';
            } elseif ($file['size'] > $maxSize) {
                $errors['image'] = 'Image must be less than 5MB';
            } else {
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'blog_' . time() . '_' . uniqid() . '.' . $extension;
                $uploadPath = '../uploads/blog/' . $filename;
                
                if (!is_dir('../uploads/blog')) {
                    mkdir('../uploads/blog', 0755, true);
                }
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                    $errors['image'] = 'Failed to upload image';
                    $filename = null;
                }
            }
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO blog_posts (title, content, excerpt, image) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $formData['title'],
                    $formData['content'],
                    $formData['excerpt'] ?: substr(strip_tags($formData['content']), 0, 150) . '...',
                    $filename
                ]);
                
                setFlashMessage('success', 'Blog post published!');
                header('Location: blogs.php');
                exit;
            } catch (PDOException $e) {
                error_log("Add blog error: " . $e->getMessage());
                $errors['general'] = 'An error occurred. Please try again.';
                if ($filename && file_exists('../uploads/blog/' . $filename)) {
                    unlink('../uploads/blog/' . $filename);
                }
            }
        }
    }
}

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
            <h1>Add Blog Post</h1>
            <a href="blogs.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Posts
            </a>
        </div>
        
        <div class="admin-card">
            <?php if (isset($errors['general'])): ?>
            <div class="flash-message flash-error" style="position: static; transform: none; margin-bottom: 20px;">
                <?php echo h($errors['general']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo h($formData['title']); ?>" required>
                    <?php if (isset($errors['title'])): ?>
                    <span class="form-error"><?php echo h($errors['title']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="excerpt">Excerpt (short description)</label>
                    <textarea id="excerpt" name="excerpt" rows="2" placeholder="Brief summary for listing pages"><?php echo h($formData['excerpt']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" required><?php echo h($formData['content']); ?></textarea>
                    <?php if (isset($errors['content'])): ?>
                    <span class="form-error"><?php echo h($errors['content']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="image">Featured Image</label>
                    <label class="image-upload-admin" style="display: block; cursor: pointer;">
                        <input type="file" id="blogImageInput" name="image" accept="image/*" style="display: none;">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload image</p>
                    </label>
                    <img id="blogImagePreview" src="" alt="Preview" class="current-image" style="display: none; margin-top: 15px;">
                    <?php if (isset($errors['image'])): ?>
                    <span class="form-error"><?php echo h($errors['image']); ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">Publish Post</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
