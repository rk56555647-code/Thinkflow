<?php
$pageTitle = 'Dashboard';
include '../includes/auth.php';
include '../config/db.php';
requireLogin();

$userId = getCurrentUserId();
$stmt = $conn->prepare("SELECT posts.*, 
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as like_count,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) as comment_count
    FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId); $stmt->execute();
$posts = $stmt->get_result();
$totalPosts = $posts->num_rows;

$stmt = $conn->prepare("SELECT COUNT(*) as t FROM likes WHERE post_id IN (SELECT id FROM posts WHERE user_id=?)");
$stmt->bind_param("i", $userId); $stmt->execute();
$totalLikes = $stmt->get_result()->fetch_assoc()['t'];

$stmt = $conn->prepare("SELECT COUNT(*) as t FROM comments WHERE post_id IN (SELECT id FROM posts WHERE user_id=?)");
$stmt->bind_param("i", $userId); $stmt->execute();
$totalComments = $stmt->get_result()->fetch_assoc()['t'];

include '../includes/header.php';
?>

<div class="main-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="center-feed">
        <div class="feed-header"><i class="bi bi-grid"></i> Dashboard</div>
        <div class="page-container">
            <div class="row g-3 mb-4">
                <div class="col-4"><div class="stat-card"><div class="stat-number"><?php echo $totalPosts; ?></div><div class="stat-label">Posts</div></div></div>
                <div class="col-4"><div class="stat-card"><div class="stat-number"><?php echo $totalLikes; ?></div><div class="stat-label">Likes</div></div></div>
                <div class="col-4"><div class="stat-card"><div class="stat-number"><?php echo $totalComments; ?></div><div class="stat-label">Comments</div></div></div>
            </div>

            <div class="page-header-x">
                <h1 style="font-size:1.2rem;">My Posts</h1>
                <a href="../posts/create.php" class="btn btn-accent btn-sm"><i class="bi bi-plus"></i> New</a>
            </div>

            <?php if ($totalPosts > 0): ?>
            <div class="table-x">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Title</th><th><i class="bi bi-heart-fill" style="color:var(--danger);"></i></th><th><i class="bi bi-chat-fill" style="color:var(--link);"></i></th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($row = $posts->fetch_assoc()): ?>
                        <tr>
                            <td><a href="../posts/view.php?id=<?php echo $row['id']; ?>" style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($row['title']); ?></a></td>
                            <td><?php echo $row['like_count']; ?></td>
                            <td><?php echo $row['comment_count']; ?></td>
                            <td style="color:var(--text-muted); font-size:0.85rem;"><?php echo date('M d', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="../posts/edit.php?id=<?php echo $row['id']; ?>" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-danger-x btn-sm" onclick="confirmDelete('../posts/delete.php?id=<?php echo $row['id']; ?>')"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state"><h2>No posts yet</h2><p>Create your first post!</p></div>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../includes/right_sidebar.php'; ?>
</div>

<?php include '../includes/footer.php'; ?>