<?php
/**
 * Header Include
 * LUMIÈRE - Luxury Makeup Brand
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$cartCount = getCartCount();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? h($pageTitle) . ' | LUMIÈRE' : 'LUMIÈRE - Where Beauty Meets Elegance'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?php echo isset($isAdmin) ? '../css/style.css' : 'css/style.css'; ?>">
</head>
<body>
    <?php if ($flash): ?>
    <div class="flash-message flash-<?php echo h($flash['type']); ?>" id="flashMessage">
        <?php echo h($flash['message']); ?>
        <button onclick="this.parentElement.remove()" class="flash-close">&times;</button>
    </div>
    <?php endif; ?>

    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <a href="<?php echo isset($isAdmin) ? '../index.php' : 'index.php'; ?>" class="logo">
                    <span class="logo-text">LUMIÈRE</span>
                    <span class="logo-tagline">Where Beauty Meets Elegance</span>
                </a>

                <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <ul class="nav-menu" id="navMenu">
                    <li><a href="<?php echo isset($isAdmin) ? '../index.php' : 'index.php'; ?>">Home</a></li>
                    <li><a href="<?php echo isset($isAdmin) ? '../shop.php' : 'shop.php'; ?>">Shop</a></li>
                    <li><a href="<?php echo isset($isAdmin) ? '../blog.php' : 'blog.php'; ?>">Blog</a></li>
                    <li><a href="<?php echo isset($isAdmin) ? '../community.php' : 'community.php'; ?>">Community</a></li>
                    
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-dropdown">
                            <a href="#" class="dropdown-toggle">
                                <img src="<?php echo isset($isAdmin) ? '../uploads/avatars/' : 'uploads/avatars/'; ?><?php echo h($_SESSION['user_avatar'] ?? 'default-avatar.png'); ?>" alt="Avatar" class="nav-avatar">
                                <?php echo h($_SESSION['user_name']); ?>
                                <i class="fas fa-chevron-down"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo isset($isAdmin) ? '../profile.php' : 'profile.php'; ?>"><i class="fas fa-user"></i> Profile</a></li>
                                <?php if (isAdmin()): ?>
                                <li><a href="<?php echo isset($isAdmin) ? 'dashboard.php' : 'admin/dashboard.php'; ?>"><i class="fas fa-cog"></i> Admin Panel</a></li>
                                <?php endif; ?>
                                <li><a href="<?php echo isset($isAdmin) ? '../logout.php' : 'logout.php'; ?>"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="<?php echo isset($isAdmin) ? '../login.php' : 'login.php'; ?>">Login</a></li>
                    <?php endif; ?>
                    
                    <li>
                        <a href="<?php echo isset($isAdmin) ? '../cart.php' : 'cart.php'; ?>" class="cart-link">
                            <i class="fas fa-shopping-bag"></i>
                            <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="main-content">
