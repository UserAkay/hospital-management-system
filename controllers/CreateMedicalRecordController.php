<?php

session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/doctor_dashboard.php");
    exit();
}

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'doctor') {
    header("Location: ../views/login.php");
    exit();
}

$visit_id = $_POST['visit_id'] ?? null;
$diagnosis = trim($_POST['diagnosis'] ?? '');
$prescription = trim($_POST['prescription'] ?? '');

if (!$visit_id || $diagnosis === '') {
    die("Invalid data.");
}

$database = new Database();
$conn = $database->connect();

/* Get patient and doctor from visit */

$stmt = $conn->prepare("
SELECT patient_id, doctor_id
FROM visit
WHERE visit_id = :visit_id
LIMIT 1
");

$stmt->execute([':visit_id'=>$visit_id]);

$visit = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$visit){
    die("Visit not found.");
}

/* Insert medical record */

$stmt = $conn->prepare("
INSERT INTO medical_records
(visit_id,patient_id,doctor_id,diagnosis,prescription)
VALUES
(:visit_id,:patient_id,:doctor_id,:diagnosis,:prescription)
");

$stmt->execute([
':visit_id'=>$visit_id,
':patient_id'=>$visit['patient_id'],
':doctor_id'=>$visit['doctor_id'],
':diagnosis'=>$diagnosis,
':prescription'=>$prescription
]);

header("Location: ../views/my_patients.php?success=record_added");
exit();