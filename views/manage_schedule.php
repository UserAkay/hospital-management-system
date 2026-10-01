<?php
session_start();
require_once "../config/session_check.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

if (!isset($_GET['doctor_id'])) {
    die("Doctor ID missing.");
}

$doctor_id = (int) $_GET['doctor_id'];

$stmt = $conn->prepare("
    SELECT schedule_id, day_of_week, start_time, end_time, slot_duration
    FROM doctor_schedule
    WHERE doctor_id = :doctor_id
    ORDER BY FIELD(day_of_week, 
        'Monday','Tuesday','Wednesday','Thursday',
        'Friday','Saturday','Sunday')
");

$stmt->execute([':doctor_id' => $doctor_id]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Doctor Schedule</h2>

<a href="set_schedule.php?doctor_id=<?= $doctor_id ?>">Add New Day</a>

<table border="1" cellpadding="8">
<tr>
    <th>Day</th>
    <th>Start</th>
    <th>End</th>
    <th>Slot</th>
    <th>Actions</th>
</tr>

<?php foreach ($schedules as $row): ?>
<tr>
    <td><?= $row['day_of_week'] ?></td>
    <td><?= $row['start_time'] ?></td>
    <td><?= $row['end_time'] ?></td>
    <td><?= $row['slot_duration'] ?> mins</td>
    <td>
        <a href="edit_schedule.php?schedule_id=<?= $row['schedule_id'] ?>">Edit</a>
        |
        <a href="../controllers/DeleteScheduleController.php?schedule_id=<?= $row['schedule_id'] ?>&doctor_id=<?= $doctor_id ?>"
           onclick="return confirm('Delete this schedule?')">
           Delete
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>

<br>
<a href="manage_doctors.php">Back to Doctors</a>