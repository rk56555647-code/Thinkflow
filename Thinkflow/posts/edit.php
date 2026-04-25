<?php
$pageTitle = 'Edit Post';
include '../includes/auth.php';
include '../config/db.php';
include '../includes/csrf.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: ../index.php"); exit(); }

$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) die("Post not found.");
if ($post['user_id'] != getCurrentUserId()) die("Unauthorized.");

$error = '';
if (isset($_POST['update'])) {
    requireValidCsrf();
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    if (empty($title) || empty($content)) $error = "Title and content required.";
    else {
        $image = $post['image'];
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 5*1024*1024) {
                $newImg = uniqid('post_', true) . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$newImg)) {
                    if (!empty($post['image']) && file_exists("../uploads/".$post['image'])) unlink("../uploads/".$post['image']);
                    $image = $newImg;
                }
            }
        }
        $stmt = $conn->prepare("UPDATE posts SET title=?, content=?, image=? WHERE id=?");
        $stmt->bind_param("sssi", $title, $content, $image, $id);
        if ($stmt->execute()) { header("Location: ../user/dashboard.php"); exit(); }
        else $error = "Update failed.";
    }
}
include '../includes/header.php';
?>

<div class="main-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="center-feed">
        <div class="feed-header"><i class="bi bi-pencil"></i> Edit Post</div>
        <div class="page-container">
            <?php if ($error): ?><div class="alert alert-x-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <div class="card p-4">
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Title</label>
                        <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($post['title']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Content</label>
                        <textarea name="content" class="form-control" rows="6" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                    </div>
                    <?php if (!empty($post['image'])): ?>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--text-secondary);">Current Image</label>
                        <img src="../uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Current" style="max-height:200px; border-radius:16px; display:block;">
                    </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--text-secondary); font-weight:600;"><i class="bi bi-image"></i> Replace Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="update" class="btn btn-accent"><i class="bi bi-check-lg"></i> Update</button>
                        <a href="../user/dashboard.php" class="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <div class="right-sidebar"></div>
</div>

<?php include '../includes/footer.php'; ?>