<?php
session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

if (!isset($_GET['schedule_id'])) {
    die("Schedule ID missing.");
}

$schedule_id = (int) $_GET['schedule_id'];

$stmt = $conn->prepare("
    SELECT * FROM doctor_schedule WHERE schedule_id = :id
");
$stmt->execute([':id' => $schedule_id]);
$schedule = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$schedule) {
    die("Schedule not found.");
}
?>

<h2>Edit Schedule</h2>

<form method="POST" action="../controllers/UpdateScheduleController.php">

<input type="hidden" name="schedule_id" value="<?= $schedule['schedule_id'] ?>">
<input type="hidden" name="doctor_id" value="<?= $schedule['doctor_id'] ?>">

Day: <?= $schedule['day_of_week'] ?><br><br>

Start Time:
<input type="time" name="start_time" value="<?= $schedule['start_time'] ?>" required><br><br>

End Time:
<input type="time" name="end_time" value="<?= $schedule['end_time'] ?>" required><br><br>

Slot Duration:
<input type="number" name="slot_duration" value="<?= $schedule['slot_duration'] ?>" required><br><br>

<button type="submit">Update</button>

</form>