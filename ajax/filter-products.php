<?php
/**
 * AJAX: Filter Products by Category
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Prepared statements, input sanitization
 */

header('Content-Type: application/json');

require_once '../includes/db.php';

$category = trim($_GET['category'] ?? 'All');

try {
    if ($category === 'All') {
        $stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC");
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
        $stmt->execute([$category]);
    }
    
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        echo json_encode([
            'success' => true,
            'html' => '<p style="text-align: center; padding: 40px; grid-column: 1/-1;">No products found in this category.</p>'
        ]);
        exit;
    }
    
    $html = '';
    foreach ($products as $product) {
        $html .= '
        <article class="product-card fade-in">
            <div class="product-image">
                <img src="uploads/products/' . h($product['image']) . '" 
                     alt="' . h($product['name']) . '"
                     onerror="this.src=\'https://placehold.co/400x400/f5f0e8/c9a84c?text=' . urlencode($product['name']) . '\'">
                <div class="product-overlay">
                    <a href="product.php?id=' . $product['id'] . '" class="btn btn-primary">View Details</a>
                </div>
            </div>
            <div class="product-info">
                <span class="product-category">' . h($product['category']) . '</span>
                <h3 class="product-name">' . h($product['name']) . '</h3>
                <span class="product-price">$' . number_format($product['price'], 2) . '</span>
            </div>
        </article>';
    }
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (PDOException $e) {
    error_log("Filter products error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
