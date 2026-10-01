<?php
// Your original PHP code remains EXACTLY the same
session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

/* ========================= */
/* ROLE SECURITY CHECK       */
/* ========================= */
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'doctor') {
    header("Location: login.php");
    exit();
}

/* ========================= */
/* VALIDATE APPOINTMENT ID   */
/* ========================= */
if (!isset($_GET['appointment_id']) || !is_numeric($_GET['appointment_id'])) {
    die("Invalid request.");
}

$appointment_id = (int) $_GET['appointment_id'];
$database = new Database();
$conn = $database->connect();

/* ========================= */
/* GET DOCTOR ID             */
/* ========================= */
if (isset($_SESSION['doctor_id'])) {
    $doctor_id = $_SESSION['doctor_id'];
} else {
    $stmt = $conn->prepare("SELECT doctor_id FROM doctor WHERE user_id = :user_id LIMIT 1");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$doctor) {
        die("Doctor record not found.");
    }
    $doctor_id = $doctor['doctor_id'];
    $_SESSION['doctor_id'] = $doctor_id;
}

/* ========================= */
/* CHECK APPOINTMENT         */
/* ========================= */
$stmt = $conn->prepare("
    SELECT
        a.appointment_id,
        a.patient_id,
        a.appointment_date,
        a.appointment_time,
        a.status,
        u.username AS patient_name
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN users u ON p.user_id = u.user_id
    WHERE a.appointment_id = :id
    AND a.doctor_id = :doctor_id
    AND a.status = 'Scheduled'
    LIMIT 1
");
$stmt->execute([
    ':id' => $appointment_id,
    ':doctor_id' => $doctor_id
]);
$appointment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$appointment) {
    die("Unauthorized or invalid appointment.");
}

/* ========================= */
/* CHECK IF VISIT EXISTS     */
/* ========================= */
$stmt = $conn->prepare("SELECT visit_id FROM visit WHERE appointment_id = :id LIMIT 1");
$stmt->execute([':id' => $appointment_id]);
if ($stmt->fetch()) {
    die("Visit already created for this appointment.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Visit - <?= htmlspecialchars($appointment['patient_name']) ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 30px 15px;
            color: #333;
        }
        
        .main-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .card-header {
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            color: white;
            padding: 25px;
            border-bottom: none;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 14px 16px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.3rem rgba(59, 130, 246, 0.25);
            outline: none;
        }
        
        .form-label {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 10px;
        }
        
        .btn-complete {
            background: linear-gradient(90deg, #10b981, #059669);
            border: none;
            padding: 14px 40px;
            font-size: 1.15rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            transition: all 0.3s ease;
        }
        
        .btn-complete:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(16, 185, 129, 0.5);
        }
        
        .patient-box {
            background: linear-gradient(90deg, #f0f9ff, #e0f2fe);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 6px solid #3b82f6;
        }
        
        textarea {
            min-height: 130px;
            resize: vertical;
        }
        
        .icon {
            font-size: 1.4rem;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-md-11">
            
            <a href="javascript:history.back()" class="text-white text-decoration-none mb-4 d-inline-flex align-items-center">
                <i class="bi bi-arrow-left fs-5 me-2"></i> Back to Appointments
            </a>

            <div class="main-card">
                <!-- Header -->
                <div class="card-header text-center">
                    <i class="bi bi-clipboard2-pulse fs-1 mb-3 d-block"></i>
                    <h3 class="mb-1">Create Medical Visit</h3>
                    <p class="mb-0 opacity-90">Appointment #<?= $appointment_id ?></p>
                </div>

                <div class="card-body p-5">

                    <!-- Patient Information -->
                    <div class="patient-box">
                        <div class="row text-center text-md-start">
                            <div class="col-md-5">
                                <strong>Patient:</strong><br>
                                <h5 class="mb-0"><?= htmlspecialchars($appointment['patient_name']) ?></h5>
                            </div>
                            <div class="col-md-3">
                                <strong>Date:</strong><br>
                                <?= date('d M Y', strtotime($appointment['appointment_date'])) ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Time:</strong><br>
                                <?= htmlspecialchars($appointment['appointment_time']) ?>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="../controllers/CreateVisitController.php">
                        <input type="hidden" name="appointment_id" value="<?= $appointment_id ?>">

                        <!-- Diagnosis -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-clipboard-check icon text-primary"></i>
                                Diagnosis <span class="text-danger">*</span>
                            </label>
                            <textarea name="diagnosis" class="form-control" rows="4" 
                                placeholder="Enter diagnosis and important clinical findings..." required></textarea>
                        </div>

                        <!-- Prescription -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-prescription2 icon text-success"></i>
                                Prescription
                            </label>
                            <textarea name="prescription" class="form-control" rows="5"
                                placeholder="Medications, dosage, frequency, and duration..."></textarea>
                        </div>

                        <!-- Notes -->
                        <div class="mb-5">
                            <label class="form-label">
                                <i class="bi bi-journal-text icon text-info"></i>
                                Additional Notes / Follow-up Advice
                            </label>
                            <textarea name="notes" class="form-control" rows="4"
                                placeholder="Observations, lifestyle recommendations, next visit instructions..."></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-complete text-white">
                                <i class="bi bi-check2-circle me-2"></i>
                                Complete Visit & Save Record
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="card-footer bg-light py-4 text-center border-0">
                    <small class="text-muted">
                        <i class="bi bi-shield-lock"></i> All data is encrypted and securely saved in patient medical records
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>