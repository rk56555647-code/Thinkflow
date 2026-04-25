<?php
include '../includes/auth.php';
include '../config/db.php';

requireLogin();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../index.php");
    exit();
}

// Fetch the post to verify ownership
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    die("Post not found.");
}

// Only the owner or an admin can delete
if ($post['user_id'] != getCurrentUserId() && !isAdmin()) {
    die("Unauthorized access.");
}

// Delete associated likes
$stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Delete associated comments
$stmt = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Delete the image file if exists
if (!empty($post['image']) && file_exists("../uploads/" . $post['image'])) {
    unlink("../uploads/" . $post['image']);
}

// Delete the post
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../user/dashboard.php");
exit();
?>