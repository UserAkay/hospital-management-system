<?php
session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'receptionist') {
    header("Location: login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

$query = "
    SELECT
        ds.schedule_id,
        u.username AS doctor_name,
        ds.day_of_week,
        ds.start_time,
        ds.end_time,
        ds.slot_duration
    FROM doctor_schedule ds
    JOIN doctor d ON ds.doctor_id = d.doctor_id
    JOIN users u ON d.user_id = u.user_id
    ORDER BY u.username, FIELD(ds.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')
";

$stmt = $conn->prepare($query);
$stmt->execute();
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-50">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Doctor Schedules • HMS Reception</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
          integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="min-h-screen antialiased">

    <!-- Header -->
    <header class="bg-gradient-to-r from-cyan-600 to-blue-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-center sm:text-left">
                    <h1 class="text-2xl sm:text-3xl font-bold">Doctor Schedules</h1>
                    <p class="mt-1 text-cyan-100">Current availability overview</p>
                </div>
                <a href="reception_dashboard.php"
                   class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 px-5 py-2.5 rounded-lg font-medium transition backdrop-blur-sm">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <?php if (empty($schedules)): ?>
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow border border-gray-100 p-12 text-center">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-5">
                    <i class="fas fa-calendar-times text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No schedules found</h3>
                <p class="text-gray-500">Doctors' availability has not been set yet.</p>
            </div>
        <?php else: ?>

            <!-- Table Card -->
            <div class="bg-white rounded-xl shadow border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-cyan-600 to-blue-600">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">
                                    <i class="fas fa-user-md mr-2"></i>Doctor
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">
                                    <i class="fas fa-calendar-day mr-2"></i>Day
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">
                                    <i class="fas fa-clock mr-2"></i>Start Time
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">
                                    <i class="fas fa-clock mr-2"></i>End Time
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-sm font-semibold text-white uppercase tracking-wider">
                                    <i class="fas fa-stopwatch mr-2"></i>Slot Duration
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php
                            $prev_doctor = null;
                            foreach ($schedules as $row):
                                $is_new_doctor = $prev_doctor !== $row['doctor_name'];
                                $prev_doctor = $row['doctor_name'];
                            ?>
                                <tr class="hover:bg-blue-50/60 transition-colors <?= $is_new_doctor ? 'border-t-4 border-t-cyan-100' : '' ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($is_new_doctor): ?>
                                            <div class="font-medium text-gray-900">
                                                Dr. <?= htmlspecialchars($row['doctor_name']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                            <?php
                                            switch(strtolower($row['day_of_week'])) {
                                                case 'monday':    echo 'bg-blue-100 text-blue-800'; break;
                                                case 'tuesday':   echo 'bg-indigo-100 text-indigo-800'; break;
                                                case 'wednesday': echo 'bg-purple-100 text-purple-800'; break;
                                                case 'thursday':  echo 'bg-pink-100 text-pink-800'; break;
                                                case 'friday':    echo 'bg-green-100 text-green-800'; break;
                                                case 'saturday':  echo 'bg-amber-100 text-amber-800'; break;
                                                case 'sunday':    echo 'bg-red-100 text-red-800'; break;
                                                default:          echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?= htmlspecialchars($row['day_of_week']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                        <?= htmlspecialchars($row['start_time']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                        <?= htmlspecialchars($row['end_time']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-cyan-50 text-cyan-800">
                                            <?= htmlspecialchars($row['slot_duration']) ?> min
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Stats (optional nice touch) -->
            <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl shadow border text-center">
                    <p class="text-sm text-gray-500">Total Doctors</p>
                    <p class="text-2xl font-bold text-cyan-700 mt-1">
                        <?= count(array_unique(array_column($schedules, 'doctor_name'))) ?>
                    </p>
                </div>
                <div class="bg-white p-5 rounded-xl shadow border text-center">
                    <p class="text-sm text-gray-500">Total Schedules</p>
                    <p class="text-2xl font-bold text-cyan-700 mt-1"><?= count($schedules) ?></p>
                </div>
            </div>

        <?php endif; ?>

    </main>

</body>
</html>