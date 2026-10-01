<?php

session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

/* =============================
   ALLOW ONLY POST REQUEST
============================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/doctor_dashboard.php");
    exit();
}


/* =============================
   ROLE SECURITY CHECK
============================= */

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'doctor') {
    header("Location: ../views/login.php");
    exit();
}


/* =============================
   INPUT VALIDATION
============================= */

if (!isset($_POST['appointment_id']) || !is_numeric($_POST['appointment_id'])) {
    die("Invalid appointment request.");
}

$appointment_id = (int) $_POST['appointment_id'];

$diagnosis = trim($_POST['diagnosis'] ?? '');
$prescription = trim($_POST['prescription'] ?? '');
$notes = trim($_POST['notes'] ?? '');

if ($diagnosis === '') {
    die("Diagnosis is required.");
}


/* =============================
   DATABASE CONNECTION
============================= */

$database = new Database();
$conn = $database->connect();

try {

    /* =============================
       GET DOCTOR ID
    ============================== */

    $stmt = $conn->prepare("
        SELECT doctor_id
        FROM doctor
        WHERE user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ':user_id' => $_SESSION['user_id']
    ]);

    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        throw new Exception("Doctor record not found.");
    }

    $doctor_id = $doctor['doctor_id'];


    /* =============================
       VALIDATE APPOINTMENT
    ============================== */

    $stmt = $conn->prepare("
        SELECT patient_id
        FROM appointment
        WHERE appointment_id = :appointment_id
        AND doctor_id = :doctor_id
        AND status = 'Scheduled'
        LIMIT 1
    ");

    $stmt->execute([
        ':appointment_id' => $appointment_id,
        ':doctor_id' => $doctor_id
    ]);

    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        throw new Exception("Invalid or already completed appointment.");
    }

    $patient_id = $appointment['patient_id'];


    /* =============================
       START TRANSACTION
    ============================== */

    $conn->beginTransaction();


    /* =============================
       INSERT VISIT
    ============================== */

    $stmt = $conn->prepare("
        INSERT INTO visit
        (appointment_id, patient_id, doctor_id, diagnosis, prescription, notes, visit_date, completed_by)
        VALUES
        (:appointment_id, :patient_id, :doctor_id, :diagnosis, :prescription, :notes, NOW(), :completed_by)
    ");

    $stmt->execute([
        ':appointment_id' => $appointment_id,
        ':patient_id' => $patient_id,
        ':doctor_id' => $doctor_id,
        ':diagnosis' => $diagnosis,
        ':prescription' => $prescription,
        ':notes' => $notes,
        ':completed_by' => $_SESSION['user_id']
    ]);

    require_once "../utils/audit_logger.php";

logAudit(
$conn,
$_SESSION['user_id'],
'INSERT',
'visit',
$appointment_id,
'Doctor completed visit and created medical record'
);


    /* =============================
       UPDATE APPOINTMENT STATUS
    ============================== */

    $stmt = $conn->prepare("
        UPDATE appointment
        SET status = 'Completed'
        WHERE appointment_id = :appointment_id
    ");

    $stmt->execute([
        ':appointment_id' => $appointment_id
    ]);


    $conn->commit();


    header("Location: ../views/doctor_dashboard.php?success=visit_completed");
    exit();

} catch (PDOException $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    /* Handle duplicate visit attempt */

    if ($e->getCode() == 23000) {
        die("Visit already exists for this appointment.");
    }

    die("Database error occurred.");

} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die($e->getMessage());
}