<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_ADMIN]);

// Get current user
$user = getCurrentUser();

// Handle export request
if ($_POST && isset($_POST['export'])) {
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? '';
    
    if ($startDate && $endDate) {
        // Validate dates
        if (strtotime($startDate) > strtotime($endDate)) {
            setMessage('Start date cannot be later than end date.', 'error');
        } else {
            // Redirect to export script
            header("Location: export_excel.php?start_date=" . urlencode($startDate) . "&end_date=" . urlencode($endDate));
            exit;
        }
    } else {
        setMessage('Please select both start and end dates.', 'error');
    }
}

// Get message
$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Orders - <?php echo SITE_NAME; ?></title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div class="flex">
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="ml-64 flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Export Orders to Excel</h1>
                <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="bg-gray-600 hover:bg-gray-700 text-white py-2 px-4 rounded-lg transition-colors duration-300">
                    Back to Dashboard
                </a>
            </div>

            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300'; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Select Date Range for Export</h2>
                
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input 
                                type="date" 
                                id="start_date" 
                                name="start_date" 
                                value="<?php echo $_POST['start_date'] ?? getCurrentDate(); ?>"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                style="border: 1px solid #d1d5db; padding: 0.5rem;"
                                required
                            >
                        </div>
                        
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input 
                                type="date" 
                                id="end_date" 
                                name="end_date" 
                                value="<?php echo $_POST['end_date'] ?? getCurrentDate(); ?>"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                style="border: 1px solid #d1d5db; padding: 0.5rem;"
                                required
                            >
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-blue-800 mb-2">Export Information</h3>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Orders data will be exported for the selected date range</li>
                            <li>• Export includes: Order ID, Department, Items, Total Price, Order Date, Status</li>
                            <li>• File will be downloaded as Excel (.xlsx) format</li>
                            <li>• Date range is inclusive (both start and end dates included)</li>
                        </ul>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button 
                            type="button" 
                            onclick="setQuickRange('today')"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition-colors duration-300"
                        >
                            Today
                        </button>
                        <button 
                            type="button" 
                            onclick="setQuickRange('week')"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition-colors duration-300"
                        >
                            This Week
                        </button>
                        <button 
                            type="button" 
                            onclick="setQuickRange('month')"
                            class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition-colors duration-300"
                        >
                            This Month
                        </button>
                        <button 
                            type="submit" 
                            name="export"
                            class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-colors duration-300 flex items-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export to Excel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Preview Section -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Recent Orders Preview</h2>
                
                <?php
                // Show recent orders as preview
                $allOrders = readJsonFile(ORDERS_FILE);
                $recentOrders = array_slice(array_reverse($allOrders), 0, 5);
                ?>
                
                <?php if (empty($recentOrders)): ?>
                    <p class="text-gray-600">No orders found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            #<?php echo getOrderSerialNumber($order['id']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo htmlspecialchars($order['department_name']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <?php echo count($order['items']); ?> item(s)
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo formatPrice($order['total_price']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo formatDate($order['order_date'], 'Y-m-d'); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                switch($order['status']) {
                                                    case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    case 'approved': echo 'bg-blue-100 text-blue-800'; break;
                                                    case 'preparing': echo 'bg-purple-100 text-purple-800'; break;
                                                    case 'ready': echo 'bg-green-100 text-green-800'; break;
                                                    case 'completed': echo 'bg-gray-100 text-gray-800'; break;
                                                    case 'rejected': echo 'bg-red-100 text-red-800'; break;
                                                    default: echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-sm text-gray-500 mt-4">Showing last 5 orders. Use the export feature above to get complete data for any date range.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function setQuickRange(range) {
            const today = new Date();
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            
            switch(range) {
                case 'today':
                    const todayStr = today.toISOString().split('T')[0];
                    startDateInput.value = todayStr;
                    endDateInput.value = todayStr;
                    break;
                    
                case 'week':
                    const weekStart = new Date(today);
                    weekStart.setDate(today.getDate() - today.getDay());
                    startDateInput.value = weekStart.toISOString().split('T')[0];
                    endDateInput.value = today.toISOString().split('T')[0];
                    break;
                    
                case 'month':
                    const monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
                    startDateInput.value = monthStart.toISOString().split('T')[0];
                    endDateInput.value = today.toISOString().split('T')[0];
                    break;
            }
        }
    </script>
</body>
</html>
