<?php
$pageTitle = 'Manage Posts';
include '../includes/auth.php';
include '../config/db.php';
requireAdmin();

$posts = $conn->query("SELECT posts.*, users.username,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id=posts.id) as like_count,
    (SELECT COUNT(*) FROM comments WHERE comments.post_id=posts.id) as comment_count
    FROM posts JOIN users ON posts.user_id=users.id ORDER BY posts.created_at DESC");
include '../includes/header.php';
?>

<div class="main-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="center-feed">
        <div class="feed-header"><i class="bi bi-journal-text"></i> Manage Posts</div>
        <div class="page-container">
            <div class="table-x">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Title</th><th>Author</th><th><i class="bi bi-heart-fill" style="color:var(--danger);"></i></th><th><i class="bi bi-chat-fill" style="color:var(--link);"></i></th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($row = $posts->fetch_assoc()): ?>
                        <tr>
                            <td><a href="../posts/view.php?id=<?php echo $row['id']; ?>" style="color:var(--text-primary); font-weight:600;"><?php echo htmlspecialchars($row['title']); ?></a></td>
                            <td style="color:var(--text-muted);"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo $row['like_count']; ?></td>
                            <td><?php echo $row['comment_count']; ?></td>
                            <td style="color:var(--text-muted); font-size:0.85rem;"><?php echo date('M d', strtotime($row['created_at'])); ?></td>
                            <td>
                                <a href="../posts/view.php?id=<?php echo $row['id']; ?>" class="btn btn-ghost btn-sm"><i class="bi bi-eye"></i></a>
                                <button class="btn btn-danger-x btn-sm" onclick="confirmDelete('../posts/delete.php?id=<?php echo $row['id']; ?>')"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <div class="right-sidebar"></div>
</div>

<?php include '../includes/footer.php'; ?>
