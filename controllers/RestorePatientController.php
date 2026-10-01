<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['patient_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../views/manage_archived_patients.php");
    exit();
}

$patient_id = (int) $_POST['patient_id'];

$database = new Database();
$conn = $database->connect();

try {

    $conn->beginTransaction();

    // Check if patient exists
    $stmt = $conn->prepare("
        SELECT patient_id, user_id
        FROM patient
        WHERE patient_id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        throw new Exception("Patient not found.");
    }

    // Restore patient
    $stmt = $conn->prepare("
        UPDATE patient
        SET is_active = 1
        WHERE patient_id = :id
    ");
    $stmt->execute([':id' => $patient_id]);

    // Restore linked user account (IMPORTANT)
    if (!empty($patient['user_id'])) {

        $stmt = $conn->prepare("
            UPDATE users
            SET is_active = 1
            WHERE user_id = :uid
        ");
        $stmt->execute([':uid' => $patient['user_id']]);
    }

    $conn->commit();

    $_SESSION['success'] = "Patient restored successfully.";

} catch (Exception $e) {

    $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../views/manage_archived_patients.php");
exit();