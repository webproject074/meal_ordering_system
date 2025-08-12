<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_DEPARTMENT]);

// Get current user
$user = getCurrentUser();

// Get order ID from URL
$orderId = $_GET['id'] ?? '';

if (empty($orderId)) {
    setMessage('Order ID is required', 'error');
    redirect(BASE_URL . '/department/dashboard.php');
}

// Get all orders
$orders = readJsonFile(ORDERS_FILE);

// Find the order
$order = null;
foreach ($orders as $o) {
    if ($o['id'] === $orderId && $o['department_id'] === $user['id']) {
        $order = $o;
        break;
    }
}

// If order not found, redirect to dashboard
if (!$order) {
    setMessage('Order not found', 'error');
    redirect(BASE_URL . '/department/dashboard.php');
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
        <?php include '../includes/department_sidebar.php'; ?>
        
        <div class="ml-64 flex-1 p-8">
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800">View Order</h1>
                <div class="flex gap-2">
                    <?php if ($order['status'] === 'pending'): ?>
                        <a href="<?php echo BASE_URL; ?>/department/delete_order.php?id=<?php echo $order['id']; ?>" 
                           class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg transition-colors duration-300"
                           onclick="return confirm('Are you sure you want to delete this order?')">
                            Delete Order
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo BASE_URL; ?>/department/dashboard.php" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg transition-colors duration-300">
                        Back to Dashboard
                    </a>
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
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <?php if ($order['status'] === 'pending'): ?>
                            <div class="p-4 bg-blue-50 text-blue-700 rounded-lg border border-blue-100">
                                <p class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    Your order is pending approval from the admin.
                                </p>
                            </div>
                        <?php elseif ($order['status'] === 'approved'): ?>
                            <div class="p-4 bg-blue-50 text-blue-700 rounded-lg border border-blue-100">
                                <p class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Your order has been approved and is being processed by the canteen.
                                </p>
                            </div>
                        <?php elseif ($order['status'] === 'rejected'): ?>
                            <div class="p-4 bg-red-50 text-red-700 rounded-lg border border-red-100">
                                <p class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    Your order has been rejected by the admin.
                                </p>
                            </div>
                        <?php elseif ($order['status'] === 'preparing'): ?>
                            <div class="p-4 bg-purple-50 text-purple-700 rounded-lg border border-purple-100">
                                <p class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    Your order is being prepared by the canteen.
                                </p>
                            </div>
                        <?php elseif ($order['status'] === 'ready'): ?>
                            <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-100">
                                <p class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Your order is ready for pickup.
                                </p>
                            </div>
                        <?php elseif ($order['status'] === 'completed'): ?>
                            <div class="p-4 bg-green-50 text-green-700 rounded-lg border border-green-100">
                                <p class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Your order has been completed.
                                </p>
                            </div>
                        <?php endif; ?>
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
