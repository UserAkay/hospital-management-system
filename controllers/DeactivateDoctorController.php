<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST['doctor_id']) || empty($_POST['doctor_id'])) {
        $_SESSION['error'] = "Doctor not found.";
        header("Location: ../views/manage_doctors.php");
        exit();
    }

    $doctor_id = $_POST['doctor_id'];

    $database = new Database();
    $conn = $database->connect();

    $stmt = $conn->prepare("UPDATE doctor SET is_active = 0 WHERE doctor_id = ?");
    $stmt->execute([$doctor_id]);

    $_SESSION['success'] = "Doctor deactivated successfully.";

    header("Location: ../views/manage_doctors.php");
    exit();
}
?>