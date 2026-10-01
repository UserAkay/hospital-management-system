<?php

session_start();

require_once "../config/session_check.php";

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'Admin'
) {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();


/*
|--------------------------------------------------------------------------
| FORMAT CONTACT NUMBER FOR DISPLAY
|--------------------------------------------------------------------------
*/

function formatPhoneNumber($phone)
{
    $phone = trim($phone);

    if ($phone === '') {
        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | INDIA
    |--------------------------------------------------------------------------
    */

    if (preg_match('/^\+91([0-9]{10})$/', $phone, $matches)) {

        $number = $matches[1];

        return '+91 ' .
               substr($number, 0, 5) . ' ' .
               substr($number, 5);
    }

    /*
    |--------------------------------------------------------------------------
    | USA / CANADA
    |--------------------------------------------------------------------------
    */

    if (preg_match('/^\+1([0-9]{10})$/', $phone, $matches)) {

        $number = $matches[1];

        return '+1 ' .
               '(' . substr($number, 0, 3) . ') ' .
               substr($number, 3, 3) . '-' .
               substr($number, 6);
    }

    /*
    |--------------------------------------------------------------------------
    | UK
    |--------------------------------------------------------------------------
    */

    if (preg_match('/^\+44([0-9]+)$/', $phone, $matches)) {

        $number = $matches[1];

        if (strlen($number) >= 10) {

            return '+44 ' .
                   substr($number, 0, 4) . ' ' .
                   substr($number, 4);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GENERIC INTERNATIONAL NUMBER
    |--------------------------------------------------------------------------
    */

    return $phone;
}


/*
|--------------------------------------------------------------------------
| SEARCH + FILTER + SORT
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$specialization = trim(
    $_GET['specialization'] ?? ''
);

$sort = $_GET['sort'] ?? 'id_asc';


/*
|--------------------------------------------------------------------------
| ALLOWED SORT OPTIONS
|--------------------------------------------------------------------------
|
| Doctor ID sorting now uses doctor_code.
|
| IMPORTANT:
| doctor_id is still the internal database primary key.
|
*/

$allowedSorts = [

    'id_asc' => [
        'label' => 'Doctor ID: Low to High',
        'sql'   => 'd.doctor_code ASC'
    ],

    'id_desc' => [
        'label' => 'Doctor ID: High to Low',
        'sql'   => 'd.doctor_code DESC'
    ],

    'name_asc' => [
        'label' => 'Username: A to Z',
        'sql'   => 'u.username ASC'
    ],

    'name_desc' => [
        'label' => 'Username: Z to A',
        'sql'   => 'u.username DESC'
    ],

    'specialization_asc' => [
        'label' => 'Specialization: A to Z',
        'sql'   => 'd.specialization ASC'
    ],

    'specialization_desc' => [
        'label' => 'Specialization: Z to A',
        'sql'   => 'd.specialization DESC'
    ]

];


/*
|--------------------------------------------------------------------------
| FALLBACK TO DEFAULT SORT
|--------------------------------------------------------------------------
*/

if (!array_key_exists($sort, $allowedSorts)) {

    $sort = 'id_asc';
}


/*
|--------------------------------------------------------------------------
| GET ACTIVE DOCTORS
|--------------------------------------------------------------------------
|
| doctor_id:
|   Internal permanent numeric primary key.
|
| doctor_code:
|   Human-facing permanent Doctor ID.
|
*/

$query = "
    SELECT
        d.doctor_id,
        d.doctor_code,
        u.username,
        d.specialization,
        d.contact_number,
        d.is_active

    FROM doctor d

    JOIN users u
        ON d.user_id = u.user_id

    WHERE d.is_active = 1
";

$params = [];


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $query .= "
        AND (
            u.username LIKE :search
            OR d.contact_number LIKE :search
        )
    ";

    $params[':search'] = "%{$search}%";
}


/*
|--------------------------------------------------------------------------
| SPECIALIZATION FILTER
|--------------------------------------------------------------------------
*/

if ($specialization !== '') {

    $query .= "
        AND d.specialization = :specialization
    ";

    $params[':specialization'] = $specialization;
}


/*
|--------------------------------------------------------------------------
| SORT
|--------------------------------------------------------------------------
*/

$query .= "
    ORDER BY " . $allowedSorts[$sort]['sql'];


/*
|--------------------------------------------------------------------------
| SECONDARY SORT
|--------------------------------------------------------------------------
|
| doctor_id is used only internally to make ordering deterministic.
|
*/

$query .= ",
    d.doctor_id ASC
";


/*
|--------------------------------------------------------------------------
| EXECUTE DOCTOR QUERY
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare($query);

$stmt->execute($params);

$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| GET SPECIALIZATION LIST
|--------------------------------------------------------------------------
*/

$spec_query = $conn->query("
    SELECT DISTINCT specialization
    FROM doctor
    WHERE is_active = 1
    ORDER BY specialization ASC
");

$specializations = $spec_query->fetchAll(
    PDO::FETCH_COLUMN
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Active Doctors • HMS</title>


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >


    <!-- Tailwind -->

    <script src="https://cdn.tailwindcss.com"></script>


    <style>

        .glass {

            background: rgba(30, 41, 59, 0.68);

            backdrop-filter: blur(16px);

            -webkit-backdrop-filter: blur(16px);

        }

    </style>

</head>


<body
    class="min-h-screen bg-gradient-to-br from-gray-950 via-indigo-950 to-purple-950 text-gray-100 p-6"
>


<div class="max-w-7xl mx-auto">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div
        class="glass rounded-3xl shadow-2xl border border-gray-700 mb-8"
    >

        <div
            class="px-6 py-10 flex flex-col md:flex-row justify-between items-center gap-6"
        >

            <div>

                <h1 class="text-3xl font-bold">

                    Active Doctors

                </h1>

                <p class="text-cyan-200 mt-2">

                    Currently available medical professionals

                </p>

            </div>


            <div class="flex gap-4 flex-wrap justify-center">

                <a
                    href="add_doctor.php"
                    class="px-6 py-3 bg-cyan-600 hover:bg-cyan-700 rounded-xl font-semibold transition"
                >

                    <i class="fas fa-user-plus mr-1"></i>

                    Add Doctor

                </a>


                <a
                    href="manage_archived_doctors.php"
                    class="px-6 py-3 border border-cyan-500 text-cyan-400 hover:bg-cyan-500 hover:text-white rounded-xl transition"
                >

                    <i class="fas fa-user-slash mr-1"></i>

                    View Deactivated

                </a>

            </div>

        </div>

    </div>


    <!-- =====================================================
         SEARCH + FILTER + SORT
    ====================================================== -->

    <div
        class="glass rounded-xl border border-gray-700 p-6 mb-6"
    >

        <form
            method="GET"
            class="flex flex-wrap gap-4 items-center"
        >


            <!-- SEARCH -->

            <input
                type="text"
                name="search"
                placeholder="Search doctor name or contact"
                value="<?= htmlspecialchars(
                    $search,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
                class="px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
            >


            <!-- SPECIALIZATION -->

            <select
                name="specialization"
                class="px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
            >

                <option value="">
                    All Specializations
                </option>


                <?php foreach ($specializations as $spec): ?>

                    <option
                        value="<?= htmlspecialchars(
                            $spec,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= ($specialization === $spec)
                            ? 'selected'
                            : '' ?>
                    >

                        <?= htmlspecialchars(
                            $spec,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>


            <!-- SORT -->

            <select
                name="sort"
                class="px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-cyan-500"
            >

                <?php foreach ($allowedSorts as $sortKey => $sortOption): ?>

                    <option
                        value="<?= htmlspecialchars(
                            $sortKey,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>"
                        <?= ($sort === $sortKey)
                            ? 'selected'
                            : '' ?>
                    >

                        <?= htmlspecialchars(
                            $sortOption['label'],
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>


            <!-- SEARCH BUTTON -->

            <button
                type="submit"
                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 rounded-lg transition font-semibold"
            >

                <i class="fas fa-search mr-1"></i>

                Search

            </button>


            <!-- RESET -->

            <a
                href="manage_doctors.php"
                class="px-6 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition font-semibold"
            >

                <i class="fas fa-rotate-left mr-1"></i>

                Reset

            </a>

        </form>

    </div>


    <!-- =====================================================
         DOCTOR TABLE
    ====================================================== -->

    <div
        class="glass rounded-3xl border border-gray-700 overflow-hidden"
    >

        <?php if (count($doctors) > 0): ?>

            <div class="overflow-x-auto">

                <table class="w-full text-left">


                    <!-- =================================================
                         TABLE HEADER
                    ================================================== -->

                    <thead class="bg-gray-800">

                        <tr>

                            <th class="px-6 py-4">
                                Serial No.
                            </th>

                            <th class="px-6 py-4">
                                Doctor ID
                            </th>

                            <th class="px-6 py-4">
                                Username
                            </th>

                            <th class="px-6 py-4">
                                Specialization
                            </th>

                            <th class="px-6 py-4">
                                Contact
                            </th>

                            <th class="px-6 py-4">
                                Status
                            </th>

                            <th class="px-6 py-4 text-center">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <!-- =================================================
                         TABLE BODY
                    ================================================== -->

                    <tbody>

                        <?php foreach ($doctors as $index => $doctor): ?>

                            <tr
                                class="border-b border-gray-700 hover:bg-gray-800 transition"
                            >


                                <!-- =================================================
                                     SERIAL NUMBER
                                     =================================================
                                     This is NOT stored in the database.
                                     It is generated from the current displayed
                                     sorted/filtered list.
                                ================================================== -->

                                <td class="px-6 py-4 font-semibold">

                                    #<?= $index + 1 ?>

                                </td>


                                <!-- =================================================
                                     PERMANENT DOCTOR ID
                                     =================================================
                                     This is doctor_code.
                                     Example: DOC-000001
                                ================================================== -->

                                <td class="px-6 py-4">

                                    <span
                                        class="font-semibold text-cyan-300 whitespace-nowrap"
                                    >

                                        <?= htmlspecialchars(
                                            $doctor['doctor_code'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- USERNAME -->

                                <td class="px-6 py-4">

                                    <?= htmlspecialchars(
                                        $doctor['username'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- SPECIALIZATION -->

                                <td class="px-6 py-4">

                                    <?= htmlspecialchars(
                                        $doctor['specialization'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>

                                </td>


                                <!-- CONTACT -->

                                <td class="px-6 py-4">

                                    <?php

                                    $formattedPhone =
                                        formatPhoneNumber(
                                            $doctor['contact_number']
                                        );

                                    ?>

                                    <span
                                        class="font-medium text-cyan-200 whitespace-nowrap"
                                    >

                                        <?= htmlspecialchars(
                                            $formattedPhone,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>

                                    </span>

                                </td>


                                <!-- STATUS -->

                                <td class="px-6 py-4">

                                    <span
                                        class="px-3 py-1 bg-green-800 text-green-300 rounded-full text-sm"
                                    >

                                        Active

                                    </span>

                                </td>


                                <!-- =================================================
                                     ACTIONS
                                ================================================== -->

                                <td class="px-6 py-4 text-center">

                                    <div
                                        class="flex justify-center gap-3"
                                    >


                                        <!-- EDIT -->

                                        <a
                                            href="edit_doctor.php?id=<?= (int)$doctor['doctor_id'] ?>"
                                            class="px-3 py-2 bg-cyan-800 hover:bg-cyan-700 rounded transition"
                                        >

                                            <i class="fas fa-edit mr-1"></i>

                                            Edit

                                        </a>


                                        <!-- DEACTIVATE -->

                                        <form
                                            action="../controllers/DeactivateDoctorController.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="doctor_id"
                                                value="<?= (int)$doctor['doctor_id'] ?>"
                                            >


                                            <button
                                                type="submit"
                                                onclick="return confirm('Deactivate this doctor?')"
                                                class="px-3 py-2 bg-red-700 hover:bg-red-800 rounded transition"
                                            >

                                                <i class="fas fa-user-slash mr-1"></i>

                                                Deactivate

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <!-- =================================================
                 NO DOCTORS
            ================================================== -->

            <div
                class="py-20 text-center text-gray-400"
            >

                <i
                    class="fas fa-user-doctor text-6xl mb-6"
                ></i>


                <p class="text-lg">

                    No active doctors found.

                </p>

            </div>

        <?php endif; ?>

    </div>


    <!-- =====================================================
         BACK BUTTON
    ====================================================== -->

    <div class="mt-10 text-center">

        <a
            href="admin_dashboard.php"
            class="px-8 py-4 bg-gray-800 hover:bg-gray-700 rounded-xl transition"
        >

            <i class="fas fa-arrow-left mr-1"></i>

            Back to Dashboard

        </a>

    </div>


</div>

</body>

</html>