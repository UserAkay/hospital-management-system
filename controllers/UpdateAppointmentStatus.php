<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Doctor') {
    header("Location: ../views/login.php");
    exit();
}

if (!isset($_POST['appointment_id']) || !isset($_POST['status'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../views/doctor_appointments.php");
    exit();
}

$appointment_id = (int) $_POST['appointment_id'];
$newStatus = $_POST['status'];

$allowedStatuses = ['Approved', 'Completed'];

if (!in_array($newStatus, $allowedStatuses)) {
    $_SESSION['error'] = "Invalid status value.";
    header("Location: ../views/doctor_appointments.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

try {

    $conn->beginTransaction();

    /* 1️⃣ Get doctor_id */
    $stmt = $conn->prepare("SELECT doctor_id FROM doctor WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        throw new Exception("Doctor not found.");
    }

    $doctor_id = $doctor['doctor_id'];

    /* 2️⃣ Get appointment + patient info */
    $stmt = $conn->prepare("
        SELECT a.status,
               p.patient_id,
               p.user_id AS patient_user_id
        FROM appointment a
        JOIN patient p ON a.patient_id = p.patient_id
        WHERE a.appointment_id = :id
        AND a.doctor_id = :doctor_id
        FOR UPDATE
    ");

    $stmt->execute([
        ':id' => $appointment_id,
        ':doctor_id' => $doctor_id
    ]);

    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        throw new Exception("Unauthorized or appointment not found.");
    }

    $currentStatus = $appointment['status'];

    /* 3️⃣ Prevent modification of finalized */
    if (in_array($currentStatus, ['Completed', 'Cancelled'])) {
        throw new Exception("Finalized appointments cannot be modified.");
    }

    /* 4️⃣ Strict transition rules */
    $validTransitions = [
        'Pending'  => ['Approved'],
        'Approved' => ['Completed']
    ];

    if (
        !isset($validTransitions[$currentStatus]) ||
        !in_array($newStatus, $validTransitions[$currentStatus])
    ) {
        throw new Exception("Invalid status transition.");
    }

    /* 5️⃣ Update status (concurrency safe) */
    $stmt = $conn->prepare("
        UPDATE appointment
        SET status = :status
        WHERE appointment_id = :id
        AND doctor_id = :doctor_id
        AND status = :current_status
    ");

    $stmt->execute([
        ':status' => $newStatus,
        ':id' => $appointment_id,
        ':doctor_id' => $doctor_id,
        ':current_status' => $currentStatus
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception("Concurrent modification detected.");
    }

    /* 6️⃣ Create medical record if completed */
    if ($newStatus === 'Completed') {

        $stmt = $conn->prepare("
            INSERT INTO medical_records (appointment_id, patient_id, doctor_id)
            VALUES (:appointment_id, :patient_id, :doctor_id)
            ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            ':appointment_id' => $appointment_id,
            ':patient_id' => $appointment['patient_id'],
            ':doctor_id' => $doctor_id
        ]);
    }

    /* 7️⃣ Insert notification */
    $message = "Your appointment status has been updated to $newStatus.";

    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, message)
        VALUES (:user_id, :message)
    ");

    $stmt->execute([
        ':user_id' => $appointment['patient_user_id'],
        ':message' => $message
    ]);

    $conn->commit();

    $_SESSION['success'] = "Appointment status updated successfully.";

} catch (Exception $e) {

    $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../views/doctor_appointments.php");
exit();