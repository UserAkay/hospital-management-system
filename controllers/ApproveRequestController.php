<?php
session_start();
require_once "../config/database.php";

// Only receptionist or admin can approve
if (!isset($_SESSION['user_id']) || 
    !in_array(strtolower($_SESSION['role']), ['admin', 'receptionist'])) {
    header("Location: ../views/login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

if (isset($_GET['request_id'])) {

    $request_id = $_GET['request_id'];

    // Get request details
    $stmt = $conn->prepare("SELECT * FROM appointment_requests WHERE request_id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($request && $request['status'] === 'Pending') {

        // Insert into appointment table
        $insert = $conn->prepare("
            INSERT INTO appointment 
            (patient_id, doctor_id, appointment_date, appointment_time, status)
            VALUES (?, ?, ?, ?, 'Approved')
        ");

        $insert->execute([
            $request['patient_id'],
            $request['doctor_id'],
            $request['preferred_date'],
            $request['preferred_time']
        ]);

        // Update request status
        $update = $conn->prepare("
            UPDATE appointment_requests 
            SET status = 'Approved'
            WHERE request_id = ?
        ");
        $update->execute([$request_id]);
    }

    header("Location: ../views/manage_requests.php");
    exit();
}