<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Patient') {
    header("Location: login.php");
    exit();
}

$displayName = htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Patient');
$currentYear = date("Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Patient Dashboard • HMS</title>

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
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-12">
        <i class="fa-solid fa-heart-pulse absolute text-9xl text-cyan-500 animate-pulse-slow -left-20 top-20 rotate-12"></i>
        <i class="fa-solid fa-hospital-user absolute text-10xl text-blue-400 animate-float right-10 bottom-20 -rotate-6"></i>
        <i class="fa-solid fa-notes-medical absolute text-8xl text-indigo-400 animate-pulse-slow -right-16 top-1/3"></i>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto">

        <!-- Welcome Header -->
        <div class="glass rounded-3xl shadow-2xl overflow-hidden border border-gray-700/40 mb-12">
            <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-gray-950 px-6 py-12 md:py-16 text-center relative overflow-hidden">
                <div class="relative z-10">
                    <div class="mx-auto w-24 h-24 bg-gradient-to-br from-cyan-700 to-blue-800 rounded-full flex items-center justify-center mb-8 border-2 border-cyan-400/40 shadow-2xl animate-float">
                        <i class="fa-solid fa-user-injured text-5xl text-white drop-shadow-lg"></i>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-xl">
                        Patient Dashboard
                    </h1>
                    <div class="mt-4 text-2xl md:text-3xl font-semibold text-cyan-100/90">
                        Welcome back, <?= $displayName ?>!
                    </div>
                    <p class="mt-5 text-xl text-cyan-100/70">
                        Manage your healthcare journey with ease
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Action Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

            <!-- Book Appointment Card (highlighted) -->
            <div class="glass rounded-3xl p-8 md:p-10 border border-gray-700/40 hover:border-cyan-500/50 transition-all duration-300 shadow-2xl hover:shadow-3xl group">
                <div class="flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-cyan-600 to-blue-700 rounded-full flex items-center justify-center mb-8 shadow-xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-calendar-plus text-4xl text-white"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-white mb-4">Book New Appointment</h2>
                    <p class="text-gray-300 text-lg mb-8 leading-relaxed">
                        Schedule a visit with your preferred doctor,<br>
                        choose convenient date & time, and get instant confirmation.
                    </p>
                    <a href="create_appointment.php"
                       class="inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white font-semibold text-xl rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1.5 transition-all duration-300">
                        <i class="fas fa-calendar-plus text-2xl"></i>
                        Book Appointment Now
                    </a>
                </div>
            </div>

            <!-- My Appointments Card -->
            <div class="glass rounded-3xl p-8 md:p-10 border border-gray-700/40 hover:border-cyan-500/50 transition-all duration-300 shadow-2xl hover:shadow-3xl group">
                <div class="flex flex-col items-center text-center">
                    <div class="w-20 h-20 bg-gradient-to-br from-indigo-600 to-purple-700 rounded-full flex items-center justify-center mb-8 shadow-xl group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-calendar-check text-4xl text-white"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-white mb-4">My Appointments</h2>
                    <p class="text-gray-300 text-lg mb-8 leading-relaxed">
                        View upcoming & past appointments,<br>
                        reschedule, cancel, or check doctor details and status.
                    </p>
                    <a href="my_appointments.php"
                       class="inline-flex items-center gap-3 px-10 py-5 bg-gradient-to-r from-indigo-600 to-purple-700 hover:from-indigo-700 hover:to-purple-800 text-white font-semibold text-xl rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1.5 transition-all duration-300">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                        View My Appointments
                    </a>
                </div>
            </div>

        </div>

        <!-- Logout & Footer -->
        <div class="mt-16 text-center">
            <a href="../controllers/logout.php"
               class="inline-flex items-center gap-4 px-12 py-5 bg-gradient-to-r from-red-700 to-rose-700 hover:from-red-800 hover:to-rose-800 text-white font-semibold text-xl rounded-2xl shadow-2xl hover:shadow-3xl transform hover:-translate-y-1.5 transition-all duration-300 border border-red-600/40">
                <i class="fas fa-sign-out-alt text-2xl"></i>
                Logout
            </a>
        </div>

        <footer class="mt-16 text-center text-gray-500 text-sm">
            Hospital Management System • © <?= $currentYear ?> • Patient Portal
        </footer>

    </div>

    <!-- Auto logout after 20 minutes -->
    <script>
        setTimeout(function() {
            window.location.href = "login.php?error=Session expired";
        }, 1200000);
    </script>

</body>
</html>