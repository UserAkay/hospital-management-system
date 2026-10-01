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

$schedule_id = (int) $_POST['schedule_id'];
$doctor_id = (int) $_POST['doctor_id'];
$new_start = $_POST['start_time'];
$new_end = $_POST['end_time'];
$new_slot = (int) $_POST['slot_duration'];

if (strtotime($new_start) >= strtotime($new_end)) {
    die("Start time must be earlier than end time.");
}

/* Get existing schedule */
$stmt = $conn->prepare("
    SELECT day_of_week, slot_duration 
    FROM doctor_schedule 
    WHERE schedule_id = :id
");
$stmt->execute([':id' => $schedule_id]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current) {
    die("Schedule not found.");
}

$day = $current['day_of_week'];
$current_slot = (int)$current['slot_duration'];
$today = date('Y-m-d');

/* Check if future non-cancelled appointments exist */
$check = $conn->prepare("
    SELECT COUNT(*) 
    FROM appointment
    WHERE doctor_id = :doctor_id
    AND appointment_date >= :today
    AND DAYNAME(appointment_date) = :day
    AND status IN ('Pending','Confirmed')
");

$check->execute([
    ':doctor_id' => $doctor_id,
    ':today' => $today,
    ':day' => $day
]);

$futureCount = $check->fetchColumn();

/* BLOCK slot_duration change if future appointments exist */
if ($futureCount > 0 && $new_slot !== $current_slot) {
    die("Cannot change slot duration. Future appointments already exist.");
}

/* Now validate time range against existing appointments */
$appointments = $conn->prepare("
    SELECT appointment_time
    FROM appointment
    WHERE doctor_id = :doctor_id
    AND appointment_date >= :today
    AND DAYNAME(appointment_date) = :day
    AND status IN ('Pending','Confirmed')
");

$appointments->execute([
    ':doctor_id' => $doctor_id,
    ':today' => $today,
    ':day' => $day
]);

$startTime = strtotime($new_start);
$endTime = strtotime($new_end);

while ($row = $appointments->fetch(PDO::FETCH_ASSOC)) {

    $apptStart = strtotime($row['appointment_time']);
    $apptEnd = $apptStart + ($current_slot * 60);

    if ($apptStart < $startTime || $apptEnd > $endTime) {
        die("Cannot update schedule. Some future appointments fall outside new time range.");
    }
}

/* Safe to update */
$update = $conn->prepare("
    UPDATE doctor_schedule
    SET start_time = :start,
        end_time = :end,
        slot_duration = :slot
    WHERE schedule_id = :id
");

$update->execute([
    ':start' => $new_start,
    ':end' => $new_end,
    ':slot' => $new_slot,
    ':id' => $schedule_id
]);

header("Location: ../views/manage_schedule.php?doctor_id=" . $doctor_id);
exit();