<?php

session_start();

require_once "../config/session_check.php";

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'Admin'
) {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| CHECK DOCTOR ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id'])) {

    header("Location: manage_doctors.php");
    exit();
}


$doctor_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

$database = new Database();
$conn = $database->connect();


/*
|--------------------------------------------------------------------------
| GET DOCTOR
|--------------------------------------------------------------------------
*/

$query = "
    SELECT
        d.doctor_id,
        d.specialization,
        d.contact_number,
        u.username
    FROM doctor d
    JOIN users u
        ON d.user_id = u.user_id
    WHERE d.doctor_id = :doctor_id
";


$stmt = $conn->prepare($query);

$stmt->bindValue(
    ":doctor_id",
    $doctor_id,
    PDO::PARAM_INT
);

$stmt->execute();

$doctor = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| DOCTOR NOT FOUND
|--------------------------------------------------------------------------
*/

if (!$doctor) {

    header("Location: manage_doctors.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| FORMAT PHONE NUMBER FOR FORM DISPLAY
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| Database value:
| +919876543210
|
| Form displays:
| +91 98765 43210
|
*/

$contactNumber = htmlspecialchars(
    $doctor['contact_number'] ?? '',
    ENT_QUOTES,
    'UTF-8'
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    />

    <title>Edit Doctor • HMS</title>


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />


    <!-- Tailwind -->

    <script src="https://cdn.tailwindcss.com"></script>

    <!-- International Telephone Input -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/intl-tel-input@29.2.3/dist/css/intlTelInput.css"
    />


    <script>

        tailwind.config = {

            theme: {

                extend: {

                    animation: {

                        'pulse-slow':
                            'pulse 5s cubic-bezier(0.4, 0, 0.6, 1) infinite',

                        'float':
                            'float 8s ease-in-out infinite',

                    },

                    keyframes: {

                        float: {

                            '0%, 100%': {
                                transform: 'translateY(0)'
                            },

                            '50%': {
                                transform: 'translateY(-16px)'
                            },

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


        /* intl-tel-input — match the working Add Doctor phone field */
        .iti {
            width: 100%;
            display: block;
        }

        .iti input[type="tel"] {
            width: 100% !important;
            padding-left: 100px !important;
        }

        .iti__country-container {
            z-index: 20;
        }

        .iti__selected-country {
            background: transparent !important;
            color: #ffffff !important;
            border-radius: 1rem 0 0 1rem;
        }

        .iti__selected-dial-code {
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 500;
            margin-left: 6px;
            display: inline-block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .iti__arrow {
            border-top-color: #9ca3af !important;
            margin-left: 6px;
        }

        .iti__arrow--up {
            border-bottom-color: #9ca3af !important;
            border-top-color: transparent !important;
        }

        .iti__dropdown-content {
            z-index: 99999 !important;
            background: #1f2937 !important;
            border: 1px solid #4b5563 !important;
            color: #ffffff !important;
        }

        .iti__country-list {
            background: #1f2937 !important;
            color: #ffffff !important;
        }

        .iti__country {
            color: #ffffff !important;
        }

        .iti__country:hover,
        .iti__country--highlight {
            background: #374151 !important;
        }

        .iti__country-name {
            color: #ffffff !important;
        }

        .iti__dial-code {
            color: #9ca3af !important;
        }

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


<body
    class="min-h-screen bg-gradient-to-br from-gray-950 via-indigo-950 to-purple-950 text-gray-100 antialiased px-5 py-8 md:px-10 md:py-12 relative overflow-x-hidden"
>


    <!-- Floating background icons -->

    <div
        class="absolute inset-0 pointer-events-none overflow-hidden opacity-15"
    >

        <i
            class="fa-solid fa-user-doctor absolute text-8xl text-cyan-500 animate-pulse-slow -left-16 top-20 rotate-12"
        ></i>

        <i
            class="fa-solid fa-stethoscope absolute text-9xl text-blue-400 animate-float right-12 bottom-24 -rotate-6"
        ></i>

        <i
            class="fa-solid fa-hospital-user absolute text-7xl text-indigo-400 animate-pulse-slow -right-20 top-1/3"
        ></i>

    </div>


    <div class="relative z-10 max-w-lg mx-auto">


        <!-- Back link -->

        <a
            href="manage_doctors.php"
            class="inline-flex items-center gap-3 text-cyan-400 hover:text-cyan-300 font-medium mb-10 transition-all duration-200 group"
        >

            <i
                class="fas fa-arrow-left text-xl transform group-hover:-translate-x-1 transition-transform"
            ></i>

            Back to Doctors List

        </a>


        <!-- Header -->

        <div class="text-center mb-12">

            <div
                class="mx-auto w-20 h-20 bg-gradient-to-br from-cyan-700 to-blue-800 rounded-full flex items-center justify-center mb-6 shadow-xl animate-float border-2 border-cyan-400/40"
            >

                <i
                    class="fa-solid fa-user-doctor text-4xl text-white"
                ></i>

            </div>


            <h1
                class="text-4xl md:text-5xl font-extrabold tracking-tight text-white drop-shadow-lg"
            >

                Edit Doctor

            </h1>


            <p class="mt-4 text-xl text-cyan-100/80">

                Update doctor profile information

            </p>

        </div>


        <!-- Form Card -->

        <div
            class="glass rounded-3xl shadow-2xl p-8 md:p-10 border border-gray-700/50"
        >

            <form
                id="doctor-form"
                method="POST"
                action="../controllers/UpdateDoctorController.php"
                class="space-y-8"
            >


                <!-- Doctor ID -->

                <input
                    type="hidden"
                    name="doctor_id"
                    value="<?= (int) $doctor['doctor_id'] ?>"
                >


                <!-- Username -->

                <div>

                    <label
                        for="username"
                        class="block text-lg font-semibold text-gray-200 mb-3"
                    >

                        Username
                        <span class="text-red-400">*</span>

                    </label>


                    <div class="relative group">

                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors"
                        >

                            <i class="fas fa-user text-xl"></i>

                        </div>


                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars(
                                $doctor['username'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                            class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                        />

                    </div>

                </div>


                <!-- Specialization -->

                <div>

                    <label
                        for="specialization"
                        class="block text-lg font-semibold text-gray-200 mb-3"
                    >

                        Specialization
                        <span class="text-red-400">*</span>

                    </label>


                    <div class="relative group">

                        <div
                            class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-cyan-400 transition-colors"
                        >

                            <i class="fas fa-stethoscope text-xl"></i>

                        </div>


                        <input
                            type="text"
                            id="specialization"
                            name="specialization"
                            value="<?= htmlspecialchars(
                                $doctor['specialization'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                            placeholder="e.g. Cardiology, Pediatrics, Neurology..."
                            class="w-full pl-12 pr-5 py-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                        />

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

                    <div class="relative group">

                        <input
                            type="tel"
                            id="contact_number"
                            name="contact_number"
                            value="<?= $contactNumber ?>"
                            required
                            autocomplete="tel"
                            inputmode="tel"
                            placeholder="98765 43210"
                            class="w-full py-4 pr-4 bg-gray-800/60 border border-gray-600 rounded-2xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 focus:shadow-xl transition-all duration-300 text-white text-base placeholder-gray-500"
                        />

                    </div>

                    <p
                        id="phone-error"
                        class="hidden mt-2 text-sm text-red-400"
                    ></p>

                </div>


                <!-- Action Buttons -->

                <div class="flex flex-col sm:flex-row gap-4 pt-6">


                    <!-- SAVE -->

                    <button
                        id="submit-button"
                        type="submit"
                        class="group flex-1 flex items-center justify-center gap-3 py-5 px-8 bg-gradient-to-r from-cyan-600 to-blue-700 hover:from-cyan-700 hover:to-blue-800 text-white font-semibold text-lg rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-1.5 focus:outline-none focus:ring-4 focus:ring-cyan-500/30 transition-all duration-300"
                    >

                        <i class="fas fa-save text-xl"></i>

                        <span id="submit-text">Save Changes</span>

                    </button>


                    <!-- CANCEL -->

                    <a
                        href="manage_doctors.php"
                        class="flex-1 flex items-center justify-center gap-3 py-5 px-8 bg-gray-700/60 hover:bg-gray-600/70 text-gray-300 hover:text-white font-semibold text-lg rounded-2xl border border-gray-600 transition-all duration-300"
                    >

                        <i class="fas fa-arrow-left text-xl"></i>

                        Cancel

                    </a>

                </div>

            </form>

        </div>

    </div>


    <!-- International Telephone Input JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/intl-tel-input@29.2.3/dist/js/intlTelInput.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const phoneInput = document.getElementById("contact_number");
            const form = document.getElementById("doctor-form");
            const phoneError = document.getElementById("phone-error");
            const submitButton = document.getElementById("submit-button");
            const submitText = document.getElementById("submit-text");

            if (!phoneInput || !form || !window.intlTelInput) {
                console.error("intl-tel-input could not be initialized.");
                return;
            }

            const iti = window.intlTelInput(phoneInput, {
                initialCountry: "in",
                separateDialCode: true,
                formatAsYouType: true,
                countrySearch: true,
                preferredCountries: ["in", "us", "gb", "ae", "au", "ca"],
                loadUtils: () =>
                    import(
                        "https://cdn.jsdelivr.net/npm/intl-tel-input@29.2.3/dist/js/utils.js"
                    )
            });

            function clearPhoneError() {
                if (phoneError) {
                    phoneError.textContent = "";
                    phoneError.classList.add("hidden");
                }
                phoneInput.classList.remove("border-red-500");
            }

            function showPhoneError(message) {
                if (phoneError) {
                    phoneError.textContent = message;
                    phoneError.classList.remove("hidden");
                }
                phoneInput.classList.add("border-red-500");
            }

            /*
             * Restore the existing database value.
             * Database example:
             * +919876543210
             *
             * intl-tel-input displays:
             * 🇮🇳 +91 | 98765 43210
             */
            if (phoneInput.value.trim() !== "") {
                try {
                    iti.setNumber(phoneInput.value.trim());
                } catch (error) {
                    console.error("Phone restore error:", error);
                }
            }

            form.addEventListener("submit", async function (event) {
                event.preventDefault();
                clearPhoneError();

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                if (submitButton) {
                    submitButton.disabled = true;
                }

                if (submitText) {
                    submitText.textContent = "Validating...";
                }

                try {
                    await iti.promise;

                    if (!iti.isValidNumber()) {
                        showPhoneError(
                            "Please enter a valid contact number."
                        );

                        phoneInput.focus();

                        if (submitButton) {
                            submitButton.disabled = false;
                        }

                        if (submitText) {
                            submitText.textContent = "Save Changes";
                        }

                        return;
                    }

                    const fullNumber = iti.getNumber();

                    if (!fullNumber) {
                        showPhoneError(
                            "Unable to read the contact number."
                        );

                        phoneInput.focus();

                        if (submitButton) {
                            submitButton.disabled = false;
                        }

                        if (submitText) {
                            submitText.textContent = "Save Changes";
                        }

                        return;
                    }

                    /*
                     * PHP receives the complete E.164 number.
                     * Example: +919876543210
                     */
                    phoneInput.value = fullNumber;

                    if (submitText) {
                        submitText.textContent = "Updating Doctor...";
                    }

                    form.submit();

                } catch (error) {
                    console.error("Phone validation error:", error);

                    showPhoneError(
                        "Unable to validate the contact number. Please try again."
                    );

                    if (submitButton) {
                        submitButton.disabled = false;
                    }

                    if (submitText) {
                        submitText.textContent = "Save Changes";
                    }
                }
            });
        });
    </script>
</body>

</html>