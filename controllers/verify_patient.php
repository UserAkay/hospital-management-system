<?php
session_start();
require_once "../config/database.php";

if ($_SESSION['role'] !== 'Receptionist') {
    header("Location: ../views/login.php");
    exit();
}

$user_id = $_POST['user_id'];

$database = new Database();
$conn = $database->connect();

$stmt = $conn->prepare("
UPDATE users
SET verified = 1
WHERE user_id = ?
");

$stmt->execute([$user_id]);

header("Location: ../views/patient_verification.php");
exit();