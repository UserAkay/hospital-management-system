<?php
session_start();
/* Redirect if already logged in */
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    $role = strtolower($_SESSION['role']);
    $redirects = [
        'admin'        => 'admin_dashboard.php',
        'doctor'       => 'doctor_dashboard.php',
        'receptionist' => 'reception_dashboard.php',
        'patient'      => 'patient_dashboard.php'
    ];
    if (isset($redirects[$role])) {
        header("Location: " . $redirects[$role]);
    } else {
        session_destroy();
        header("Location: login.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>HMS – Secure Access</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
          integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 6s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-12px)' },
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-950 via-indigo-950 to-purple-950 text-gray-100 antialiased flex items-center justify-center px-5 py-10">

    <div class="w-full max-w-md relative">

        <!-- Floating medical background icons -->
        <div class="absolute -inset-20 pointer-events-none overflow-hidden opacity-20">
            <i class="fa-solid fa-heart-pulse absolute text-8xl text-cyan-500 animate-pulse-slow -left-10 top-20"></i>
            <i class="fa-solid fa-stethoscope absolute text-9xl text-blue-400 animate-float right-8 bottom-16 rotate-12"></i>
            <i class="fa-solid fa-hospital absolute text-7xl text-indigo-400 animate-pulse-slow -right-12 top-40"></i>
        </div>

        <div class="bg-gray-900/60 backdrop-blur-2xl rounded-3xl shadow-2xl overflow-hidden border border-gray-700/50 transform transition-all duration-500 hover:shadow-3xl hover:border-indigo-500/30 relative z-10">

            <!-- Header -->
            <div class="relative bg-gradient-to-br from-indigo-900 via-indigo-950 to-gray-950 px-8 py-14 text-center overflow-hidden">
                <!-- Animated heartbeat line -->
                <div class="absolute inset-0 opacity-20">
                    <svg class="w-full h-full" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#67e8f9"/>
                                <stop offset="50%" stop-color="#60a5fa"/>
                                <stop offset="100%" stop-color="#67e8f9"/>
                            </linearGradient>
                        </defs>
                        <path d="M0,80 Q120,40 240,80 T480,80" fill="none" stroke="url(#grad)" stroke-width="3" class="animate-pulse"/>
                    </svg>
                </div>

                <div class="relative z-10">
                    <!-- Logo circle – NO BLUR, solid gradient -->
                    <div class="mx-auto w-24 h-24 bg-gradient-to-br from-cyan-600 to-blue-700 rounded-full flex items-center justify-center mb-6 border-2 border-cyan-400/60 shadow-xl animate-float">
                        <i class="fa-solid fa-hospital-user text-5xl text-white drop-shadow-lg"></i>
                    </div>

                    <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-xl text-white">
                        HospitalOS Login
                    </h1>
                    <p class="mt-3 text-cyan-100/80 text-lg font-light tracking-wide">
                        Secure Professional Access
                    </p>
                </div>
            </div>

            <!-- Form Area -->
            <div class="p-8 sm:p-10">
                <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-8 p-5 bg-red-950/40 border-l-4 border-red-500 rounded-2xl text-red-200 flex items-start gap-3 animate-fade-in">
                    <i class="fas fa-circle-exclamation text-2xl mt-0.5 flex-shrink-0"></i>
                    <div class="text-sm font-medium leading-relaxed">
                        <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                </div>
                <?php unset($_SESSION['error']); endif; ?>

                <form method="POST" action="../controllers/AuthController.php" class="space-y-7">
                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2.5 tracking-wide">
                            Username / Employee ID
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-all duration-200 group-focus-within:text-cyan-400 group-focus-within:scale-110">
                                <i class="fas fa-user text-gray-400 text-xl"></i>
                            </div>
                            <input
                                type="text"
                                name="username"
                                required
                                autocomplete="username"
                                placeholder="Enter your username"
                                class="block w-full pl-12 pr-5 py-4 bg-gray-800/50 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 placeholder-gray-500 text-white text-base"
                            />
                        </div>
                    </div>

                    <!-- Password with toggle -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-300 mb-2.5 tracking-wide">
                            Password
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-all duration-200 group-focus-within:text-cyan-400 group-focus-within:scale-110">
                                <i class="fas fa-lock text-gray-400 text-xl"></i>
                            </div>
                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="block w-full pl-12 pr-14 py-4 bg-gray-800/50 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 placeholder-gray-500 text-white text-base"
                            />
                            <button
                                type="button"
                                id="togglePassword"
                                class="absolute inset-y-0 right-0 pr-5 flex items-center text-gray-400 hover:text-cyan-400 focus:outline-none transition-all duration-200"
                                aria-label="Toggle password visibility"
                            >
                                <i class="fas fa-eye text-xl" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button type="submit"
                                class="group relative w-full flex items-center justify-center gap-3 py-4 px-8 bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white font-semibold text-lg rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1.5 focus:outline-none focus:ring-4 focus:ring-cyan-500/30 transition-all duration-300 overflow-hidden">
                            <span>Sign In Securely</span>
                            <i class="fas fa-arrow-right transform group-hover:translate-x-2 transition-transform duration-300"></i>
                            <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        </button>
                    </div>
                </form>

                <!-- Links -->
                <div class="mt-10 text-center space-y-6 text-sm text-gray-400">
                    <a href="forgot_password.php"
                       class="text-cyan-400 hover:text-cyan-300 font-medium transition inline-flex items-center gap-2 hover:underline underline-offset-4">
                        <i class="fas fa-key"></i>
                        Forgot your password?
                    </a>
                    <div class="pt-2">
                        New to HMS?
                        <a href="self_register.php"
                           class="text-cyan-400 hover:text-cyan-300 font-medium transition hover:underline underline-offset-4">
                            Create patient account
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-xs mt-8">
            © <?= date('Y') ?> Hospital Management System • Patna, Bihar
        </p>
    </div>

    <!-- Password visibility toggle script -->
    <script>
        const toggle = document.querySelector('#togglePassword');
        const pwd = document.querySelector('#password');
        const eye = document.querySelector('#eyeIcon');

        if (toggle && pwd && eye) {
            toggle.addEventListener('click', () => {
                const type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
                pwd.setAttribute('type', type);
                eye.classList.toggle('fa-eye');
                eye.classList.toggle('fa-eye-slash');
            });
        }
    </script>

</body>
</html>