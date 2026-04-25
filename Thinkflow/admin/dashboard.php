<?php
$pageTitle = 'Admin Dashboard';
include '../includes/auth.php';
include '../config/db.php';
requireAdmin();

$totalUsers = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$totalPosts = $conn->query("SELECT COUNT(*) as c FROM posts")->fetch_assoc()['c'];
$totalComments = $conn->query("SELECT COUNT(*) as c FROM comments")->fetch_assoc()['c'];
$totalLikes = $conn->query("SELECT COUNT(*) as c FROM likes")->fetch_assoc()['c'];
$recentPosts = $conn->query("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id ORDER BY posts.created_at DESC LIMIT 5");

include '../includes/header.php';
?>

<div class="main-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="center-feed">
        <div class="feed-header"><i class="bi bi-shield-lock"></i> Admin Dashboard</div>
        <div class="page-container">
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-number"><?php echo $totalUsers; ?></div><div class="stat-label">Users</div></div></div>
                <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-number"><?php echo $totalPosts; ?></div><div class="stat-label">Posts</div></div></div>
                <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-number"><?php echo $totalComments; ?></div><div class="stat-label">Comments</div></div></div>
                <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-number"><?php echo $totalLikes; ?></div><div class="stat-label">Likes</div></div></div>
            </div>

            <div class="d-flex gap-2 mb-4">
                <a href="users.php" class="btn btn-accent"><i class="bi bi-people"></i> Manage Users</a>
                <a href="posts.php" class="btn btn-outline-accent"><i class="bi bi-journal-text"></i> Manage Posts</a>
            </div>

            <h5 style="font-weight:700; margin-bottom:1rem;">Recent Posts</h5>
            <div class="table-x">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Title</th><th>Author</th><th>Date</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($row = $recentPosts->fetch_assoc()): ?>
                        <tr>
                            <td style="font-weight:600;"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td style="color:var(--text-muted);"><?php echo htmlspecialchars($row['username']); ?></td>
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

    <?php include '../includes/right_sidebar.php'; ?>
</div>

<?php include '../includes/footer.php'; ?>
