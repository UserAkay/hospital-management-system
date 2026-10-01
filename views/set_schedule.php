<?php
session_start();
require_once "../config/session_check.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

require_once "../config/database.php";
$database = new Database();
$conn = $database->connect();

if (!isset($_GET['doctor_id'])) {
    die("Doctor ID is missing.");
}

$doctor_id = (int)$_GET['doctor_id'];

// Optional: You can fetch doctor's name here to show in the header
$doctor_name = "Dr. Unknown"; // ← replace with real query if you want
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Set Doctor Schedule</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet"/>
    
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3f37c9;
            --light: #f8f9fa;
            --gray: #6c757d;
            --success: #38d39f;
            --danger: #e63946;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f4ff 0%, #e6e9ff 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
            color: #333;
        }

        .container {
            max-width: 520px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(67, 97, 238, 0.12);
            overflow: hidden;
        }

        .header {
            background: var(--primary);
            color: white;
            padding: 1.8rem 2rem;
            text-align: center;
        }

        .header h2 {
            font-weight: 600;
            margin: 0;
            font-size: 1.6rem;
        }

        .header p {
            margin-top: 0.4rem;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .form-content {
            padding: 2.2rem 2rem;
        }

        .form-group {
            margin-bottom: 1.6rem;
        }

        label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: #444;
        }

        .days-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 0.9rem;
            margin-top: 0.5rem;
        }

        .day-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .day-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        input[type="time"],
        input[type="number"] {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 1.5px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.2s;
        }

        input[type="time"]:focus,
        input[type="number"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }

        .time-row {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .time-row > div {
            flex: 1;
            min-width: 160px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.05rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.25s;
            margin-top: 1.8rem;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
        }

        .btn:active {
            transform: translateY(0);
        }

        @media (max-width: 480px) {
            .form-content {
                padding: 1.8rem 1.4rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Set Doctor Schedule</h2>
        <p>Configure availability for <?= htmlspecialchars($doctor_name) ?></p>
    </div>

    <div class="form-content">
        <form method="POST" action="../controllers/SetScheduleController.php">
            <input type="hidden" name="doctor_id" value="<?= $doctor_id ?>">

            <div class="form-group">
                <label>Select Working Days:</label>
                <div class="days-grid">
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="Monday"> Monday
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="Tuesday"> Tuesday
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="Wednesday"> Wednesday
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="Thursday"> Thursday
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="Friday"> Friday
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="Saturday"> Saturday
                    </label>
                    <label class="day-checkbox">
                        <input type="checkbox" name="days[]" value="Sunday"> Sunday
                    </label>
                </div>
            </div>

            <div class="form-group time-row">
                <div>
                    <label for="start_time">Start Time</label>
                    <input type="time" id="start_time" name="start_time" required>
                </div>
                <div>
                    <label for="end_time">End Time</label>
                    <input type="time" id="end_time" name="end_time" required>
                </div>
            </div>

            <div class="form-group">
                <label for="slot_duration">Appointment Slot Duration (minutes)</label>
                <input type="number" id="slot_duration" name="slot_duration" 
                       value="30" min="5" max="120" step="5" required>
            </div>

            <button type="submit" class="btn">Save Schedule</button>
        </form>
    </div>
</div>

</body>
</html>