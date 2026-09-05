<?php
/**
 * Admin Products Management
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Admin role required, CSRF protection
 */

$pageTitle = 'Manage Products';
$isAdmin = true;
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
        if ($productId) {
            // Get product image
            $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
            
            // Delete product
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            
            // Delete image file
            if ($product && file_exists('../uploads/products/' . $product['image'])) {
                unlink('../uploads/products/' . $product['image']);
            }
            
            setFlashMessage('success', 'Product deleted successfully');
        }
    }
    header('Location: products.php');
    exit;
}

// Fetch products
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();

require_once '../includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <ul class="admin-sidebar-menu">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="blogs.php"><i class="fas fa-newspaper"></i> Blog Posts</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
        </ul>
    </aside>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1>Products</h1>
            <a href="add-product.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
        
        <div class="admin-card">
            <?php if (empty($products)): ?>
            <p style="color: var(--color-gray);">No products yet. <a href="add-product.php">Add your first product</a></p>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <img src="../uploads/products/<?php echo h($product['image']); ?>" 
                                 alt="<?php echo h($product['name']); ?>"
                                 onerror="this.src='https://placehold.co/50x50/f5f0e8/c9a84c?text=P'">
                        </td>
                        <td><?php echo h($product['name']); ?></td>
                        <td><?php echo h($product['category']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><?php echo $product['featured'] ? '<i class="fas fa-star" style="color: var(--color-gold);"></i>' : '-'; ?></td>
                        <td>
                            <div class="action-btns">
                                <a href="edit-product.php?id=<?php echo $product['id']; ?>" class="action-btn action-btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="delete_product" class="action-btn action-btn-delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
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
