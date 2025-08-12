<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_CANTEEN]);

// Get all orders
$orders = readJsonFile(ORDERS_FILE);

// Filter only approved orders
$approvedOrders = array_filter($orders, function($order) {
    return $order['status'] === 'approved';
});

// Sort orders by date (newest first)
usort($approvedOrders, function($a, $b) {
    return strtotime($b['order_date']) - strtotime($a['order_date']);
});

// Get message
$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canteen Dashboard - <?php echo SITE_NAME; ?></title>
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
        <?php include '../includes/canteen_sidebar.php'; ?>
        
        <div class="md:ml-64 w-full p-4 md:p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Canteen Dashboard</h1>
                <a href="<?php echo BASE_URL; ?>/canteen/manage_menu.php" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg transition-colors duration-300">
                    Manage Menu
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Approved Orders</h2>
                
                <?php if (empty($approvedOrders)): ?>
                    <p class="text-gray-600">No approved orders found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $orderCount = count($approvedOrders); $serial = $orderCount; foreach ($approvedOrders as $order): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php printf('%03d', $serial--); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $order['department_name']; ?></td>
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
                                                        case 'order seen':
                                                            echo 'bg-indigo-100 text-indigo-800';
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
                                            <a href="<?php echo BASE_URL; ?>/canteen/view_order.php?id=<?php echo $order['id']; ?>" class="text-primary-600 hover:text-primary-900 mr-3">View</a>
                                            <?php if ($order['status'] === 'approved'): ?>
                                                <a href="<?php echo BASE_URL; ?>/canteen/update_order.php?id=<?php echo $order['id']; ?>&status=order seen" class="text-indigo-600 hover:text-indigo-900 mr-3">Mark as Seen</a>
                                            <?php elseif ($order['status'] === 'order seen'): ?>
                                                <a href="<?php echo BASE_URL; ?>/canteen/update_order.php?id=<?php echo $order['id']; ?>&status=preparing" class="text-yellow-600 hover:text-yellow-900 mr-3">Mark Preparing</a>
                                            <?php elseif ($order['status'] === 'preparing'): ?>
                                                <a href="<?php echo BASE_URL; ?>/canteen/update_order.php?id=<?php echo $order['id']; ?>&status=ready" class="text-green-600 hover:text-green-900 mr-3">Mark Ready</a>
                                            <?php elseif ($order['status'] === 'ready'): ?>
                                                <a href="<?php echo BASE_URL; ?>/canteen/update_order.php?id=<?php echo $order['id']; ?>&status=completed" class="text-gray-600 hover:text-gray-900">Mark as Completed</a>
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
