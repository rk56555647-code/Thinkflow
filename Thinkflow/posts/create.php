<?php
$pageTitle = 'Create Post';
include '../includes/auth.php';
include '../config/db.php';
include '../includes/csrf.php';
requireLogin();

$error = '';
if (isset($_POST['create'])) {
    requireValidCsrf();
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = getCurrentUserId();

    if (empty($title) || empty($content)) { $error = "Title and content are required."; }
    else {
        $image = '';
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) $error = "Only JPG, PNG, GIF, WEBP allowed.";
            elseif ($_FILES['image']['size'] > 5*1024*1024) $error = "Max 5MB.";
            else {
                $image = uniqid('post_', true) . '.' . $ext;
                if (!is_dir("../uploads")) mkdir("../uploads", 0755, true);
                if (!move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/".$image)) { $error = "Upload failed."; $image = ''; }
            }
        }
        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO posts (title, content, image, user_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $title, $content, $image, $user_id);
            if ($stmt->execute()) { header("Location: ../user/dashboard.php"); exit(); }
            else $error = "Failed to create post.";
        }
    }
}
include '../includes/header.php';
?>

<div class="main-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="center-feed">
        <div class="feed-header"><i class="bi bi-pencil-square"></i> Create Post</div>
        <div class="page-container">
            <?php if ($error): ?><div class="alert alert-x-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <div class="card p-4">
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Post Title</label>
                        <input type="text" name="title" class="form-control" placeholder="What's happening?" required
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Content</label>
                        <textarea name="content" class="form-control" rows="6" placeholder="Share your thoughts..." required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--text-secondary); font-weight:600;">
                            <i class="bi bi-image"></i> Image (optional)
                        </label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="create" class="btn btn-accent"><i class="bi bi-send"></i> Publish</button>
                        <a href="../index.php" class="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <div class="right-sidebar"></div>
</div>

<?php include '../includes/footer.php'; ?>