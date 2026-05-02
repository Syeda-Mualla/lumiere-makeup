<?php
/**
 * Admin Dashboard
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Admin role required
 */

$pageTitle = 'Admin Dashboard';
$isAdmin = true;
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Require admin access
requireAdmin();

// Fetch statistics
$stats = [];

// Total products
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$stats['products'] = $stmt->fetchColumn();

// Total orders
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$stats['orders'] = $stmt->fetchColumn();

// Total users
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$stats['users'] = $stmt->fetchColumn();

// Total revenue
$stmt = $pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status != 'cancelled'");
$stats['revenue'] = $stmt->fetchColumn();

// Recent orders
$stmt = $pdo->query("
    SELECT o.*, u.name as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recentOrders = $stmt->fetchAll();

// Low stock products
$stmt = $pdo->query("SELECT * FROM products WHERE stock <= 10 ORDER BY stock ASC LIMIT 5");
$lowStockProducts = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <ul class="admin-sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="blogs.php"><i class="fas fa-newspaper"></i> Blog Posts</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
        </ul>
    </aside>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo h($_SESSION['user_name']); ?></p>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['products']; ?></h3>
                    <p>Products</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['orders']; ?></h3>
                    <p>Orders</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>$<?php echo number_format($stats['revenue'], 2); ?></h3>
                    <p>Revenue</p>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="admin-card">
            <h3>Recent Orders</h3>
            <?php if (empty($recentOrders)): ?>
            <p style="color: var(--color-gray);">No orders yet.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo h($order['customer_name']); ?></td>
                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        
        <!-- Low Stock Products -->
        <div class="admin-card">
            <h3>Low Stock Alert</h3>
            <?php if (empty($lowStockProducts)): ?>
            <p style="color: var(--color-gray);">All products are well stocked.</p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lowStockProducts as $product): ?>
                    <tr>
                        <td><?php echo h($product['name']); ?></td>
                        <td><?php echo h($product['category']); ?></td>
                        <td style="color: <?php echo $product['stock'] <= 5 ? 'var(--color-error)' : 'var(--color-warning)'; ?>; font-weight: 600;">
                            <?php echo $product['stock']; ?> units
                        </td>
                        <td>
                            <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="action-btn action-btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
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
