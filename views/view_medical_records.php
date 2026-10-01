<?php
session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

if (!isset($_GET['patient_id'])) {
    die("Patient not specified.");
}

$patient_id = (int)$_GET['patient_id'];
$database = new Database();
$conn = $database->connect();

/* Get patient name */
$stmt = $conn->prepare("SELECT full_name FROM patient WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}

/* Get medical records */
$stmt = $conn->prepare("
    SELECT
        m.diagnosis,
        m.prescription,
        m.record_date,
        u.username AS doctor_name
    FROM medical_records m
    JOIN doctor d ON m.doctor_id = d.doctor_id
    JOIN users u ON d.user_id = u.user_id
    WHERE m.patient_id = ?
    ORDER BY m.record_date DESC
");
$stmt->execute([$patient_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Medical History – <?= htmlspecialchars($patient['full_name']) ?> • HMS</title>

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
                Medical History
            </h1>
            <p class="mt-4 text-2xl md:text-3xl font-semibold text-cyan-100/90">
                <?= htmlspecialchars($patient['full_name']) ?>
            </p>
            <p class="mt-2 text-lg text-cyan-100/70">
                All recorded medical visits and prescriptions
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

        <!-- Medical Records Timeline -->
        <?php if (empty($records)): ?>
            <div class="glass rounded-2xl p-16 text-center text-gray-400 border border-gray-700/40">
                <i class="fas fa-folder-open text-8xl opacity-40 mb-6 block"></i>
                <h3 class="text-3xl font-bold text-white mb-4">No medical records found</h3>
                <p class="text-xl">No consultations or prescriptions recorded yet for this patient.</p>
            </div>
        <?php else: ?>
            <div class="space-y-8">
                <?php foreach ($records as $record): ?>
                    <div class="glass rounded-2xl p-6 md:p-8 border border-gray-700/40 hover:border-cyan-500/40 transition-all duration-300 shadow-xl hover:shadow-2xl">
                        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">

                            <!-- Left: Date & Doctor -->
                            <div class="min-w-[220px]">
                                <div class="flex items-center gap-3 mb-2">
                                    <i class="fas fa-calendar-day text-cyan-400 text-xl"></i>
                                    <div class="text-xl font-bold text-white">
                                        <?= htmlspecialchars(date('d M Y', strtotime($record['record_date']))) ?>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 text-gray-300">
                                    <i class="fas fa-user-md text-indigo-400"></i>
                                    Dr. <?= htmlspecialchars($record['doctor_name']) ?>
                                </div>
                            </div>

                            <!-- Right: Content -->
                            <div class="flex-1 space-y-6">

                                <!-- Diagnosis -->
                                <?php if (!empty($record['diagnosis'])): ?>
                                    <div>
                                        <h3 class="text-lg font-semibold text-emerald-400 mb-2 flex items-center gap-2">
                                            <i class="fas fa-stethoscope"></i> Diagnosis
                                        </h3>
                                        <div class="bg-gray-900/50 rounded-lg p-4 text-gray-200 border border-gray-700/50 whitespace-pre-line">
                                            <?= nl2br(htmlspecialchars($record['diagnosis'])) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Prescription -->
                                <?php if (!empty($record['prescription'])): ?>
                                    <div>
                                        <h3 class="text-lg font-semibold text-cyan-400 mb-2 flex items-center gap-2">
                                            <i class="fas fa-prescription-bottle-medical"></i> Prescription
                                        </h3>
                                        <div class="bg-gray-900/50 rounded-lg p-4 text-gray-200 border border-gray-700/50 whitespace-pre-line">
                                            <?= nl2br(htmlspecialchars($record['prescription'])) ?>
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