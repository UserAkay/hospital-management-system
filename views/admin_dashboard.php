<?php
session_start();
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();
$username = htmlspecialchars($_SESSION['username'] ?? 'Admin');

/* ===============================
   ANALYTICS
=============================== */
$monthlyRevenue = array_fill(1, 12, 0);
$stmt = $conn->query("
    SELECT MONTH(created_at) as month, SUM(total_amount) as total
    FROM billing
    WHERE YEAR(created_at) = YEAR(CURDATE())
    GROUP BY MONTH(created_at)
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $monthlyRevenue[(int)$row['month']] = (float)$row['total'];
}
$revenueData = json_encode(array_values($monthlyRevenue));

$stmt = $conn->query("
    SELECT DATE(created_at) as d, SUM(total_amount) as total
    FROM billing
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
");
$dailyMap = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dailyMap[$row['d']] = (float)$row['total'];
}
$dailyLabels = $dailyRevenue = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date("Y-m-d", strtotime("-$i days"));
    $dailyLabels[] = date("d M", strtotime($date));
    $dailyRevenue[] = $dailyMap[$date] ?? 0;
}
$dailyLabelsJSON  = json_encode($dailyLabels);
$dailyRevenueJSON = json_encode($dailyRevenue);

/* ===============================
   KPIs
=============================== */
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalDoctors = $conn->query("SELECT COUNT(*) FROM doctor")->fetchColumn();
$totalPatients = $conn->query("SELECT COUNT(*) FROM patient")->fetchColumn();
$totalAppointments = $conn->query("SELECT COUNT(*) FROM appointment_requests")->fetchColumn();

/* ===============================
   DATA
=============================== */
$doctors = $conn->query("
    SELECT u.username, d.specialization
    FROM doctor d
    JOIN users u ON d.user_id = u.user_id
")->fetchAll(PDO::FETCH_ASSOC);

$patients = $conn->query("
    SELECT full_name, contact_number
    FROM patient
")->fetchAll(PDO::FETCH_ASSOC);

$todayAppointments = $conn->query("
    SELECT p.full_name, u.username AS doctor, a.appointment_time
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN doctor d ON a.doctor_id = d.doctor_id
    JOIN users u ON d.user_id = u.user_id
    WHERE a.appointment_date = CURDATE()
")->fetchAll(PDO::FETCH_ASSOC);

$recentLogs = $conn->query("
    SELECT action, table_name
    FROM audit_log
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(rgba(0,0,0,0.88), rgba(0,0,0,0.92)), 
                        url('https://images.unsplash.com/photo-1512678080530-7760d81faba6?ixlib=rb-4.0.3&auto=format&fit=crop&q=80') center/cover no-repeat fixed;
        }
        .glass {
            background: rgba(31, 41, 55, 0.82);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(75, 85, 99, 0.5);
        }
        .nav-card {
            transition: all 0.3s ease;
        }
        .nav-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.5);
        }
        .scroll-box {
            max-height: 240px;
            overflow-y: auto;
            padding-right: 8px;
        }
        .scroll-box::-webkit-scrollbar {
            width: 6px;
        }
        .scroll-box::-webkit-scrollbar-thumb {
            background: #22d3ee;
            border-radius: 10px;
        }
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
        }
    </style>
</head>
<body class="text-gray-100 min-h-screen p-6">

<div class="max-w-7xl mx-auto">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-12">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-cyan-500 rounded-3xl flex items-center justify-center text-4xl shadow-lg">
                🏥
            </div>
            <div>
                <h1 class="text-4xl font-bold tracking-tight">MediCare Admin</h1>
                <p class="text-cyan-300 text-lg">Hospital Management System</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-gray-300">Welcome, <strong><?= $username ?></strong></span>
            <a href="../controllers/logout.php" 
               class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-2xl flex items-center gap-2 transition-all font-medium">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- NAVIGATION -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-12">
        <a href="create_user.php" class="glass nav-card p-6 rounded-3xl text-center flex flex-col items-center gap-3">
            <i class="fas fa-user-plus text-4xl text-blue-400"></i>
            <span class="font-semibold">Create User</span>
        </a>
        <a href="manage_doctors.php" class="glass nav-card p-6 rounded-3xl text-center flex flex-col items-center gap-3">
            <i class="fas fa-user-md text-4xl text-emerald-400"></i>
            <span class="font-semibold">Doctors</span>
        </a>
        <a href="manage_patients.php" class="glass nav-card p-6 rounded-3xl text-center flex flex-col items-center gap-3">
            <i class="fas fa-hospital-user text-4xl text-amber-400"></i>
            <span class="font-semibold">Patients</span>
        </a>
        <a href="doctor_appointments.php" class="glass nav-card p-6 rounded-3xl text-center flex flex-col items-center gap-3">
            <i class="fas fa-calendar-check text-4xl text-violet-400"></i>
            <span class="font-semibold">Appointments</span>
        </a>
        <a href="manage_bills.php" class="glass nav-card p-6 rounded-3xl text-center flex flex-col items-center gap-3">
            <i class="fas fa-file-invoice-dollar text-4xl text-pink-400"></i>
            <span class="font-semibold">Billing</span>
        </a>
        <a href="audit_logs.php" class="glass nav-card p-6 rounded-3xl text-center flex flex-col items-center gap-3">
            <i class="fas fa-clipboard-list text-4xl text-gray-400"></i>
            <span class="font-semibold">Audit Logs</span>
        </a>
    </div>

    <!-- KPI CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12">
        <div class="glass stat-card p-7 rounded-3xl">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-sm">Total Users</p>
                    <p class="text-4xl font-bold mt-2"><?= number_format($totalUsers) ?></p>
                </div>
                <i class="fas fa-users text-5xl text-blue-400 opacity-75"></i>
            </div>
        </div>
        <div class="glass stat-card p-7 rounded-3xl">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-sm">Doctors</p>
                    <p class="text-4xl font-bold mt-2"><?= number_format($totalDoctors) ?></p>
                </div>
                <i class="fas fa-user-md text-5xl text-emerald-400 opacity-75"></i>
            </div>
        </div>
        <div class="glass stat-card p-7 rounded-3xl">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-sm">Patients</p>
                    <p class="text-4xl font-bold mt-2"><?= number_format($totalPatients) ?></p>
                </div>
                <i class="fas fa-hospital-user text-5xl text-amber-400 opacity-75"></i>
            </div>
        </div>
        <div class="glass stat-card p-7 rounded-3xl">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-400 text-sm">Appointments</p>
                    <p class="text-4xl font-bold mt-2"><?= number_format($totalAppointments) ?></p>
                </div>
                <i class="fas fa-calendar-alt text-5xl text-violet-400 opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- CHARTS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <div class="glass p-8 rounded-3xl">
            <h3 class="text-xl font-semibold mb-6 flex items-center gap-3">
                <i class="fas fa-chart-bar text-cyan-400"></i>
                Monthly Revenue (<?= date("Y") ?>)
            </h3>
            <canvas id="monthlyChart" height="120"></canvas>
        </div>
        <div class="glass p-8 rounded-3xl">
            <h3 class="text-xl font-semibold mb-6 flex items-center gap-3">
                <i class="fas fa-chart-line text-cyan-400"></i>
                Daily Revenue (Last 7 Days)
            </h3>
            <canvas id="dailyChart" height="120"></canvas>
        </div>
    </div>

    <!-- QUICK CREATE USER -->
    <div class="glass p-8 rounded-3xl mb-12">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
            <i class="fas fa-user-plus text-blue-400"></i> Quick Create User
        </h2>
        <form method="POST" action="../controllers/create_user_controller.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="username" required placeholder="Username" 
                   class="bg-gray-900 border border-gray-700 focus:border-cyan-500 rounded-2xl px-5 py-4 outline-none">
            <input type="password" name="password" required placeholder="Password" 
                   class="bg-gray-900 border border-gray-700 focus:border-cyan-500 rounded-2xl px-5 py-4 outline-none">
            <div class="flex gap-3">
                <select name="role" class="bg-gray-900 border border-gray-700 focus:border-cyan-500 rounded-2xl px-5 py-4 outline-none flex-1">
                    <option>Admin</option>
                    <option>Doctor</option>
                    <option>Receptionist</option>
                </select>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 px-8 py-4 rounded-2xl font-semibold transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i> Create
                </button>
            </div>
        </form>
    </div>

    <!-- QUICK VIEWS GRID -->
    <div class="grid md:grid-cols-2 gap-8">

        <!-- Doctors -->
        <div class="glass p-8 rounded-3xl">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
                <i class="fas fa-user-md text-emerald-400"></i> Doctors
            </h2>
            <div class="scroll-box space-y-3">
                <?php foreach ($doctors as $d): ?>
                <div class="bg-gray-900/70 border border-gray-700 p-4 rounded-2xl">
                    Dr. <span class="font-medium"><?= htmlspecialchars($d['username']) ?></span> 
                    <span class="text-gray-400"> (<?= htmlspecialchars($d['specialization']) ?>)</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Patients -->
        <div class="glass p-8 rounded-3xl">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
                <i class="fas fa-users text-amber-400"></i> Patients
            </h2>
            <div class="scroll-box space-y-3">
                <?php foreach ($patients as $p): ?>
                <div class="bg-gray-900/70 border border-gray-700 p-4 rounded-2xl">
                    <?= htmlspecialchars($p['full_name']) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Today Appointments -->
        <div class="glass p-8 rounded-3xl">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
                <i class="fas fa-calendar-day text-violet-400"></i> Today's Appointments
            </h2>
            <div class="scroll-box space-y-3">
                <?php foreach ($todayAppointments as $a): ?>
                <div class="bg-gray-900/70 border border-gray-700 p-4 rounded-2xl">
                    <?= htmlspecialchars($a['full_name']) ?> 
                    <span class="text-gray-400 mx-2">→</span> 
                    Dr. <?= htmlspecialchars($a['doctor']) ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="glass p-8 rounded-3xl">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
                <i class="fas fa-history text-rose-400"></i> Recent Activity
            </h2>
            <div class="scroll-box space-y-3">
                <?php foreach ($recentLogs as $l): ?>
                <div class="bg-gray-900/70 border border-gray-700 p-4 rounded-2xl text-sm">
                    <?= htmlspecialchars($l['action']) ?> 
                    <span class="text-gray-400">on <?= htmlspecialchars($l['table_name']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<script>
// Monthly Revenue Chart
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{ 
            data: <?= $revenueData ?>,
            backgroundColor: '#22d3ee',
            borderColor: '#67e8f9',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(75,85,99,0.4)' } },
            x: { grid: { color: 'rgba(75,85,99,0.4)' } }
        }
    }
});

// Daily Revenue Chart
new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: <?= $dailyLabelsJSON ?>,
        datasets: [{ 
            data: <?= $dailyRevenueJSON ?>,
            borderColor: '#67e8f9',
            backgroundColor: 'rgba(103, 232, 249, 0.2)',
            tension: 0.4,
            borderWidth: 3,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(75,85,99,0.4)' } },
            x: { grid: { color: 'rgba(75,85,99,0.4)' } }
        }
    }
});
</script>

</body>
</html>