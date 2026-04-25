<?php
$pageTitle = 'Login';
include '../includes/auth.php';
include '../config/db.php';
include '../includes/csrf.php';

if (isLoggedIn()) { header("Location: ../user/dashboard.php"); exit(); }

$error = '';
if (isset($_POST['login'])) {
    requireValidCsrf();
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        loginUser($user);
        header("Location: ../user/dashboard.php"); exit();
    } else {
        $error = "Invalid email or password.";
    }
}
include '../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <h1><img src="../assets/images/logo.png" alt="Thinkflow" style="width:36px; height:36px; border-radius:8px; vertical-align:middle;"> Thinkflow</h1>
        <p class="subtitle">Sign in to your account</p>

        <?php if ($error): ?>
            <div class="alert alert-x-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrfField(); ?>
            <div class="mb-3">
                <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Email</label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label" style="color:var(--text-secondary); font-weight:600;">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" name="login" class="btn btn-accent w-100 py-2" style="font-size:1rem;">Sign In</button>
        </form>
        <p class="auth-footer">Don't have an account? <a href="register.php">Create one</a></p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>