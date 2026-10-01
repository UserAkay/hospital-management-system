<?php
session_start();


// If already logged in -> don't allow access
if (isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Patient Registration • City Hospital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            content: [],
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out forwards',
                        'slide-up': 'slideUp 0.5s ease-out forwards',
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .input-focus {
            transition: all 0.3s ease;
        }
        .input-focus:focus {
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);
            border-color: rgb(59 130 246);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-purple-950 antialiased flex items-center justify-center px-4 py-12 overflow-hidden">

    <!-- Subtle background pattern -->
    <div class="absolute inset-0 bg-[radial-gradient(at_center,#4f46e520_0%,transparent_50%)]"></div>

    <div class="w-full max-w-lg relative z-10">
        <!-- Card -->
        <div class="glass-card rounded-3xl shadow-2xl overflow-hidden animate-slide-up">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-violet-600 px-8 py-12 text-center relative">
                <div class="absolute inset-0 bg-black/20"></div>
                
                <!-- Back Button -->
                <a href="javascript:history.back()" 
                   class="absolute left-6 top-6 flex items-center gap-2 text-white/80 hover:text-white transition-all text-sm font-medium px-4 py-2 rounded-full hover:bg-white/10">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </a>

                <div class="relative z-10">
                    <div class="mx-auto w-24 h-24 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mb-6 border border-white/20 shadow-inner">
                        <i class="fas fa-user-plus text-4xl text-white"></i>
                    </div>
                    <h1 class="text-4xl font-bold tracking-tighter text-white">Create Account</h1>
                    <p class="mt-3 text-indigo-100 text-lg">
                        Join City Hospital and manage your health easily
                    </p>
                </div>
            </div>

            <!-- Form Area -->
            <div class="p-8 sm:p-10">
                <form method="POST" action="../controllers/RegisterPatientController.php" class="space-y-8">

                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Full Name <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-user"></i>
                            </div>
                            <input 
                                type="text" 
                                name="full_name" 
                                required
                                placeholder="Enter your full name"
                                class="input-focus block w-full pl-11 pr-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-gray-400 focus:outline-none"
                            >
                        </div>
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Username <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-at"></i>
                            </div>
                            <input 
                                type="text" 
                                name="username" 
                                required
                                placeholder="Choose a unique username"
                                class="input-focus block w-full pl-11 pr-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-gray-400 focus:outline-none"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Password <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input 
                                type="password" 
                                name="password" 
                                required
                                placeholder="Create a strong password"
                                class="input-focus block w-full pl-11 pr-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-gray-400 focus:outline-none"
                            >
                        </div>
                    </div>

                    <!-- Gender & DOB -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Gender <span class="text-red-400">*</span></label>
                            <select 
                                name="gender" 
                                required
                                class="input-focus block w-full px-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white focus:outline-none appearance-none">
                                <option value="" disabled selected class="bg-slate-900">Select Gender</option>
                                <option value="Male" class="bg-slate-900">Male</option>
                                <option value="Female" class="bg-slate-900">Female</option>
                                <option value="Other" class="bg-slate-900">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Date of Birth <span class="text-red-400">*</span></label>
                            <input 
                                type="date" 
                                name="date_of_birth" 
                                required
                                class="input-focus block w-full px-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white focus:outline-none"
                            >
                        </div>
                    </div>

                    <!-- Contact -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Contact Number <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-phone"></i>
                            </div>
                            <input 
                                type="tel" 
                                name="contact_number" 
                                required
                                placeholder="+91 98765 43210"
                                pattern="[0-9+\s-]{8,15}"
                                class="input-focus block w-full pl-11 pr-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-gray-400 focus:outline-none"
                            >
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Full Address <span class="text-red-400">*</span></label>
                        <textarea 
                            name="address" 
                            rows="3"
                            required
                            placeholder="House no, Street, Area, City, State, PIN Code"
                            class="input-focus block w-full px-4 py-4 bg-white/5 border border-white/10 rounded-2xl text-white placeholder-gray-400 focus:outline-none resize-y min-h-[110px]"
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit"
                        class="w-full py-4 px-6 bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-semibold text-lg rounded-2xl shadow-lg shadow-indigo-500/30 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3">
                        <i class="fas fa-user-plus"></i>
                        Create My Account
                    </button>
                </form>

                <!-- Login Link -->
                <div class="mt-8 text-center">
                    <p class="text-gray-400">
                        Already have an account? 
                        <a href="login.php" class="text-indigo-400 hover:text-indigo-300 font-medium transition-colors">
                            Sign in here
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-gray-500 text-xs mt-8">
            © <?= date('Y') ?> City Hospital Management System • All Rights Reserved
        </p>
    </div>
</body>
</html>