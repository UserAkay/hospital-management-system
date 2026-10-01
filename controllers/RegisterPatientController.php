<?php
session_start();
require_once "../config/database.php";

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header("Location: ../views/register_patient.php");
    exit();
}

$username = $_POST['username'];
$password = $_POST['password'];
$full_name = $_POST['full_name'];
$gender = $_POST['gender'];
$dob = $_POST['date_of_birth'];
$contact = $_POST['contact_number'];
$address = $_POST['address'];

$database = new Database();
$conn = $database->connect();

/* CHECK USERNAME */

$stmt = $conn->prepare("SELECT user_id FROM users WHERE username=?");
$stmt->execute([$username]);

if($stmt->fetch()){
    $_SESSION['error']="Username already exists";
    header("Location: ../views/register_patient.php");
    exit();
}

/* CREATE USER */

$hashed = password_hash($password,PASSWORD_DEFAULT);

$stmt = $conn->prepare("
INSERT INTO users (username,password,role)
VALUES (?,?, 'Patient')
");

$stmt->execute([$username,$hashed]);

$user_id = $conn->lastInsertId();

/* CREATE PATIENT RECORD */

$stmt = $conn->prepare("
INSERT INTO patient
(user_id,full_name,gender,date_of_birth,contact_number,address)
VALUES (?,?,?,?,?,?)
");

$stmt->execute([
$user_id,
$full_name,
$gender,
$dob,
$contact,
$address
]);

$_SESSION['success']="Registration successful. Please login.";

header("Location: ../views/login.php");
exit();