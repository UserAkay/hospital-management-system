<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/create_appointment.php");
    exit();
}

$role = strtolower($_SESSION['role']);

$doctor_id = $_POST['doctor_id'] ?? null;
$preferred_date = $_POST['preferred_date'] ?? null;
$preferred_time = $_POST['preferred_time'] ?? null;

if (!$doctor_id || !$preferred_date || !$preferred_time) {
    die("All fields are required.");
}

try {

    /* ===============================
       CHECK DOCTOR SCHEDULE
    =============================== */

    $day_of_week = date('l', strtotime($preferred_date));

    $scheduleCheck = $conn->prepare("
        SELECT *
        FROM doctor_schedule
        WHERE doctor_id = ?
        AND day_of_week = ?
    ");

    $scheduleCheck->execute([$doctor_id, $day_of_week]);

    $schedule = $scheduleCheck->fetch(PDO::FETCH_ASSOC);

    if (!$schedule) {
        die("Doctor is not available on this day.");
    }

    /* ===============================
       CHECK WORKING HOURS
    =============================== */

    if (
        $preferred_time < $schedule['start_time'] ||
        $preferred_time >= $schedule['end_time']
    ) {
        die("Selected time is outside doctor's working hours.");
    }

    /* ===============================
       CHECK SLOT DURATION
    =============================== */

    $start = strtotime($schedule['start_time']);
    $chosen = strtotime($preferred_time);
    $slot = $schedule['slot_duration'] * 60;

    if (($chosen - $start) % $slot !== 0) {
        die("Invalid appointment slot.");
    }

    /* ===============================
       PATIENT WORKFLOW
    =============================== */

    if ($role === 'patient') {

        $stmt = $conn->prepare("SELECT patient_id FROM patient WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);

        $patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            die("Patient record not found.");
        }

        $query = "INSERT INTO appointment_requests
                  (patient_id, doctor_id, preferred_date, preferred_time, status)
                  VALUES (?, ?, ?, ?, 'Pending')";

        $stmt = $conn->prepare($query);

        $stmt->execute([
            $patient['patient_id'],
            $doctor_id,
            $preferred_date,
            $preferred_time
        ]);

        header("Location: ../views/patient_dashboard.php?message=Request submitted");
        exit();
    }

    /* ===============================
       RECEPTIONIST WORKFLOW
    =============================== */

    elseif ($role === 'receptionist') {

        $patient_id = $_POST['patient_id'] ?? null;

        if (!$patient_id) {
            die("Patient must be selected.");
        }

        /* CHECK DOUBLE BOOKING */

        $check = $conn->prepare("
            SELECT COUNT(*)
            FROM appointment
            WHERE doctor_id = ?
            AND appointment_date = ?
            AND appointment_time = ?
            AND status != 'Cancelled'
        ");

        $check->execute([
            $doctor_id,
            $preferred_date,
            $preferred_time
        ]);

        if ($check->fetchColumn() > 0) {
            die("This time slot is already booked.");
        }

        /* INSERT APPOINTMENT */

        $query = "INSERT INTO appointment
                  (patient_id, doctor_id, appointment_date, appointment_time, status)
                  VALUES (?, ?, ?, ?, 'Scheduled')";

        $stmt = $conn->prepare($query);

        $stmt->execute([
            $patient_id,
            $doctor_id,
            $preferred_date,
            $preferred_time
        ]);

        header("Location: ../views/reception_dashboard.php?message=Appointment booked");
        exit();
    }

} catch (PDOException $e) {

    if ($e->getCode() == 23000) {
        die("This time slot is already booked.");
    }

    die("Database error: " . $e->getMessage());
}