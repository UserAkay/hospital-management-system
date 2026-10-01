<?php
session_start();
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'receptionist') {
    header("Location: login.php");
    exit();
}
require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

/* ================= SEARCH FILTER ================= */
$search    = $_GET['search'] ?? '';
$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date']   ?? '';
$where  = " WHERE p.verification_status = 'Approved' ";
$params = [];
if ($search) {
    $where .= " AND (p.full_name LIKE ? OR u.username LIKE ?) ";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($from_date) {
    $where .= " AND a.preferred_date >= ? ";
    $params[] = $from_date;
}
if ($to_date) {
    $where .= " AND a.preferred_date <= ? ";
    $params[] = $to_date;
}

/* ================= FETCH FUNCTION ================= */
function fetchAppointments($conn, $where, $params, $status) {
    $sql = "
        SELECT a.request_id,
               p.full_name AS patient_name,
               u.username AS doctor_username,
               a.preferred_date,
               a.preferred_time,
               a.status
        FROM appointment_requests a
        JOIN patient p ON a.patient_id = p.patient_id
        JOIN doctor d ON a.doctor_id = d.doctor_id
        JOIN users u ON d.user_id = u.user_id
        $where
        AND a.status = ?
        ORDER BY a.preferred_date ASC, a.preferred_time ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array_merge($params, [$status]));
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* ================= DATA ================= */
$pending  = fetchAppointments($conn, $where, $params, 'Pending');
$pendingPatients = $conn->query("
    SELECT patient_id, full_name, contact_number
    FROM patient
    WHERE verification_status = 'Pending'
    ORDER BY patient_id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$pendingCount      = count($pending);
$totalPatients     = $conn->query("SELECT COUNT(*) FROM patient WHERE verification_status='Approved'")->fetchColumn();
$totalDoctors      = $conn->query("SELECT COUNT(*) FROM doctor")->fetchColumn();
$todayAppointments = $conn->query("SELECT COUNT(*) FROM appointment WHERE appointment_date = CURDATE()")->fetchColumn();

$todayList = $conn->query("
    SELECT a.appointment_time,
           p.full_name AS patient_name,
           u.username AS doctor_name
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN doctor d ON a.doctor_id = d.doctor_id
    JOIN users u ON d.user_id = u.user_id
    WHERE a.appointment_date = CURDATE()
    ORDER BY a.appointment_time ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reception Dashboard • MediCare HMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.85), rgba(0,0,0,0.92)), 
                        url('https://images.unsplash.com/photo-1512678080530-7760d81faba6?ixlib=rb-4.0.3&auto=format&fit=crop&q=80') center/cover no-repeat fixed;
        }
        .glass {
            background: rgba(31, 41, 55, 0.88);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(75, 85, 99, 0.4);
        }
        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.5);
        }
        .stat-number {
            font-size: 3rem;
            line-height: 1;
            font-weight: 700;
        }
    </style>
</head>
<body class="text-gray-100 min-h-screen p-6 lg:p-8">

<div class="max-w-7xl mx-auto">

    <!-- HEADER -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 mb-12">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-cyan-500 rounded-3xl flex items-center justify-center text-4xl shadow-xl">
                🏥
            </div>
            <div>
                <h1 class="text-4xl font-bold tracking-tight">Reception Dashboard</h1>
                <p class="text-cyan-300">Hospital Management System</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <span class="text-gray-300">Welcome, <strong class="text-white"><?= htmlspecialchars($_SESSION['username'] ?? 'Receptionist') ?></strong></span>
            <a href="../controllers/logout.php"
               class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-2xl font-medium flex items-center gap-2 transition-all">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="mb-12">
        <h2 class="text-lg font-medium text-gray-400 mb-5 flex items-center gap-2">
            <i class="fas fa-bolt"></i> Quick Actions
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <a href="register_patient.php" 
               class="glass card-hover border border-green-500/30 hover:border-green-500 p-8 rounded-3xl text-center transition-all group">
                <i class="fas fa-user-plus text-5xl text-green-400 mb-5 group-hover:scale-110 transition-transform"></i>
                <p class="font-semibold text-lg">Register Patient</p>
            </a>
            <a href="manage_patients.php" 
               class="glass card-hover border border-blue-500/30 hover:border-blue-500 p-8 rounded-3xl text-center transition-all group">
                <i class="fas fa-users text-5xl text-blue-400 mb-5 group-hover:scale-110 transition-transform"></i>
                <p class="font-semibold text-lg">Manage Patients</p>
            </a>
            <a href="create_bill.php" 
               class="glass card-hover border border-purple-500/30 hover:border-purple-500 p-8 rounded-3xl text-center transition-all group">
                <i class="fas fa-file-invoice-dollar text-5xl text-purple-400 mb-5 group-hover:scale-110 transition-transform"></i>
                <p class="font-semibold text-lg">Create Bill</p>
            </a>
            <a href="create_appointment.php" 
               class="glass card-hover border border-cyan-500/30 hover:border-cyan-500 p-8 rounded-3xl text-center transition-all group">
                <i class="fas fa-calendar-plus text-5xl text-cyan-400 mb-5 group-hover:scale-110 transition-transform"></i>
                <p class="font-semibold text-lg">New Appointment</p>
            </a>
        </div>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
        <div class="glass card-hover rounded-3xl p-8 text-center border border-amber-500/30">
            <i class="fas fa-hourglass-half text-4xl text-amber-400 mb-4"></i>
            <p class="text-amber-400 text-sm font-medium">Pending Requests</p>
            <p class="stat-number text-white mt-2"><?= $pendingCount ?></p>
        </div>
        <div class="glass card-hover rounded-3xl p-8 text-center border border-cyan-500/30">
            <i class="fas fa-calendar-day text-4xl text-cyan-400 mb-4"></i>
            <p class="text-cyan-400 text-sm font-medium">Today’s Appointments</p>
            <p class="stat-number text-white mt-2"><?= $todayAppointments ?></p>
        </div>
        <div class="glass card-hover rounded-3xl p-8 text-center border border-emerald-500/30">
            <i class="fas fa-user-check text-4xl text-emerald-400 mb-4"></i>
            <p class="text-emerald-400 text-sm font-medium">Verified Patients</p>
            <p class="stat-number text-white mt-2"><?= $totalPatients ?></p>
        </div>
        <div class="glass card-hover rounded-3xl p-8 text-center border border-violet-500/30">
            <i class="fas fa-user-md text-4xl text-violet-400 mb-4"></i>
            <p class="text-violet-400 text-sm font-medium">Total Doctors</p>
            <p class="stat-number text-white mt-2"><?= $totalDoctors ?></p>
        </div>
    </div>

    <!-- TODAY'S APPOINTMENTS -->
    <div class="mb-12">
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
            <i class="fas fa-calendar-day text-cyan-400"></i> Today's Appointments
        </h2>
        <?php if (empty($todayList)): ?>
            <div class="glass rounded-3xl p-16 text-center">
                <i class="fas fa-calendar-times text-7xl text-gray-500 mb-6"></i>
                <p class="text-gray-400 text-xl">No appointments scheduled for today</p>
            </div>
        <?php else: ?>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($todayList as $t): ?>
                    <div class="glass card-hover rounded-3xl p-7">
                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-12 h-12 bg-cyan-500/10 rounded-2xl flex items-center justify-center text-2xl">
                                👤
                            </div>
                            <div class="font-semibold text-xl"><?= htmlspecialchars($t['patient_name']) ?></div>
                        </div>
                        <div class="flex items-center gap-3 text-gray-300 mb-3">
                            <i class="fas fa-clock text-cyan-400"></i>
                            <span class="font-medium"><?= htmlspecialchars($t['appointment_time']) ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-gray-300">
                            <i class="fas fa-user-md text-indigo-400"></i>
                            <span>Dr. <?= htmlspecialchars($t['doctor_name']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- PENDING PATIENT VERIFICATION -->
    <div class="mb-12">
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
            <i class="fas fa-user-check text-amber-400"></i> Pending Patient Verification
        </h2>
        <?php if (empty($pendingPatients)): ?>
            <div class="glass rounded-3xl p-16 text-center">
                <i class="fas fa-check-circle text-7xl text-emerald-400 mb-6"></i>
                <p class="text-emerald-400 text-xl">All patients are verified ✓</p>
            </div>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($pendingPatients as $p): ?>
                    <div class="glass card-hover rounded-3xl p-7 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 bg-amber-500/10 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-3xl text-amber-400"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-lg"><?= htmlspecialchars($p['full_name']) ?></p>
                                <p class="text-gray-400"><?= htmlspecialchars($p['contact_number']) ?></p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <form method="POST" action="../controllers/patient_verification_action.php">
                                <input type="hidden" name="patient_id" value="<?= $p['patient_id'] ?>">
                                <button name="action" value="approve"
                                        class="bg-emerald-600 hover:bg-emerald-700 px-8 py-3.5 rounded-2xl font-medium flex items-center gap-2 transition-all">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            <form method="POST" action="../controllers/patient_verification_action.php">
                                <input type="hidden" name="patient_id" value="<?= $p['patient_id'] ?>">
                                <button name="action" value="reject"
                                        class="bg-red-600 hover:bg-red-700 px-8 py-3.5 rounded-2xl font-medium flex items-center gap-2 transition-all">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- PENDING APPOINTMENT REQUESTS -->
    <div>
        <h2 class="text-2xl font-semibold mb-6 flex items-center gap-3">
            <i class="fas fa-hourglass-half text-orange-400"></i> Pending Appointment Requests
        </h2>
        <?php if (empty($pending)): ?>
            <div class="glass rounded-3xl p-16 text-center">
                <i class="fas fa-calendar-check text-7xl text-gray-500 mb-6"></i>
                <p class="text-gray-400 text-xl">No pending appointment requests</p>
            </div>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($pending as $a): ?>
                    <div class="glass card-hover rounded-3xl p-7 flex flex-col lg:flex-row justify-between gap-6">
                        <div class="flex-1">
                            <p class="font-semibold text-xl"><?= htmlspecialchars($a['patient_name']) ?></p>
                            <div class="mt-3 flex items-center gap-6 text-gray-300">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-calendar text-cyan-400"></i>
                                    <span><?= htmlspecialchars($a['preferred_date']) ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-clock text-amber-400"></i>
                                    <span><?= htmlspecialchars($a['preferred_time']) ?></span>
                                </div>
                            </div>
                            <p class="mt-3 text-indigo-400 font-medium">
                                Dr. <?= htmlspecialchars($a['doctor_username']) ?>
                            </p>
                        </div>
                        <div class="flex gap-4 self-start lg:self-center">
                            <form method="POST" action="../controllers/receptionist_action.php">
                                <input type="hidden" name="request_id" value="<?= $a['request_id'] ?>">
                                <button name="action" value="approve"
                                        class="bg-emerald-600 hover:bg-emerald-700 px-8 py-3.5 rounded-2xl font-medium flex items-center gap-2 transition-all">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            <form method="POST" action="../controllers/receptionist_action.php">
                                <input type="hidden" name="request_id" value="<?= $a['request_id'] ?>">
                                <button name="action" value="reject"
                                        class="bg-red-600 hover:bg-red-700 px-8 py-3.5 rounded-2xl font-medium flex items-center gap-2 transition-all">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

</body>
</html>