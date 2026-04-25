<?php
$pageTitle = 'Search';
include 'config/db.php';
include 'includes/auth.php';

$query = trim($_GET['q'] ?? '');
$results = null;
if (!empty($query)) {
    $searchTerm = '%' . $query . '%';
    $stmt = $conn->prepare("SELECT posts.*, users.username, users.profile_photo,
                            (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as like_count,
                            (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) as comment_count
                            FROM posts JOIN users ON posts.user_id = users.id 
                            WHERE posts.title LIKE ? OR posts.content LIKE ?
                            ORDER BY posts.created_at DESC");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $results = $stmt->get_result();
}

include_once 'includes/avatar.php';
include 'includes/header.php';
?>

<div class="main-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="center-feed">
        <div class="feed-header"><i class="bi bi-search"></i> Explore</div>

        <!-- Search Form -->
        <div style="padding:1rem; border-bottom:1px solid var(--border-color);">
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" class="form-control" placeholder="Search posts by title or content..." 
                       value="<?php echo htmlspecialchars($query); ?>">
                <button class="btn btn-accent" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>

        <?php if (empty($query)): ?>
            <div class="empty-state">
                <i class="bi bi-search" style="font-size:3rem; color:var(--accent);"></i>
                <h2>Explore Posts</h2>
                <p>Use the search bar to find posts by title or content.</p>
            </div>
        <?php elseif ($results && $results->num_rows > 0): ?>
            <div style="padding:0.8rem 1rem; color:var(--text-muted); font-size:0.85rem; border-bottom:1px solid var(--border-color);">
                Found <?php echo $results->num_rows; ?> result(s) for "<strong style="color:var(--text-primary);"><?php echo htmlspecialchars($query); ?></strong>"
            </div>
            <?php while ($row = $results->fetch_assoc()): ?>
            <div class="post-card">
                <div class="d-flex gap-3">
                    <div style="flex-shrink:0;">
                        <?php renderAvatar($row, 'md', ''); ?>
                    </div>
                    <div style="flex:1; min-width:0;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="post-author"><?php echo htmlspecialchars($row['username']); ?></span>
                            <span class="post-handle">@<?php echo strtolower($row['username']); ?></span>
                            <span class="post-time">· <?php echo date('M d', strtotime($row['created_at'])); ?></span>
                        </div>
                        <div class="post-title"><a href="posts/view.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></div>
                        <div class="post-text"><?php echo htmlspecialchars(mb_substr($row['content'], 0, 200)); ?></div>
                        <div class="post-actions-bar">
                            <span class="action-btn"><i class="bi bi-chat"></i> <?php echo $row['comment_count']; ?></span>
                            <span class="action-btn"><i class="bi bi-heart"></i> <?php echo $row['like_count']; ?></span>
                            <a href="posts/view.php?id=<?php echo $row['id']; ?>" class="btn btn-accent btn-sm">Read</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-emoji-frown" style="font-size:3rem; color:var(--text-muted);"></i>
                <h2>No results</h2>
                <p>Nothing matches "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
            </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/right_sidebar.php'; ?>
</div>

<?php include 'includes/footer.php'; ?>
