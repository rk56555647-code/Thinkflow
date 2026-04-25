<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "login_required";
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);

if ($post_id <= 0) {
    echo "invalid";
    exit();
}

// Check if already liked
$stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Unlike
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    echo "unliked";
} else {
    // Like
    $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    echo "liked";
}
?>