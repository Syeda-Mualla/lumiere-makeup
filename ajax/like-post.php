<?php
/**
 * AJAX: Like/Unlike Community Post
 * LUMIÈRE - Luxury Makeup Brand
 * 
 * Security: Authentication required, prepared statements
 */

header('Content-Type: application/json');

session_start();
require_once '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Login required'
    ]);
    exit;
}

$postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
$userId = $_SESSION['user_id'];

if (!$postId) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid post'
    ]);
    exit;
}

try {
    // Check if post exists
    $stmt = $pdo->prepare("SELECT id, likes FROM community WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        echo json_encode([
            'success' => false,
            'message' => 'Post not found'
        ]);
        exit;
    }
    
    // Check if user already liked
    $stmt = $pdo->prepare("SELECT id FROM community_likes WHERE user_id = ? AND community_id = ?");
    $stmt->execute([$userId, $postId]);
    $existingLike = $stmt->fetch();
    
    $pdo->beginTransaction();
    
    if ($existingLike) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM community_likes WHERE user_id = ? AND community_id = ?");
        $stmt->execute([$userId, $postId]);
        
        $stmt = $pdo->prepare("UPDATE community SET likes = likes - 1 WHERE id = ? AND likes > 0");
        $stmt->execute([$postId]);
        
        $liked = false;
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO community_likes (user_id, community_id) VALUES (?, ?)");
        $stmt->execute([$userId, $postId]);
        
        $stmt = $pdo->prepare("UPDATE community SET likes = likes + 1 WHERE id = ?");
        $stmt->execute([$postId]);
        
        $liked = true;
    }
    
    $pdo->commit();
    
    // Get updated like count
    $stmt = $pdo->prepare("SELECT likes FROM community WHERE id = ?");
    $stmt->execute([$postId]);
    $newLikes = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'likes' => $newLikes
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Like error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred'
    ]);
}
