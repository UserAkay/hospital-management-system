<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/database.php";
require_once "../utils/audit_logger.php";

$database = new Database();
$conn = $database->connect();

/* ===============================
   ALLOW ONLY POST REQUEST
=============================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/login.php");
    exit();
}

/* ===============================
   GET LOGIN DATA
=============================== */

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {

    $_SESSION['error'] = "Username and password are required.";
    header("Location: ../views/login.php");
    exit();
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

try {

    /* ===============================
       CHECK FAILED LOGIN LIMIT
    =============================== */

    $limitCheck = $conn->prepare("
        SELECT COUNT(*) 
        FROM login_logs
        WHERE username = ?
        AND status = 'FAILED'
        AND login_time > (NOW() - INTERVAL 15 MINUTE)
    ");

    $limitCheck->execute([$username]);
    $failedAttempts = $limitCheck->fetchColumn();

    if ($failedAttempts >= 5) {

        $_SESSION['error'] = "Too many failed login attempts. Try again in 15 minutes.";
        header("Location: ../views/login.php");
        exit();
    }

    /* ===============================
       FETCH USER
    =============================== */

    $stmt = $conn->prepare("
        SELECT u.*, d.is_active, p.verification_status
        FROM users u
        LEFT JOIN doctor d ON u.user_id = d.user_id
        LEFT JOIN patient p ON u.user_id = p.user_id
        WHERE u.username = :username
        LIMIT 1
    ");

    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {

        /* LOG FAILED LOGIN */

        $log = $conn->prepare("
            INSERT INTO login_logs (username,status,ip_address)
            VALUES (?, 'FAILED', ?)
        ");

        $log->execute([$username, $ip]);

        $_SESSION['error'] = "Invalid username or password.";
        header("Location: ../views/login.php");
        exit();
    }

    /* ===============================
       PASSWORD RESET CHECK
    =============================== */

    if (!empty($user['reset_token'])) {

        $_SESSION['error'] = "Password reset required. Please use the 'Forgot Password' option.";
        header("Location: ../views/login.php");
        exit();
    }

    /* ===============================
       VERIFY PASSWORD
    =============================== */

    if (!password_verify($password, $user['password'])) {

        $log = $conn->prepare("
            INSERT INTO login_logs (username,user_id,status,ip_address)
            VALUES (?, ?, 'FAILED', ?)
        ");

        $log->execute([$username, $user['user_id'], $ip]);

        $_SESSION['error'] = "Invalid username or password.";
        header("Location: ../views/login.php");
        exit();
    }

    /* ===============================
       PATIENT VERIFICATION CHECK
    =============================== */

    if (
        strtolower($user['role']) === 'patient' &&
        $user['verification_status'] !== 'Approved'
    ) {

        $_SESSION['error'] = "Your account is pending verification by hospital receptionist.";
        header("Location: ../views/login.php");
        exit();
    }

    /* ===============================
       DOCTOR ACTIVE CHECK
    =============================== */

    if (
        strtolower($user['role']) === 'doctor' &&
        isset($user['is_active']) &&
        $user['is_active'] == 0
    ) {

        $_SESSION['error'] = "Access denied. Your doctor account has been deactivated.";
        header("Location: ../views/login.php");
        exit();
    }

    /* ===============================
       LOG SUCCESSFUL LOGIN
    =============================== */

    $log = $conn->prepare("
        INSERT INTO login_logs (username,user_id,status,ip_address)
        VALUES (?, ?, 'SUCCESS', ?)
    ");

    $log->execute([$username, $user['user_id'], $ip]);

    /* ===============================
       CREATE SESSION
    =============================== */

    session_regenerate_id(true);

    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['LAST_ACTIVITY'] = time();

    /* ===============================
       AUDIT LOG
    =============================== */

    logAudit(
        $conn,
        $user['user_id'],
        'LOGIN',
        'users',
        $user['user_id'],
        'User logged into system'
    );

    /* ===============================
       ROLE BASED REDIRECT
    =============================== */

    switch (strtolower($user['role'])) {

        case 'admin':
            header("Location: ../views/admin_dashboard.php");
            break;

        case 'doctor':
            header("Location: ../views/doctor_dashboard.php");
            break;

        case 'receptionist':
            header("Location: ../views/reception_dashboard.php");
            break;

        case 'patient':
            header("Location: ../views/patient_dashboard.php");
            break;

        default:
            session_destroy();
            die("Unknown role.");
    }

    exit();

} catch (PDOException $e) {

    die("Database Error: " . $e->getMessage());
}
?>