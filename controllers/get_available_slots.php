<?php

require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

$doctor_id = $_GET['doctor_id'] ?? null;
$date = $_GET['date'] ?? null;

if (!$doctor_id || !$date) {
    echo json_encode([]);
    exit();
}

$day = date('l', strtotime($date));

/* GET DOCTOR SCHEDULE */

$stmt = $conn->prepare("
SELECT start_time,end_time,slot_duration
FROM doctor_schedule
WHERE doctor_id = ?
AND day_of_week = ?
");

$stmt->execute([$doctor_id,$day]);

$schedule = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$schedule){
    echo json_encode([]);
    exit();
}

$leaveCheck = $conn->prepare("
SELECT COUNT(*)
FROM doctor_leave
WHERE doctor_id = ?
AND leave_date = ?
");

$leaveCheck->execute([$doctor_id,$date]);

if($leaveCheck->fetchColumn() > 0){

echo json_encode([]);
exit();

}

/* GENERATE SLOTS */

$slots = [];

$start = strtotime($schedule['start_time']);
$end = strtotime($schedule['end_time']);
$duration = $schedule['slot_duration'] * 60;

for($time = $start; $time < $end; $time += $duration){

    $slot = date("H:i:s",$time);

    /* CHECK IF SLOT ALREADY BOOKED */

    $check = $conn->prepare("
    SELECT COUNT(*)
    FROM appointment
    WHERE doctor_id = ?
    AND appointment_date = ?
    AND appointment_time = ?
    AND status != 'Cancelled'
    ");

    $check->execute([$doctor_id,$date,$slot]);

    if($check->fetchColumn() == 0){
        $slots[] = date("H:i",$time);
    }

}

echo json_encode($slots);