<?php

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ===============================
   ACCESS CONTROL
=============================== */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

$role = strtolower($_SESSION['role']);

if (!in_array($role, ['admin','receptionist'])) {
    die("Access denied.");
}

/* ===============================
   DATABASE CONNECTION
=============================== */

require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

/* ===============================
   VALIDATE REQUEST
=============================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/admin_dashboard.php");
    exit();
}

$doctor_id = $_POST['doctor_id'] ?? null;
$leave_date = $_POST['leave_date'] ?? null;
$reason = $_POST['reason'] ?? '';

if (!$doctor_id || !$leave_date) {
    die("Doctor and leave date are required.");
}

try {

    /* ===============================
       PREVENT DUPLICATE LEAVE
    =============================== */

    $duplicateCheck = $conn->prepare("
        SELECT COUNT(*)
        FROM doctor_leave
        WHERE doctor_id = ?
        AND leave_date = ?
    ");

    $duplicateCheck->execute([$doctor_id, $leave_date]);

    if ($duplicateCheck->fetchColumn() > 0) {
        die("Leave already exists for this doctor on the selected date.");
    }

    /* ===============================
       STEP 5 – CHECK APPOINTMENT CONFLICT
    =============================== */

    $appointmentCheck = $conn->prepare("
        SELECT COUNT(*)
        FROM appointment
        WHERE doctor_id = ?
        AND appointment_date = ?
        AND status != 'Cancelled'
    ");

    $appointmentCheck->execute([$doctor_id, $leave_date]);

    if ($appointmentCheck->fetchColumn() > 0) {
        die("Cannot add leave. Doctor already has appointments scheduled on this date.");
    }

    /* ===============================
       INSERT LEAVE
    =============================== */

    $insert = $conn->prepare("
        INSERT INTO doctor_leave (doctor_id, leave_date, reason)
        VALUES (?, ?, ?)
    ");

    $insert->execute([
        $doctor_id,
        $leave_date,
        $reason
    ]);

    header("Location: ../views/admin_dashboard.php?message=Doctor leave added successfully");
    exit();

} catch (PDOException $e) {

    die("Database error: " . $e->getMessage());

}