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

/* 1️⃣ Validate patient_id */
if (!isset($_GET['patient_id']) || !is_numeric($_GET['patient_id'])) {
    header("Location: my_patients.php?error=invalid_request");
    exit();
}
$patient_id = (int)$_GET['patient_id'];

/* 2️⃣ Get doctor_id */
$stmt = $conn->prepare("SELECT doctor_id FROM doctor WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$doctor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doctor) {
    die("Doctor record not found.");
}
$doctor_id = $doctor['doctor_id'];

/* 3️⃣ Security: Confirm this patient has been seen by this doctor */
$stmt = $conn->prepare("
    SELECT p.patient_id, p.full_name
    FROM visit v
    JOIN patient p ON v.patient_id = p.patient_id
    WHERE v.patient_id = ? AND v.doctor_id = ?
    LIMIT 1
");
$stmt->execute([$patient_id, $doctor_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header("Location: my_patients.php?error=unauthorized");
    exit();
}

/* 4️⃣ Fetch Visit History */
$stmt = $conn->prepare("
    SELECT
        v.visit_date,
        v.diagnosis,
        v.prescription,
        v.notes,
        a.appointment_date,
        a.appointment_time
    FROM visit v
    LEFT JOIN appointment a ON v.appointment_id = a.appointment_id
    WHERE v.patient_id = ? AND v.doctor_id = ?
    ORDER BY v.visit_date DESC
");
$stmt->execute([$patient_id, $doctor_id]);
$visits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Visit History – <?= htmlspecialchars($patient['full_name']) ?> • HMS</title>

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
        <i class="fa-solid fa-notes-medical absolute text-9xl text-cyan-500 animate-pulse-slow -left-20 top-20 rotate-12"></i>
        <i class="fa-solid fa-user-injured absolute text-8xl text-blue-400 animate-float right-12 bottom-24 -rotate-6"></i>
        <i class="fa-solid fa-heart-pulse absolute text-7xl text-indigo-400 animate-pulse-slow -right-16 top-1/3"></i>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto">

        <!-- Header -->
        <div class="text-center mb-12">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-cyan-700 to-blue-800 rounded-full flex items-center justify-center mb-6 shadow-xl animate-float border-2 border-cyan-400/40">
                <i class="fa-solid fa-notes-medical text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-lg">
                Visit History
            </h1>
            <p class="mt-4 text-2xl md:text-3xl font-semibold text-cyan-100/90">
                <?= htmlspecialchars($patient['full_name']) ?>
            </p>
            <p class="mt-2 text-lg text-cyan-100/70">
                All recorded consultations with you
            </p>
        </div>

        <!-- Back link -->
        <div class="text-center mb-10">
            <a href="my_patients.php"
               class="inline-flex items-center gap-3 px-8 py-4 bg-gray-800/50 hover:bg-gray-700/60 text-gray-300 hover:text-white rounded-2xl border border-gray-700 transition-all duration-300 shadow-lg hover:shadow-xl">
                <i class="fas fa-arrow-left"></i>
                Back to My Patients
            </a>
        </div>

        <!-- Visit List -->
        <?php if (empty($visits)): ?>
            <div class="glass rounded-2xl p-16 text-center text-gray-400 border border-gray-700/40">
                <i class="fas fa-calendar-times text-8xl opacity-40 mb-6 block"></i>
                <h3 class="text-3xl font-bold text-white mb-4">No visits recorded yet</h3>
                <p class="text-xl">This patient has no previous consultation history with you.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($visits as $visit): ?>
                    <div class="glass rounded-2xl p-6 md:p-8 border border-gray-700/40 hover:border-cyan-500/40 transition-all duration-300 shadow-xl hover:shadow-2xl">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">

                            <!-- Left: Date & Time -->
                            <div class="min-w-[180px]">
                                <div class="flex items-center gap-3 mb-2">
                                    <i class="fas fa-calendar-day text-cyan-400 text-xl"></i>
                                    <div class="text-xl font-bold text-white">
                                        <?= htmlspecialchars(date('d M Y', strtotime($visit['visit_date']))) ?>
                                    </div>
                                </div>
                                <?php if ($visit['appointment_time']): ?>
                                    <div class="flex items-center gap-3 text-gray-300">
                                        <i class="far fa-clock text-cyan-400"></i>
                                        <?= htmlspecialchars($visit['appointment_time']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Right: Content -->
                            <div class="flex-1 space-y-6">

                                <!-- Diagnosis -->
                                <?php if (!empty($visit['diagnosis'])): ?>
                                    <div>
                                        <h3 class="text-lg font-semibold text-emerald-400 mb-2 flex items-center gap-2">
                                            <i class="fas fa-stethoscope"></i> Diagnosis
                                        </h3>
                                        <div class="bg-gray-900/50 rounded-lg p-4 text-gray-200 border border-gray-700/50">
                                            <?= nl2br(htmlspecialchars($visit['diagnosis'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Prescription -->
                                <?php if (!empty($visit['prescription'])): ?>
                                    <div>
                                        <h3 class="text-lg font-semibold text-cyan-400 mb-2 flex items-center gap-2">
                                            <i class="fas fa-prescription-bottle-medical"></i> Prescription
                                        </h3>
                                        <div class="bg-gray-900/50 rounded-lg p-4 text-gray-200 border border-gray-700/50 whitespace-pre-line">
                                            <?= htmlspecialchars($visit['prescription']) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Notes -->
                                <?php if (!empty($visit['notes'])): ?>
                                    <div>
                                        <h3 class="text-lg font-semibold text-indigo-400 mb-2 flex items-center gap-2">
                                            <i class="fas fa-note-sticky"></i> Doctor Notes
                                        </h3>
                                        <div class="bg-gray-900/50 rounded-lg p-4 text-gray-200 border border-gray-700/50 italic">
                                            <?= nl2br(htmlspecialchars($visit['notes'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>