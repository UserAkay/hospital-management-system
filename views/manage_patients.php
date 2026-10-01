<?php
session_start();
require_once "../config/session_check.php";

/* Allow Admin + Receptionist */
if (!isset($_SESSION['user_id']) || 
   !in_array(strtolower($_SESSION['role']), ['admin','receptionist'])) {

    header("Location: login.php");
    exit();
}

require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

/* SEARCH + FILTER */
$search = $_GET['search'] ?? '';
$gender = $_GET['gender'] ?? '';

$query = "SELECT p.patient_id, p.full_name, p.gender, p.date_of_birth,
                 p.contact_number, p.address, u.username
          FROM patient p
          JOIN users u ON p.user_id = u.user_id
          WHERE p.is_active = TRUE";

$params = [];

if (!empty($search)) {
    $query .= " AND (
        p.full_name LIKE :search1
        OR u.username LIKE :search2
        OR p.contact_number LIKE :search3
    )";
    $searchTerm = "%$search%";
    $params['search1'] = $searchTerm;
    $params['search2'] = $searchTerm;
    $params['search3'] = $searchTerm;
}

if (!empty($gender)) {
    $query .= " AND p.gender = :gender";
    $params['gender'] = $gender;
}

$query .= " ORDER BY p.patient_id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Patients • HMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .glass {
            background: rgba(30, 41, 59, 0.78);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(75, 85, 99, 0.35);
        }
        .patient-card:hover, tr:hover {
            background-color: rgba(55, 65, 81, 0.4);
            transform: translateY(-1px);
        }
        .gender-male { @apply bg-blue-950/60 text-blue-300 border-blue-700/40; }
        .gender-female { @apply bg-pink-950/60 text-pink-300 border-pink-700/40; }
        .gender-other { @apply bg-purple-950/60 text-purple-300 border-purple-700/40; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-950 via-slate-950 to-indigo-950/40 text-gray-100 antialiased">

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-10">

    <!-- Header -->
    <div class="glass rounded-2xl p-6 lg:p-8 mb-8 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <div>
                <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-cyan-400 via-blue-400 to-indigo-400 bg-clip-text text-transparent">
                    Active Patients
                </h1>
                <p class="mt-2 text-gray-300 flex items-center gap-2">
                    <i class="fas fa-hospital-user text-cyan-400"></i>
                    Manage currently registered & active patient records
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="add_patient.php"
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-600 to-teal-600 hover:from-cyan-500 hover:to-teal-500 rounded-xl font-medium shadow-lg shadow-cyan-900/30 transition-all duration-300">
                    <i class="fas fa-user-plus mr-2"></i> Add Patient
                </a>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="glass rounded-xl p-5 lg:p-6 mb-7">
        <form method="GET" class="flex flex-col sm:flex-row flex-wrap gap-4">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search name, username or phone..."
                class="flex-1 min-w-[260px] px-5 py-3 bg-gray-800/60 border border-gray-700 rounded-xl focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30 transition"
            >
            <select
                name="gender"
                class="px-5 py-3 bg-gray-800/60 border border-gray-700 rounded-xl focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30 min-w-[160px]">
                <option value="">All Genders</option>
                <option value="Male"   <?= $gender === 'Male'   ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other"  <?= $gender === 'Other'  ? 'selected' : '' ?>>Other</option>
            </select>
            <div class="flex gap-3">
                <button type="submit"
                        class="px-7 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 rounded-xl font-medium shadow-md shadow-blue-900/30 transition-all">
                    <i class="fas fa-search mr-2"></i>Search
                </button>
                <a href="manage_patients.php"
                   class="px-7 py-3 bg-gray-700/70 hover:bg-gray-600/70 border border-gray-600 rounded-xl transition-all text-center font-medium">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="../exports/export_patients_csv.php?search=<?= urlencode($search) ?>&gender=<?= urlencode($gender) ?>"
           class="inline-flex items-center px-5 py-2.5 bg-emerald-900/50 hover:bg-emerald-800/60 border border-emerald-700/40 rounded-lg transition text-emerald-200">
            <i class="fas fa-file-csv mr-2"></i> CSV
        </a>
        <a href="../exports/export_patients_excel.php?search=<?= urlencode($search) ?>&gender=<?= urlencode($gender) ?>"
           class="inline-flex items-center px-5 py-2.5 bg-green-900/50 hover:bg-green-800/60 border border-green-700/40 rounded-lg transition text-green-200">
            <i class="fas fa-file-excel mr-2"></i> Excel
        </a>
        <a href="../exports/export_patients_pdf.php?search=<?= urlencode($search) ?>&gender=<?= urlencode($gender) ?>"
           class="inline-flex items-center px-5 py-2.5 bg-rose-900/50 hover:bg-rose-800/60 border border-rose-700/40 rounded-lg transition text-rose-200">
            <i class="fas fa-file-pdf mr-2"></i> PDF
        </a>
    </div>

    <!-- Patient List -->
    <?php if (count($patients) > 0): ?>

        <!-- Desktop Table -->
        <div class="glass rounded-2xl overflow-hidden shadow-2xl hidden lg:block">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gradient-to-r from-gray-800 to-gray-900">
                        <tr>
                            <th class="px-6 py-4 font-semibold text-gray-200">ID</th>
                            <th class="px-6 py-4 font-semibold text-gray-200">Username</th>
                            <th class="px-6 py-4 font-semibold text-gray-200">Full Name</th>
                            <th class="px-6 py-4 font-semibold text-gray-200">Gender</th>
                            <th class="px-6 py-4 font-semibold text-gray-200">DOB</th>
                            <th class="px-6 py-4 font-semibold text-gray-200">Contact</th>
                            <th class="px-6 py-4 font-semibold text-gray-200 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <?php foreach ($patients as $p): ?>
                            <tr class="hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 font-medium text-cyan-300">#<?= htmlspecialchars($p['patient_id']) ?></td>
                                <td class="px-6 py-4 text-gray-300">@<?= htmlspecialchars($p['username']) ?></td>
                                <td class="px-6 py-4 font-medium text-white"><?= htmlspecialchars($p['full_name']) ?></td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 text-xs font-medium rounded-full border
                                        <?= $p['gender'] === 'Male' ? 'gender-male' : '' ?>
                                        <?= $p['gender'] === 'Female' ? 'gender-female' : '' ?>
                                        <?= $p['gender'] === 'Other' ? 'gender-other' : '' ?>">
                                        <?= htmlspecialchars($p['gender'] ?: '—') ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-300"><?= htmlspecialchars($p['date_of_birth'] ?: '—') ?></td>
                                <td class="px-6 py-4 text-gray-300"><?= htmlspecialchars($p['contact_number'] ?: '—') ?></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-3">
                                        <a href="edit_patient.php?id=<?= $p['patient_id'] ?>"
                                           class="p-2.5 bg-cyan-900/50 hover:bg-cyan-800/70 rounded-lg text-cyan-300 hover:text-cyan-100 transition">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="../controllers/DeletePatientController.php" class="inline">
                                            <input type="hidden" name="patient_id" value="<?= $p['patient_id'] ?>">
                                            <button type="submit" onclick="return confirm('Archive this patient?')"
                                                    class="p-2.5 bg-red-900/50 hover:bg-red-800/70 rounded-lg text-red-300 hover:text-red-100 transition">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden space-y-5">
            <?php foreach ($patients as $p): ?>
                <div class="glass rounded-xl p-5 shadow-lg patient-card transition-all">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <div class="text-lg font-bold text-white"><?= htmlspecialchars($p['full_name']) ?></div>
                            <div class="text-sm text-gray-400">@<?= htmlspecialchars($p['username']) ?></div>
                        </div>
                        <span class="inline-flex px-3 py-1 text-xs font-medium rounded-full border
                            <?= $p['gender'] === 'Male' ? 'gender-male' : '' ?>
                            <?= $p['gender'] === 'Female' ? 'gender-female' : '' ?>
                            <?= $p['gender'] === 'Other' ? 'gender-other' : '' ?>">
                            <?= htmlspecialchars($p['gender'] ?: '—') ?>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                        <div>
                            <span class="text-gray-400 block">ID</span>
                            #<?= htmlspecialchars($p['patient_id']) ?>
                        </div>
                        <div>
                            <span class="text-gray-400 block">DOB</span>
                            <?= htmlspecialchars($p['date_of_birth'] ?: '—') ?>
                        </div>
                        <div class="col-span-2">
                            <span class="text-gray-400 block">Contact</span>
                            <?= htmlspecialchars($p['contact_number'] ?: '—') ?>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="edit_patient.php?id=<?= $p['patient_id'] ?>"
                           class="flex-1 py-2.5 bg-cyan-900/50 hover:bg-cyan-800/70 rounded-lg text-center text-cyan-300 hover:text-cyan-100 transition">
                            <i class="fas fa-edit mr-2"></i>Edit
                        </a>
                        <form method="POST" action="../controllers/DeletePatientController.php" class="flex-1">
                            <input type="hidden" name="patient_id" value="<?= $p['patient_id'] ?>">
                            <button type="submit" onclick="return confirm('Archive this patient?')"
                                    class="w-full py-2.5 bg-red-900/50 hover:bg-red-800/70 rounded-lg text-red-300 hover:text-red-100 transition">
                                <i class="fas fa-archive mr-2"></i>Archive
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="glass rounded-2xl p-12 text-center">
            <i class="fas fa-users-slash text-6xl text-gray-600 mb-6 block"></i>
            <h3 class="text-2xl font-semibold text-gray-200 mb-3">No active patients found</h3>
            <p class="text-gray-400 max-w-md mx-auto">
                Try changing search terms or gender filter
            </p>
        </div>
    <?php endif; ?>

    <!-- Back Button -->
    <div class="mt-10 text-center">
        <a href="admin_dashboard.php"
           class="inline-flex items-center px-8 py-4 bg-gray-800/70 hover:bg-gray-700/80 border border-gray-700 rounded-xl text-lg font-medium transition-all shadow-lg">
            <i class="fas fa-arrow-left mr-3"></i> Back to Dashboard
        </a>
    </div>

</div>

</body>
</html>