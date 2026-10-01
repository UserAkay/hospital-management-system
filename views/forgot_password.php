<?php
session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    
    if (!$username) {
        $_SESSION['error'] = "Please enter your username.";
        header("Location: forgot_password.php");
        exit();
    }

    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = "No account found with that username.";
        header("Location: forgot_password.php");
        exit();
    }

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    $stmt = $conn->prepare("
        INSERT INTO password_resets (user_id, token, expires_at) 
        VALUES (:uid, :token, :expires)
    ");
    $stmt->execute([
        ':uid'     => $user['user_id'],
        ':token'   => $token,
        ':expires' => $expires
    ]);

    $resetLink = "http://localhost/hospital-management/views/reset_password.php?token=$token";
    
    // In production → send real email here
    $_SESSION['success'] = "Password reset link has been prepared:<br>
        <strong style='word-break:break-all;'>$resetLink</strong><br>
        <small>(This is shown for development — in real system this would be emailed to you)</small>";
    
    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Hospital Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body style="margin:0; padding:0; font-family:'Poppins', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height:100vh; display:flex; align-items:center; justify-content:center;">

    <div style="background:white; width:100%; max-width:420px; margin:20px; border-radius:16px; overflow:hidden; box-shadow:0 20px 40px rgba(0,0,0,0.22);">

        <!-- Header -->
        <div style="background: #4f46e5; color:white; padding:30px 40px; text-align:center;">
            <h1 style="margin:0; font-size:28px; font-weight:600;">Forgot Password?</h1>
            <p style="margin:12px 0 0; opacity:0.9; font-size:15px;">
                No worries! We'll help you reset it.
            </p>
        </div>

        <!-- Form Area -->
        <div style="padding:40px 36px 36px;">

            <?php if(isset($_SESSION['error'])): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:14px 18px; border-radius:10px; margin-bottom:24px; font-size:15px; border-left:5px solid #ef4444;">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <?php unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['success'])): ?>
            <div style="background:#ecfdf5; color:#065f46; padding:16px 18px; border-radius:10px; margin-bottom:28px; font-size:15px; line-height:1.5; border-left:5px solid #10b981;">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div style="margin-bottom:24px;">
                    <label style="display:block; margin-bottom:8px; color:#374151; font-weight:500; font-size:15px;">
                        Username / Email
                    </label>
                    <input 
                        type="text" 
                        name="username" 
                        required 
                        autocomplete="username"
                        placeholder="Enter your username"
                        style="width:100%; padding:14px 16px; border:2px solid #d1d5db; border-radius:10px; font-size:16px; transition:all 0.2s; outline:none;"
                        onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 3px rgba(99,102,241,0.15)';"
                        onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';"
                    >
                </div>

                <button 
                    type="submit"
                    style="width:100%; padding:15px; background:#4f46e5; color:white; border:none; border-radius:10px; font-size:16px; font-weight:600; cursor:pointer; transition:all 0.25s;"
                    onmouseover="this.style.background='#4338ca'; transform:translateY(-1px);"
                    onmouseout="this.style.background='#4f46e5'; transform:translateY(0);"
                >
                    Send Reset Link
                </button>

                <div style="text-align:center; margin-top:28px; color:#6b7280; font-size:14px;">
                    <a href="login.php" style="color:#4f46e5; text-decoration:none; font-weight:500;">
                        ← Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>