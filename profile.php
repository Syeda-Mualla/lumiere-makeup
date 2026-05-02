<?php
/**
 * User Profile Page
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Authentication required, file upload validation, CSRF protection
 */

$pageTitle = 'My Profile';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Require login
requireLogin();

$user = getCurrentUser($pdo);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        
        // Validate name
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($name) > 100) {
            $errors['name'] = 'Name must not exceed 100 characters';
        }
        
        // Validate bio
        if (strlen($bio) > 500) {
            $errors['bio'] = 'Bio must not exceed 500 characters';
        }
        
        // Handle avatar upload
        $avatarFileName = $user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            // Validate file type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors['avatar'] = 'Only JPEG, PNG, GIF, and WebP images are allowed';
            } elseif ($file['size'] > $maxSize) {
                $errors['avatar'] = 'Image must be less than 5MB';
            } else {
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $avatarFileName = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
                $uploadPath = 'uploads/avatars/' . $avatarFileName;
                
                // Create directory if not exists
                if (!is_dir('uploads/avatars')) {
                    mkdir('uploads/avatars', 0755, true);
                }
                
                if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
                    $errors['avatar'] = 'Failed to upload image. Please try again.';
                    $avatarFileName = $user['avatar'];
                } else {
                    // Delete old avatar if not default
                    if ($user['avatar'] !== 'default-avatar.png' && file_exists('uploads/avatars/' . $user['avatar'])) {
                        unlink('uploads/avatars/' . $user['avatar']);
                    }
                }
            }
        }
        
        if (empty($errors)) {
            // Update user in database
            $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, avatar = ? WHERE id = ?");
            
            try {
                $stmt->execute([$name, $bio, $avatarFileName, $_SESSION['user_id']]);
                
                // Update session
                $_SESSION['user_name'] = $name;
                $_SESSION['user_avatar'] = $avatarFileName;
                
                // Refresh user data
                $user = getCurrentUser($pdo);
                $success = true;
                setFlashMessage('success', 'Profile updated successfully!');
                
                // Redirect to prevent form resubmission
                header('Location: profile.php');
                exit;
            } catch (PDOException $e) {
                error_log("Profile update error: " . $e->getMessage());
                $errors['general'] = 'An error occurred. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <h1>My Profile</h1>
    <p>Manage your account settings</p>
</div>

<section class="profile-section">
    <div class="profile-header">
        <div class="profile-avatar">
            <img src="uploads/avatars/<?php echo h($user['avatar']); ?>" alt="Profile Avatar" id="avatarPreview">
            <label class="avatar-upload">
                <input type="file" name="avatar" id="avatarInput" accept="image/*" form="profileForm">
                <i class="fas fa-camera"></i> Change
            </label>
        </div>
        <div class="profile-info">
            <h1><?php echo h($user['name']); ?></h1>
            <p class="profile-email"><?php echo h($user['email']); ?></p>
            <?php if ($user['bio']): ?>
            <p class="profile-bio"><?php echo h($user['bio']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($errors['general'])): ?>
    <div class="flash-message flash-error" style="position: static; transform: none; margin-bottom: 20px;">
        <?php echo h($errors['general']); ?>
    </div>
    <?php endif; ?>

    <div class="profile-form">
        <h3>Update Profile</h3>
        
        <form method="POST" action="" enctype="multipart/form-data" id="profileForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo h($user['name']); ?>" required>
                <?php if (isset($errors['name'])): ?>
                <span class="form-error"><?php echo h($errors['name']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" value="<?php echo h($user['email']); ?>" disabled>
                <small style="color: var(--color-gray);">Email cannot be changed</small>
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..."><?php echo h($user['bio'] ?? ''); ?></textarea>
                <?php if (isset($errors['bio'])): ?>
                <span class="form-error"><?php echo h($errors['bio']); ?></span>
                <?php endif; ?>
            </div>

            <?php if (isset($errors['avatar'])): ?>
            <div class="form-group">
                <span class="form-error"><?php echo h($errors['avatar']); ?></span>
            </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
