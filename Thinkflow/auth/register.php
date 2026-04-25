<?php
$pageTitle = 'Register';
include '../includes/auth.php';
include '../config/db.php';
include '../includes/csrf.php';

if (isLoggedIn()) { header("Location: ../user/dashboard.php"); exit(); }

$error = ''; $success = '';
if (isset($_POST['register'])) {
    requireValidCsrf();
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (strlen($username) < 3) $error = "Username must be at least 3 characters.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "Invalid email.";
    elseif (strlen($password) < 6) $error = "Password must be at least 6 characters.";
    elseif ($password !== $confirm) $error = "Passwords do not match.";
    else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email); $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $error = "Email already exists.";
        else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username); $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) $error = "Username taken.";
            else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $role = 'user';
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $username, $email, $hashed, $role);
                if ($stmt->execute()) $success = "Account created! You can now log in.";
                else $error = "Registration failed.";
            }
        }
    }
}
include '../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h1><img src="../assets/images/logo.png" alt="Thinkflow" style="width:36px; height:36px; border-radius:8px; vertical-align:middle;"> Thinkflow</h1>
        <p class="subtitle">Join the Thinkflow community</p>

        <?php if ($error): ?><div class="alert alert-x-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-x-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <form method="POST">
            <?php echo csrfField(); ?>
            <div class="mb-3">
                <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required minlength="3"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Email</label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
            </div>
            <button type="submit" name="register" class="btn btn-accent w-100 py-2" style="font-size:1rem;">Create Account</button>
        </form>
        <p class="auth-footer">Already have an account? <a href="login.php">Sign in</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>