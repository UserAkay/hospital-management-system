<?php
session_start();

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: login.php");
    exit();
}

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

/* ======================
   SEARCH (FIXED)
====================== */
if (!empty($search)) {
    $sql .= " AND (
        p.full_name LIKE :search1
        OR b.bill_id LIKE :search2
    )";

    $searchTerm = "%$search%";
    $params['search1'] = $searchTerm;
    $params['search2'] = $searchTerm;
}

/* ======================
   STATUS
====================== */
if (!empty($status)) {
    $sql .= " AND b.status = :status";
    $params['status'] = $status;
}

/* ======================
   DATE RANGE
====================== */
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
$bills = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ======================
   REVENUE STATS
====================== */

$totalRevenue = $conn->query("SELECT SUM(total_amount) FROM billing")->fetchColumn();
$paidRevenue = $conn->query("SELECT SUM(total_amount) FROM billing WHERE status='Paid'")->fetchColumn();
$pendingRevenue = $conn->query("SELECT SUM(total_amount) FROM billing WHERE status='Pending'")->fetchColumn();

?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Bills</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-900 text-white p-6">

<div class="max-w-7xl mx-auto">

<h1 class="text-3xl font-bold mb-6">Manage Bills</h1>

<!-- ======================
     REVENUE STATS
====================== -->

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

<div class="bg-green-700 p-4 rounded">
<h3>Total Revenue</h3>
<p class="text-2xl font-bold">₹<?= number_format($totalRevenue ?? 0,2) ?></p>
</div>

<div class="bg-blue-700 p-4 rounded">
<h3>Paid</h3>
<p class="text-2xl font-bold">₹<?= number_format($paidRevenue ?? 0,2) ?></p>
</div>

<div class="bg-red-700 p-4 rounded">
<h3>Pending</h3>
<p class="text-2xl font-bold">₹<?= number_format($pendingRevenue ?? 0,2) ?></p>
</div>

</div>

<!-- ======================
     FILTER
====================== -->

<form method="GET" class="flex flex-wrap gap-3 mb-6">

<input type="text" name="search"
value="<?= htmlspecialchars($search) ?>"
placeholder="Search patient or bill ID"
class="p-2 rounded bg-gray-800">

<select name="status" class="p-2 rounded bg-gray-800">
<option value="">All Status</option>
<option value="Pending" <?= $status=='Pending'?'selected':'' ?>>Pending</option>
<option value="Paid" <?= $status=='Paid'?'selected':'' ?>>Paid</option>
</select>

<input type="date" name="from" value="<?= $from ?>" class="p-2 bg-gray-800 rounded">
<input type="date" name="to" value="<?= $to ?>" class="p-2 bg-gray-800 rounded">

<button class="bg-blue-600 px-4 py-2 rounded">Filter</button>

<a href="manage_bills.php" class="bg-gray-700 px-4 py-2 rounded">Reset</a>

</form>

<!-- ======================
     EXPORT BUTTONS (FIXED)
====================== -->

<div class="mb-4 flex gap-3">

<a href="../exports/export_bills_csv.php?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&from=<?= $from ?>&to=<?= $to ?>"
class="bg-green-600 px-4 py-2 rounded">
Export CSV
</a>

<a href="../exports/export_bills_pdf.php?search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>&from=<?= $from ?>&to=<?= $to ?>"
class="bg-red-600 px-4 py-2 rounded">
Export PDF
</a>

</div>

<!-- ======================
     TABLE
====================== -->

<?php if(count($bills) > 0): ?>

<table class="w-full border border-gray-700">

<thead class="bg-gray-800">
<tr>
<th class="p-3 border">Bill ID</th>
<th class="p-3 border">Patient</th>
<th class="p-3 border">Total</th>
<th class="p-3 border">Status</th>
<th class="p-3 border">Date</th>
<th class="p-3 border">Action</th>
</tr>
</thead>

<tbody>

<?php foreach($bills as $b): ?>

<tr class="border-b border-gray-700">

<td class="p-3">#<?= $b['bill_id'] ?></td>
<td class="p-3"><?= htmlspecialchars($b['full_name']) ?></td>
<td class="p-3">₹<?= number_format($b['total_amount'],2) ?></td>

<td class="p-3">
<span class="<?= $b['status']=='Paid' ? 'text-green-400' : 'text-red-400' ?>">
<?= $b['status'] ?>
</span>
</td>

<td class="p-3"><?= date("d M Y", strtotime($b['created_at'])) ?></td>

<td class="p-3">
<a href="view_bill.php?id=<?= $b['bill_id'] ?>"
class="bg-blue-600 px-3 py-1 rounded">
View
</a>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php else: ?>

<p>No bills found</p>

<?php endif; ?>

<br>

<a href="admin_dashboard.php" class="bg-gray-700 px-4 py-2 rounded">
Back to Dashboard
</a>

</div>

</body>
</html>