<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../views/login.php");
    exit();
}

date_default_timezone_set('Asia/Kolkata');

$database = new Database();
$conn = $database->connect();

$schedule_id = (int) $_GET['schedule_id'];
$doctor_id = (int) $_GET['doctor_id'];

/* Get schedule details */
$stmt = $conn->prepare("
    SELECT day_of_week 
    FROM doctor_schedule 
    WHERE schedule_id = :id
");
$stmt->execute([':id' => $schedule_id]);
$schedule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$schedule) {
    die("Schedule not found.");
}

$day = $schedule['day_of_week'];

/* Check for future appointments on that day */
$today = date('Y-m-d');

$check = $conn->prepare("
    SELECT COUNT(*) 
    FROM appointment 
    WHERE doctor_id = :doctor_id
    AND appointment_date >= :today
    AND DAYNAME(appointment_date) = :day
    AND status IN (''Pending', 'Confirmed')
");

$check->execute([
    ':doctor_id' => $doctor_id,
    ':today' => $today,
    ':day' => $day
]);

$count = $check->fetchColumn();

if ($count > 0) {
    die("Cannot delete schedule. Future appointments exist on this day.");
}

/* Safe to delete */
$delete = $conn->prepare("
    DELETE FROM doctor_schedule
    WHERE schedule_id = :id
");

$delete->execute([':id' => $schedule_id]);

header("Location: ../views/manage_schedule.php?doctor_id=" . $doctor_id);
exit();