<?php
require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bill_id = $_POST['bill_id'];

    $stmt = $conn->prepare("UPDATE billing SET status='Paid' WHERE bill_id=?");
    $stmt->execute([$bill_id]);

    header("Location: ../views/view_bill.php?id=".$bill_id);
}