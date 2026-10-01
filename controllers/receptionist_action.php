<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'receptionist') {
    header("Location: ../views/login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

$request_id = $_POST['request_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$request_id || !in_array($action, ['approve','reject'])) {
    die("Invalid request parameters.");
}

try {

$conn->beginTransaction();

/* Get request */

$stmt = $conn->prepare("
SELECT * FROM appointment_requests 
WHERE request_id = ? AND status='Pending'
");

$stmt->execute([$request_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
$conn->rollBack();
die("Request not found or already processed.");
}


/* Check patient verification */

$patientCheck = $conn->prepare("
SELECT verification_status 
FROM patient 
WHERE patient_id = ?
");

$patientCheck->execute([$request['patient_id']]);
$status = $patientCheck->fetchColumn();

if ($status !== 'Approved') {
$conn->rollBack();
die("Patient is not verified.");
}


if ($action === 'approve') {

    /* Check slot */

    $check = $conn->prepare("
        SELECT COUNT(*) FROM appointment
        WHERE doctor_id = ?
        AND appointment_date = ?
        AND appointment_time = ?
    ");

    $check->execute([
        $request['doctor_id'],
        $request['preferred_date'],
        $request['preferred_time']
    ]);

    if ($check->fetchColumn() > 0) {
        $conn->rollBack();
        die("Doctor already booked for this time slot.");
    }

    /* Create appointment */

    $insert = $conn->prepare("
        INSERT INTO appointment
        (patient_id, doctor_id, appointment_date, appointment_time, status)
        VALUES (?, ?, ?, ?, 'Scheduled')
    ");

    $insert->execute([
        $request['patient_id'],
        $request['doctor_id'],
        $request['preferred_date'],
        $request['preferred_time']
    ]);

    $update = $conn->prepare("
        UPDATE appointment_requests
        SET status = 'Approved'
        WHERE request_id = ?
    ");

    $update->execute([$request_id]);

} else {

    $update = $conn->prepare("
        UPDATE appointment_requests
        SET status = 'Rejected'
        WHERE request_id = ?
    ");

    $update->execute([$request_id]);
}


/* Audit log */

$log = $conn->prepare("
INSERT INTO audit_log (user_id,action,description)
VALUES (?,?,?)
");

$desc = "Receptionist ".$_SESSION['username']." ".$action." request #".$request_id;

$log->execute([
$_SESSION['user_id'],
"Appointment ".$action,
$desc
]);


$conn->commit();

header("Location: ../views/reception_dashboard.php?message=Action completed");
exit();

} catch (PDOException $e) {

$conn->rollBack();
die("Database error: ".$e->getMessage());

}