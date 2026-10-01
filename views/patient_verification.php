<?php
session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

if ($_SESSION['role'] !== 'Receptionist') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

$stmt = $conn->prepare("
SELECT user_id, username 
FROM users 
WHERE role='Patient' 
AND verified=0
");

$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Pending Patient Verification</h2>

<table border="1">

<tr>
<th>Username</th>
<th>Action</th>
</tr>

<?php foreach ($patients as $p): ?>

<tr>
<td><?= $p['username'] ?></td>

<td>

<form method="POST" action="../controllers/verify_patient.php">

<input type="hidden" name="user_id" value="<?= $p['user_id'] ?>">

<button type="submit">Verify</button>

</form>

</td>

</tr>

<?php endforeach; ?>

</table>