<?php
session_start();
require_once "../config/database.php";

$database = new Database();
$conn = $database->connect();

/* FETCH PATIENTS */
$patients = $conn->query("SELECT patient_id, full_name FROM patient WHERE is_active = 1")
                 ->fetchAll(PDO::FETCH_ASSOC);

/* HANDLE FORM */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['patient_id'])) {
        die("Patient not selected");
    }

    $patient_id = $_POST['patient_id'];
    $items      = $_POST['item_name'] ?? [];
    $qtys       = $_POST['quantity'] ?? [];
    $prices     = $_POST['price'] ?? [];

    // 1. insert billing
    $stmt = $conn->prepare("INSERT INTO billing (patient_id) VALUES (?)");
    $stmt->execute([$patient_id]);
    $bill_id = $conn->lastInsertId();

    $total_amount = 0;

    // 2. insert items
    for ($i = 0; $i < count($items); $i++) {
        if (empty($items[$i]) || empty($qtys[$i])) continue;

        $qty   = (int)$qtys[$i];
        $price = (float)$prices[$i];
        $total = $qty * $price;
        $total_amount += $total;

        $stmt = $conn->prepare("
            INSERT INTO billing_items
            (bill_id, item_name, quantity, price, total)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$bill_id, $items[$i], $qty, $price, $total]);
    }

    // 3. update total
    $stmt = $conn->prepare("UPDATE billing SET total_amount = ? WHERE bill_id = ?");
    $stmt->execute([$total_amount, $bill_id]);

    header("Location: view_bill.php?id=" . $bill_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en" class="bg-gray-950 text-gray-100">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Create New Bill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',     // indigo-500
                        primaryDark: '#4f46e5', // indigo-600
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-b from-gray-950 via-gray-900 to-gray-950">

    <div class="container mx-auto px-4 py-10 max-w-5xl">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white tracking-tight">
                    Create New Bill
                </h1>
                <p class="text-gray-400 mt-1">Enter patient details and bill items</p>
            </div>
            <a href="admin_dashboard.php" class="text-gray-400 hover:text-white transition">
                <i class="fas fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        <form method="POST" class="bg-gray-800/60 backdrop-blur-sm border border-gray-700 rounded-2xl shadow-2xl shadow-indigo-950/30 p-6 md:p-8">

            <!-- Patient Selection -->
            <div class="mb-8">
                <label class="block text-lg font-semibold mb-3 text-gray-200">
                    Select Patient <span class="text-red-400">*</span>
                </label>
                <select name="patient_id" required
                        class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white
                               focus:ring-2 focus:ring-primary focus:border-primary outline-none transition">
                    <option value="">— Choose Patient —</option>
                    <?php foreach($patients as $p): ?>
                        <option value="<?= $p['patient_id'] ?>">
                            <?= htmlspecialchars($p['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Items Table -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-100">Bill Items</h2>
                    <button type="button" onclick="addRow()"
                            class="bg-primary hover:bg-primaryDark text-white px-5 py-2.5 rounded-lg font-medium
                                   transition shadow-md flex items-center gap-2">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table id="itemsTable" class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-gray-300 bg-gray-900/70">
                                <th class="px-4 py-3 rounded-l-lg font-medium">Item Description</th>
                                <th class="px-4 py-3 font-medium">Quantity</th>
                                <th class="px-4 py-3 font-medium">Unit Price (₹)</th>
                                <th class="px-4 py-3 font-medium text-right">Total</th>
                                <th class="px-4 py-3 rounded-r-lg w-12"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Initial row -->
                            <tr class="bg-gray-800/40 hover:bg-gray-700/40 transition">
                                <td class="px-4 py-3"><input name="item_name[]" required placeholder="e.g. Consultation" class="w-full bg-transparent border-0 focus:outline-none text-white"></td>
                                <td class="px-4 py-3"><input name="quantity[]" type="number" min="1" value="1" required class="w-20 bg-transparent border-0 focus:outline-none text-white text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"></td>
                                <td class="px-4 py-3"><input name="price[]" type="number" step="0.01" min="0" required placeholder="0.00" class="w-28 bg-transparent border-0 focus:outline-none text-white text-right" oninput="calculateTotals()"></td>
                                <td class="px-4 py-3 text-right font-medium text-indigo-300" data-total>0.00</td>
                                <td class="px-4 py-3 text-center"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Grand Total -->
                <div class="mt-6 flex justify-end items-center gap-6 bg-gray-900/70 px-6 py-5 rounded-xl border border-gray-700">
                    <span class="text-xl font-semibold text-gray-200">Grand Total:</span>
                    <span id="grandTotal" class="text-3xl font-bold text-indigo-400">₹ 0.00</span>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400
                               text-white px-10 py-4 rounded-xl font-semibold text-lg shadow-lg shadow-indigo-900/40
                               transition transform hover:scale-[1.02] flex items-center gap-3">
                    <i class="fas fa-file-invoice-dollar text-xl"></i>
                    Create & Save Bill
                </button>
            </div>

        </form>
    </div>

    <script>
        let rowIndex = 1;

        function addRow() {
            const tbody = document.getElementById('itemsBody');
            const row = document.createElement('tr');
            row.className = 'bg-gray-800/40 hover:bg-gray-700/40 transition';
            row.innerHTML = `
                <td class="px-4 py-3"><input name="item_name[]" required placeholder="e.g. Blood Test" class="w-full bg-transparent border-0 focus:outline-none text-white"></td>
                <td class="px-4 py-3"><input name="quantity[]" type="number" min="1" value="1" required class="w-20 bg-transparent border-0 focus:outline-none text-white text-center [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" oninput="calculateTotals()"></td>
                <td class="px-4 py-3"><input name="price[]" type="number" step="0.01" min="0" required placeholder="0.00" class="w-28 bg-transparent border-0 focus:outline-none text-white text-right" oninput="calculateTotals()"></td>
                <td class="px-4 py-3 text-right font-medium text-indigo-300" data-total>0.00</td>
                <td class="px-4 py-3 text-center">
                    <button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-300 transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
            rowIndex++;
            calculateTotals();
        }

        function removeRow(btn) {
            if (document.querySelectorAll('#itemsBody tr').length <= 1) {
                alert("At least one item is required.");
                return;
            }
            btn.closest('tr').remove();
            calculateTotals();
        }

        function calculateTotals() {
            let grand = 0;
            document.querySelectorAll('#itemsBody tr').forEach(row => {
                const qty   = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
                const price = parseFloat(row.querySelector('input[name="price[]"]').value)    || 0;
                const total = qty * price;
                row.querySelector('[data-total]').textContent = total.toFixed(2);
                grand += total;
            });
            document.getElementById('grandTotal').textContent = '₹ ' + grand.toFixed(2);
        }

        // Initial calculation
        document.addEventListener('DOMContentLoaded', calculateTotals);
    </script>

</body>
</html>