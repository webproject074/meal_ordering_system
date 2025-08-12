<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_ADMIN]);

// Get order ID from URL
$orderId = $_GET['id'] ?? '';

if (empty($orderId)) {
    setMessage('Order ID is required', 'error');
    redirect(BASE_URL . '/admin/dashboard.php');
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
    redirect(BASE_URL . '/admin/dashboard.php');
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
        <?php include '../includes/admin_sidebar.php'; ?>
        
        <div class="ml-64 flex-1 p-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 space-y-4 md:space-y-0">
                <h1 class="text-2xl font-bold text-gray-800">View Order</h1>
                <div class="flex flex-wrap gap-2">
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="px-3 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700 transition-colors duration-300">
                        Back to Dashboard
                    </a>
                    
                    <?php if ($order['status'] === 'pending'): ?>
                        <a href="<?php echo BASE_URL; ?>/admin/update_order.php?id=<?php echo $order['id']; ?>&action=approve" class="px-3 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors duration-300">
                            Approve Order
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/update_order.php?id=<?php echo $order['id']; ?>&action=reject" class="px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 transition-colors duration-300">
                            Reject Order
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Order Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="space-y-3">
                            <p class="flex justify-between">
                                <span class="font-medium text-gray-600">Order ID:</span>
                                <span class="text-gray-800"><?php echo $order['id']; ?></span>
                            </p>
                            <p class="flex justify-between">
                                <span class="font-medium text-gray-600">Department:</span>
                                <span class="text-gray-800"><?php echo $order['department_name']; ?></span>
                            </p>
                            <p class="flex justify-between">
                                <span class="font-medium text-gray-600">Order Date:</span>
                                <span class="text-gray-800"><?php echo formatDate($order['order_date']); ?></span>
                            </p>
                            <p class="flex justify-between">
                                <span class="font-medium text-gray-600">Request Date:</span>
                                <span class="text-gray-800"><?php echo isset($order['request_date']) ? date('Y-m-d', strtotime($order['request_date'])) : 'N/A'; ?></span>
                            </p>
                            <p class="flex justify-between">
                                <span class="font-medium text-gray-600">Visitors:</span>
                                <span class="text-gray-800"><?php echo isset($order['visitors']) ? $order['visitors'] : 'N/A'; ?></span>
                            </p>
                            <p class="flex justify-between items-center">
                                <span class="font-medium text-gray-600">Status:</span>
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
                            </p>
                        </div>
                    </div>
                </div>
                
                <h3 class="text-lg font-medium text-gray-800 mb-4 border-b border-gray-200 pb-2">Order Items</h3>
                
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
                                <th class="px-6 py-3 text-left text-sm font-medium text-primary-700"><?php echo formatPrice($order['total_price']); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
