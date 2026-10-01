<?php
session_start();

require_once "../config/session_check.php";

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Preserve old input after controller redirects back
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);

$username = htmlspecialchars($old['username'] ?? '', ENT_QUOTES, 'UTF-8');
$specialization = htmlspecialchars($old['specialization'] ?? '', ENT_QUOTES, 'UTF-8');
$contact_number = htmlspecialchars($old['contact_number'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add New Doctor • HMS</title>

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    >

    <!-- intl-tel-input CSS -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/intl-tel-input@29.2.3/dist/css/intlTelInput.css"
    >

    <!-- Tailwind CSS -->
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
                            '0%, 100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-16px)'
                            }
                        }
                    }
                }
            }
        };
    </script>

    <style>

    .glass {
        background: rgba(30, 41, 59, 0.68);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
    }

    /* =========================================================
       INTL TEL INPUT
    ========================================================= */

    .iti {
        width: 100%;
        display: block;
    }

    .iti input[type="tel"] {
        width: 100% !important;
        padding-left: 100px !important;
    }

    /* Country selector */
    .iti__country-container {
        z-index: 20;
    }

    /* Selected country area */
    .iti__selected-country {
        background: transparent !important;
        color: #ffffff !important;
        border-radius: 1rem 0 0 1rem;
    }

    .iti__selected-country:hover {
        background: rgba(255, 255, 255, 0.05) !important;
    }

    /* Country flag */
    .iti__flag {
        flex-shrink: 0;
    }

    /* Country dial code */
    .iti__selected-dial-code {
        color: #ffffff !important;
        font-size: 16px;
        font-weight: 500;
        margin-left: 6px;
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Arrow */
    .iti__arrow {
        border-top-color: #9ca3af !important;
        margin-left: 6px;
    }

    .iti__arrow--up {
        border-bottom-color: #9ca3af !important;
        border-top-color: transparent !important;
    }

    /* Country dropdown */
    .iti__dropdown-content {
        z-index: 99999 !important;
        background: #1f2937 !important;
        border: 1px solid #4b5563 !important;
        color: #ffffff !important;
    }

    /* Country list */
    .iti__country-list {
        background: #1f2937 !important;
        color: #ffffff !important;
    }

    /* Country item */
    .iti__country {
        color: #ffffff !important;
    }

    .iti__country:hover,
    .iti__country--highlight {
        background: #374151 !important;
    }

    /* Country name */
    .iti__country-name {
        color: #ffffff !important;
    }

    /* Dial code inside dropdown */
    .iti__dial-code {
        color: #9ca3af !important;
    }

    /* Search box */
    .iti__search-input {
        background: #111827 !important;
        color: #ffffff !important;
        border: 1px solid #4b5563 !important;
    }

    .iti__search-input::placeholder {
        color: #6b7280 !important;
    }

</style>


</head>

<body class="min-h-screen bg-gradient-to-br from-gray-950 via-indigo-950 to-purple-950 text-gray-100 antialiased px-5 py-8 md:px-10 md:py-12 relative overflow-x-hidden">

    <!-- Floating background icons -->
    <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-15">

        <i class="fa-solid fa-user-md absolute text-8xl text-cyan-500 animate-pulse-slow -left-16 top-20 rotate-12"></i>

        <i class="fa-solid fa-stethoscope absolute text-9xl text-blue-400 animate-float right-12 bottom-24 -rotate-6"></i>

        <i class="fa-solid fa-hospital-user absolute text-7xl text-indigo-400 animate-pulse-slow -right-20 top-1/3"></i>

    </div>


    <div class="relative z-10 max-w-lg mx-auto">

        <!-- Back link -->
        <a
            href="manage_doctors.php"
            class="inline-flex items-center gap-3 text-cyan-400 hover:text-cyan-300 font-medium mb-10 transition-all duration-200 group"
        >

            <i class="fas fa-arrow-left text-xl transform group-hover:-translate-x-1 transition-transform"></i>

            Back to Doctors List

        </a>


        <!-- Header -->
        <div class="text-center mb-12">

            <div class="mx-auto w-20 h-20 bg-gradient-to-br from-cyan-700 to-blue-800 rounded-full flex items-center justify-center mb-6 shadow-xl animate-float border-2 border-cyan-400/40">

                <i class="fa-solid fa-user-md text-4xl text-white"></i>

            </div>

            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-lg">

                Add New Doctor

            </h1>

            <p class="mt-4 text-xl text-cyan-100/80">

                Register a new doctor to the system

            </p>

        </div>


        <!-- Username exists error -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'username_exists'): ?>

            <div class="mb-8 p-6 bg-red-950/60 border-l-4 border-red-500 rounded-2xl text-red-200 flex items-start gap-4 shadow-lg">

                <i class="fas fa-circle-exclamation text-3xl mt-1 flex-shrink-0"></i>

                <div class="text-base font-medium">

                    Username already exists. Please choose a different username.

                </div>

            </div>

        <?php endif; ?>


        <!-- Invalid phone error -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_phone'): ?>

            <div class="mb-8 p-6 bg-red-950/60 border-l-4 border-red-500 rounded-2xl text-red-200 flex items-start gap-4 shadow-lg">

                <i class="fas fa-circle-exclamation text-3xl mt-1 flex-shrink-0"></i>

                <div class="text-base font-medium">

                    Please enter a valid contact number.

                </div>

            </div>

        <?php endif; ?>


        <!-- Invalid input error -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'invalid_input'): ?>

            <div class="mb-8 p-6 bg-red-950/60 border-l-4 border-red-500 rounded-2xl text-red-200 flex items-start gap-4 shadow-lg">

                <i class="fas fa-circle-exclamation text-3xl mt-1 flex-shrink-0"></i>

                <div class="text-base font-medium">

                    Please fill in all required fields correctly.

                </div>

            </div>

        <?php endif; ?>


        <!-- Form Card -->
        <div class="glass rounded-3xl shadow-2xl p-8 md:p-10 border border-gray-700/50">

            <form
                id="doctor-form"
                method="POST"
                action="../controllers/AddDoctorController.php"
                class="space-y-8"
            >

                <!-- Username -->
                <div>

                    <label
                        for="username"
                        class="block text-lg font-semibold text-gray-200 mb-3"
                    >

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
                            value="<?= $username ?>"
                            required
                            minlength="3"
                            maxlength="50"
                            autocomplete="username"
                            placeholder="Enter unique username"
                            class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                        >

                    </div>

                </div>


                <!-- Password -->
                <div>

                    <label
                        for="password"
                        class="block text-lg font-semibold text-gray-200 mb-3"
                    >

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
                            minlength="6"
                            maxlength="255"
                            autocomplete="new-password"
                            placeholder="Enter secure password"
                            class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                        >

                    </div>

                </div>


                <!-- Specialization -->
                <div>

                    <label
                        for="specialization"
                        class="block text-lg font-semibold text-gray-200 mb-3"
                    >

                        Specialization <span class="text-red-400">*</span>

                    </label>


                    <div class="relative group">

                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors">

                            <i class="fas fa-stethoscope text-xl"></i>

                        </div>


                        <input
                            type="text"
                            id="specialization"
                            name="specialization"
                            value="<?= $specialization ?>"
                            required
                            maxlength="100"
                            autocomplete="off"
                            placeholder="e.g. Cardiology, Pediatrics, Neurology, Orthopedics..."
                            class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                        >

                    </div>

                </div>


                <!-- Contact Number -->
    
<div>

    <label
        for="contact_number"
        class="block text-lg font-semibold text-gray-200 mb-3"
    >
        Contact Number
        <span class="text-red-400">*</span>
    </label>


    <div class="relative">

        <input
            type="tel"
            id="contact_number"
            name="contact_number"
            value="<?= $contact_number ?>"
            required
            autocomplete="tel"
            placeholder="98765 43210"
            class="w-full py-4 pr-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
        >

    </div>


    <p class="mt-2 text-sm text-gray-400">
        Select the country and enter the contact number.
    </p>


    <p
        id="phone-error"
        class="hidden mt-2 text-sm text-red-400"
        role="alert"
    ></p>

</div>



                <!-- Submit Button -->
                <button
                    type="submit"
                    id="submit-button"
                    class="group relative w-full flex items-center justify-center gap-4 py-5 px-8 bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white font-semibold text-xl rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1.5 focus:outline-none focus:ring-4 focus:ring-cyan-500/30 transition-all duration-300 mt-6 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none"
                >

                    <i class="fas fa-user-md text-2xl"></i>

                    <span id="submit-text">
                        Add Doctor
                    </span>

                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>

                </button>

            </form>

        </div>

    </div>


    <!-- intl-tel-input JS -->
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@29.2.3/dist/js/intlTelInput.min.js"></script>


    <script>

    document.addEventListener("DOMContentLoaded", function () {

        const phoneInput = document.getElementById("contact_number");
        const form = document.getElementById("doctor-form");
        const phoneError = document.getElementById("phone-error");
        const submitButton = document.getElementById("submit-button");
        const submitText = document.getElementById("submit-text");


        if (!phoneInput || !form) {
            return;
        }


        const iti = window.intlTelInput(phoneInput, {

            initialCountry: "in",

            separateDialCode: true,

            formatAsYouType: true,

            countrySearch: true,

            loadUtils: () =>
                import(
                    "https://cdn.jsdelivr.net/npm/intl-tel-input@29.2.3/dist/js/utils.js"
                )

        });


        function clearPhoneError() {

            phoneError.textContent = "";

            phoneError.classList.add("hidden");

            phoneInput.classList.remove("border-red-500");

        }


        function showPhoneError(message) {

            phoneError.textContent = message;

            phoneError.classList.remove("hidden");

            phoneInput.classList.add("border-red-500");

        }


        /*
        |--------------------------------------------------------------------------
        | Restore previous phone number
        |--------------------------------------------------------------------------
        */

        if (phoneInput.value.trim() !== "") {

            try {

                iti.setNumber(phoneInput.value.trim());

            } catch (error) {

                console.log(
                    "Phone restore error:",
                    error
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | Submit
        |--------------------------------------------------------------------------
        */

        form.addEventListener(
            "submit",
            async function (event) {

                event.preventDefault();

                clearPhoneError();


                if (!form.checkValidity()) {

                    form.reportValidity();

                    return;

                }


                submitButton.disabled = true;

                if (submitText) {
                    submitText.textContent = "Validating...";
                }


                try {

                    await iti.promise;


                    /*
                    |--------------------------------------------------------------------------
                    | Validate phone
                    |--------------------------------------------------------------------------
                    */

                    if (!iti.isValidNumber()) {

                        showPhoneError(
                            "Please enter a valid contact number."
                        );

                        phoneInput.focus();

                        submitButton.disabled = false;

                        if (submitText) {
                            submitText.textContent = "Add Doctor";
                        }

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Get full international number
                    |--------------------------------------------------------------------------
                    */

                    const fullNumber = iti.getNumber();


                    if (!fullNumber) {

                        showPhoneError(
                            "Unable to read the contact number."
                        );

                        phoneInput.focus();

                        submitButton.disabled = false;

                        if (submitText) {
                            submitText.textContent = "Add Doctor";
                        }

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Send E.164 number to PHP
                    |--------------------------------------------------------------------------
                    */

                    phoneInput.value = fullNumber;


                    if (submitText) {
                        submitText.textContent = "Adding Doctor...";
                    }


                    form.submit();

                } catch (error) {

                    console.error(
                        "Phone validation error:",
                        error
                    );


                    showPhoneError(
                        "Unable to validate the contact number. Please try again."
                    );


                    submitButton.disabled = false;

                    if (submitText) {
                        submitText.textContent = "Add Doctor";
                    }

                }

            }
        );


        phoneInput.addEventListener(
            "input",
            function () {
                clearPhoneError();
            }
        );


        phoneInput.addEventListener(
            "countrychange",
            function () {
                clearPhoneError();
            }
        );

    });

</script>


</body>

</html>
