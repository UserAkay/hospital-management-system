<?php
session_start();
require_once "../config/session_check.php";

// Admin-only access
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get messages from URL (after redirect)
$success_msg = $_GET['success'] ?? '';
$error_msg   = $_GET['error']   ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Create New User • HMS</title>

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

    <!-- Floating background medical icons -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-15">
        <i class="fa-solid fa-user-plus absolute text-8xl text-cyan-500 animate-pulse-slow -left-16 top-20"></i>
        <i class="fa-solid fa-user-shield absolute text-9xl text-blue-400 animate-float right-12 bottom-24 rotate-12"></i>
        <i class="fa-solid fa-hospital-user absolute text-7xl text-indigo-400 animate-pulse-slow -right-20 top-1/3"></i>
    </div>

    <div class="relative z-10 max-w-lg mx-auto">

        <!-- Back button -->
        <a href="admin_dashboard.php"
           class="inline-flex items-center gap-3 text-cyan-400 hover:text-cyan-300 font-medium mb-10 transition-all duration-200 group">
            <i class="fas fa-arrow-left text-xl transform group-hover:-translate-x-1 transition-transform"></i>
            Back to Dashboard
        </a>

        <!-- Header -->
        <div class="text-center mb-12">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-cyan-700 to-blue-800 rounded-full flex items-center justify-center mb-6 shadow-xl animate-float border-2 border-cyan-400/40">
                <i class="fa-solid fa-user-plus text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-lg">
                Create New User
            </h1>
            <p class="mt-4 text-xl text-cyan-100/80">
                Add doctors, patients, receptionists or staff
            </p>
        </div>

        <!-- Messages -->
        <?php if ($success_msg): ?>
        <div class="mb-8 p-6 bg-emerald-950/60 border-l-4 border-emerald-500 rounded-2xl text-emerald-200 flex items-start gap-4 shadow-lg">
            <i class="fas fa-check-circle text-3xl mt-1 flex-shrink-0"></i>
            <div class="text-base font-medium">
                <?= htmlspecialchars(urldecode($success_msg)) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
        <div class="mb-8 p-6 bg-red-950/60 border-l-4 border-red-500 rounded-2xl text-red-200 flex items-start gap-4 shadow-lg">
            <i class="fas fa-circle-exclamation text-3xl mt-1 flex-shrink-0"></i>
            <div class="text-base font-medium">
                <?= htmlspecialchars(urldecode($error_msg)) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="glass rounded-3xl shadow-2xl p-8 md:p-10 border border-gray-700/50">
            <form method="POST" action="../controllers/create_user_controller.php" class="space-y-8">

                <!-- Username -->
                <div>
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
                            required
                            autocomplete="off"
                            placeholder="Enter unique username"
                            class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 placeholder-gray-500 text-white text-base"
                        />
                    </div>
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-lg font-semibold text-gray-200 mb-3">
                        Role <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                            <i class="fas fa-user-tag text-xl"></i>
                        </div>
                        <select
                            id="role"
                            name="role"
                            required
                            class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base appearance-none"
                        >
                            <option value="" class="bg-gray-900">— Select Role —</option>
                            <option value="Admin">Admin</option>
                            <option value="Doctor">Doctor</option>
                            <option value="Receptionist">Receptionist</option>
                            <option value="Staff">Staff</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>

                <!-- Password (optional) -->
                <div>
                    <label for="password" class="block text-lg font-semibold text-gray-200 mb-3">
                        Password
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                            <i class="fas fa-lock text-xl"></i>
                        </div>
                        <input
                            type="text"
                            id="password"
                            name="password"
                            placeholder="Leave blank to auto-generate secure password"
                            class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 placeholder-gray-500 text-white text-base"
                        />
                    </div>
                    <p class="mt-3 text-sm text-gray-400">
                        A strong temporary password will be generated and displayed after creation if this field is left empty.
                    </p>
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="group relative w-full flex items-center justify-center gap-4 py-5 px-8 bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white font-semibold text-xl rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1.5 focus:outline-none focus:ring-4 focus:ring-cyan-500/30 transition-all duration-300 mt-6">
                    <i class="fas fa-user-plus text-2xl"></i>
                    <span>Create User</span>
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                </button>

            </form>
        </div>

    </div>

</body>
</html>