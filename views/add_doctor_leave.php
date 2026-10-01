<?php
session_start();
require_once "../config/session_check.php";

// Security check - only allow authorized roles
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']), ['admin', 'receptionist'])) {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

// Fetch active doctors
try {
    $stmt = $conn->prepare("
        SELECT d.doctor_id, u.username, u.full_name 
        FROM doctor d
        JOIN users u ON d.user_id = u.user_id
        WHERE u.is_active = 1
        ORDER BY u.full_name ASC
    ");
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Failed to load doctors: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Add Doctor Leave • HMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" 
          integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" 
          crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 antialiased flex items-center justify-center px-4 py-12">

    <div class="w-full max-w-lg">

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100/80 overflow-hidden">

            <!-- Header -->
            <div class="bg-gradient-to-r from-rose-600 to-pink-600 px-8 py-10 text-center text-white relative">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="relative z-10">
                    <div class="mx-auto w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mb-5 backdrop-blur-sm border border-white/30">
                        <i class="fas fa-calendar-xmark text-3xl"></i>
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight">Add Doctor Leave</h1>
                    <p class="mt-3 text-rose-100 text-lg opacity-90">
                        Mark unavailable dates for doctors
                    </p>
                </div>
            </div>

            <!-- Form Content -->
            <div class="p-8 sm:p-10">

                <?php if (isset($error)): ?>
                <div class="mb-8 p-5 bg-red-50 border-l-4 border-red-500 rounded-r-xl text-red-800 flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-xl mt-0.5"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-8 p-5 bg-green-50 border-l-4 border-green-500 rounded-r-xl text-green-800 flex items-start gap-3">
                    <i class="fas fa-check-circle text-xl mt-0.5"></i>
                    <div><?= htmlspecialchars($_SESSION['success']) ?></div>
                </div>
                <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form method="POST" action="../controllers/AddDoctorLeaveController.php" class="space-y-7">

                    <!-- Doctor Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Select Doctor <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user-doctor text-gray-400"></i>
                            </div>
                            <select 
                                name="doctor_id" 
                                required
                                class="block w-full pl-11 pr-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition shadow-sm bg-white appearance-none"
                            >
                                <option value="" disabled selected>— Choose doctor —</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?= htmlspecialchars($doctor['doctor_id']) ?>">
                                        <?= htmlspecialchars($doctor['full_name'] ?: $doctor['username']) ?>
                                        <?= $doctor['full_name'] ? " (@{$doctor['username']})" : "" ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Leave Date -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Leave Date <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-xmark text-gray-400"></i>
                            </div>
                            <input 
                                type="date" 
                                name="leave_date" 
                                required
                                min="<?= date('Y-m-d') ?>"
                                class="block w-full pl-11 pr-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition shadow-sm"
                            >
                        </div>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Reason for Leave (optional)
                        </label>
                        <div class="relative">
                            <div class="absolute top-3.5 left-0 pl-4 pointer-events-none">
                                <i class="fas fa-comment-medical text-gray-400"></i>
                            </div>
                            <textarea 
                                name="reason" 
                                rows="3"
                                placeholder="e.g., Conference, Personal reasons, Medical leave, Vacation..."
                                class="block w-full pl-11 pr-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-rose-500 transition shadow-sm placeholder-gray-400 resize-y"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-3 py-4 px-6 bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white font-semibold text-lg rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-rose-300">
                            <i class="fas fa-calendar-plus"></i>
                            Add Leave Entry
                        </button>
                    </div>

                </form>

                <!-- Back link -->
                <div class="mt-8 text-center">
                    <a href="previous_page.php"  <!-- change to your actual back page -->
                       class="inline-flex items-center text-rose-600 hover:text-rose-800 font-medium transition">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Doctor Management
                    </a>
                </div>

            </div>
        </div>

    </div>

</body>
</html>