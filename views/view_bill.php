
<?php
session_start();
require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

if (!isset($_GET['id'])) {
    die("Bill ID not provided");
}

$bill_id = (int)$_GET['id'];


/* FETCH BILL */ 
$stmt = $conn->prepare("     
    SELECT b.*, p.full_name     
    FROM billing b     
    JOIN patient p ON b.patient_id = p.patient_id     
    WHERE b.bill_id = ? "); 
$stmt->execute([$bill_id]); 
$bill = $stmt->fetch(PDO::FETCH_ASSOC); 

if (!$bill) {     
    die("Bill not found"); 
} 

/* FETCH ITEMS */ 
$stmt = $conn->prepare("     
    SELECT item_name, quantity, price, total     
    FROM billing_items     
    WHERE bill_id = ? "); 
$stmt->execute([$bill_id]); 
$items = $stmt->fetchAll(PDO::FETCH_ASSOC); 

/* CALCULATIONS */ 
$subtotal    = (float)$bill['total_amount']; 
$gst_rate    = 0.18; 
$gst         = $subtotal * $gst_rate; 
$grand_total = $subtotal + $gst; 
$created_date = $bill['created_at'] ?? date('Y-m-d'); 
// =========================================================================
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $bill_id ?> - City Hospital</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            padding: 40px 15px;
        }
        
        .invoice-card {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }
        
        .invoice-header {
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            color: white;
            padding: 30px 40px;
        }
        
        .hospital-name {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -1px;
        }
        
        .invoice-title {
            font-size: 2.8rem;
            font-weight: 800;
            letter-spacing: -2px;
            opacity: 0.95;
        }
        
        .section-title {
            color: #1e40af;
            font-weight: 600;
            border-bottom: 3px solid #bfdbfe;
            padding-bottom: 8px;
        }
        
        table {
            border-radius: 12px;
            overflow: hidden;
        }
        
        th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
            padding: 16px 20px !important;
        }
        
        td {
            padding: 16px 20px !important;
            vertical-align: middle;
        }
        
        .amount {
            font-weight: 600;
            color: #1e40af;
        }
        
        .total-row {
            background: #f8fafc;
            font-size: 1.15rem;
        }
        
        .grand-total {
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
        }
        
        .btn-action {
            border-radius: 50px;
            padding: 10px 28px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-print {
            background: #64748b;
            color: white;
        }
        
        .btn-print:hover {
            background: #475569;
            transform: translateY(-2px);
        }
        
        .status-paid {
            background: #10b981;
            color: white;
            padding: 6px 18px;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="invoice-card">
    <!-- Header -->
    <div class="invoice-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="hospital-name mb-0">City Hospital</h1>
                <p class="mb-0 mt-2">Patna, Bihar • +91-XXXXXXXXXX<br>
                GSTIN: 10XXXXXXXXXXXX</p>
            </div>
            <div class="col-md-6 text-md-end">
                <h2 class="invoice-title mb-0">INVOICE</h2>
                <h4 class="mb-0">#<?= str_pad($bill_id, 6, '0', STR_PAD_LEFT) ?></h4>
            </div>
        </div>
    </div>

    <div class="p-5">
        <!-- Bill To & Info -->
        <div class="row mb-5">
            <div class="col-md-6">
                <h5 class="section-title">Bill To</h5>
                <h4 class="mt-3"><?= htmlspecialchars($bill['full_name']) ?></h4>
                <p class="text-muted mb-1">Patient ID: <?= htmlspecialchars($bill['patient_id']) ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="mb-3">
                    <strong>Issue Date:</strong> 
                    <?= date('d M Y', strtotime($created_date)) ?>
                </div>
                <div>
                    <strong>Status:</strong> 
                    <?php if ($bill['status'] == 'Paid'): ?>
                        <span class="status-paid">Paid</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Pending</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($bill['paid_at'])): ?>
                <div class="mt-2">
                    <strong>Paid On:</strong> 
                    <?= date('d M Y', strtotime($bill['paid_at'])) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Items Table -->
        <table class="table table-bordered mb-5">
            <thead>
                <tr>
                    <th width="55%">Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td class="text-center"><?= $item['quantity'] ?></td>
                    <td class="text-end">₹<?= number_format($item['price'], 2) ?></td>
                    <td class="text-end amount">₹<?= number_format($item['total'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="row justify-content-end">
            <div class="col-md-5">
                <table class="table">
                    <tr class="total-row">
                        <td><strong>Subtotal</strong></td>
                        <td class="text-end">₹<?= number_format($subtotal, 2) ?></td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>GST (18%)</strong></td>
                        <td class="text-end">₹<?= number_format($gst, 2) ?></td>
                    </tr>
                    <tr class="grand-total">
                        <td><strong>Grand Total</strong></td>
                        <td class="text-end">₹<?= number_format($grand_total, 2) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap gap-3 justify-content-center mt-5">
            <a href="create_bill.php" class="btn btn-primary btn-action">
                <i class="bi bi-plus-circle"></i> Create New Bill
            </a>
            
            <?php if ($bill['status'] != 'Paid'): ?>
            <a href="../controllers/MarkBillPaid.php?id=<?= $bill_id ?>" 
               class="btn btn-success btn-action">
                <i class="bi bi-check-circle"></i> Mark as Paid
            </a>
            <?php endif; ?>
            
            <button onclick="window.print()" class="btn btn-print btn-action">
                <i class="bi bi-printer"></i> Print Invoice
            </button>
        </div>

        <!-- Thank You Message -->
        <div class="text-center mt-5 pt-4 border-top">
            <p class="text-muted fs-5">
                Thank you for choosing <strong>City Hospital</strong>.<br>
                Wishing you a speedy recovery!
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>