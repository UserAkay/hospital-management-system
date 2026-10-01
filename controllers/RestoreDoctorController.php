<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../views/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST['doctor_id']) || empty($_POST['doctor_id'])) {
        die("Invalid doctor ID");
    }

    $doctor_id = $_POST['doctor_id'];

    $database = new Database();
    $conn = $database->connect();

    try {
        $conn->beginTransaction();

        // Get linked user
        $stmt = $conn->prepare("SELECT user_id FROM doctor WHERE doctor_id = ?");
        $stmt->execute([$doctor_id]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$doctor) {
            throw new Exception("Doctor not found");
        }

        // Restore doctor
        $stmt = $conn->prepare("UPDATE doctor SET is_active = 1 WHERE doctor_id = ?");
        $stmt->execute([$doctor_id]);

        // Restore linked user
        $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE user_id = ?");
        $stmt->execute([$doctor['user_id']]);

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollBack();
        die($e->getMessage());
    }

    header("Location: ../views/manage_doctors.php");
    exit();
}