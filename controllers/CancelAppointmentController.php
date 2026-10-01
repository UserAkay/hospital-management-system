<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: ../views/login.php");
    exit();
}

if (!isset($_POST['appointment_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../views/my_appointments.php");
    exit();
}

$appointment_id = (int) $_POST['appointment_id'];

$database = new Database();
$conn = $database->connect();

/* 1️⃣ Get patient_id */
$stmt = $conn->prepare("SELECT patient_id FROM patient WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    $_SESSION['error'] = "Patient not found.";
    header("Location: ../views/my_appointments.php");
    exit();
}

$patient_id = $patient['patient_id'];

/* 2️⃣ Atomic update (better than SELECT + UPDATE) */
$stmt = $conn->prepare("
    UPDATE appointment
    SET status = 'Cancelled'
    WHERE appointment_id = :id
    AND patient_id = :patient_id
    AND status IN ('Pending', 'Approved')
");

$stmt->execute([
    ':id' => $appointment_id,
    ':patient_id' => $patient_id
]);

if ($stmt->rowCount() === 0) {
    $_SESSION['error'] = "Unauthorized or invalid appointment status.";
} else {
    $_SESSION['success'] = "Appointment cancelled successfully.";
}

header("Location: ../views/my_appointments.php");
exit();