<?php
$pageTitle = 'Home';
include 'config/db.php';
include 'includes/auth.php';

// Fetch all posts with authors and counts
$result = $conn->query("SELECT posts.*, users.username, users.profile_photo,
                        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as like_count,
                        (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) as comment_count
                        FROM posts 
                        JOIN users ON posts.user_id = users.id 
                        ORDER BY posts.created_at DESC");

include_once 'includes/avatar.php';
include 'includes/header.php';
?>

<div class="main-layout">
    <!-- Left Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Center Feed -->
    <main class="center-feed">
        <div class="feed-header">
            <i class="bi bi-house-door"></i> Home
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                // Check if current user liked this post
                $userLiked = false;
                if (isLoggedIn()) {
                    $ls = $conn->prepare("SELECT id FROM likes WHERE user_id=? AND post_id=?");
                    $ls->bind_param("ii", $_SESSION['user_id'], $row['id']);
                    $ls->execute();
                    $userLiked = $ls->get_result()->num_rows > 0;
                }

                // Get recent comments
                $cs = $conn->prepare("SELECT comments.*, users.username, users.profile_photo FROM comments 
                                      JOIN users ON comments.user_id = users.id 
                                      WHERE comments.post_id = ? ORDER BY comments.created_at DESC LIMIT 2");
                $cs->bind_param("i", $row['id']);
                $cs->execute();
                $comments = $cs->get_result();
                ?>
                <div class="post-card">
                    <div class="d-flex gap-3">
                        <!-- Avatar -->
                        <div style="flex-shrink:0;">
                            <?php renderAvatar($row, 'md', ''); ?>
                        </div>

                        <!-- Content -->
                        <div style="flex:1; min-width:0;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="post-author"><?php echo htmlspecialchars($row['username']); ?></span>
                                <span class="post-handle">@<?php echo htmlspecialchars(strtolower($row['username'])); ?></span>
                                <span class="post-time">· <?php echo date('M d', strtotime($row['created_at'])); ?></span>
                            </div>

                            <div class="post-title">
                                <a href="posts/view.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a>
                            </div>
                            <div class="post-text">
                                <?php echo htmlspecialchars(mb_substr($row['content'], 0, 200)) . (mb_strlen($row['content']) > 200 ? '...' : ''); ?>
                            </div>

                            <?php if (!empty($row['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Post image" class="post-image">
                            <?php endif; ?>

                            <!-- Action Bar -->
                            <div class="post-actions-bar">
                                <button class="action-btn comment-btn" onclick="document.getElementById('comment-input-<?php echo $row['id']; ?>').focus()">
                                    <i class="bi bi-chat"></i> <span id="comment-count-<?php echo $row['id']; ?>"><?php echo $row['comment_count']; ?></span>
                                </button>
                                <button class="action-btn like-btn <?php echo $userLiked ? 'liked' : ''; ?>" onclick="likePost(<?php echo $row['id']; ?>, this)">
                                    <i class="bi bi-heart<?php echo $userLiked ? '-fill' : ''; ?>"></i> <span id="like-count-<?php echo $row['id']; ?>"><?php echo $row['like_count']; ?></span>
                                </button>
                                <a href="posts/view.php?id=<?php echo $row['id']; ?>" class="action-btn">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                                <?php if (isLoggedIn() && $_SESSION['user_id'] == $row['user_id']): ?>
                                <a href="posts/edit.php?id=<?php echo $row['id']; ?>" class="action-btn">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button class="action-btn" onclick="confirmDelete('posts/delete.php?id=<?php echo $row['id']; ?>')" style="color:var(--danger);">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>

                            <!-- Comments -->
                            <div id="comment-list-<?php echo $row['id']; ?>">
                                <?php while ($c = $comments->fetch_assoc()): ?>
                                <div class="comment-item">
                                    <div style="flex-shrink:0;">
                                        <?php renderAvatar($c, 'xs', ''); ?>
                                    </div>
                                    <div>
                                        <div class="comment-author"><?php echo htmlspecialchars($c['username']); ?></div>
                                        <div class="comment-text"><?php echo htmlspecialchars($c['comment']); ?></div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>

                            <?php if (isLoggedIn()): ?>
                            <div class="comment-input-row">
                                <input type="text" id="comment-input-<?php echo $row['id']; ?>" placeholder="Post your reply...">
                                <button class="btn btn-accent btn-sm" onclick="addComment(<?php echo $row['id']; ?>)">Reply</button>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-journal-text" style="font-size:3rem; color:var(--accent);"></i>
                <h2>No posts yet</h2>
                <p>Be the first to share something!</p>
                <?php if (isLoggedIn()): ?>
                    <a href="posts/create.php" class="btn btn-accent mt-3">Create Post</a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-accent mt-3">Get Started</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Right Sidebar -->
    <?php include 'includes/right_sidebar.php'; ?>
</div>

<!-- Floating Create Button (mobile) -->
<?php if (isLoggedIn()): ?>
<a href="posts/create.php" class="fab-create"><i class="bi bi-plus-lg"></i></a>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>