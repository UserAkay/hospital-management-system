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
$username  = htmlspecialchars($_SESSION['username'] ?? 'Doctor');

/* TODAY'S APPOINTMENTS */
$stmt = $conn->prepare("
    SELECT
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        p.patient_id,
        p.full_name
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    WHERE a.doctor_id = :doctor_id
    AND a.status = 'Scheduled'
    AND DATE(a.appointment_date) = CURDATE()
    ORDER BY a.appointment_time ASC
");
$stmt->execute([':doctor_id' => $doctor_id]);
$todayAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* UPCOMING APPOINTMENTS */
$stmt = $conn->prepare("
    SELECT
        a.appointment_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        p.patient_id,
        p.full_name
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    WHERE a.doctor_id = :doctor_id
    AND a.status = 'Scheduled'
    AND DATE(a.appointment_date) > CURDATE()
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
");
$stmt->execute([':doctor_id' => $doctor_id]);
$upcomingAppointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Doctor Dashboard • HMS</title>

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

<body class="min-h-screen bg-gradient-to-br from-gray-950 via-indigo-950 to-purple-950 text-gray-100 antialiased relative overflow-x-hidden">

    <!-- Floating background icons -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-12">
        <i class="fa-solid fa-stethoscope absolute text-9xl text-cyan-500 animate-pulse-slow -left-20 top-20 rotate-12"></i>
        <i class="fa-solid fa-hospital-user absolute text-10xl text-blue-400 animate-float right-10 bottom-20 -rotate-6"></i>
        <i class="fa-solid fa-calendar-check absolute text-8xl text-indigo-400 animate-pulse-slow -right-16 top-1/3"></i>
    </div>

    <!-- Header / Welcome -->
    <header class="relative z-10 bg-gradient-to-r from-emerald-950 via-teal-950 to-gray-950 border-b border-gray-800/50 shadow-2xl backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-emerald-900/50 flex items-center justify-center text-3xl font-bold text-emerald-300 ring-2 ring-emerald-600/40 shadow-xl animate-float">
                    <?= strtoupper(substr($username, 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent drop-shadow-lg">
                        Dr. <?= $username ?>
                    </h1>
                    <p class="text-gray-300 mt-1 text-lg">Here's your schedule overview</p>
                </div>
            </div>

            <a href="../controllers/logout.php"
               class="flex items-center gap-3 px-6 py-3 bg-red-900/70 hover:bg-red-800/80 text-red-200 hover:text-white rounded-xl font-medium transition-all shadow-lg border border-red-800/40 hover:shadow-xl">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </header>

    <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Quick Actions -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
            <a href="my_patients.php"
               class="group glass rounded-2xl p-8 border border-gray-700/40 hover:border-emerald-600/50 hover:shadow-2xl hover:shadow-emerald-900/30 transition-all duration-300">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-xl bg-emerald-900/50 flex items-center justify-center text-emerald-400 text-3xl shadow-inner">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-white group-hover:text-emerald-400 transition-colors">My Patients</h3>
                        <p class="text-gray-400 mt-2">Access records & visit history</p>
                    </div>
                </div>
            </a>

            <!-- You can add more quick action cards here -->
            <!-- Example: Upcoming Schedule, Messages, Profile -->
        </section>

        <!-- Today's Appointments -->
        <section class="mb-16">
            <h2 class="text-3xl font-bold text-white mb-6 flex items-center gap-4">
                <i class="fas fa-calendar-day text-cyan-400 text-3xl"></i>
                Today's Appointments
            </h2>

            <?php if (empty($todayAppointments)): ?>
                <div class="glass rounded-2xl p-12 text-center text-gray-400 border border-gray-700/40">
                    <i class="fas fa-calendar-times text-7xl opacity-40 mb-6 block"></i>
                    <h3 class="text-2xl font-semibold text-white mb-4">No appointments today</h3>
                    <p class="text-lg">Check upcoming schedule or take a break</p>
                </div>
            <?php else: ?>
                <div class="glass rounded-2xl overflow-hidden border border-gray-700/40 shadow-2xl">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900/70">
                                <tr>
                                    <th class="px-6 py-5 text-left text-sm font-semibold text-cyan-300 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-5 text-left text-sm font-semibold text-cyan-300 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-5 text-left text-sm font-semibold text-cyan-300 uppercase tracking-wider hidden md:table-cell">Status</th>
                                    <th class="px-6 py-5 text-left text-sm font-semibold text-cyan-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <?php foreach ($todayAppointments as $appt): ?>
                                    <tr class="hover:bg-emerald-950/30 transition-colors duration-200 group">
                                        <td class="px-6 py-5 whitespace-nowrap font-medium text-gray-200 text-lg">
                                            <?= htmlspecialchars($appt['appointment_time']) ?>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <div class="font-semibold text-white text-lg">
                                                <?= htmlspecialchars($appt['full_name']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap hidden md:table-cell">
                                            <span class="px-4 py-1.5 rounded-full text-sm font-medium bg-emerald-900/60 text-emerald-300 border border-emerald-700/40">
                                                Scheduled
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-sm">
                                            <div class="flex flex-wrap gap-4">
                                                <a href="create_visit.php?appointment_id=<?= $appt['appointment_id'] ?>"
                                                   class="inline-flex items-center gap-2 bg-emerald-700 hover:bg-emerald-800 text-white px-5 py-2.5 rounded-lg transition shadow-md hover:shadow-lg">
                                                    <i class="fas fa-play text-sm"></i> Start Visit
                                                </a>
                                                <a href="view_patient_visits.php?patient_id=<?= $appt['patient_id'] ?>"
                                                   class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-5 py-2.5 rounded-lg transition shadow-md hover:shadow-lg">
                                                    <i class="fas fa-history"></i> History
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </section>

        <!-- Upcoming Appointments -->
        <section>
            <h2 class="text-3xl font-bold text-white mb-6 flex items-center gap-4">
                <i class="fas fa-calendar-alt text-teal-400 text-3xl"></i>
                Upcoming Appointments
            </h2>

            <?php if (empty($upcomingAppointments)): ?>
                <div class="glass rounded-2xl p-12 text-center text-gray-400 border border-gray-700/40">
                    <i class="fas fa-calendar-check text-7xl opacity-40 mb-6 block"></i>
                    <h3 class="text-2xl font-semibold text-white mb-4">No upcoming appointments</h3>
                    <p class="text-lg">Your schedule looks clear ahead</p>
                </div>
            <?php else: ?>
                <div class="glass rounded-2xl overflow-hidden border border-gray-700/40 shadow-2xl">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-gray-900/70">
                                <tr>
                                    <th class="px-6 py-5 text-left text-sm font-semibold text-cyan-300 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-5 text-left text-sm font-semibold text-cyan-300 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-5 text-left text-sm font-semibold text-cyan-300 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-5 text-left text-sm font-semibold text-cyan-300 uppercase tracking-wider hidden md:table-cell">Status</th>
                                    <th class="px-6 py-5 text-left text-sm font-semibold text-cyan-300 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <?php foreach ($upcomingAppointments as $appt): ?>
                                    <tr class="hover:bg-teal-950/30 transition-colors duration-200 group">
                                        <td class="px-6 py-5 whitespace-nowrap font-medium text-gray-200 text-lg">
                                            <?= htmlspecialchars(date('d M Y', strtotime($appt['appointment_date']))) ?>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-gray-200 text-lg">
                                            <?= htmlspecialchars($appt['appointment_time']) ?>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap">
                                            <div class="font-semibold text-white text-lg">
                                                <?= htmlspecialchars($appt['full_name']) ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap hidden md:table-cell">
                                            <span class="px-4 py-1.5 rounded-full text-sm font-medium bg-teal-900/60 text-teal-300 border border-teal-700/40">
                                                Scheduled
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 whitespace-nowrap text-sm">
                                            <a href="view_patient_visits.php?patient_id=<?= $appt['patient_id'] ?>"
                                               class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-5 py-2.5 rounded-lg transition shadow-md hover:shadow-lg">
                                                <i class="fas fa-history"></i> View History
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </section>

    </main>

</body>
</html>