<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "login_required";
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($post_id <= 0) {
    echo "invalid";
    exit();
}

if (empty($comment)) {
    echo "empty_comment";
    exit();
}

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $post_id, $user_id, $comment);
$stmt->execute();

echo "success";
?>