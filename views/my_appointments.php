<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

/* GET PATIENT ID */
$stmt = $conn->prepare("SELECT patient_id FROM patient WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$patient) {
    die("Patient record not found.");
}
$patient_id = $patient['patient_id'];

/* FILTER STATUS */
$status_filter = $_GET['status'] ?? '';

/* FETCH APPOINTMENTS */
$sql = "
SELECT
    a.request_id,
    a.preferred_date,
    a.preferred_time,
    a.status,
    u.username AS doctor_username
FROM appointment_requests a
JOIN doctor d ON a.doctor_id = d.doctor_id
JOIN users u ON d.user_id = u.user_id
WHERE a.patient_id = :patient_id
";
if ($status_filter !== '') {
    $sql .= " AND a.status = :status";
}
$sql .= " ORDER BY a.preferred_date ASC, a.preferred_time ASC";

$stmt = $conn->prepare($sql);
$params = ['patient_id' => $patient_id];
if ($status_filter !== '') {
    $params['status'] = $status_filter;
}
$stmt->execute($params);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* GROUP BY STATUS */
$pending = $approved = $rejected = [];
foreach ($appointments as $a) {
    if ($a['status'] === 'Pending') $pending[] = $a;
    elseif ($a['status'] === 'Approved') $approved[] = $a;
    else $rejected[] = $a;
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments • HMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#3b82f6',
                        'primary-dark': '#2563eb',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-950 to-black text-gray-100">

    <div class="max-w-5xl mx-auto px-4 py-10">

        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-center mb-10 gap-6">
            <h1 class="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent">
                My Appointments
            </h1>
            <a href="patient_dashboard.php"
               class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-white px-6 py-3 rounded-xl transition-all shadow-lg border border-gray-700">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </header>

        <!-- Filter -->
        <form method="GET" class="flex flex-wrap justify-center gap-4 mb-12">
            <select name="status"
                    class="bg-gray-800 text-white border border-gray-600 rounded-xl px-5 py-3 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition min-w-[180px]">
                <option value="">All Status</option>
                <option value="Pending"  <?= $status_filter === 'Pending'  ? 'selected' : '' ?>>Pending</option>
                <option value="Approved" <?= $status_filter === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Rejected" <?= $status_filter === 'Rejected' ? 'selected' : '' ?>>Rejected / Cancelled</option>
            </select>
            <button type="submit"
                    class="bg-primary hover:bg-primary-dark px-8 py-3 rounded-xl font-semibold transition-all shadow-lg shadow-blue-900/30 flex items-center gap-2">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>

        <!-- Appointment Sections -->
        <?php
        $sections = [
            ['title' => 'Pending Requests', 'data' => $pending,  'color' => 'yellow', 'icon' => 'fa-hourglass-half'],
            ['title' => 'Approved Appointments', 'data' => $approved, 'color' => 'emerald', 'icon' => 'fa-check-circle'],
            ['title' => 'Rejected / Cancelled', 'data' => $rejected, 'color' => 'rose', 'icon' => 'fa-times-circle'],
        ];

        foreach ($sections as $section):
            $color = $section['color'];
        ?>
            <section class="mb-14">
                <h2 class="text-2xl font-bold mb-5 flex items-center gap-3">
                    <i class="fas <?= $section['icon'] ?> text-<?= $color ?>-400"></i>
                    <span class="bg-gradient-to-r from-<?= $color ?>-400 to-<?= $color ?>-500 bg-clip-text text-transparent">
                        <?= $section['title'] ?>
                    </span>
                </h2>

                <?php if (empty($section['data'])): ?>
                    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-2xl p-8 text-center text-gray-400">
                        <i class="fas fa-calendar-times text-4xl mb-4 opacity-50"></i>
                        <p class="text-lg">No appointments in this category right now.</p>
                    </div>
                <?php else: ?>
                    <div class="grid gap-5 md:grid-cols-2">
                        <?php foreach ($section['data'] as $appt): ?>
                            <div class="group bg-gray-800/70 backdrop-blur-sm border border-gray-700/80 rounded-2xl p-6 hover:border-<?= $color ?>-500/50 hover:shadow-xl hover:shadow-<?= $color ?>-900/20 transition-all duration-300">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-xl font-semibold text-white">
                                            Dr. <?= htmlspecialchars($appt['doctor_username']) ?>
                                        </h3>
                                        <div class="mt-3 space-y-1.5 text-gray-300">
                                            <p class="flex items-center gap-2">
                                                <i class="far fa-calendar-alt w-5 text-<?= $color ?>-400"></i>
                                                <?= htmlspecialchars($appt['preferred_date']) ?>
                                            </p>
                                            <p class="flex items-center gap-2">
                                                <i class="far fa-clock w-5 text-<?= $color ?>-400"></i>
                                                <?= htmlspecialchars($appt['preferred_time']) ?>
                                            </p>
                                        </div>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-<?= $color ?>-500/20 text-<?= $color ?>-300 border border-<?= $color ?>-500/30">
                                        <?= $appt['status'] ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endforeach; ?>

    </div>

    <script>
        // Auto logout after 20 minutes (1200000 ms)
        setTimeout(() => {
            window.location.href = "login.php?error=Session expired";
        }, 1200000);
    </script>
</body>
</html>