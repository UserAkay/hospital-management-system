<?php
session_start();
require_once "../config/session_check.php";
require_once "../config/database.php";

if (strtolower($_SESSION['role']) !== 'admin') {
    die("Access denied.");
}

$database = new Database();
$conn = $database->connect();

$stmt = $conn->query("
    SELECT
        a.action,
        a.table_name,
        a.description,
        a.created_at,
        u.username
    FROM audit_log a
    JOIN users u ON a.user_id = u.user_id
    ORDER BY a.created_at DESC
    LIMIT 100
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Audit Logs • HMS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'admin-primary': '#7c3aed',   // violet-600
                        'admin-dark': '#4c1d95',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-950 via-gray-900 to-black text-gray-100">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- Header -->
        <header class="flex flex-col sm:flex-row justify-between items-center mb-10 gap-6">
            <div>
                <h1 class="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-violet-400 to-purple-500 bg-clip-text text-transparent">
                    System Audit Logs
                </h1>
                <p class="mt-2 text-gray-400">Last 100 actions • Real-time activity tracking</p>
            </div>
            <a href="admin_dashboard.php"
               class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 px-6 py-3 rounded-xl border border-gray-700 transition-all shadow-lg">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </header>

        <!-- Table Container -->
        <div class="bg-gray-800/60 backdrop-blur-sm border border-gray-700/70 rounded-2xl shadow-2xl overflow-hidden">

            <?php if (empty($logs)): ?>
                <div class="p-12 text-center">
                    <i class="fas fa-history text-6xl text-gray-600 mb-6"></i>
                    <h3 class="text-xl font-semibold text-gray-300 mb-2">No audit logs found</h3>
                    <p class="text-gray-500">System actions will appear here once activity occurs.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700">
                        <thead class="bg-gray-900/70">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                    Action
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">
                                    Table
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">
                                    Date / Time
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <?php foreach ($logs as $log): 
                                $actionClass = match(strtolower($log['action'])) {
                                    'insert' => 'bg-green-500/20 text-green-300 border-green-500/30',
                                    'update' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
                                    'delete' => 'bg-red-500/20 text-red-300 border-red-500/30',
                                    'login'  => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
                                    default  => 'bg-gray-500/20 text-gray-300 border-gray-500/30',
                                };
                            ?>
                                <tr class="hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-violet-900/50 flex items-center justify-center text-violet-300 text-sm font-medium">
                                                <?= strtoupper(substr($log['username'], 0, 1)) ?>
                                            </div>
                                            <span class="font-medium text-gray-200">
                                                <?= htmlspecialchars($log['username']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium border <?= $actionClass ?>">
                                            <?= htmlspecialchars($log['action']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-300 hidden md:table-cell">
                                        <?= htmlspecialchars($log['table_name'] ?: '—') ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-300 max-w-xl truncate">
                                        <?= htmlspecialchars($log['description']) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-400 text-sm hidden lg:table-cell">
                                        <?= date('d M Y • H:i', strtotime($log['created_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-900/50 text-center text-sm text-gray-500 border-t border-gray-700">
                    Showing last 100 records • Sorted by most recent first
                </div>
            <?php endif; ?>

        </div>

    </div>

</body>
</html>