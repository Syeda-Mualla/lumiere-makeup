<?php
/**
 * Login Page
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Password verification with password_verify(), CSRF protection
 */

$pageTitle = 'Login';
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate input
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
        
        if (empty($errors)) {
            // Fetch user from database using prepared statement
            $stmt = $pdo->prepare("SELECT id, name, email, password, avatar, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                loginUser($user);
                
                // Redirect to intended page or homepage
                $redirect = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                
                setFlashMessage('success', 'Welcome back, ' . $user['name'] . '!');
                header('Location: ' . $redirect);
                exit;
            } else {
                $errors['general'] = 'Invalid email or password';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your LUMIÈRE account</p>
        </div>

        <?php if (isset($errors['general'])): ?>
        <div class="flash-message flash-error" style="position: static; transform: none; margin-bottom: 20px;">
            <?php echo h($errors['general']); ?>
        </div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="" id="loginForm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
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
                       placeholder="Enter your password" required>
                <?php if (isset($errors['password'])): ?>
                <span class="form-error"><?php echo h($errors['password']); ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <div class="auth-divider">
            <span>or</span>
        </div>

        <p class="auth-link">
            Don't have an account? <a href="register.php">Create one</a>
        </p>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
