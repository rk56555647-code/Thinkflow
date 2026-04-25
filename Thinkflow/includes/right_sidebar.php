<?php
// Right sidebar widgets
// Variables $base, $conn should be available
?>
<aside class="right-sidebar">
    <!-- Trending Topics Widget -->
    <div class="widget">
        <h5><i class="bi bi-fire"></i> Trending</h5>
        <?php
        $trending = $conn->query("SELECT posts.title, posts.id, 
                                  (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as likes
                                  FROM posts ORDER BY likes DESC, posts.created_at DESC LIMIT 5");
        if ($trending && $trending->num_rows > 0):
            $i = 1;
            while ($t = $trending->fetch_assoc()):
        ?>
        <div class="widget-item">
            <div>
                <div class="topic-label">Trending #<?php echo $i++; ?></div>
                <a href="<?php echo $base; ?>posts/view.php?id=<?php echo $t['id']; ?>" class="topic-name" style="color:var(--text-primary);">
                    <?php echo htmlspecialchars(mb_substr($t['title'], 0, 40)); ?>
                </a>
                <div class="topic-count"><i class="bi bi-heart-fill" style="color:var(--danger);"></i> <?php echo $t['likes']; ?> likes</div>
            </div>
        </div>
        <?php endwhile; else: ?>
        <p style="color:var(--text-muted); font-size:0.85rem;">No trending posts yet.</p>
        <?php endif; ?>
    </div>

    <!-- Active Authors Widget -->
    <div class="widget">
        <h5><i class="bi bi-people-fill"></i> Active Authors</h5>
        <?php
        $authors = $conn->query("SELECT users.username, users.id, users.profile_photo,
                                 (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) as post_count
                                 FROM users 
                                 HAVING post_count > 0
                                 ORDER BY post_count DESC LIMIT 4");
        if ($authors && $authors->num_rows > 0):
            while ($a = $authors->fetch_assoc()):
        ?>
        <div class="widget-item">
            <div style="flex-shrink:0;">
                <?php renderAvatar($a, 'sm', $base); ?>
            </div>
            <div>
                <div style="font-weight:700; font-size:0.9rem;"><?php echo htmlspecialchars($a['username']); ?></div>
                <div style="font-size:0.75rem; color:var(--text-muted);"><?php echo $a['post_count']; ?> posts</div>
            </div>
        </div>
        <?php endwhile; else: ?>
        <p style="color:var(--text-muted); font-size:0.85rem;">No authors yet.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="footer-x" style="border:none; margin-top:0.5rem;">
        <p>&copy; <?php echo date('Y'); ?> Thinkflow</p>
    </div>
</aside>
