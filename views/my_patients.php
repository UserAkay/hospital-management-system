<?php
session_start();
require_once "../config/session_check.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'doctor') {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

/* GET DOCTOR ID */
$stmt = $conn->prepare("SELECT doctor_id FROM doctor WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    die("Doctor record not found.");
}

$doctor_id = $doctor['doctor_id'];

/* SEARCH */
$search = trim($_GET['search'] ?? '');

/* FETCH PATIENTS */
$sql = "
    SELECT 
        p.patient_id,
        p.full_name,
        u.username,
        MAX(a.appointment_date) AS last_visit
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN users u ON p.user_id = u.user_id
    WHERE a.doctor_id = :doctor_id
";

$params = [':doctor_id' => $doctor_id];

if ($search !== '') {
    $sql .= " AND (p.full_name LIKE :search OR u.username LIKE :search OR p.patient_id LIKE :search)";
    $searchParam = "%$search%";
    $params[':search'] = $searchParam;
}

$sql .= "
    GROUP BY p.patient_id, p.full_name, u.username
    ORDER BY last_visit DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>My Patients • HMS</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
          integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'pulse-slow': 'pulse 5s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 8s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-16px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .glass {
            background: rgba(30, 41, 59, 0.68);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-950 via-indigo-950 to-purple-950 text-gray-100 antialiased px-5 py-8 md:px-10 md:py-12 relative overflow-x-hidden">

    <!-- Floating background icons -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-12">
        <i class="fa-solid fa-users absolute text-9xl text-cyan-500 animate-pulse-slow -left-20 top-20 rotate-12"></i>
        <i class="fa-solid fa-hospital-user absolute text-8xl text-blue-400 animate-float right-10 bottom-20 -rotate-6"></i>
        <i class="fa-solid fa-notes-medical absolute text-7xl text-indigo-400 animate-pulse-slow -right-16 top-1/3"></i>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-12">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-cyan-700 to-blue-800 rounded-full flex items-center justify-center mb-6 shadow-xl animate-float border-2 border-cyan-400/40">
                <i class="fa-solid fa-users text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-lg">
                My Patients
            </h1>
            <p class="mt-4 text-xl text-cyan-100/80">
                View and manage your patient list
            </p>
        </div>

        <!-- Back to Dashboard -->
        <div class="text-center mb-10">
            <a href="doctor_dashboard.php"
               class="inline-flex items-center gap-3 px-8 py-4 bg-gray-800/50 hover:bg-gray-700/60 text-gray-300 hover:text-white rounded-2xl border border-gray-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <!-- Search Bar -->
        <div class="glass rounded-2xl p-6 mb-10 border border-gray-700/40">
            <form method="GET" class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search text-xl"></i>
                    </div>
                    <input
                        type="text"
                        name="search"
                        placeholder="Search by patient name, username or ID..."
                        value="<?= htmlspecialchars($search) ?>"
                        class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                    >
                </div>
                <button type="submit"
                        class="px-8 py-4 bg-cyan-600 hover:bg-cyan-700 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center gap-3">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </form>
        </div>

        <!-- Patient List -->
        <?php if (empty($patients)): ?>
            <div class="glass rounded-2xl p-16 text-center text-gray-400 border border-gray-700/40">
                <i class="fas fa-users-slash text-8xl opacity-40 mb-6 block"></i>
                <h3 class="text-2xl font-bold text-white mb-4">No patients found</h3>
                <p class="text-lg">
                    <?= $search ? 'No matches for your search term.' : 'You don\'t have any registered patients yet.' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="glass rounded-2xl shadow-2xl overflow-hidden border border-gray-700/40">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-800/70">
                            <tr>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Patient Name</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Last Visit</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <?php foreach ($patients as $p): ?>
                                <tr class="hover:bg-gray-800/40 transition-all duration-200 group">
                                    <td class="px-6 py-5 font-medium text-white">
                                        <?= htmlspecialchars($p['full_name'] ?: $p['username']) ?>
                                    </td>
                                    <td class="px-6 py-5 text-gray-300">
                                        <?= htmlspecialchars($p['username'] ?: '—') ?>
                                    </td>
                                    <td class="px-6 py-5 text-gray-300">
                                        <?= $p['last_visit'] ? htmlspecialchars(date('d M Y', strtotime($p['last_visit']))) : 'Never' ?>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <div class="flex items-center justify-center gap-4 flex-wrap">
                                            <a href="view_patient_visits.php?patient_id=<?= $p['patient_id'] ?>"
                                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-cyan-900/60 hover:bg-cyan-800/70 text-cyan-300 hover:text-white rounded-lg transition-all duration-200">
                                                <i class="fas fa-history"></i>
                                                Visit History
                                            </a>
                                            <a href="view_medical_records.php?patient_id=<?= $p['patient_id'] ?>"
                                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-900/60 hover:bg-indigo-800/70 text-indigo-300 hover:text-white rounded-lg transition-all duration-200">
                                                <i class="fas fa-notes-medical"></i>
                                                Medical Records
                                            </a>
                                            <a href="../views/create_bill.php?patient_id=<?= $patient['patient_id'] ?>">
    Create Bill
</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-center text-gray-500 text-sm mt-6">
                Showing <?= count($patients) ?> patient<?= count($patients) === 1 ? '' : 's' ?>
            </p>
        <?php endif; ?>

    </div>

</body>
</html>