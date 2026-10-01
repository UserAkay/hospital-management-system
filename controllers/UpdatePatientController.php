<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../views/login.php");
    exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $database = new Database();
    $conn = $database->connect();

    try {
        $conn->beginTransaction();

        $patient_id = $_POST['patient_id'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $full_name = $_POST['full_name'];
        $gender = $_POST['gender'];
        $date_of_birth = $_POST['date_of_birth'];
        $contact_number = $_POST['contact_number'];
        $address = $_POST['address'];

        // Get user_id
        $stmt = $conn->prepare("SELECT user_id FROM patient WHERE patient_id = :patient_id");
        $stmt->bindParam(":patient_id", $patient_id);
        $stmt->execute();
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$patient) {
            throw new Exception("Patient not found.");
        }

        $user_id = $patient['user_id'];

        // Update username
        $updateUser = $conn->prepare("UPDATE users SET username = :username WHERE user_id = :user_id");
        $updateUser->bindParam(":username", $username);
        $updateUser->bindParam(":user_id", $user_id);
        $updateUser->execute();

        // If password entered → update it
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updatePass = $conn->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
            $updatePass->bindParam(":password", $hashedPassword);
            $updatePass->bindParam(":user_id", $user_id);
            $updatePass->execute();
        }

        // Update patient table
        $updatePatient = $conn->prepare("UPDATE patient
                                         SET full_name = :full_name,
                                             gender = :gender,
                                             date_of_birth = :date_of_birth,
                                             contact_number = :contact_number,
                                             address = :address
                                         WHERE patient_id = :patient_id");

        $updatePatient->bindParam(":full_name", $full_name);
        $updatePatient->bindParam(":gender", $gender);
        $updatePatient->bindParam(":date_of_birth", $date_of_birth);
        $updatePatient->bindParam(":contact_number", $contact_number);
        $updatePatient->bindParam(":address", $address);
        $updatePatient->bindParam(":patient_id", $patient_id);
        $updatePatient->execute();

        $conn->commit();

        header("Location: ../views/manage_patients.php");
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}