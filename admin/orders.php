<?php
/**
 * Admin Orders Management
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Admin role required, CSRF protection
 */

$pageTitle = 'Manage Orders';
$isAdmin = true;
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        
        if ($orderId && in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);
            setFlashMessage('success', 'Order status updated');
        }
    }
    header('Location: orders.php');
    exit;
}

// Fetch orders
$stmt = $pdo->query("
    SELECT o.*, u.name as customer_name, u.email as customer_email
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <ul class="admin-sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="blogs.php"><i class="fas fa-newspaper"></i> Blog Posts</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
        </ul>
    </aside>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1>Orders</h1>
        </div>
        
        <div class="admin-card">
            <?php if (empty($orders)): ?>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td>
                            <strong><?php echo h($order['customer_name']); ?></strong><br>
                            <small style="color: var(--color-gray);"><?php echo h($order['customer_email']); ?></small>
                        </td>
                        <td>$<?php echo number_format($order['total_price'], 2); ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="status-badge status-<?php echo $order['status']; ?>" style="border: none; cursor: pointer;">
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <button onclick="viewOrder(<?php echo $order['id']; ?>)" class="action-btn action-btn-edit" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div id="orderModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
        <h3 style="margin-bottom: 20px;">Order Details</h3>
        <div id="orderDetails"></div>
        <button onclick="document.getElementById('orderModal').style.display='none'" class="btn btn-outline" style="margin-top: 20px;">Close</button>
    </div>
</div>

<script>
async function viewOrder(orderId) {
    const modal = document.getElementById('orderModal');
    const details = document.getElementById('orderDetails');
    details.innerHTML = '<div class="spinner"></div>';
    modal.style.display = 'flex';
    
    try {
        const response = await fetch('ajax/get-order.php?id=' + orderId);
        const data = await response.json();
        
        if (data.success) {
            let html = `
                <p><strong>Order #:</strong> ${data.order.id.toString().padStart(6, '0')}</p>
                <p><strong>Customer:</strong> ${data.order.customer_name}</p>
                <p><strong>Shipping:</strong><br>${data.order.shipping_name}<br>${data.order.shipping_address}<br>${data.order.shipping_city}, ${data.order.shipping_zip}</p>
                <hr style="margin: 20px 0;">
                <h4>Items</h4>
            `;
            
            data.items.forEach(item => {
                html += `<p>${item.name} x ${item.quantity} - $${parseFloat(item.price).toFixed(2)}</p>`;
            });
            
            html += `<hr style="margin: 20px 0;"><p><strong>Total:</strong> $${parseFloat(data.order.total_price).toFixed(2)}</p>`;
            
            details.innerHTML = html;
        } else {
            details.innerHTML = '<p>Error loading order details</p>';
        }
    } catch (e) {
        details.innerHTML = '<p>Error loading order details</p>';
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
