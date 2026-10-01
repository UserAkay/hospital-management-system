<?php
session_start();
require_once "../config/session_check.php";

/* Allow Admin + Receptionist */
if (!isset($_SESSION['user_id']) || 
    !in_array(strtolower($_SESSION['role']), ['admin','receptionist'])) {

    header("Location: login.php");
    exit();
}


require_once "../config/database.php";

if (!isset($_GET['id'])) {
    header("Location: manage_patients.php");
    exit();
}

$patient_id = (int)$_GET['id'];
$database = new Database();
$conn = $database->connect();

$query = "SELECT p.patient_id, p.full_name, p.gender, p.date_of_birth,
                 p.contact_number, p.address, u.username
          FROM patient p
          JOIN users u ON p.user_id = u.user_id
          WHERE p.patient_id = :patient_id";

$stmt = $conn->prepare($query);
$stmt->bindParam(":patient_id", $patient_id, PDO::PARAM_INT);
$stmt->execute();
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    header("Location: manage_patients.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Edit Patient • HMS</title>

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
        <i class="fa-solid fa-user-injured absolute text-8xl text-cyan-500 animate-pulse-slow -left-16 top-20 rotate-12"></i>
        <i class="fa-solid fa-hospital-user absolute text-9xl text-blue-400 animate-float right-12 bottom-24 -rotate-6"></i>
        <i class="fa-solid fa-heart-pulse absolute text-7xl text-indigo-400 animate-pulse-slow -right-20 top-1/3"></i>
    </div>

    <div class="relative z-10 max-w-lg mx-auto">

        <!-- Back link -->
        <a href="manage_patients.php"
           class="inline-flex items-center gap-3 text-cyan-400 hover:text-cyan-300 font-medium mb-10 transition-all duration-200 group">
            <i class="fas fa-arrow-left text-xl transform group-hover:-translate-x-1 transition-transform"></i>
            Back to Patients List
        </a>

        <!-- Header -->
        <div class="text-center mb-12">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-cyan-700 to-blue-800 rounded-full flex items-center justify-center mb-6 shadow-xl animate-float border-2 border-cyan-400/40">
                <i class="fa-solid fa-user-injured text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-lg">
                Edit Patient
            </h1>
            <p class="mt-4 text-xl text-cyan-100/80">
                Update patient profile information
            </p>
        </div>

        <!-- Form Card -->
        <div class="glass rounded-3xl shadow-2xl p-8 md:p-10 border border-gray-700/50">
            <form method="POST" action="../controllers/UpdatePatientController.php" class="space-y-8">

                <input type="hidden" name="patient_id" value="<?= htmlspecialchars($patient['patient_id']) ?>">

                <!-- Account Information -->
                <div class="pb-6 border-b border-gray-700/50">
                    <h2 class="text-2xl font-bold text-cyan-400 mb-6">Account Information</h2>

                    <!-- Username -->
                    <div class="mb-8">
                        <label for="username" class="block text-lg font-semibold text-gray-200 mb-3">
                            Username <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                                <i class="fas fa-user text-xl"></i>
                            </div>
                            <input
                                type="text"
                                id="username"
                                name="username"
                                value="<?= htmlspecialchars($patient['username']) ?>"
                                required
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                            />
                        </div>
                    </div>

                    <!-- Password (optional) -->
                    <div>
                        <label for="password" class="block text-lg font-semibold text-gray-200 mb-3">
                            New Password
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                                <i class="fas fa-lock text-xl"></i>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Leave blank to keep current password"
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                            />
                        </div>
                        <p class="mt-3 text-sm text-gray-400">
                            Leave empty if password should remain unchanged
                        </p>
                    </div>
                </div>

                <!-- Personal Information -->
                <div>
                    <h2 class="text-2xl font-bold text-cyan-400 mb-6">Personal Information</h2>

                    <!-- Full Name -->
                    <div class="mb-8">
                        <label for="full_name" class="block text-lg font-semibold text-gray-200 mb-3">
                            Full Name <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                                <i class="fas fa-user-tag text-xl"></i>
                            </div>
                            <input
                                type="text"
                                id="full_name"
                                name="full_name"
                                value="<?= htmlspecialchars($patient['full_name']) ?>"
                                required
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                            />
                        </div>
                    </div>

                    <!-- Gender -->
                    <div class="mb-8">
                        <label for="gender" class="block text-lg font-semibold text-gray-200 mb-3">
                            Gender <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                                <i class="fas fa-venus-mars text-xl"></i>
                            </div>
                            <select
                                id="gender"
                                name="gender"
                                required
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base appearance-none"
                            >
                                <option value="Male"  <?= $patient['gender'] === 'Male'  ? 'selected' : '' ?>>Male</option>
                                <option value="Female"<?= $patient['gender'] === 'Female'? 'selected' : '' ?>>Female</option>
                                <option value="Other" <?= $patient['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Date of Birth -->
                    <div class="mb-8">
                        <label for="date_of_birth" class="block text-lg font-semibold text-gray-200 mb-3">
                            Date of Birth <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                                <i class="fas fa-calendar-days text-xl"></i>
                            </div>
                            <input
                                type="date"
                                id="date_of_birth"
                                name="date_of_birth"
                                value="<?= htmlspecialchars($patient['date_of_birth']) ?>"
                                required
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base"
                            />
                        </div>
                    </div>

                    <!-- Contact Number -->
                    <div class="mb-8">
                        <label for="contact_number" class="block text-lg font-semibold text-gray-200 mb-3">
                            Contact Number <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                                <i class="fas fa-phone text-xl"></i>
                            </div>
                            <input
                                type="text"
                                id="contact_number"
                                name="contact_number"
                                value="<?= htmlspecialchars($patient['contact_number']) ?>"
                                required
                                placeholder="e.g. +91 98765 43210"
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                            />
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-lg font-semibold text-gray-200 mb-3">
                            Address <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute top-4 left-0 pl-4 flex items-start pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                                <i class="fas fa-home text-xl"></i>
                            </div>
                            <textarea
                                id="address"
                                name="address"
                                required
                                rows="4"
                                placeholder="Full address..."
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500 resize-y"
                            ><?= htmlspecialchars($patient['address']) ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-8">
                    <button type="submit"
                            class="group flex-1 flex items-center justify-center gap-3 py-5 px-8 bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white font-semibold text-lg rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1.5 focus:outline-none focus:ring-4 focus:ring-cyan-500/30 transition-all duration-300">
                        <i class="fas fa-save text-xl"></i>
                        <span>Save Changes</span>
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                    </button>

                    <a href="manage_patients.php"
                       class="flex-1 flex items-center justify-center gap-3 py-5 px-8 bg-gray-700/60 hover:bg-gray-600/70 text-gray-300 hover:text-white font-semibold text-lg rounded-2xl border border-gray-600 transition-all duration-300">
                        <i class="fas fa-arrow-left text-xl"></i>
                        Cancel
                    </a>
                </div>

            </form>
        </div>

    </div>

</body>
</html>