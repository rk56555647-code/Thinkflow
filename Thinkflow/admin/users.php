<?php
$pageTitle = 'Manage Users';
include '../includes/auth.php';
include '../config/db.php';
requireAdmin();

if (isset($_GET['role']) && isset($_GET['uid'])) {
    $newRole = $_GET['role'] === 'admin' ? 'admin' : 'user';
    $uid = intval($_GET['uid']);
    if ($uid != getCurrentUserId()) {
        $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
        $stmt->bind_param("si", $newRole, $uid); $stmt->execute();
    }
    header("Location: users.php"); exit();
}
if (isset($_GET['delete'])) {
    $uid = intval($_GET['delete']);
    if ($uid != getCurrentUserId()) {
        $conn->query("DELETE FROM comments WHERE user_id=$uid");
        $conn->query("DELETE FROM likes WHERE user_id=$uid");
        $conn->query("DELETE FROM posts WHERE user_id=$uid");
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $uid); $stmt->execute();
    }
    header("Location: users.php"); exit();
}

$users = $conn->query("SELECT users.*, (SELECT COUNT(*) FROM posts WHERE posts.user_id=users.id) as post_count FROM users ORDER BY users.id");
include_once '../includes/avatar.php';
include '../includes/header.php';
?>

<div class="main-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="center-feed">
        <div class="feed-header"><i class="bi bi-people"></i> Manage Users</div>
        <div class="page-container">
            <div class="table-x">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>User</th><th>Email</th><th>Role</th><th>Posts</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php while ($u = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><div class="d-flex align-items-center gap-2"><div style="flex-shrink:0;"><?php renderAvatar($u, 'xs', '../'); ?></div><strong><?php echo htmlspecialchars($u['username']); ?></strong></div></td>
                            <td style="color:var(--text-muted);"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><span style="color:<?php echo $u['role']==='admin' ? 'var(--warning)' : 'var(--success)'; ?>; font-weight:700;"><?php echo ucfirst($u['role'] ?? 'user'); ?></span></td>
                            <td><?php echo $u['post_count']; ?></td>
                            <td>
                                <?php if ($u['id'] != getCurrentUserId()): ?>
                                    <?php if (($u['role'] ?? 'user') === 'user'): ?>
                                        <a href="users.php?uid=<?php echo $u['id']; ?>&role=admin" class="btn btn-ghost btn-sm" onclick="return confirm('Make admin?')"><i class="bi bi-shield-plus"></i></a>
                                    <?php else: ?>
                                        <a href="users.php?uid=<?php echo $u['id']; ?>&role=user" class="btn btn-ghost btn-sm" onclick="return confirm('Remove admin?')"><i class="bi bi-shield-minus"></i></a>
                                    <?php endif; ?>
                                    <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger-x btn-sm" onclick="return confirm('Delete user?')"><i class="bi bi-trash"></i></a>
                                <?php else: ?>
                                    <span style="color:var(--text-muted); font-size:0.8rem;">You</span>
                                <?php endif; ?>
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
