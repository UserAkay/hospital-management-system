<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../views/login.php");
    exit();
}

require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/manage_doctors.php");
    exit();
}

$doctor_id = $_POST['doctor_id'];
$days = $_POST['days'] ?? [];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$slot_duration = $_POST['slot_duration'];

if (empty($days)) {
    die("Please select at least one working day.");
}

try {

    $stmt = $conn->prepare("
        INSERT INTO doctor_schedule
        (doctor_id, day_of_week, start_time, end_time, slot_duration)
        VALUES (:doctor_id, :day, :start_time, :end_time, :slot_duration)
    ");

    foreach ($days as $day) {

        $stmt->execute([
            ':doctor_id' => $doctor_id,
            ':day' => $day,
            ':start_time' => $start_time,
            ':end_time' => $end_time,
            ':slot_duration' => $slot_duration
        ]);

    }

    header("Location: ../views/manage_doctors.php?success=Schedule saved");
    exit();

} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}