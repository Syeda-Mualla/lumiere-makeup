<?php
/**
 * Blog Post Detail Page
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Features: Comments section for logged-in users
 * Security: CSRF protection, input sanitization, prepared statements
 */

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Get post ID
$postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$postId) {
    header('Location: blog.php');
    exit;
}

// Fetch post
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$postId]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blog.php');
    exit;
}

$pageTitle = $post['title'];
$errors = [];

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please login to comment');
        header('Location: login.php');
        exit;
    }
    
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        $commentBody = trim($_POST['comment'] ?? '');
        
        if (empty($commentBody)) {
            $errors['comment'] = 'Comment cannot be empty';
        } elseif (strlen($commentBody) > 1000) {
            $errors['comment'] = 'Comment must not exceed 1000 characters';
        }
        
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, post_type, body) VALUES (?, ?, 'blog', ?)");
                $stmt->execute([$_SESSION['user_id'], $postId, $commentBody]);
                
                setFlashMessage('success', 'Comment added successfully!');
                header('Location: post.php?id=' . $postId . '#comments');
                exit;
            } catch (PDOException $e) {
                error_log("Comment error: " . $e->getMessage());
                $errors['general'] = 'An error occurred. Please try again.';
            }
        }
    }
}

// Fetch comments
$stmt = $pdo->prepare("
    SELECT c.*, u.name as author_name, u.avatar as author_avatar 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = ? AND c.post_type = 'blog' 
    ORDER BY c.created_at DESC
");
$stmt->execute([$postId]);
$comments = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<section class="post-detail">
    <article>
        <header class="post-header">
            <span class="blog-date"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></span>
            <h1><?php echo h($post['title']); ?></h1>
        </header>
        
        <?php if ($post['image']): ?>
        <div class="post-featured-image">
            <img src="uploads/blog/<?php echo h($post['image']); ?>" 
                 alt="<?php echo h($post['title']); ?>"
                 onerror="this.src='https://placehold.co/800x450/f5f0e8/c9a84c?text=Blog'">
        </div>
        <?php endif; ?>
        
        <div class="post-content">
            <?php echo nl2br(h($post['content'])); ?>
        </div>
    </article>
    
    <!-- Comments Section -->
    <section class="comments-section" id="comments">
        <h3>Comments (<?php echo count($comments); ?>)</h3>
        
        <?php if (empty($comments)): ?>
        <p style="color: var(--color-gray); padding: 20px 0;">No comments yet. Be the first to share your thoughts!</p>
        <?php else: ?>
        <?php foreach ($comments as $comment): ?>
        <div class="comment">
            <div class="comment-avatar">
                <img src="uploads/avatars/<?php echo h($comment['author_avatar']); ?>" 
                     alt="<?php echo h($comment['author_name']); ?>"
                     onerror="this.src='https://placehold.co/50x50/f5f0e8/c9a84c?text=User'">
            </div>
            <div class="comment-body">
                <div class="comment-header">
                    <span class="comment-author"><?php echo h($comment['author_name']); ?></span>
                    <span class="comment-date"><?php echo date('M j, Y \a\t g:i A', strtotime($comment['created_at'])); ?></span>
                </div>
                <p class="comment-text"><?php echo nl2br(h($comment['body'])); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Comment Form -->
        <div class="comment-form">
            <h4>Leave a Comment</h4>
            
            <?php if (isLoggedIn()): ?>
            
            <?php if (isset($errors['general'])): ?>
            <div class="flash-message flash-error" style="position: static; transform: none; margin-bottom: 20px;">
                <?php echo h($errors['general']); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="commentForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <textarea name="comment" placeholder="Share your thoughts..." required><?php echo h($_POST['comment'] ?? ''); ?></textarea>
                    <?php if (isset($errors['comment'])): ?>
                    <span class="form-error"><?php echo h($errors['comment']); ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" name="submit_comment" class="btn btn-primary">Post Comment</button>
            </form>
            
            <?php else: ?>
            <p style="background-color: var(--color-cream); padding: 20px; text-align: center;">
                <a href="login.php" style="color: var(--color-gold); font-weight: 500;">Login</a> or 
                <a href="register.php" style="color: var(--color-gold); font-weight: 500;">Register</a> 
                to leave a comment.
            </p>
            <?php endif; ?>
        </div>
    </section>
</section>

<?php require_once 'includes/footer.php'; ?>
