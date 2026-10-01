<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../tcpdf/tcpdf.php';

$database = new Database();
$conn = $database->connect();

/* ======================
   FILTERS
====================== */
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$from   = $_GET['from'] ?? '';
$to     = $_GET['to'] ?? '';

$sql = "
SELECT 
    b.bill_id,
    p.full_name,
    b.total_amount,
    b.status,
    b.created_at
FROM billing b
JOIN patient p ON b.patient_id = p.patient_id
WHERE 1=1
";

$params = [];

/* SEARCH */
if (!empty($search)) {
    $sql .= " AND (
        p.full_name LIKE :search1
        OR b.bill_id LIKE :search2
    )";

    $searchTerm = "%$search%";
    $params['search1'] = $searchTerm;
    $params['search2'] = $searchTerm;
}

/* STATUS */
if (!empty($status)) {
    $sql .= " AND b.status = :status";
    $params['status'] = $status;
}

/* DATE */
if (!empty($from)) {
    $sql .= " AND DATE(b.created_at) >= :from";
    $params['from'] = $from;
}

if (!empty($to)) {
    $sql .= " AND DATE(b.created_at) <= :to";
    $params['to'] = $to;
}

$sql .= " ORDER BY b.bill_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ======================
   PDF GENERATION
====================== */

$pdf = new TCPDF();
$pdf->SetCreator('HMS');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Billing Report');
$pdf->AddPage();

$html = "
<h2>Billing Report</h2>
<table border='1' cellpadding='5'>
<tr>
<th><b>Bill ID</b></th>
<th><b>Patient</b></th>
<th><b>Total</b></th>
<th><b>Status</b></th>
<th><b>Date</b></th>
</tr>
";

/* DATA */
foreach ($data as $row) {
    $html .= "
    <tr>
        <td>{$row['bill_id']}</td>
        <td>{$row['full_name']}</td>
        <td>{$row['total_amount']}</td>
        <td>{$row['status']}</td>
        <td>{$row['created_at']}</td>
    </tr>
    ";
}

$html .= "</table>";

$pdf->writeHTML($html);
$pdf->Output("bills.pdf", "I");
exit;