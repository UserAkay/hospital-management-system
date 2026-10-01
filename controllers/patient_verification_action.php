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
    die("Invalid request.");
}

$patient_id = $_POST['patient_id'] ?? null;
$action     = $_POST['action'] ?? null;

if (!$patient_id || !in_array($action, ['approve','reject'])) {
    die("Invalid parameters.");
}

$status = ($action === 'approve') ? 'Approved' : 'Rejected';

try {

    $conn->beginTransaction();

    /* Update patient status */

    $query = $conn->prepare("
        UPDATE patient
        SET verification_status = ?
        WHERE patient_id = ?
    ");

    $query->execute([$status,$patient_id]);

    /* Audit log */

   $log = $conn->prepare("
    INSERT INTO audit_log 
    (user_id, action, table_name, record_id, description, ip_address)
    VALUES (?, ?, ?, ?, ?, ?)
");

$desc = "Receptionist ".$_SESSION['username']." ".$action." patient #".$patient_id;

$log->execute([
    $_SESSION['user_id'],
    strtoupper($action),   // APPROVE / REJECT
    'patient',
    $patient_id,
    $desc,
    $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
]);

    $conn->commit();

    header("Location: ../views/reception_dashboard.php");
    exit();

} catch(PDOException $e){

    $conn->rollBack();
    die("Database error: ".$e->getMessage());

}
?>