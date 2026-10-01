<?php
session_start();
require_once "../config/database.php";

/* 1️⃣ Role Check */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../views/login.php");
    exit();
}

/* 2️⃣ Request Validation */
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['patient_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../views/manage_patients.php");
    exit();
}

$patient_id = (int) $_POST['patient_id'];

if ($patient_id <= 0) {
    $_SESSION['error'] = "Invalid patient ID.";
    header("Location: ../views/manage_patients.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

try {

    $conn->beginTransaction();

    /* 3️⃣ Lock Patient Row */
    $stmt = $conn->prepare("
        SELECT patient_id, user_id, is_active
        FROM patient
        WHERE patient_id = :id
        FOR UPDATE
    ");
    $stmt->execute([':id' => $patient_id]);

    $patient = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$patient) {
        throw new Exception("Patient not found.");
    }

    if ((int)$patient['is_active'] === 0) {
        throw new Exception("Patient is already archived.");
    }

    /* 4️⃣ Soft Delete Patient */
    $stmt = $conn->prepare("
        UPDATE patient
        SET is_active = FALSE
        WHERE patient_id = :id
    ");
    $stmt->execute([':id' => $patient_id]);

    /* 5️⃣ Disable User Login */
    $stmt = $conn->prepare("
        UPDATE users
        SET is_active = FALSE
        WHERE user_id = :user_id
    ");
    $stmt->execute([':user_id' => $patient['user_id']]);

    $conn->commit();

    $_SESSION['success'] = "Patient archived successfully.";

} catch (Exception $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log($e->getMessage()); // backend logging
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../views/manage_patients.php");
exit();