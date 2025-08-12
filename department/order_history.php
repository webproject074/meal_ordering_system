<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_DEPARTMENT]);

// Get current user
$user = getCurrentUser();

// Get all orders for this department
$allOrders = readJsonFile(ORDERS_FILE);
$departmentOrders = array_filter($allOrders, function($order) use ($user) {
    return $order['department_id'] === $user['id'];
});

// Sort orders by date (newest first)
usort($departmentOrders, function($a, $b) {
    return strtotime($b['order_date']) - strtotime($a['order_date']);
});

// Filter by status if provided
$statusFilter = $_GET['status'] ?? '';
if (!empty($statusFilter)) {
    $departmentOrders = array_filter($departmentOrders, function($order) use ($statusFilter) {
        return $order['status'] === $statusFilter;
    });
}

// Get message
$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - <?php echo SITE_NAME; ?></title>
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
        <?php include '../includes/department_sidebar.php'; ?>
        
        <div class="ml-64 flex-1 p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 space-y-4 md:space-y-0">
                <h1 class="text-2xl font-bold text-gray-800">Order History</h1>
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo BASE_URL; ?>/department/order_history.php" class="px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700 transition-colors duration-300 <?php echo empty($statusFilter) ? 'ring-2 ring-offset-2 ring-primary-500' : ''; ?>">
                        All Orders
                    </a>
                    <a href="<?php echo BASE_URL; ?>/department/order_history.php?status=pending" class="px-3 py-2 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600 transition-colors duration-300 <?php echo $statusFilter === 'pending' ? 'ring-2 ring-offset-2 ring-yellow-500' : ''; ?>">
                        Pending
                    </a>
                    <a href="<?php echo BASE_URL; ?>/department/order_history.php?status=approved" class="px-3 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition-colors duration-300 <?php echo $statusFilter === 'approved' ? 'ring-2 ring-offset-2 ring-blue-500' : ''; ?>">
                        Approved
                    </a>
                    <a href="<?php echo BASE_URL; ?>/department/order_history.php?status=rejected" class="px-3 py-2 bg-red-500 text-white text-sm font-medium rounded-md hover:bg-red-600 transition-colors duration-300 <?php echo $statusFilter === 'rejected' ? 'ring-2 ring-offset-2 ring-red-500' : ''; ?>">
                        Rejected
                    </a>
                    <a href="<?php echo BASE_URL; ?>/department/order_history.php?status=completed" class="px-3 py-2 bg-green-500 text-white text-sm font-medium rounded-md hover:bg-green-600 transition-colors duration-300 <?php echo $statusFilter === 'completed' ? 'ring-2 ring-offset-2 ring-green-500' : ''; ?>">
                        Completed
                    </a>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">
                    <?php echo !empty($statusFilter) ? ucfirst($statusFilter) . ' Orders' : 'All Orders'; ?>
                </h2>
                
                <?php if (empty($departmentOrders)): ?>
                    <p class="text-gray-600">No orders found. <a href="<?php echo BASE_URL; ?>/department/place_order.php" class="text-primary-600 hover:text-primary-800 underline">Place your first order</a>.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($departmentOrders as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $order['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($order['order_date']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatPrice($order['total_price']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php 
                                                    switch($order['status']) {
                                                        case 'pending':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'approved':
                                                            echo 'bg-blue-100 text-blue-800';
                                                            break;
                                                        case 'rejected':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        case 'preparing':
                                                            echo 'bg-purple-100 text-purple-800';
                                                            break;
                                                        case 'ready':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'completed':
                                                            echo 'bg-gray-100 text-gray-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                    }
                                                ?>
                                            ">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="<?php echo BASE_URL; ?>/department/view_order.php?id=<?php echo $order['id']; ?>" class="text-primary-600 hover:text-primary-900">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
