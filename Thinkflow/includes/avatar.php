<?php
/**
 * Render a user avatar (profile photo or gradient initial)
 * @param array  $user  User data array (needs 'username' and optionally 'profile_photo')
 * @param string $size  CSS class suffix: 'sm' (40px), 'md' (48px), 'lg' (80px), 'xl' (120px)
 * @param string $base  Base URL path for image src
 */
function renderAvatar($user, $size = 'md', $base = '') {
    $username = $user['username'] ?? 'U';
    $photo = $user['profile_photo'] ?? '';
    $initial = strtoupper(substr($username, 0, 1));

    $sizeClass = "avatar-$size";

    if (!empty($photo)) {
        echo '<img src="' . $base . 'uploads/avatars/' . htmlspecialchars($photo) . '" alt="' . htmlspecialchars($username) . '" class="avatar-img ' . $sizeClass . '">';
    } else {
        echo '<div class="avatar-initial ' . $sizeClass . '">' . $initial . '</div>';
    }
}

/**
 * Get user profile data by ID
 */
function getUserProfile($conn, $userId) {
    $stmt = $conn->prepare("SELECT id, username, email, role, profile_photo, bio, website, github, twitter, linkedin, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}
?>
