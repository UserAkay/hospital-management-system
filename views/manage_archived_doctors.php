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

$query = "SELECT d.doctor_id,
                 u.username,
                 d.specialization,
                 d.contact_number,
                 d.is_active
          FROM doctor d
          JOIN users u ON d.user_id = u.user_id
          WHERE d.is_active = 0
          ORDER BY d.doctor_id DESC";

$stmt = $conn->prepare($query);
$stmt->execute();
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Archived Doctors • HMS</title>

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

    <!-- Floating medical icons background -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-15">
        <i class="fa-solid fa-box-archive absolute text-9xl text-cyan-500 animate-pulse-slow -left-20 top-10 rotate-12"></i>
        <i class="fa-solid fa-user-slash absolute text-10xl text-red-400/70 animate-float right-8 bottom-20 -rotate-6"></i>
        <i class="fa-solid fa-hospital absolute text-8xl text-indigo-400 animate-pulse-slow -right-16 top-1/4"></i>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <!-- Header -->
        <div class="glass rounded-3xl shadow-2xl overflow-hidden border border-gray-700/40 mb-10">
            <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-gray-950 px-6 py-10 md:py-12 flex flex-col md:flex-row justify-between items-center gap-6 text-center md:text-left">
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-lg">
                        Archived Doctors
                    </h1>
                    <p class="mt-2 text-cyan-100/80 text-lg">
                        Deactivated / Inactive doctor profiles
                    </p>
                </div>

                <a href="manage_doctors.php"
                   class="inline-flex items-center gap-3 px-7 py-4 bg-gray-700/60 hover:bg-gray-600/70 text-gray-300 hover:text-white font-semibold rounded-xl border border-gray-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    <i class="fas fa-arrow-left"></i>
                    Back to Active Doctors
                </a>
            </div>
        </div>

        <!-- Table Card -->
        <div class="glass rounded-3xl shadow-2xl overflow-hidden border border-gray-700/40">
            <?php if (count($doctors) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-800/70">
                            <tr>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Specialization</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-5 text-sm font-semibold text-cyan-300 uppercase tracking-wider text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            <?php foreach ($doctors as $doctor): ?>
                                <tr class="hover:bg-gray-800/40 transition-all duration-200 group">
                                    <td class="px-6 py-5 text-gray-300">#<?= htmlspecialchars($doctor['doctor_id']) ?></td>
                                    <td class="px-6 py-5 font-medium text-white"><?= htmlspecialchars($doctor['username'] ?: '—') ?></td>
                                    <td class="px-6 py-5 text-gray-300"><?= htmlspecialchars($doctor['specialization'] ?: '—') ?></td>
                                    <td class="px-6 py-5 text-gray-300"><?= htmlspecialchars($doctor['contact_number'] ?: '—') ?></td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex px-4 py-1.5 bg-red-900/60 text-red-300 text-sm font-medium rounded-full border border-red-700/50">
                                            Inactive
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-center">
                                        <form action="../controllers/RestoreDoctorController.php" method="POST" class="inline">
                                            <input type="hidden" name="doctor_id" value="<?= $doctor['doctor_id'] ?>">
                                            <button type="submit"
                                                    onclick="return confirm('Restore this doctor to active list?');"
                                                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-900/60 hover:bg-emerald-800/70 text-emerald-300 hover:text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                                                <i class="fas fa-undo-alt"></i>
                                                Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="py-24 px-6 text-center text-gray-400">
                    <i class="fas fa-box-archive text-8xl opacity-30 mb-6 block"></i>
                    <p class="text-xl font-medium">No archived doctors found.</p>
                    <p class="mt-2 text-gray-500">Deactivated doctors will appear here.</p>
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