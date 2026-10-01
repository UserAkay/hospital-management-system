<?php
session_start();
require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

/* Allow both flows */
$isStaff = isset($_SESSION['role']) && 
           in_array(strtolower($_SESSION['role']), ['admin','receptionist']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/login.php");
    exit();
}

/* Collect form data */
$username      = trim($_POST['username']);
$password      = trim($_POST['password']);
$full_name     = trim($_POST['full_name']);
$gender        = $_POST['gender'];
$date_of_birth = $_POST['date_of_birth'];
$contact       = trim($_POST['contact_number']);
$address       = trim($_POST['address']);

try {

    $conn->beginTransaction();

    /* Check username */
    $check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $check->execute([$username]);

    if ($check->fetchColumn() > 0) {

        if ($isStaff) {
            header("Location: ../views/add_patient.php?error=Username already exists");
        } else {
            header("Location: ../views/self_register.php?error=Username already exists");
        }
        exit();
    }

    /* Insert into users */
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $userInsert = $conn->prepare("
        INSERT INTO users (username, password, role, created_at)
        VALUES (?, ?, 'Patient', NOW())
    ");

    $userInsert->execute([$username, $hashedPassword]);

    $user_id = $conn->lastInsertId();

    /* Decide verification status */
    $verification_status = $isStaff ? 'Approved' : 'Pending';

    /* Insert patient */
    $patientInsert = $conn->prepare("
        INSERT INTO patient
        (user_id, full_name, gender, date_of_birth, contact_number, address, is_active, verification_status)
        VALUES (?, ?, ?, ?, ?, ?, 1, ?)
    ");

    $patientInsert->execute([
        $user_id,
        $full_name,
        $gender,
        $date_of_birth,
        $contact,
        $address,
        $verification_status
    ]);

    $conn->commit();

    /* Redirect based on flow */
    if ($isStaff) {
        header("Location: ../views/add_patient.php?message=Patient registered successfully");
    } else {
        header("Location: ../views/login.php?message=Registration submitted. Wait for approval.");
    }

    exit();

} catch(PDOException $e){

    $conn->rollBack();

    if ($isStaff) {
        header("Location: ../views/add_patient.php?error=Database error");
    } else {
        header("Location: ../views/self_register.php?error=Database error");
    }

    exit();
}
?>