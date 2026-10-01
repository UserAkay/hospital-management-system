<?php

session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'Admin'
) {
    header("Location: ../views/login.php");
    exit();
}

require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();


/*
|--------------------------------------------------------------------------
| ONLY POST REQUESTS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../views/add_doctor.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$username = trim($_POST['username'] ?? '');
$plainPassword = $_POST['password'] ?? '';
$specialization = trim($_POST['specialization'] ?? '');
$contact_number = trim($_POST['contact_number'] ?? '');


/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if (
    $username === '' ||
    $plainPassword === '' ||
    $specialization === ''
) {
    header(
        "Location: ../views/add_doctor.php?error=" .
        urlencode("Please fill in all required fields.")
    );
    exit();
}


/*
|--------------------------------------------------------------------------
| HASH PASSWORD
|--------------------------------------------------------------------------
*/

$password = password_hash(
    $plainPassword,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| GENERATE UNIQUE PERMANENT DOCTOR CODE
|--------------------------------------------------------------------------
|
| Format:
| DOC-XXXXXX
|
| Example:
| DOC-A7K92M
|
| This code is permanent.
|
*/

function generateDoctorCode(PDO $conn)
{
    $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    $length = strlen($characters);

    for ($attempt = 0; $attempt < 20; $attempt++) {

        $code = 'DOC-';

        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[
                random_int(0, $length - 1)
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | CHECK DATABASE
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare("
            SELECT doctor_id
            FROM doctor
            WHERE doctor_code = :doctor_code
            LIMIT 1
        ");

        $check->execute([
            ':doctor_code' => $code
        ]);


        if (!$check->fetch()) {
            return $code;
        }
    }


    throw new Exception(
        "Unable to generate a unique Doctor Code."
    );
}


/*
|--------------------------------------------------------------------------
| INSERT USER + DOCTOR
|--------------------------------------------------------------------------
*/

try {

    $conn->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | CHECK USERNAME
    |--------------------------------------------------------------------------
    */

    $checkUsername = $conn->prepare("
        SELECT user_id
        FROM users
        WHERE username = :username
        LIMIT 1
    ");

    $checkUsername->execute([
        ':username' => $username
    ]);


    if ($checkUsername->fetch()) {

        $conn->rollBack();

        header(
            "Location: ../views/add_doctor.php?error=" .
            urlencode("Username already exists.")
        );

        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT INTO USERS
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | This matches your original project.
    |
    */

    $stmt1 = $conn->prepare("
        INSERT INTO users
        (
            username,
            password,
            role
        )
        VALUES
        (
            :username,
            :password,
            'Doctor'
        )
    ");

    $stmt1->execute([
        ':username' => $username,
        ':password' => $password
    ]);


    /*
    |--------------------------------------------------------------------------
    | GET USER ID
    |--------------------------------------------------------------------------
    */

    $user_id = $conn->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | GENERATE DOCTOR CODE
    |--------------------------------------------------------------------------
    */

    $doctor_code = generateDoctorCode($conn);


    /*
    |--------------------------------------------------------------------------
    | INSERT INTO DOCTOR
    |--------------------------------------------------------------------------
    |
    | doctor_id is NOT manually supplied.
    |
    | MySQL continues to generate the numeric doctor_id.
    |
    */

    $stmt2 = $conn->prepare("
        INSERT INTO doctor
        (
            doctor_code,
            specialization,
            contact_number,
            is_active,
            user_id
        )
        VALUES
        (
            :doctor_code,
            :specialization,
            :contact_number,
            1,
            :user_id
        )
    ");

    $stmt2->execute([
        ':doctor_code' => $doctor_code,
        ':specialization' => $specialization,
        ':contact_number' => $contact_number,
        ':user_id' => $user_id
    ]);


    /*
    |--------------------------------------------------------------------------
    | GET ORIGINAL INTERNAL NUMERIC DOCTOR ID
    |--------------------------------------------------------------------------
    */

    $doctor_id = $conn->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | COMMIT EVERYTHING
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | SEND TO SCHEDULE SETUP
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | set_schedule.php still receives numeric doctor_id.
    |
    | We are NOT replacing doctor_id.
    |
    */

    header(
        "Location: ../views/set_schedule.php?doctor_id=" .
        urlencode($doctor_id)
    );

    exit();


} catch (Throwable $e) {


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
    | HANDLE DUPLICATE KEY
    |--------------------------------------------------------------------------
    */

    if (
        $e instanceof PDOException &&
        isset($e->errorInfo[1]) &&
        $e->errorInfo[1] == 1062
    ) {

        header(
            "Location: ../views/add_doctor.php?error=" .
            urlencode("Username or Doctor Code already exists.")
        );

        exit();
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW ERROR
    |--------------------------------------------------------------------------
    |
    | Temporarily show the real error so we can identify
    | any remaining database problem.
    |
    */

    die(
        "Unable to add doctor.<br><br>" .
        htmlspecialchars(
            $e->getMessage(),
            ENT_QUOTES,
            'UTF-8'
        )
    );
}
?>