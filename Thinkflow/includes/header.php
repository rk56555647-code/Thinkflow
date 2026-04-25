<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine base URL dynamically
$base = '';
if (strpos($_SERVER['SCRIPT_NAME'], '/auth/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/posts/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/user/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false ||
    strpos($_SERVER['SCRIPT_NAME'], '/api/') !== false) {
    $base = '../';
}

// Detect current page for active nav
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Thinkflow — A modern content platform for sharing ideas and stories">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — Thinkflow' : 'Thinkflow — Share Your Ideas'; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>
<body>
    <!-- Top Navbar -->
    <nav class="top-navbar">
        <div class="container-fluid d-flex align-items-center justify-content-between" style="max-width:1280px; margin:0 auto;">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo $base; ?>index.php">
                <img src="<?php echo $base; ?>assets/images/logo.png" alt="Thinkflow" style="width:28px; height:28px; border-radius:6px;"> Thinkflow
            </a>

            <div class="d-flex align-items-center gap-2">
                <!-- Search -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <form class="d-none d-md-flex" method="GET" action="<?php echo $base; ?>search.php">
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Search posts..." 
                           value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" style="width:220px;">
                </form>
                <?php endif; ?>

                <!-- Theme Toggle -->
                <div class="theme-toggle" id="themeToggle" title="Toggle dark/light mode">
                    <div class="toggle-icons">
                        <span><i class="bi bi-moon-fill"></i></span>
                        <span><i class="bi bi-sun-fill"></i></span>
                    </div>
                    <div class="toggle-thumb"></div>
                </div>

                <!-- Auth Links -->
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $base; ?>user/profile.php" class="btn btn-ghost btn-sm d-none d-md-inline-flex">
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Profile'); ?>
                    </a>
                    <a href="<?php echo $base; ?>auth/logout.php" class="btn btn-outline-accent btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo $base; ?>auth/login.php" class="btn btn-ghost btn-sm">Login</a>
                    <a href="<?php echo $base; ?>auth/register.php" class="btn btn-accent btn-sm">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
