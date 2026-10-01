<?php
require_once "../config/database.php";

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

/* ======================
   CSV DOWNLOAD
====================== */
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="bills.csv"');

$output = fopen("php://output", "w");

/* HEADER */
fputcsv($output, [
    'Bill ID',
    'Patient Name',
    'Total Amount',
    'Status',
    'Date'
]);

/* DATA */
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['bill_id'],
        $row['full_name'],
        $row['total_amount'],
        $row['status'],
        $row['created_at']
    ]);
}

fclose($output);
exit;