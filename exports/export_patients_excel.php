<?php
require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

/* FILTERS */
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';

$sql = "SELECT 
            b.bill_id,
            p.full_name,
            b.total_amount,
            b.status,
            b.created_at
        FROM billing b
        JOIN patient p ON b.patient_id = p.patient_id
        WHERE 1=1";

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

/* DATE RANGE */
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

/* EXCEL HEADERS */
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=bills.xls");
header("Pragma: no-cache");
header("Expires: 0");

/* OUTPUT */
$output = fopen("php://output", "w");

/* HEADER */
fputcsv($output, ['Bill ID','Patient','Total','Status','Date'], "\t");

/* DATA */
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row, "\t");
}

fclose($output);
exit;