<?php
// Left Sidebar Navigation (X-style)
// Variables $base, $currentPage, $currentDir should be set by header.php
include_once(__DIR__ . '/avatar.php');

// Get current user's profile photo for sidebar
$sidebarUser = null;
if (isset($_SESSION['user_id'])) {
    $sidebarUser = ['username' => $_SESSION['username'] ?? 'U', 'profile_photo' => $_SESSION['profile_photo'] ?? ''];
}
?>
<aside class="left-sidebar">
    <ul class="sidebar-nav">
        <li><a href="<?php echo $base; ?>index.php" class="<?php echo ($currentPage === 'index' && $currentDir !== 'admin') ? 'active' : ''; ?>">
            <span class="nav-icon"><i class="bi bi-house-door-fill"></i></span> Home
        </a></li>
        <li><a href="<?php echo $base; ?>search.php">
            <span class="nav-icon"><i class="bi bi-search"></i></span> Explore
        </a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
        <li><a href="<?php echo $base; ?>posts/create.php" class="<?php echo $currentPage === 'create' ? 'active' : ''; ?>">
            <span class="nav-icon"><i class="bi bi-plus-circle"></i></span> Create Post
        </a></li>
        <li><a href="<?php echo $base; ?>user/dashboard.php" class="<?php echo ($currentPage === 'dashboard' && $currentDir === 'user') ? 'active' : ''; ?>">
            <span class="nav-icon"><i class="bi bi-grid"></i></span> Dashboard
        </a></li>
        <li><a href="<?php echo $base; ?>user/profile.php" class="<?php echo $currentPage === 'profile' ? 'active' : ''; ?>">
            <span class="nav-icon"><i class="bi bi-person"></i></span> Profile
        </a></li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <li><a href="<?php echo $base; ?>admin/dashboard.php" class="<?php echo ($currentPage === 'dashboard' && $currentDir === 'admin') ? 'active' : ''; ?>">
            <span class="nav-icon"><i class="bi bi-shield-lock"></i></span> Admin
        </a></li>
        <?php endif; ?>
        <?php endif; ?>
    </ul>

    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="<?php echo $base; ?>posts/create.php" class="btn btn-create-post">
        <i class="bi bi-pencil-square"></i> Create Post
    </a>
    <?php else: ?>
    <a href="<?php echo $base; ?>auth/register.php" class="btn btn-create-post">
        <i class="bi bi-person-plus"></i> Get Started
    </a>
    <?php endif; ?>

    <!-- User Info at bottom -->
    <?php if (isset($_SESSION['user_id']) && $sidebarUser): ?>
    <div class="d-flex align-items-center gap-2 mt-4 p-2" style="border-radius:9999px;">
        <?php renderAvatar($sidebarUser, 'sm', $base); ?>
        <div style="min-width:0;">
            <div style="font-weight:700; font-size:0.85rem;" class="text-truncate"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></div>
            <div style="font-size:0.75rem; color:var(--text-muted);" class="text-truncate">@<?php echo strtolower($_SESSION['username'] ?? ''); ?></div>
        </div>
    </div>
    <?php endif; ?>
</aside>
