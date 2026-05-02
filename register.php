<?php
/**
 * Registration Page
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Password hashing with password_hash(), input validation, CSRF protection
 */

$pageTitle = 'Create Account';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate name
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        } elseif (strlen($name) < 2) {
            $errors['name'] = 'Name must be at least 2 characters';
        } elseif (strlen($name) > 100) {
            $errors['name'] = 'Name must not exceed 100 characters';
        }
        
        // Validate email
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        } else {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = 'This email is already registered';
            }
        }
        
        // Validate password
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        // Validate confirm password
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        
        if (empty($errors)) {
            // Hash password securely
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database using prepared statement
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            
            try {
                $stmt->execute([$name, $email, $hashedPassword]);
                
                // Get the new user
                $userId = $pdo->lastInsertId();
                $user = [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'user',
                    'avatar' => 'default-avatar.png'
                ];
                
                // Log the user in
                loginUser($user);
                
                setFlashMessage('success', 'Welcome to LUMIÈRE, ' . $name . '!');
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                $errors['general'] = 'An error occurred. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Create Account</h1>
            <p>Join the LUMIÈRE beauty community</p>
        </div>

        <?php if (isset($errors['general'])): ?>
        <div class="flash-message flash-error" style="position: static; transform: none; margin-bottom: 20px;">
            <?php echo h($errors['general']); ?>
        </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="" id="registerForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo h($name); ?>" 
                       placeholder="Enter your name" required>
                <?php if (isset($errors['name'])): ?>
                <span class="form-error"><?php echo h($errors['name']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo h($email); ?>" 
                       placeholder="your@email.com" required>
                <?php if (isset($errors['email'])): ?>
                <span class="form-error"><?php echo h($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Create a password (min. 8 characters)" required>
                <?php if (isset($errors['password'])): ?>
                <span class="form-error"><?php echo h($errors['password']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Confirm your password" required>
                <?php if (isset($errors['confirm_password'])): ?>
                <span class="form-error"><?php echo h($errors['confirm_password']); ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <div class="auth-divider">
            <span>or</span>
        </div>

        <p class="auth-link">
            Already have an account? <a href="login.php">Sign in</a>
        </p>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
