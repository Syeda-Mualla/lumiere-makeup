<?php
/**
 * Admin Add Product
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Admin role required, file upload validation, CSRF protection
 */

$pageTitle = 'Add Product';
$isAdmin = true;
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireAdmin();

$errors = [];
$formData = [
    'name' => '',
    'description' => '',
    'price' => '',
    'category' => '',
    'stock' => '',
    'featured' => 0
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors['general'] = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $formData['name'] = trim($_POST['name'] ?? '');
        $formData['description'] = trim($_POST['description'] ?? '');
        $formData['price'] = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $formData['category'] = $_POST['category'] ?? '';
        $formData['stock'] = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
        $formData['featured'] = isset($_POST['featured']) ? 1 : 0;
        
        // Validate
        if (empty($formData['name'])) {
            $errors['name'] = 'Product name is required';
        }
        if ($formData['price'] === false || $formData['price'] <= 0) {
            $errors['price'] = 'Please enter a valid price';
        }
        if (!in_array($formData['category'], ['Lips', 'Eyes', 'Face', 'Sets'])) {
            $errors['category'] = 'Please select a valid category';
        }
        if ($formData['stock'] === false || $formData['stock'] < 0) {
            $errors['stock'] = 'Please enter a valid stock quantity';
        }
        
        // Validate image
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors['image'] = 'Product image is required';
        } else {
            $file = $_FILES['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors['image'] = 'Only JPEG, PNG, GIF, and WebP images are allowed';
            } elseif ($file['size'] > $maxSize) {
                $errors['image'] = 'Image must be less than 5MB';
            }
        }
        
        if (empty($errors)) {
            // Upload image
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = '../uploads/products/' . $filename;
            
            if (!is_dir('../uploads/products')) {
                mkdir('../uploads/products', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO products (name, description, price, category, image, stock, featured)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $formData['name'],
                        $formData['description'],
                        $formData['price'],
                        $formData['category'],
                        $filename,
                        $formData['stock'],
                        $formData['featured']
                    ]);
                    
                    setFlashMessage('success', 'Product added successfully!');
                    header('Location: products.php');
                    exit;
                } catch (PDOException $e) {
                    error_log("Add product error: " . $e->getMessage());
                    $errors['general'] = 'An error occurred. Please try again.';
                    unlink($uploadPath);
                }
            } else {
                $errors['image'] = 'Failed to upload image';
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
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="blogs.php"><i class="fas fa-newspaper"></i> Blog Posts</a></li>
            <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a></li>
        </ul>
    </aside>
    
    <div class="admin-main">
        <div class="admin-header">
            <h1>Add Product</h1>
            <a href="products.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Products
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
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" value="<?php echo h($formData['name']); ?>" required>
                    <?php if (isset($errors['name'])): ?>
                    <span class="form-error"><?php echo h($errors['name']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo h($formData['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price ($) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" 
                               value="<?php echo h($formData['price']); ?>" required>
                        <?php if (isset($errors['price'])): ?>
                        <span class="form-error"><?php echo h($errors['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock">Stock Quantity *</label>
                        <input type="number" id="stock" name="stock" min="0" 
                               value="<?php echo h($formData['stock']); ?>" required>
                        <?php if (isset($errors['stock'])): ?>
                        <span class="form-error"><?php echo h($errors['stock']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Lips" <?php echo $formData['category'] === 'Lips' ? 'selected' : ''; ?>>Lips</option>
                        <option value="Eyes" <?php echo $formData['category'] === 'Eyes' ? 'selected' : ''; ?>>Eyes</option>
                        <option value="Face" <?php echo $formData['category'] === 'Face' ? 'selected' : ''; ?>>Face</option>
                        <option value="Sets" <?php echo $formData['category'] === 'Sets' ? 'selected' : ''; ?>>Sets</option>
                    </select>
                    <?php if (isset($errors['category'])): ?>
                    <span class="form-error"><?php echo h($errors['category']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="image">Product Image *</label>
                    <label class="image-upload-admin" style="display: block; cursor: pointer;">
                        <input type="file" id="productImageInput" name="image" accept="image/*" style="display: none;" required>
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Click to upload image</p>
                    </label>
                    <img id="productImagePreview" src="" alt="Preview" class="current-image" style="display: none; margin-top: 15px;">
                    <?php if (isset($errors['image'])): ?>
                    <span class="form-error"><?php echo h($errors['image']); ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="featured" value="1" <?php echo $formData['featured'] ? 'checked' : ''; ?>>
                        Feature this product on homepage
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
