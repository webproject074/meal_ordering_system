<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_ADMIN]);

// Handle order deletion by admin
if (isset($_GET['delete'])) {
    $deleteId = $_GET['delete'];
    $orders = readJsonFile(ORDERS_FILE);
    $found = false;
    foreach ($orders as $idx => $order) {
        if ($order['id'] === $deleteId) {
            array_splice($orders, $idx, 1);
            $found = true;
            break;
        }
    }
    if ($found && writeJsonFile(ORDERS_FILE, $orders)) {
        setMessage('Order deleted successfully.', 'success');
    } else {
        setMessage('Order not found or failed to delete.', 'error');
    }
    redirect(BASE_URL . '/admin/view_orders.php');
}
// Get all orders
$orders = readJsonFile(ORDERS_FILE);

// Sort orders by date (newest first)
usort($orders, function($a, $b) {
    return strtotime($b['order_date']) - strtotime($a['order_date']);
});

// Filter by status if provided
$statusFilter = $_GET['status'] ?? '';
if (!empty($statusFilter)) {
    $orders = array_filter($orders, function($order) use ($statusFilter) {
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
    <title>All Orders - <?php echo SITE_NAME; ?></title>
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
        
        <div class="md:ml-64 w-full p-4 md:p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 space-y-4 md:space-y-0">
                <h1 class="text-2xl font-bold text-gray-800">All Orders</h1>
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/view_orders.php" class="px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700 transition-colors duration-300 <?php echo empty($statusFilter) ? 'ring-2 ring-offset-2 ring-primary-500' : ''; ?>">
                        All Orders
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/view_orders.php?status=pending" class="px-3 py-2 bg-yellow-500 text-white text-sm font-medium rounded-md hover:bg-yellow-600 transition-colors duration-300 <?php echo $statusFilter === 'pending' ? 'ring-2 ring-offset-2 ring-yellow-500' : ''; ?>">
                        Pending
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/view_orders.php?status=approved" class="px-3 py-2 bg-green-500 text-white text-sm font-medium rounded-md hover:bg-green-600 transition-colors duration-300 <?php echo $statusFilter === 'approved' ? 'ring-2 ring-offset-2 ring-green-500' : ''; ?>">
                        Approved
                    </a>
                    <a href="<?php echo BASE_URL; ?>/admin/view_orders.php?status=rejected" class="px-3 py-2 bg-red-500 text-white text-sm font-medium rounded-md hover:bg-red-600 transition-colors duration-300 <?php echo $statusFilter === 'rejected' ? 'ring-2 ring-offset-2 ring-red-500' : ''; ?>">
                        Rejected
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
                
                <?php if (empty($orders)): ?>
                    <p class="text-gray-600">No orders found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitors</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($orders as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $order['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $order['department_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatDate($order['order_date']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo isset($order['request_date']) ? date('Y-m-d', strtotime($order['request_date'])) : 'N/A'; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo isset($order['visitors']) ? $order['visitors'] : 'N/A'; ?></td>
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
                                            <a href="<?php echo BASE_URL; ?>/admin/view_order.php?id=<?php echo $order['id']; ?>" class="text-primary-600 hover:text-primary-900 mr-3">View</a>
                                            <a href="<?php echo BASE_URL; ?>/admin/view_orders.php?delete=<?php echo $order['id']; ?>" class="text-red-600 hover:text-red-900 mr-3" onclick="return confirm('Are you sure you want to delete this order? This cannot be undone.');">Delete</a>
                                            <?php if ($order['status'] === 'pending'): ?>
                                                <a href="<?php echo BASE_URL; ?>/admin/update_order.php?id=<?php echo $order['id']; ?>&action=approve" class="text-green-600 hover:text-green-900 mr-3">Approve</a>
                                                <a href="<?php echo BASE_URL; ?>/admin/update_order.php?id=<?php echo $order['id']; ?>&action=reject" class="text-red-600 hover:text-red-900">Reject</a>
                                            <?php endif; ?>
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
