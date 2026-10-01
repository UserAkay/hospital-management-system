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

try {
    $stmt = $conn->prepare("
        SELECT patient_id, full_name, gender, date_of_birth,
               contact_number, address, created_at
        FROM patient
        WHERE is_active = 0
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching archived patients: " . htmlspecialchars($e->getMessage()));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Archived Patients • HMS</title>

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

<body class="min-h-screen bg-gradient-to-br from-gray-950 via-indigo-950 to-purple-950 text-gray-100 antialiased px-4 py-6 md:px-8 md:py-10 relative overflow-x-hidden">

    <!-- Floating background icons -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-15">
        <i class="fa-solid fa-box-archive absolute text-9xl text-cyan-500 animate-pulse-slow -left-20 top-10 rotate-12"></i>
        <i class="fa-solid fa-users-slash absolute text-8xl text-red-400/70 animate-float right-8 bottom-20 -rotate-6"></i>
        <i class="fa-solid fa-hospital absolute text-7xl text-indigo-400 animate-pulse-slow -right-16 top-1/4"></i>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-8 p-6 bg-emerald-950/60 border-l-4 border-emerald-500 rounded-2xl text-emerald-200 flex items-start gap-4 shadow-lg">
            <i class="fas fa-check-circle text-3xl mt-1 flex-shrink-0"></i>
            <div class="text-base font-medium">
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
        </div>
        <?php unset($_SESSION['success']); endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-8 p-6 bg-red-950/60 border-l-4 border-red-500 rounded-2xl text-red-200 flex items-start gap-4 shadow-lg">
            <i class="fas fa-circle-exclamation text-3xl mt-1 flex-shrink-0"></i>
            <div class="text-base font-medium">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
        </div>
        <?php unset($_SESSION['error']); endif; ?>

        <!-- Header -->
        <div class="glass rounded-3xl shadow-2xl overflow-hidden border border-gray-700/40 mb-10">
            <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-gray-950 px-6 py-10 md:py-12 flex flex-col md:flex-row justify-between items-center gap-6 text-center md:text-left">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-lg">
                        Archived Patients
                    </h1>
                    <p class="mt-2 text-cyan-100/80 text-lg">
                        Deactivated / Inactive patient records
                    </p>
                </div>

                <a href="manage_patients.php"
                   class="inline-flex items-center gap-3 px-7 py-4 bg-gray-700/60 hover:bg-gray-600/70 text-gray-300 hover:text-white font-semibold rounded-xl border border-gray-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i class="fas fa-users"></i>
                    Active Patients
                </a>
            </div>
        </div>

        <!-- Table Card -->
        <div class="glass rounded-3xl shadow-2xl overflow-hidden border border-gray-700/40">
            <?php if (empty($patients)): ?>
                <div class="py-24 px-6 text-center text-gray-400">
                    <i class="fas fa-box-archive text-8xl opacity-30 mb-6 block"></i>
                    <p class="text-xl font-medium">No archived patients found.</p>
                    <p class="mt-2 text-gray-500">Archived patient records will appear here.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-800/70">
                            <tr>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Gender</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Date of Birth</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Address</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Archived On</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <?php foreach ($patients as $row): ?>
                                <tr class="hover:bg-gray-800/40 transition-all duration-200 group">
                                    <td class="px-6 py-5 text-gray-300"><?= htmlspecialchars($row['full_name'] ?: '—') ?></td>
                                    <td class="px-6 py-5 text-gray-300"><?= htmlspecialchars($row['gender'] ?: '—') ?></td>
                                    <td class="px-6 py-5 text-gray-300"><?= htmlspecialchars($row['date_of_birth'] ?: '—') ?></td>
                                    <td class="px-6 py-5 text-gray-300"><?= htmlspecialchars($row['contact_number'] ?: '—') ?></td>
                                    <td class="px-6 py-5 text-gray-300"><?= htmlspecialchars($row['address'] ?: '—') ?></td>
                                    <td class="px-6 py-5 text-gray-300">
                                        <?= htmlspecialchars(date('d M Y • H:i', strtotime($row['created_at']))) ?>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <form action="../controllers/RestorePatientController.php" method="POST" class="inline">
                                            <input type="hidden" name="patient_id" value="<?= $row['patient_id'] ?>">
                                            <button type="submit"
                                                    onclick="return confirm('Restore this patient record?');"
                                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-900/60 hover:bg-emerald-800/70 text-emerald-300 hover:text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                                <i class="fas fa-undo"></i>
                                                Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Dashboard link -->
        <div class="mt-12 text-center">
            <a href="admin_dashboard.php"
               class="inline-flex items-center gap-3 px-8 py-4 bg-gray-800/50 hover:bg-gray-700/60 text-gray-300 hover:text-white rounded-2xl border border-gray-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

    </div>

</body>
</html>