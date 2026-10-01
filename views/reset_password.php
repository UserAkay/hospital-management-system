<?php
session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$token = $_GET['token'] ?? '';

if (!$token) {
    die("Invalid or missing link.");
}

$stmt = $conn->prepare("
    SELECT pr.id, pr.user_id, pr.expires_at, u.username 
    FROM password_resets pr 
    JOIN users u ON pr.user_id = u.user_id 
    WHERE token = :token
");
$stmt->execute([':token' => $token]);
$reset = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reset) {
    die("Invalid or already used token.");
}

if (strtotime($reset['expires_at']) < time()) {
    die("This reset link has expired. Please request a new one.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm']  ?? '';

    if (!$password || !$confirm) {
        $_SESSION['error'] = "Please fill in both password fields.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    if ($password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match. Please try again.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = :pass WHERE user_id = :uid");
    $stmt->execute([':pass' => $hash, ':uid' => $reset['user_id']]);

    $stmt = $conn->prepare("DELETE FROM password_resets WHERE id = :id");
    $stmt->execute([':id' => $reset['id']]);

    $_SESSION['success'] = "Your password has been reset successfully! You can now log in.";
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin:0; padding:0; font-family:'Poppins', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height:100vh; display:flex; align-items:center; justify-content:center;">

    <div style="background:white; width:100%; max-width:440px; margin:20px; border-radius:16px; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.25);">

        <!-- Header -->
        <div style="background:#4f46e5; color:white; padding:32px 40px; text-align:center;">
            <h1 style="margin:0; font-size:28px; font-weight:600;">Reset Password</h1>
            <p style="margin:12px 0 4px; opacity:0.95; font-size:15px;">
                for <strong><?= htmlspecialchars($reset['username']) ?></strong>
            </p>
            <p style="margin:8px 0 0; font-size:14px; opacity:0.85;">
                Choose a strong new password
            </p>
        </div>

        <!-- Content -->
        <div style="padding:40px 36px 44px;">

            <?php if(isset($_SESSION['error'])): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:14px 18px; border-radius:10px; margin-bottom:28px; font-size:15px; border-left:5px solid #ef4444; line-height:1.45;">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div style="margin-bottom:26px;">
                    <label style="display:block; margin-bottom:8px; color:#374151; font-weight:500; font-size:15px;">
                        New Password
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        required 
                        minlength="8"
                        placeholder="At least 8 characters"
                        style="width:100%; padding:14px 16px; border:2px solid #d1d5db; border-radius:10px; font-size:16px; transition:all 0.2s; outline:none;"
                        onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.18)';"
                        onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';"
                    >
                </div>

                <div style="margin-bottom:32px;">
                    <label style="display:block; margin-bottom:8px; color:#374151; font-weight:500; font-size:15px;">
                        Confirm New Password
                    </label>
                    <input 
                        type="password" 
                        name="confirm" 
                        required 
                        placeholder="Re-enter your new password"
                        style="width:100%; padding:14px 16px; border:2px solid #d1d5db; border-radius:10px; font-size:16px; transition:all 0.2s; outline:none;"
                        onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.18)';"
                        onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';"
                    >
                </div>

                <button 
                    type="submit"
                    style="width:100%; padding:15px; background:#4f46e5; color:white; border:none; border-radius:10px; font-size:16px; font-weight:600; cursor:pointer; transition:all 0.25s; box-shadow:0 4px 12px rgba(79,70,229,0.25);"
                    onmouseover="this.style.background='#4338ca'; transform:translateY(-2px); box-shadow:0 8px 20px rgba(79,70,229,0.35);"
                    onmouseout="this.style.background='#4f46e5'; transform:translateY(0); box-shadow:0 4px 12px rgba(79,70,229,0.25);"
                >
                    Reset Password
                </button>

                <div style="text-align:center; margin-top:32px; color:#6b7280; font-size:14px;">
                    <a href="login.php" style="color:#4f46e5; text-decoration:none; font-weight:500;">
                        ← Back to Login
                    </a>
                </div>
            </form>
        </div>

        <!-- Subtle footer hint -->
        <div style="background:#f8f9fa; padding:16px; text-align:center; font-size:13px; color:#9ca3af; border-top:1px solid #e5e7eb;">
            Link expires in 30 minutes • Keep your password secure
        </div>
    </div>

</body>
</html>