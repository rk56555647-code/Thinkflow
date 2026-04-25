<?php
include '../includes/auth.php';
include '../config/db.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header("Location: ../index.php"); exit(); }

$stmt = $conn->prepare("SELECT posts.*, users.username, users.profile_photo FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
if (!$post) die("Post not found.");

$pageTitle = $post['title'];

$ls = $conn->prepare("SELECT COUNT(*) as total FROM likes WHERE post_id = ?");
$ls->bind_param("i", $id); $ls->execute();
$like_count = $ls->get_result()->fetch_assoc()['total'];

$userLiked = false;
if (isLoggedIn()) {
    $ul = $conn->prepare("SELECT id FROM likes WHERE user_id=? AND post_id=?");
    $ul->bind_param("ii", $_SESSION['user_id'], $id); $ul->execute();
    $userLiked = $ul->get_result()->num_rows > 0;
}

$cs = $conn->prepare("SELECT comments.*, users.username, users.profile_photo FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at DESC");
$cs->bind_param("i", $id); $cs->execute();
$comments = $cs->get_result();

include_once '../includes/avatar.php';
include '../includes/header.php';
?>

<div class="main-layout">
    <?php include '../includes/sidebar.php'; ?>

    <main class="center-feed">
        <div class="feed-header">
            <a href="../index.php" style="color:var(--text-primary); text-decoration:none;">
                <i class="bi bi-arrow-left"></i> Post
            </a>
        </div>

        <div class="single-post-view">
            <!-- Author header -->
            <div class="d-flex gap-3 align-items-center mb-3">
                <div style="flex-shrink:0;">
                    <?php renderAvatar($post, 'md', '../'); ?>
                </div>
                <div>
                    <div class="post-author"><?php echo htmlspecialchars($post['username']); ?></div>
                    <div class="post-handle">@<?php echo strtolower($post['username']); ?></div>
                </div>
            </div>

            <h1><?php echo htmlspecialchars($post['title']); ?></h1>

            <div class="post-body"><?php echo nl2br(htmlspecialchars($post['content'])); ?></div>

            <?php if (!empty($post['image'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post image" 
                     style="width:100%; max-height:500px; object-fit:cover; border-radius:16px; border:1px solid var(--border-color);">
            <?php endif; ?>

            <div style="padding:0.8rem 0; border-top:1px solid var(--border-color); border-bottom:1px solid var(--border-color); margin:1rem 0; color:var(--text-muted); font-size:0.85rem;">
                <?php echo date('h:i A · M d, Y', strtotime($post['created_at'])); ?>
            </div>

            <!-- Stats -->
            <div style="padding:0.8rem 0; border-bottom:1px solid var(--border-color); display:flex; gap:1.5rem; font-size:0.9rem;">
                <span><strong><?php echo $like_count; ?></strong> <span style="color:var(--text-muted);">Likes</span></span>
                <span><strong><?php echo $comments->num_rows; ?></strong> <span style="color:var(--text-muted);">Comments</span></span>
            </div>

            <!-- Actions -->
            <div class="post-actions-bar" style="max-width:100%; border-bottom:1px solid var(--border-color); padding:0.5rem 0;">
                <button class="action-btn comment-btn"><i class="bi bi-chat"></i></button>
                <button class="action-btn like-btn <?php echo $userLiked ? 'liked' : ''; ?>" onclick="likePost(<?php echo $id; ?>, this)">
                    <i class="bi bi-heart<?php echo $userLiked ? '-fill' : ''; ?>"></i> <span id="like-count-<?php echo $id; ?>"><?php echo $like_count; ?></span>
                </button>
                <?php if (isLoggedIn() && $_SESSION['user_id'] == $post['user_id']): ?>
                <a href="edit.php?id=<?php echo $id; ?>" class="action-btn"><i class="bi bi-pencil"></i></a>
                <button class="action-btn" onclick="confirmDelete('delete.php?id=<?php echo $id; ?>')" style="color:var(--danger);"><i class="bi bi-trash"></i></button>
                <?php endif; ?>
            </div>

            <!-- Comment Form -->
            <?php if (isLoggedIn()): ?>
            <div class="d-flex gap-3 py-3" style="border-bottom:1px solid var(--border-color);">
                <div style="flex-shrink:0;">
                    <?php 
                    $currentUser = ['username' => $_SESSION['username'], 'profile_photo' => $_SESSION['profile_photo'] ?? ''];
                    renderAvatar($currentUser, 'sm', '../'); 
                    ?>
                </div>
                <div style="flex:1;">
                    <textarea id="new-comment" class="form-control mb-2" rows="2" placeholder="Post your reply..." 
                              style="border-radius:16px;"></textarea>
                    <button class="btn btn-accent btn-sm" onclick="postComment(<?php echo $id; ?>)">Reply</button>
                </div>
            </div>
            <?php else: ?>
            <p style="padding:1rem 0; color:var(--text-muted);"><a href="../auth/login.php">Login</a> to leave a reply.</p>
            <?php endif; ?>

            <!-- Comments List -->
            <?php while ($c = $comments->fetch_assoc()): ?>
            <div class="d-flex gap-3 py-3" style="border-bottom:1px solid var(--border-color);">
                <div style="flex-shrink:0;">
                    <?php renderAvatar($c, 'xs', '../'); ?>
                </div>
                <div>
                    <div class="d-flex gap-2 align-items-center">
                        <span style="font-weight:700; font-size:0.85rem;"><?php echo htmlspecialchars($c['username']); ?></span>
                        <span style="color:var(--text-muted); font-size:0.8rem;">· <?php echo date('M d', strtotime($c['created_at'])); ?></span>
                    </div>
                    <div style="font-size:0.9rem; color:var(--text-secondary); margin-top:0.2rem;"><?php echo htmlspecialchars($c['comment']); ?></div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <?php include '../includes/right_sidebar.php'; ?>
</div>

<?php include '../includes/footer.php'; ?>