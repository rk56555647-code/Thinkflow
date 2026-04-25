<?php
$pageTitle = 'Profile';
include '../includes/auth.php';
include '../config/db.php';
include '../includes/csrf.php';
include '../includes/avatar.php';
requireLogin();

$userId = getCurrentUserId();
$user = getUserProfile($conn, $userId);
// Also get password hash for password change
$stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
$stmt->bind_param("i", $userId); $stmt->execute();
$userPwd = $stmt->get_result()->fetch_assoc()['password'];

$success = ''; $error = '';

// ===== Handle Profile Photo Upload =====
if (isset($_POST['upload_photo'])) {
    requireValidCsrf();
    if (!empty($_FILES['profile_photo']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG, GIF, WEBP images are allowed.";
        } elseif ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
            $error = "Photo must be less than 2MB.";
        } else {
            $avatarDir = "../uploads/avatars";
            if (!is_dir($avatarDir)) mkdir($avatarDir, 0755, true);
            
            // Delete old photo
            if (!empty($user['profile_photo']) && file_exists("$avatarDir/" . $user['profile_photo'])) {
                unlink("$avatarDir/" . $user['profile_photo']);
            }
            
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], "$avatarDir/$filename")) {
                $stmt = $conn->prepare("UPDATE users SET profile_photo=? WHERE id=?");
                $stmt->bind_param("si", $filename, $userId);
                $stmt->execute();
                $user['profile_photo'] = $filename;
                $_SESSION['profile_photo'] = $filename;
                $success = "Profile photo updated!";
            } else {
                $error = "Failed to upload photo.";
            }
        }
    }
}

// ===== Handle Remove Photo =====
if (isset($_POST['remove_photo'])) {
    requireValidCsrf();
    if (!empty($user['profile_photo'])) {
        $path = "../uploads/avatars/" . $user['profile_photo'];
        if (file_exists($path)) unlink($path);
        $stmt = $conn->prepare("UPDATE users SET profile_photo=NULL WHERE id=?");
        $stmt->bind_param("i", $userId); $stmt->execute();
        $user['profile_photo'] = '';
        $_SESSION['profile_photo'] = '';
        $success = "Photo removed.";
    }
}

// ===== Handle Profile Update =====
if (isset($_POST['update_profile'])) {
    requireValidCsrf();
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $twitter = trim($_POST['twitter'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');

    if (empty($username) || empty($email)) $error = "Username and email are required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Invalid email.";
    else {
        // Validate URLs if provided
        foreach (['website' => $website, 'github' => $github, 'linkedin' => $linkedin] as $field => $url) {
            if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
                $error = ucfirst($field) . " must be a valid URL.";
                break;
            }
        }
        if (empty($error)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE (email=? OR username=?) AND id!=?");
            $stmt->bind_param("ssi", $email, $username, $userId); $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) $error = "Username or email already taken.";
            else {
                $stmt = $conn->prepare("UPDATE users SET username=?, email=?, bio=?, website=?, github=?, twitter=?, linkedin=? WHERE id=?");
                $stmt->bind_param("sssssssi", $username, $email, $bio, $website, $github, $twitter, $linkedin, $userId);
                $stmt->execute();
                $_SESSION['username'] = $username; $_SESSION['email'] = $email;
                $user = getUserProfile($conn, $userId);
                $success = "Profile updated!";
            }
        }
    }
}

// ===== Handle Password Change =====
if (isset($_POST['change_password'])) {
    requireValidCsrf();
    $cur = $_POST['current_password']; $new = $_POST['new_password']; $conf = $_POST['confirm_password'];
    if (!password_verify($cur, $userPwd)) $error = "Current password is incorrect.";
    elseif (strlen($new) < 6) $error = "New password must be at least 6 characters.";
    elseif ($new !== $conf) $error = "Passwords do not match.";
    else {
        $h = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $h, $userId); $stmt->execute();
        $success = "Password changed successfully!";
    }
}

// Get user stats
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM posts WHERE user_id=?");
$stmt->bind_param("i", $userId); $stmt->execute();
$postCount = $stmt->get_result()->fetch_assoc()['c'];

include '../includes/header.php';
?>

<div class="main-layout">
    <?php include '../includes/sidebar.php'; ?>
    <main class="center-feed">
        <div class="feed-header"><i class="bi bi-person"></i> Profile</div>

        <!-- Banner -->
        <div class="profile-banner"></div>

        <!-- Profile Header -->
        <div class="profile-header-info">
            <?php if ($error): ?><div class="alert alert-x-error mt-3"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-x-success mt-3"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
                <!-- Avatar with upload overlay -->
                <div class="profile-avatar-wrapper">
                    <?php renderAvatar($user, 'xl', '../'); ?>
                    <label for="photo-input" class="avatar-upload-overlay">
                        <i class="bi bi-camera-fill"></i>
                    </label>
                </div>
                <div class="d-flex gap-2 mb-2">
                    <form method="POST" enctype="multipart/form-data" id="photo-form" style="display:none;">
                        <?php echo csrfField(); ?>
                        <input type="file" name="profile_photo" id="photo-input" accept="image/*" 
                               onchange="document.getElementById('photo-form').submit();">
                        <input type="hidden" name="upload_photo" value="1">
                    </form>
                    <?php if (!empty($user['profile_photo'])): ?>
                    <form method="POST" style="display:inline;">
                        <?php echo csrfField(); ?>
                        <button type="submit" name="remove_photo" class="btn btn-ghost btn-sm" 
                                onclick="return confirm('Remove profile photo?')">
                            <i class="bi bi-x-lg"></i> Remove Photo
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Name & Handle -->
            <h2 style="font-weight:800; margin:0.8rem 0 0;"><?php echo htmlspecialchars($user['username']); ?></h2>
            <div style="color:var(--text-muted);">@<?php echo strtolower($user['username']); ?></div>

            <!-- Bio -->
            <?php if (!empty($user['bio'])): ?>
                <div class="profile-bio"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></div>
            <?php endif; ?>

            <!-- Meta info -->
            <div class="profile-meta">
                <span><i class="bi bi-shield-check"></i> <?php echo ucfirst($user['role'] ?? 'user'); ?></span>
                <span><i class="bi bi-calendar3"></i> Joined <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                <span><i class="bi bi-file-text"></i> <?php echo $postCount; ?> posts</span>
            </div>

            <!-- Social Links -->
            <?php if (!empty($user['website']) || !empty($user['github']) || !empty($user['twitter']) || !empty($user['linkedin'])): ?>
            <div class="social-links">
                <?php if (!empty($user['website'])): ?>
                    <a href="<?php echo htmlspecialchars($user['website']); ?>" class="social-link" target="_blank" rel="noopener">
                        <i class="bi bi-globe2"></i> Website
                    </a>
                <?php endif; ?>
                <?php if (!empty($user['github'])): ?>
                    <a href="<?php echo htmlspecialchars($user['github']); ?>" class="social-link" target="_blank" rel="noopener">
                        <i class="bi bi-github"></i> GitHub
                    </a>
                <?php endif; ?>
                <?php if (!empty($user['twitter'])): ?>
                    <a href="https://x.com/<?php echo htmlspecialchars($user['twitter']); ?>" class="social-link" target="_blank" rel="noopener">
                        <i class="bi bi-twitter-x"></i> @<?php echo htmlspecialchars($user['twitter']); ?>
                    </a>
                <?php endif; ?>
                <?php if (!empty($user['linkedin'])): ?>
                    <a href="<?php echo htmlspecialchars($user['linkedin']); ?>" class="social-link" target="_blank" rel="noopener">
                        <i class="bi bi-linkedin"></i> LinkedIn
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="page-container" style="padding-top:1rem;">
            <!-- Edit Profile -->
            <div class="card p-4 mb-4">
                <h5 style="font-weight:700; margin-bottom:1rem;"><i class="bi bi-pencil-square"></i> Edit Profile</h5>
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Username</label>
                            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Bio</label>
                        <textarea name="bio" class="form-control" rows="3" placeholder="Tell us about yourself..." style="border-radius:16px;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>

                    <h6 style="font-weight:700; margin:1.2rem 0 0.8rem; color:var(--text-primary);">
                        <i class="bi bi-link-45deg"></i> External Links
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color:var(--text-secondary); font-weight:600;">
                                <i class="bi bi-globe2"></i> Website
                            </label>
                            <input type="url" name="website" class="form-control" placeholder="https://yoursite.com" 
                                   value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color:var(--text-secondary); font-weight:600;">
                                <i class="bi bi-github"></i> GitHub
                            </label>
                            <input type="url" name="github" class="form-control" placeholder="https://github.com/username" 
                                   value="<?php echo htmlspecialchars($user['github'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color:var(--text-secondary); font-weight:600;">
                                <i class="bi bi-twitter-x"></i> X (Twitter) Handle
                            </label>
                            <input type="text" name="twitter" class="form-control" placeholder="username (without @)" 
                                   value="<?php echo htmlspecialchars($user['twitter'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color:var(--text-secondary); font-weight:600;">
                                <i class="bi bi-linkedin"></i> LinkedIn
                            </label>
                            <input type="url" name="linkedin" class="form-control" placeholder="https://linkedin.com/in/username" 
                                   value="<?php echo htmlspecialchars($user['linkedin'] ?? ''); ?>">
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-accent mt-3">Save Changes</button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="card p-4">
                <h5 style="font-weight:700; margin-bottom:1rem;"><i class="bi bi-lock"></i> Change Password</h5>
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="color:var(--text-secondary); font-weight:600;">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Confirm</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-accent mt-3">Change Password</button>
                </form>
            </div>
        </div>
    </main>
    <div class="right-sidebar"></div>
</div>

<?php include '../includes/footer.php'; ?>
