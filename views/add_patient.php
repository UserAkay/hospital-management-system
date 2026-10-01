<?php
session_start();
require_once "../config/session_check.php";

if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']), ['admin','receptionist'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Add New Patient • HMS</title>

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

    <div class="relative z-10 max-w-2xl mx-auto">

        <!-- Back link -->
        <a href="manage_patients.php"
           class="inline-flex items-center gap-3 text-cyan-400 hover:text-cyan-300 font-medium mb-10 transition-all duration-200 group">
            <i class="fas fa-arrow-left text-xl transform group-hover:-translate-x-1 transition-transform"></i>
            Back to Patient Management
        </a>

        <!-- Header -->
        <div class="text-center mb-12">
            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-cyan-700 to-blue-800 rounded-full flex items-center justify-center mb-6 shadow-xl animate-float border-2 border-cyan-400/40">
                <i class="fa-solid fa-user-plus text-4xl text-white"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-lg">
                Register New Patient
            </h1>
            <p class="mt-4 text-xl text-cyan-100/80">
                Enter patient details carefully
            </p>
        </div>

        <!-- Messages -->
        <?php if(isset($_GET['error'])): ?>
        <div class="mb-8 p-6 bg-red-950/60 border-l-4 border-red-500 rounded-2xl text-red-200 flex items-start gap-4 shadow-lg">
            <i class="fas fa-circle-exclamation text-3xl mt-1 flex-shrink-0"></i>
            <div class="text-base font-medium">
                <?= htmlspecialchars(urldecode($_GET['error'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if(isset($_GET['message'])): ?>
        <div class="mb-8 p-6 bg-emerald-950/60 border-l-4 border-emerald-500 rounded-2xl text-emerald-200 flex items-start gap-4 shadow-lg">
            <i class="fas fa-check-circle text-3xl mt-1 flex-shrink-0"></i>
            <div class="text-base font-medium">
                <?= htmlspecialchars(urldecode($_GET['message'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="glass rounded-3xl shadow-2xl p-8 md:p-10 border border-gray-700/50">
            <form method="POST" action="../controllers/AddPatientController.php" class="space-y-8">

                <!-- Username & Password -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                placeholder="Choose a unique username"
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-lg font-semibold text-gray-200 mb-3">
                            Password <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                                <i class="fas fa-lock text-xl"></i>
                            </div>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                required
                                placeholder="Create a secure password"
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
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
                                required
                                placeholder="Enter full name as in ID"
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                            />
                        </div>
                    </div>

                    <div>
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
                                <option value="">Select gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other / Prefer not to say</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
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
                                required
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="contact_number" class="block text-lg font-semibold text-gray-200 mb-3">
                            Contact Number <span class="text-red-400">*</span>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                                <i class="fas fa-phone text-xl"></i>
                            </div>
                            <input
                                type="tel"
                                id="contact_number"
                                name="contact_number"
                                required
                                pattern="[0-9+\s-]{8,15}"
                                placeholder="+91 98765 43210"
                                class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-lg font-semibold text-gray-200 mb-3">
                        Full Address <span class="text-red-400">*</span>
                    </label>
                    <div class="relative group">
                        <div class="absolute top-4 left-0 pl-4 flex items-start pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">
                            <i class="fas fa-home text-xl"></i>
                        </div>
                        <textarea
                            id="address"
                            name="address"
                            rows="4"
                            required
                            placeholder="House/Flat no, Street, Area, City, State, PIN Code"
                            class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500 resize-y"
                        ></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-6">
                    <button type="submit"
                            class="group relative w-full flex items-center justify-center gap-4 py-5 px-8 bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white font-semibold text-xl rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1.5 focus:outline-none focus:ring-4 focus:ring-cyan-500/30 transition-all duration-300">
                        <i class="fas fa-user-plus text-2xl"></i>
                        <span>Register New Patient</span>
                        <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                    </button>
                </div>

            </form>

            <div class="mt-10 text-center">
                <a href="manage_patients.php"
                   class="inline-flex items-center text-cyan-400 hover:text-cyan-300 font-medium transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Patient Management
                </a>
            </div>
        </div>

    </div>

</body>
</html>