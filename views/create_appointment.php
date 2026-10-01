<?php
session_start();
require_once "../config/session_check.php";

if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']), ['patient','receptionist','admin'])) {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

/* FETCH DOCTORS */
try {
    $query = "SELECT d.doctor_id, u.username
              FROM doctor d
              INNER JOIN users u ON d.user_id = u.user_id
              ORDER BY u.username ASC";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

/* FETCH PATIENTS (RECEPTIONIST ONLY) */
$patients = [];
if (strtolower($_SESSION['role']) === 'receptionist') {
    try {
        $query = "SELECT patient_id, full_name
                  FROM patient
                  WHERE is_active = 1
                  ORDER BY full_name ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>
        <?= (strtolower($_SESSION['role']) === 'receptionist') ? 'Book Appointment' : 'Request Appointment' ?> • HMS
    </title>

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
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-15">
        <i class="fa-solid fa-calendar-plus absolute text-9xl text-cyan-500 animate-pulse-slow -left-20 top-20 rotate-12"></i>
        <i class="fa-solid fa-hospital-user absolute text-10xl text-blue-400 animate-float right-12 bottom-24 -rotate-6"></i>
        <i class="fa-solid fa-notes-medical absolute text-8xl text-indigo-400 animate-pulse-slow -right-16 top-1/3"></i>
    </div>

    <div class="relative z-10 max-w-lg mx-auto">

        <!-- Back link -->
        <a href="<?= strtolower($_SESSION['role']) === 'receptionist' ? 'reception_dashboard.php' : 'patient_dashboard.php' ?>"
           class="inline-flex items-center gap-3 text-cyan-400 hover:text-cyan-300 font-medium mb-10 transition-all duration-200 group">
            <i class="fas fa-arrow-left text-xl transform group-hover:-translate-x-1 transition-transform"></i>
            Back to Dashboard
        </a>

        <!-- Header -->
        <div class="text-center mb-12">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-cyan-700 to-blue-800 rounded-full flex items-center justify-center mb-6 shadow-xl animate-float border-2 border-cyan-400/40">
                <i class="fa-solid fa-calendar-check text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-lg">
                <?= (strtolower($_SESSION['role']) === 'receptionist') ? 'Book Appointment' : 'Request Appointment' ?>
            </h1>
            <p class="mt-4 text-xl text-cyan-100/80">
                <?= (strtolower($_SESSION['role']) === 'receptionist')
                    ? 'Schedule for a walk-in patient'
                    : 'Choose an available slot for consultation' ?>
            </p>
        </div>

        <!-- Form Card -->
        <div class="glass rounded-3xl shadow-2xl p-8 md:p-10 border border-gray-700/50">
            <form method="POST" action="../controllers/CreateAppointmentController.php" class="space-y-8">

                <?php if (strtolower($_SESSION['role']) === 'receptionist'): ?>
                <!-- Patient Selection (Receptionist only) -->
                <div>
                    <label class="block text-lg font-semibold text-gray-200 mb-3">
                        Select Patient <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                            <i class="fas fa-user-injured text-xl"></i>
                        </div>
                        <select name="patient_id" required
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base appearance-none">
                            <option value="">Choose patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['patient_id'] ?>">
                                    <?= htmlspecialchars($patient['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Doctor Selection -->
                <div>
                    <label class="block text-lg font-semibold text-gray-200 mb-3">
                        Select Doctor <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                            <i class="fas fa-user-md text-xl"></i>
                        </div>
                        <select id="doctor_id" name="doctor_id" required onchange="loadSlots()"
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base appearance-none">
                            <option value="">Choose doctor</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor['doctor_id'] ?>">
                                    Dr. <?= htmlspecialchars($doctor['username']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Preferred Date -->
                <div>
                    <label class="block text-lg font-semibold text-gray-200 mb-3">
                        Preferred Date <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                            <i class="fas fa-calendar-days text-xl"></i>
                        </div>
                        <input type="date" id="preferred_date" name="preferred_date" required min="<?= date('Y-m-d') ?>"
                               onchange="loadSlots()"
                               class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base">
                    </div>
                </div>

                <!-- Available Time Slots -->
                <div>
                    <label class="block text-lg font-semibold text-gray-200 mb-3">
                        Available Time Slots <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                            <i class="far fa-clock text-xl"></i>
                        </div>
                        <select id="time_slots" name="preferred_time" required
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base appearance-none">
                            <option value="">Select doctor and date first</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6">
                    <button type="submit"
                            class="group relative w-full flex items-center justify-center gap-4 py-5 px-8 bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white font-semibold text-xl rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1.5 focus:outline-none focus:ring-4 focus:ring-cyan-500/30 transition-all duration-300">
                        <i class="fas fa-calendar-check text-2xl"></i>
                        <span><?= (strtolower($_SESSION['role']) === 'receptionist') ? 'Book Appointment' : 'Submit Request' ?></span>
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- JavaScript for loading available slots -->
    <script>
        function loadSlots() {
            const doctor = document.getElementById("doctor_id").value;
            const date = document.getElementById("preferred_date").value;
            const slotSelect = document.getElementById("time_slots");

            if (!doctor || !date) {
                slotSelect.innerHTML = '<option value="">Select doctor and date first</option>';
                return;
            }

            fetch(`../controllers/get_available_slots.php?doctor_id=${doctor}&date=${date}`)
                .then(response => response.json())
                .then(slots => {
                    slotSelect.innerHTML = '';
                    if (slots.length === 0) {
                        const option = document.createElement("option");
                        option.text = "No slots available";
                        slotSelect.add(option);
                        return;
                    }
                    slots.forEach(slot => {
                        const option = document.createElement("option");
                        option.value = slot;
                        option.text = slot;
                        slotSelect.add(option);
                    });
                })
                .catch(() => {
                    slotSelect.innerHTML = '<option value="">Error loading slots</option>';
                });
        }
    </script>

</body>
</html>