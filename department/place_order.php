<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_DEPARTMENT]);

// Get current user
$user = getCurrentUser();

// Get all menu items
$menuItems = readJsonFile(MENUS_FILE);

// Filter available menu items
$availableMenuItems = array_filter($menuItems, function($item) {
    return $item['availability'] === true;
});

// Group menu items by category
$menuByCategory = [];
foreach ($availableMenuItems as $item) {
    $category = $item['category'];
    if (!in_array($category, ['Morning Snack', 'Lunch', 'Dinner'])) {
        $category = 'Other';
    }
    if (!isset($menuByCategory[$category])) {
        $menuByCategory[$category] = [];
    }
    $menuByCategory[$category][] = $item;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if we're in the summary view or final submission
    if (isset($_POST['show_summary'])) {
        $orderItems = [];
        $totalPrice = 0;
        
        // Process each menu item
        foreach ($menuItems as $item) {
            $quantity = (int) ($_POST['quantity_' . $item['id']] ?? 0);
            
            // Add item to order if quantity is greater than 0
            if ($quantity > 0) {
                $orderItems[] = [
                    'menu_item_id' => $item['id'],
                    'name' => $item['name'],
                    'quantity' => $quantity,
                    'price_per_item' => $item['price']
                ];
                
                $totalPrice += $item['price'] * $quantity;
            }
        }
        
        // Check if order is empty
        if (empty($orderItems)) {
            setMessage('Please select at least one item', 'error');
        } else {
            // Store the order items in session for the summary page
            $_SESSION['order_summary'] = [
                'items' => $orderItems,
                'total_price' => $totalPrice
            ];
            
            // Redirect to the same page without edit parameter to show the summary view
            redirect(BASE_URL . '/department/place_order.php');
        }
    } else if (isset($_POST['place_order'])) {
        // Final order submission
        if (!isset($_SESSION['order_summary'])) {
            setMessage('Order data not found. Please try again.', 'error');
            redirect(BASE_URL . '/department/place_order.php');
        }
        
        $orderItems = $_SESSION['order_summary']['items'];
        $totalPrice = $_SESSION['order_summary']['total_price'];
        $requestDate = $_POST['request_date'] ?? getCurrentDate();
        $visitors = (int) ($_POST['visitors'] ?? 0);
        
        // Check if order is empty
        if (empty($orderItems)) {
            setMessage('Please select at least one item', 'error');
        } else {
            // Create new order
            $orders = readJsonFile(ORDERS_FILE);
            
            // Generate next short order ID (e.g., '000', '001', ...)
$maxOrderNum = -1;
foreach ($orders as $o) {
    if (isset($o['id']) && preg_match('/^\d{3}$/', $o['id'])) {
        $num = intval($o['id']);
        if ($num > $maxOrderNum) {
            $maxOrderNum = $num;
        }
    }
}
$newOrderId = str_pad($maxOrderNum + 1, 3, '0', STR_PAD_LEFT);
$newOrder = [
    'id' => $newOrderId,
                'department_id' => $user['id'],
                'department_name' => $user['department_name'],
                'items' => $orderItems,
                'total_price' => $totalPrice,
                'order_date' => getCurrentTimestamp(),
                'request_date' => $requestDate,
                'visitors' => $visitors,
                'status' => 'pending'
            ];
            
            $orders[] = $newOrder;
            
            if (writeJsonFile(ORDERS_FILE, $orders)) {
                // Clear the order summary from session after successful order placement
                unset($_SESSION['order_summary']);
                
                setMessage('Order placed successfully', 'success');
                redirect(BASE_URL . '/department/view_order.php?id=' . $newOrder['id']);
            } else {
                setMessage('Failed to place order', 'error');
            }
        }
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
    <title>Place Order - <?php echo SITE_NAME; ?></title>
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
                <h1 class="text-2xl font-bold text-gray-800">Place Order</h1>
                <a href="<?php echo BASE_URL; ?>/department/dashboard.php" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-4 rounded-lg transition-colors duration-300">
                    Back to Dashboard
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message['text']; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <?php if (isset($_SESSION['order_summary']) && !empty($_SESSION['order_summary']['items']) && !isset($_GET['edit'])): ?>
                    <!-- Order Summary View -->
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Order Summary</h2>
                    
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-700 mb-3">Selected Items</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($_SESSION['order_summary']['items'] as $item): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php 
                                                    $menuImg = '';
                                                    foreach ($menuItems as $m) {
                                                        if ($m['id'] === $item['menu_item_id']) {
                                                            $menuImg = $m['image'] ?? '';
                                                            break;
                                                        }
                                                    }
                                                ?>
                                                <?php if (!empty($menuImg)): ?>
                                                    <img src="<?php echo BASE_URL . '/assets/menu_images/' . htmlspecialchars($menuImg); ?>" alt="Menu Image" class="h-12 w-12 object-cover rounded">
                                                <?php else: ?>
                                                    <span class="text-gray-400 text-xs">No Image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $item['name']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatPrice($item['price_per_item']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['quantity']; ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo formatPrice($item['price_per_item'] * $item['quantity']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-50">
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Total:</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-primary-600"><?php echo formatPrice($_SESSION['order_summary']['total_price']); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <form method="post" action="">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1">Request Date:</label>
                                <input 
                                    type="date" 
                                    id="request_date" 
                                    name="request_date" 
                                    min="<?php echo getCurrentDate(); ?>" 
                                    value="<?php echo getCurrentDate(); ?>" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    style="border: 1px solid #d1d5db; padding: 0.5rem;"
                                    required
                                >
                            </div>
                            <div>
                                <label for="visitors" class="block text-sm font-medium text-gray-700 mb-1">Number of Visitors:</label>
                                <input 
                                    type="number" 
                                    id="visitors" 
                                    name="visitors" 
                                    min="1" 
                                    value="1" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    style="border: 1px solid #d1d5db; padding: 0.5rem;"
                                    required
                                >
                            </div>
                        </div>
                        
                        <div class="flex justify-between mt-8">
                            <a href="<?php echo BASE_URL; ?>/department/place_order.php?edit=1" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-6 rounded-lg transition-colors duration-300 font-medium">Edit Order</a>
                            <button type="submit" name="place_order" value="1" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-6 rounded-lg transition-colors duration-300 font-medium">Place Order</button>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Menu Selection View -->
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Available Menu Items</h2>
                    
                    <?php if (empty($availableMenuItems)): ?>
                        <p class="text-gray-600">No menu items available at the moment.</p>
                    <?php else: ?>
                        <form method="post" action="" id="order-form">
                        <?php 
                        // Store the order items for pre-filling the form if we're editing
                        $editMode = isset($_GET['edit']) && isset($_SESSION['order_summary']);
                        $savedItems = [];
                        
                        if ($editMode) {
                            foreach ($_SESSION['order_summary']['items'] as $savedItem) {
                                $savedItems[$savedItem['menu_item_id']] = $savedItem['quantity'];
                            }
                            // Don't unset the session here, we'll keep it until form submission
                        }
                        ?>
                        <?php foreach ($menuByCategory as $category => $items): ?>
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-primary-700 mb-4 border-b border-gray-200 pb-2"><?php echo $category; ?></h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <?php foreach ($items as $item): ?>
                                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="<?php echo BASE_URL . '/assets/menu_images/' . htmlspecialchars($item['image']); ?>" alt="Menu Image" class="w-full h-40 object-cover object-center">
                                            <?php else: ?>
                                                <div class="w-full h-40 flex items-center justify-center bg-gray-100 text-gray-400">No Image</div>
                                            <?php endif; ?>
                                            <div class="p-5">
                                                <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php echo $item['name']; ?></h3>
                                                <div class="text-primary-600 font-bold mb-2"><?php echo formatPrice($item['price']); ?></div>
                                                <div class="text-xs text-gray-500 uppercase tracking-wide mb-2"><?php echo $item['category']; ?></div>
                                                <div class="text-gray-600 mb-4 text-sm"><?php echo $item['description']; ?></div>
                                                
                                                <div class="mt-4">
                                                    <label for="quantity_<?php echo $item['id']; ?>" class="block text-sm font-medium text-gray-700 mb-1">Quantity:</label>
                                                    <div class="flex items-center">
                                                        <input 
                                                            type="number" 
                                                            id="quantity_<?php echo $item['id']; ?>" 
                                                            name="quantity_<?php echo $item['id']; ?>" 
                                                            min="0" 
                                                            value="<?php echo isset($savedItems[$item['id']]) ? $savedItems[$item['id']] : 0; ?>"
                                                            class="order-item-quantity block w-20 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                                            onchange="updateTotal()"
                                                            data-price="<?php echo $item['price']; ?>"
                                                            style="border: 1px solid #d1d5db; padding: 0.5rem;"
                                                        >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-medium text-gray-800">Total:</span>
                                <span id="total-price" class="text-xl font-bold text-primary-600"><?php echo formatPrice(0); ?></span>
                            </div>
                        </div>
                        
                        <div class="mt-6 text-right">
                            <button type="submit" name="show_summary" value="1" class="bg-primary-600 hover:bg-primary-700 text-white py-2 px-6 rounded-lg transition-colors duration-300 font-medium">
                                Review Order
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function updateTotal() {
            let total = 0;
            const quantityInputs = document.querySelectorAll('.order-item-quantity');
            
            quantityInputs.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                const price = parseFloat(input.getAttribute('data-price')) || 0;
                total += quantity * price;
            });
            
            document.getElementById('total-price').textContent = 'LKR ' + total.toFixed(2);
        }
        
        // Calculate total on page load (important for edit mode)
        document.addEventListener('DOMContentLoaded', function() {
            updateTotal();
        });
    </script>
</body>
</html>
