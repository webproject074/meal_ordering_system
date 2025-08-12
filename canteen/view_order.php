<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_CANTEEN]);

// Get order ID from URL
$orderId = $_GET['id'] ?? '';

if (empty($orderId)) {
    setMessage('Order ID is required', 'error');
    redirect(BASE_URL . '/canteen/dashboard.php');
}

// Get all orders
$orders = readJsonFile(ORDERS_FILE);

// Find the order
$order = null;
foreach ($orders as $o) {
    if ($o['id'] === $orderId) {
        $order = $o;
        break;
    }
}

// If order not found, redirect to dashboard
if (!$order) {
    setMessage('Order not found', 'error');
    redirect(BASE_URL . '/canteen/dashboard.php');
}

// Get message
$message = getMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order - <?php echo SITE_NAME; ?></title>
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
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
                <h1 class="text-2xl font-bold text-gray-800">View Order</h1>
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo BASE_URL; ?>/canteen/dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">Back to Dashboard</a>
                    <a href="<?php echo BASE_URL; ?>/canteen/orders.php?status=order seen" class="bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition-colors duration-300 <?php echo ($order['status'] === 'order seen') ? 'ring-2 ring-offset-2 ring-indigo-500' : ''; ?>">Order Seen Orders</a>
                    
                    <?php if ($order['status'] === 'approved'): ?>
                        <a href="<?php echo BASE_URL; ?>/canteen/update_order.php?id=<?php echo $order['id']; ?>&status=order seen" class="bg-indigo-500 hover:bg-indigo-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">Mark as Seen</a>
                    <?php elseif ($order['status'] === 'order seen'): ?>
                        <a href="<?php echo BASE_URL; ?>/canteen/update_order.php?id=<?php echo $order['id']; ?>&status=preparing" class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">Mark as Preparing</a>
                    <?php elseif ($order['status'] === 'preparing'): ?>
                        <a href="<?php echo BASE_URL; ?>/canteen/update_order.php?id=<?php echo $order['id']; ?>&status=ready" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">Mark as Ready</a>
                    <?php elseif ($order['status'] === 'ready'): ?>
                        <a href="<?php echo BASE_URL; ?>/canteen/update_order.php?id=<?php echo $order['id']; ?>&status=completed" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition-colors duration-300">Mark as Completed</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Order Details <span class="ml-2 text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded <?php echo ($order['status'] === 'order seen') ? '' : 'hidden'; ?>">Order Seen</span></h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="mb-2"><span class="font-medium text-gray-600">Order ID:</span>
                                <span class="text-gray-800"><?php echo $order['id']; ?></span>
                        <p class="mb-2"><span class="font-medium text-gray-700">Department:</span> <span class="text-gray-900"><?php echo $order['department_name']; ?></span></p>
                        <p class="mb-2"><span class="font-medium text-gray-700">Total Price:</span> <span class="text-gray-900 font-semibold"><?php echo formatPrice($order['total_price']); ?></span></p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="mb-2"><span class="font-medium text-gray-700">Order Date:</span> <span class="text-gray-900"><?php echo formatDate($order['order_date']); ?></span></p>
                        <p class="mb-2"><span class="font-medium text-gray-700">Request Date:</span> <span class="text-gray-900"><?php echo isset($order['request_date']) ? date('Y-m-d', strtotime($order['request_date'])) : 'N/A'; ?></span></p>
                        <p class="mb-2"><span class="font-medium text-gray-700">Status:</span> 
                            <?php 
                            $statusClass = '';
                            switch($order['status']) {
                                case 'pending':
                                    $statusClass = 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'approved':
                                    $statusClass = 'bg-blue-100 text-blue-800';
                                    break;
                                case 'order seen':
                                    $statusClass = 'bg-indigo-100 text-indigo-800';
                                    break;
                                case 'rejected':
                                    $statusClass = 'bg-red-100 text-red-800';
                                    break;
                                case 'preparing':
                                    $statusClass = 'bg-purple-100 text-purple-800';
                                    break;
                                case 'ready':
                                    $statusClass = 'bg-green-100 text-green-800';
                                    break;
                                case 'completed':
                                    $statusClass = 'bg-gray-100 text-gray-800';
                                    break;
                                default:
                                    $statusClass = 'bg-gray-100 text-gray-800';
                            }
                            ?>
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                <?php echo ($order['status'] === 'order seen') ? 'Order Seen' : ucfirst($order['status']); ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <h3 class="text-lg font-medium text-gray-800 mb-4">Order Items</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($order['items'] as $item): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['name']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatPrice($item['price_per_item']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['quantity']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatPrice($item['price_per_item'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <th colspan="3" class="px-6 py-3 text-left text-sm font-medium text-gray-900">Total</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-900"><?php echo formatPrice($order['total_price']); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
