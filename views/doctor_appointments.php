<?php
session_start();
require_once "../config/session_check.php";

/* ===============================
   AUTH CHECK
=============================== */
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']), ['doctor', 'admin'])) {
    header("Location: login.php");
    exit();
}

/* ===============================
   DB CONNECTION
=============================== */
require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

$role = strtolower($_SESSION['role']);
$user_id = $_SESSION['user_id'];

/* ===============================
   FETCH APPOINTMENTS (Unchanged)
=============================== */
if ($role === 'doctor') {
    // Get doctor_id
    $stmt = $conn->prepare("SELECT doctor_id FROM doctor WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$doctor) {
        die("Doctor record not found.");
    }
    $doctor_id = $doctor['doctor_id'];

    $query = "SELECT
                a.*,
                p.patient_id,
                up.username AS patient_name,
                ud.username AS doctor_name
              FROM appointment a
              JOIN patient p ON a.patient_id = p.patient_id
              JOIN users up ON p.user_id = up.user_id
              JOIN doctor d ON a.doctor_id = d.doctor_id
              JOIN users ud ON d.user_id = ud.user_id
              WHERE a.doctor_id = :doctor_id
              ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $stmt = $conn->prepare($query);
    $stmt->execute([':doctor_id' => $doctor_id]);
} else {
    // ADMIN → fetch ALL appointments
    $query = "SELECT
                a.*,
                p.patient_id,
                up.username AS patient_name,
                ud.username AS doctor_name
              FROM appointment a
              JOIN patient p ON a.patient_id = p.patient_id
              JOIN users up ON p.user_id = up.user_id
              JOIN doctor d ON a.doctor_id = d.doctor_id
              JOIN users ud ON d.user_id = ud.user_id
              ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    $stmt = $conn->query($query);
}
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - MediCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        .glass {
            background: rgba(31, 41, 55, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(75, 85, 99, 0.6);
        }
        .status-pending { background-color: #eab308; color: black; }
        .status-approved { background-color: #22c55e; color: white; }
        .status-completed { background-color: #3b82f6; color: white; }
        .status-cancelled { background-color: #ef4444; color: white; }
        
        tr:hover {
            background-color: rgba(45, 55, 72, 0.8) !important;
            transition: all 0.2s;
        }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen p-6">

<div class="max-w-7xl mx-auto">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-10">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-cyan-500 rounded-2xl flex items-center justify-center text-3xl shadow-lg">
                📅
            </div>
            <div>
                <h1 class="text-4xl font-bold tracking-tight">Appointments</h1>
                <p class="text-cyan-300">
                    <?= $role === 'admin' ? 'All Appointments Overview' : 'Your Scheduled Appointments' ?>
                </p>
            </div>
        </div>

        <!-- Back Button -->
        <a href="<?= $role === 'admin' ? 'admin_dashboard.php' : 'doctor_dashboard.php' ?>" 
           class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 px-6 py-3 rounded-2xl transition-all">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <?php if (empty($appointments)): ?>
        <div class="glass rounded-3xl p-16 text-center">
            <div class="text-7xl mb-6">📅</div>
            <h3 class="text-2xl font-semibold mb-2">No Appointments Found</h3>
            <p class="text-gray-400">There are currently no appointments to display.</p>
        </div>
    <?php else: ?>

    <div class="glass rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <!-- TABLE HEADER -->
                <thead class="bg-gray-900">
                    <tr>
                        <th class="p-5 text-left font-medium text-gray-300">Patient</th>
                        <?php if ($role === 'admin'): ?>
                        <th class="p-5 text-left font-medium text-gray-300">Doctor</th>
                        <?php endif; ?>
                        <th class="p-5 text-left font-medium text-gray-300">Date</th>
                        <th class="p-5 text-left font-medium text-gray-300">Time</th>
                        <th class="p-5 text-left font-medium text-gray-300">Status</th>
                        <th class="p-5 text-left font-medium text-gray-300">Actions</th>
                    </tr>
                </thead>

                <!-- TABLE BODY -->
                <tbody class="divide-y divide-gray-700">
                <?php foreach ($appointments as $appt): 
                    $statusClass = '';
                    if ($appt['status'] === 'Pending') $statusClass = 'status-pending';
                    elseif ($appt['status'] === 'Approved') $statusClass = 'status-approved';
                    elseif ($appt['status'] === 'Completed') $statusClass = 'status-completed';
                    elseif ($appt['status'] === 'Cancelled') $statusClass = 'status-cancelled';
                ?>
                    <tr class="hover:bg-gray-800/70 transition-colors">
                        <!-- Patient -->
                        <td class="p-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-cyan-900 rounded-full flex items-center justify-center text-cyan-300">
                                    👤
                                </div>
                                <span class="font-medium"><?= htmlspecialchars($appt['patient_name']) ?></span>
                            </div>
                        </td>

                        <!-- Doctor (Admin Only) -->
                        <?php if ($role === 'admin'): ?>
                        <td class="p-5">
                            Dr. <?= htmlspecialchars($appt['doctor_name']) ?>
                        </td>
                        <?php endif; ?>

                        <!-- Date -->
                        <td class="p-5">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calendar text-cyan-400"></i>
                                <?= htmlspecialchars($appt['appointment_date']) ?>
                            </div>
                        </td>

                        <!-- Time -->
                        <td class="p-5">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-clock text-amber-400"></i>
                                <?= htmlspecialchars($appt['appointment_time']) ?>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="p-5">
                            <span class="px-4 py-1.5 text-sm font-semibold rounded-full <?= $statusClass ?>">
                                <?= htmlspecialchars($appt['status']) ?>
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="p-5">
                            <div class="flex flex-wrap gap-2">
                                <!-- View History -->
                                <a href="view_patient_visits.php?patient_id=<?= $appt['patient_id'] ?>" 
                                   class="bg-blue-600 hover:bg-blue-700 px-5 py-2 rounded-xl text-sm flex items-center gap-2 transition-all">
                                    <i class="fas fa-history"></i>
                                    <span>History</span>
                                </a>

                                <!-- Approve (if Pending) -->
                                <?php if ($appt['status'] === 'Pending'): ?>
                                <form method="POST" action="../controllers/UpdateAppointmentStatus.php" class="inline">
                                    <input type="hidden" name="appointment_id" value="<?= $appt['appointment_id'] ?>">
                                    <input type="hidden" name="status" value="Approved">
                                    <button type="submit" 
                                            class="bg-green-600 hover:bg-green-700 px-5 py-2 rounded-xl text-sm flex items-center gap-2 transition-all">
                                        <i class="fas fa-check"></i>
                                        Approve
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Start Visit (if Approved) -->
                                <?php if ($appt['status'] === 'Approved'): ?>
                                <form method="GET" action="create_visit.php" class="inline">
                                    <input type="hidden" name="appointment_id" value="<?= $appt['appointment_id'] ?>">
                                    <button type="submit" 
                                            class="bg-purple-600 hover:bg-purple-700 px-5 py-2 rounded-xl text-sm flex items-center gap-2 transition-all">
                                        <i class="fas fa-stethoscope"></i>
                                        Start Visit
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php endif; ?>

</div>

</body>
</html>