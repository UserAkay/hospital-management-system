<?php

declare(strict_types=1);

session_start();

// Display errors during development
ini_set('display_errors', '1');
error_reporting(E_ALL);


// ============================================================================
// ADMIN ACCESS CHECK
// ============================================================================

if (
    !isset($_SESSION['user_id'], $_SESSION['role']) ||
    strtolower((string) $_SESSION['role']) !== 'admin'
) {
    header("Location: ../views/login.php");
    exit();
}


// ============================================================================
// DATABASE CONNECTION
// ============================================================================

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->connect();


// ============================================================================
// ONLY ACCEPT POST REQUESTS
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/create_user.php");
    exit();
}


// ============================================================================
// GET FORM DATA
// ============================================================================

$username = trim((string) ($_POST['username'] ?? ''));
$role = ucfirst(strtolower(trim((string) ($_POST['role'] ?? ''))));
$password = (string) ($_POST['password'] ?? '');


// ============================================================================
// BASIC VALIDATION
// ============================================================================

if ($username === '' || $role === '') {
    header(
        "Location: ../views/create_user.php?error=" .
        urlencode("Username and role are required.")
    );
    exit();
}


// ============================================================================
// VALID ROLES
// ============================================================================

$valid_roles = [
    'Doctor',
    'Receptionist',
    'Patient'
];

if (!in_array($role, $valid_roles, true)) {
    header(
        "Location: ../views/create_user.php?error=" .
        urlencode("Invalid role selected.")
    );
    exit();
}


// ============================================================================
// GENERATE PASSWORD IF EMPTY
// ============================================================================

if ($password === '') {
    $password = bin2hex(random_bytes(4));
}


// ============================================================================
// HASH PASSWORD
// ============================================================================

$password_hash = password_hash($password, PASSWORD_DEFAULT);


// ============================================================================
// CREATE USER
// ============================================================================

try {

    $conn->beginTransaction();


    // ------------------------------------------------------------------------
    // INSERT USER ACCOUNT
    // ------------------------------------------------------------------------

    $stmt = $conn->prepare("
        INSERT INTO users (
            username,
            password,
            role
        )
        VALUES (
            :username,
            :password,
            :role
        )
    ");

    $stmt->execute([
        ':username' => $username,
        ':password' => $password_hash,
        ':role'     => $role
    ]);

    $user_id = (int) $conn->lastInsertId();


    // ------------------------------------------------------------------------
    // CREATE DOCTOR PROFILE
    // ------------------------------------------------------------------------

    if ($role === 'Doctor') {

        $name = trim((string) ($_POST['name'] ?? ''));
        $specialization = trim((string) ($_POST['specialization'] ?? ''));

        // IMPORTANT:
        // Database column is contact_number, not phone.
        $contact_number = trim((string) ($_POST['phone'] ?? ''));

        $email = trim((string) ($_POST['email'] ?? ''));


        $stmt2 = $conn->prepare("
            INSERT INTO doctor (
                user_id,
                name,
                specialization,
                contact_number,
                email,
                is_active
            )
            VALUES (
                :user_id,
                :name,
                :specialization,
                :contact_number,
                :email,
                1
            )
        ");

        $stmt2->execute([
            ':user_id'        => $user_id,
            ':name'           => $name,
            ':specialization' => $specialization,
            ':contact_number' => $contact_number,
            ':email'          => $email
        ]);
    }


    // ------------------------------------------------------------------------
    // COMMIT TRANSACTION
    // ------------------------------------------------------------------------

    $conn->commit();


    // ------------------------------------------------------------------------
    // SUCCESS
    // ------------------------------------------------------------------------

    header(
        "Location: ../views/admin_dashboard.php?message=" .
        urlencode(
            "User created successfully. Temporary password: {$password}"
        )
    );

    exit();


} catch (PDOException $e) {

    // Roll back only if a transaction is active
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }


    // ------------------------------------------------------------------------
    // DUPLICATE USERNAME
    // ------------------------------------------------------------------------

    if (
        isset($e->errorInfo[1]) &&
        (int) $e->errorInfo[1] === 1062
    ) {
        header(
            "Location: ../views/create_user.php?error=" .
            urlencode("Username already exists.")
        );
        exit();
    }


    // ------------------------------------------------------------------------
    // OTHER DATABASE ERRORS
    // ------------------------------------------------------------------------

    die("Database error: " . $e->getMessage());
}