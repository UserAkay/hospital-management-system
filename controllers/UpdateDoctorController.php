<?php

session_start();


/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'Admin'
) {
    header("Location: ../views/login.php");
    exit();
}


require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| ONLY ALLOW POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../views/manage_doctors.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| GET AND CLEAN INPUT
|--------------------------------------------------------------------------
*/

$doctor_id = filter_input(
    INPUT_POST,
    'doctor_id',
    FILTER_VALIDATE_INT
);

$username = trim($_POST['username'] ?? '');

$specialization = trim(
    $_POST['specialization'] ?? ''
);

$contact_number = trim(
    $_POST['contact_number'] ?? ''
);


/*
|--------------------------------------------------------------------------
| BASIC VALIDATION
|--------------------------------------------------------------------------
*/

if ($doctor_id === false || $doctor_id === null || $doctor_id <= 0) {

    header(
        "Location: ../views/manage_doctors.php?error=" .
        urlencode("Invalid doctor ID.")
    );

    exit();
}


if ($username === '') {

    header(
        "Location: ../views/edit_doctor.php?id=" .
        $doctor_id .
        "&error=" .
        urlencode("Username is required.")
    );

    exit();
}


if ($specialization === '') {

    header(
        "Location: ../views/edit_doctor.php?id=" .
        $doctor_id .
        "&error=" .
        urlencode("Specialization is required.")
    );

    exit();
}


if ($contact_number === '') {

    header(
        "Location: ../views/edit_doctor.php?id=" .
        $doctor_id .
        "&error=" .
        urlencode("Contact number is required.")
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| NORMALIZE CONTACT NUMBER
|--------------------------------------------------------------------------
|
| Examples:
|
| +91 98765 43210
|        ↓
| +919876543210
|
| +1 (123) 456-7890
|        ↓
| +11234567890
|
| +44 1234 567890
|        ↓
| +441234567890
|
| The database therefore keeps one consistent format.
|
*/

$normalizedPhone = preg_replace(
    '/[\s\-\(\)]/',
    '',
    $contact_number
);


/*
|--------------------------------------------------------------------------
| VALIDATE NORMALIZED PHONE
|--------------------------------------------------------------------------
|
| Allows:
| + followed by 8 to 15 digits
|
| This follows the general international phone-number
| length convention without restricting the application
| to India only.
|
*/

if (
    $normalizedPhone === null ||
    !preg_match('/^\+[0-9]{8,15}$/', $normalizedPhone)
) {

    header(
        "Location: ../views/edit_doctor.php?id=" .
        $doctor_id .
        "&error=" .
        urlencode(
            "Please enter a valid international contact number."
        )
    );

    exit();
}


$contact_number = $normalizedPhone;


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

$database = new Database();

$conn = $database->connect();


/*
|--------------------------------------------------------------------------
| UPDATE DOCTOR
|--------------------------------------------------------------------------
*/

try {

    $conn->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | GET LINKED USER ID
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        SELECT user_id
        FROM doctor
        WHERE doctor_id = :doctor_id
    ");

    $stmt->execute([
        ':doctor_id' => $doctor_id
    ]);

    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$doctor) {

        throw new Exception(
            "Doctor not found."
        );
    }


    $user_id = (int) $doctor['user_id'];


    /*
    |--------------------------------------------------------------------------
    | CHECK USERNAME DUPLICATE
    |--------------------------------------------------------------------------
    |
    | Prevent another user from already having the same username.
    |
    */

    $usernameCheck = $conn->prepare("
        SELECT user_id
        FROM users
        WHERE username = :username
          AND user_id != :user_id
        LIMIT 1
    ");

    $usernameCheck->execute([
        ':username' => $username,
        ':user_id' => $user_id
    ]);


    if ($usernameCheck->fetch(PDO::FETCH_ASSOC)) {

        throw new Exception(
            "Username is already in use."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK CONTACT NUMBER DUPLICATE
    |--------------------------------------------------------------------------
    |
    | Prevent another doctor from using the same contact number.
    |
    */

    $contactCheck = $conn->prepare("
        SELECT doctor_id
        FROM doctor
        WHERE contact_number = :contact_number
          AND doctor_id != :doctor_id
        LIMIT 1
    ");

    $contactCheck->execute([
        ':contact_number' => $contact_number,
        ':doctor_id' => $doctor_id
    ]);


    if ($contactCheck->fetch(PDO::FETCH_ASSOC)) {

        throw new Exception(
            "Contact number is already assigned to another doctor."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE USERNAME
    |--------------------------------------------------------------------------
    */

    $updateUser = $conn->prepare("
        UPDATE users
        SET username = :username
        WHERE user_id = :user_id
    ");

    $updateUser->execute([
        ':username' => $username,
        ':user_id' => $user_id
    ]);


    /*
    |--------------------------------------------------------------------------
    | UPDATE DOCTOR
    |--------------------------------------------------------------------------
    */

    $updateDoctor = $conn->prepare("
        UPDATE doctor
        SET
            specialization = :specialization,
            contact_number = :contact_number
        WHERE doctor_id = :doctor_id
    ");

    $updateDoctor->execute([
        ':specialization' => $specialization,
        ':contact_number' => $contact_number,
        ':doctor_id' => $doctor_id
    ]);


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../views/manage_doctors.php?success=" .
        urlencode("Doctor updated successfully.")
    );

    exit();


} catch (Exception $e) {


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    if ($conn->inTransaction()) {

        $conn->rollBack();
    }


    /*
    |--------------------------------------------------------------------------
    | ERROR
    |--------------------------------------------------------------------------
    */

    header(
        "Location: ../views/edit_doctor.php?id=" .
        $doctor_id .
        "&error=" .
        urlencode($e->getMessage())
    );

    exit();
}