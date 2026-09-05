<?php
/**
 * Community/Glam Feed Page
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Features: User image uploads, like button with AJAX, masonry grid
 * Security: File upload validation, CSRF protection, prepared statements
 */

$pageTitle = 'Glam Feed';
require_once 'includes/db.php';
require_once 'includes/auth.php';

$errors = [];

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_post'])) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to post');
        header('Location: login.php');
        exit;
    }
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        $caption = trim($_POST['caption'] ?? '');
        
        // Validate caption
        if (strlen($caption) > 500) {
            $errors['caption'] = 'Caption must not exceed 500 characters';
        }
        
        // Validate image upload
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = 'Please upload an image';
        } else {
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 10 * 1024 * 1024; // 10MB
            
            // Validate file type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors['image'] = 'Only JPEG, PNG, GIF, and WebP images are allowed';
            } elseif ($file['size'] > $maxSize) {
                $errors['image'] = 'Image must be less than 10MB';
            }
        }
        
        if (empty($errors)) {
            // Generate unique filename
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'glam_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
            $uploadPath = 'uploads/community/' . $filename;
            
            // Create directory if not exists
            if (!is_dir('uploads/community')) {
                mkdir('uploads/community', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO community (user_id, image, caption) VALUES (?, ?, ?)");
                    $stmt->execute([$_SESSION['user_id'], $filename, $caption]);
                    
                    setFlashMessage('success', 'Your look has been shared!');
                    header('Location: community.php');
                    exit;
                } catch (PDOException $e) {
                    error_log("Community post error: " . $e->getMessage());
                    $errors['general'] = 'An error occurred. Please try again.';
                    // Clean up uploaded file
                    unlink($uploadPath);
                }
            } else {
                $errors['image'] = 'Failed to upload image. Please try again.';
            }
        }
    }
}

// Fetch community posts
$stmt = $pdo->prepare("
    SELECT c.*, u.name as author_name, u.avatar as author_avatar 
    FROM community c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.reported = 0
    ORDER BY c.created_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll();

// Get user's liked posts
$likedPosts = [];
if (isLoggedIn()) {
    $stmt = $pdo->prepare("SELECT community_id FROM community_likes WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $likedPosts = array_column($stmt->fetchAll(), 'community_id');
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>Glam Feed</h1>
    <p>Share your makeup looks with the community</p>
</div>

<!-- Upload Section (logged-in users only) -->
<?php if (isLoggedIn()): ?>
<section class="upload-section">
    <h3>Share Your Look</h3>
    
    <?php if (isset($errors['general'])): ?>
    <div class="flash-message flash-error" style="position: static; transform: none; margin-bottom: 20px;">
        <?php echo h($errors['general']); ?>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <label class="image-upload" id="imageUploadLabel">
            <input type="file" name="image" id="communityImageInput" accept="image/*" required>
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Click to upload your makeup look</p>
        </label>
        
        <div class="image-preview" id="communityImagePreview">
            <img src="" alt="Preview">
        </div>
        
        <?php if (isset($errors['image'])): ?>
        <span class="form-error" style="display: block; margin-bottom: 15px;"><?php echo h($errors['image']); ?></span>
        <?php endif; ?>
        
        <div class="form-group">
            <textarea name="caption" placeholder="Add a caption (optional)" rows="3"><?php echo h($_POST['caption'] ?? ''); ?></textarea>
            <?php if (isset($errors['caption'])): ?>
            <span class="form-error"><?php echo h($errors['caption']); ?></span>
            <?php endif; ?>
        </div>
        
        <button type="submit" name="submit_post" class="btn btn-primary" style="width: 100%;">
            <i class="fas fa-share"></i> Share Look
        </button>
    </form>
</section>
<?php else: ?>
<div class="community-header">
    <p><a href="login.php" style="color: var(--color-gold);">Login</a> or 
       <a href="register.php" style="color: var(--color-gold);">Register</a> to share your looks!</p>
</div>
<?php endif; ?>

<!-- Community Grid -->
<section class="community-grid">
    <?php if (empty($posts)): ?>
    <p style="text-align: center; padding: 60px; grid-column: 1/-1;">
        No looks shared yet. Be the first to share your glam!
    </p>
    <?php else: ?>
    <?php foreach ($posts as $post): ?>
    <article class="community-post fade-in">
        <div class="community-post-image">
            <img src="uploads/community/<?php echo h($post['image']); ?>" 
                 alt="Look by <?php echo h($post['author_name']); ?>"
                 onerror="this.src='https://placehold.co/400x500/f5f0e8/c9a84c?text=Look'">
        </div>
        <div class="community-post-content">
            <div class="community-post-header">
                <div class="community-avatar">
                    <img src="uploads/avatars/<?php echo h($post['author_avatar']); ?>" 
                         alt="<?php echo h($post['author_name']); ?>"
                         onerror="this.src='https://placehold.co/40x40/f5f0e8/c9a84c?text=User'">
                </div>
                <div>
                    <span class="community-author"><?php echo h($post['author_name']); ?></span>
                    <span class="community-date"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                </div>
            </div>
            
            <?php if ($post['caption']): ?>
            <p class="community-caption"><?php echo nl2br(h($post['caption'])); ?></p>
            <?php endif; ?>
            
            <div class="community-actions">
                <button class="like-btn <?php echo in_array($post['id'], $likedPosts) ? 'liked' : ''; ?>" 
                        data-post-id="<?php echo $post['id']; ?>">
                    <i class="<?php echo in_array($post['id'], $likedPosts) ? 'fas' : 'far'; ?> fa-heart"></i>
                    <span class="like-count"><?php echo $post['likes']; ?></span>
                </button>
            </div>
        </div>
    </article>
    <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>
