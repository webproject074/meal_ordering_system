<?php
require_once '../includes/functions.php';

// Check if user is authorized
requireAuth([ROLE_ADMIN]);

// Get parameters
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

if (!$startDate || !$endDate) {
    setMessage('Invalid date parameters.', 'error');
    header('Location: export_orders.php');
    exit;
}

// Validate dates
if (strtotime($startDate) > strtotime($endDate)) {
    setMessage('Start date cannot be later than end date.', 'error');
    header('Location: export_orders.php');
    exit;
}

// Get all orders
$allOrders = readJsonFile(ORDERS_FILE);

// Filter orders by date range
$filteredOrders = array_filter($allOrders, function($order) use ($startDate, $endDate) {
    $orderDate = date('Y-m-d', strtotime($order['order_date']));
    return $orderDate >= $startDate && $orderDate <= $endDate;
});

// Sort orders by date (newest first)
usort($filteredOrders, function($a, $b) {
    return strtotime($b['order_date']) - strtotime($a['order_date']);
});

// Create CSV content (Excel compatible)
$filename = 'orders_export_' . $startDate . '_to_' . $endDate . '_' . date('Y-m-d_H-i-s') . '.csv';

// Set headers for download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// Add BOM for proper UTF-8 encoding in Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Write CSV headers
$headers = [
    'Order ID',
    'Serial Number',
    'Department Name',
    'Department ID',
    'Order Date',
    'Request Date',
    'Status',
    'Total Price (LKR)',
    'Number of Visitors',
    'Items Count',
    'Item Details',
    'Individual Item Prices'
];
fputcsv($output, $headers);

// Write data rows
foreach ($filteredOrders as $order) {
    // Prepare item details
    $itemDetails = [];
    $itemPrices = [];
    
    foreach ($order['items'] as $item) {
        $itemDetails[] = $item['name'] . ' (Qty: ' . $item['quantity'] . ')';
        $itemPrices[] = $item['name'] . ': LKR ' . number_format($item['price_per_item'], 2) . ' x ' . $item['quantity'] . ' = LKR ' . number_format($item['price_per_item'] * $item['quantity'], 2);
    }
    
    $row = [
        $order['id'],
        getOrderSerialNumber($order['id']),
        $order['department_name'],
        $order['department_id'],
        formatDate($order['order_date'], 'Y-m-d H:i:s'),
        isset($order['request_date']) ? $order['request_date'] : 'N/A',
        ucfirst($order['status']),
        number_format($order['total_price'], 2),
        isset($order['visitors']) ? $order['visitors'] : 'N/A',
        count($order['items']),
        implode('; ', $itemDetails),
        implode('; ', $itemPrices)
    ];
    
    fputcsv($output, $row);
}

// Add summary row
fputcsv($output, []); // Empty row
fputcsv($output, ['SUMMARY']);
fputcsv($output, ['Export Date:', getCurrentTimestamp()]);
fputcsv($output, ['Date Range:', $startDate . ' to ' . $endDate]);
fputcsv($output, ['Total Orders:', count($filteredOrders)]);

if (!empty($filteredOrders)) {
    $totalAmount = array_sum(array_column($filteredOrders, 'total_price'));
    fputcsv($output, ['Total Amount:', 'LKR ' . number_format($totalAmount, 2)]);
    
    // Status breakdown
    $statusCounts = [];
    foreach ($filteredOrders as $order) {
        $status = $order['status'];
        $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
    }
    
    fputcsv($output, []); // Empty row
    fputcsv($output, ['STATUS BREAKDOWN']);
    foreach ($statusCounts as $status => $count) {
        fputcsv($output, [ucfirst($status) . ':', $count . ' orders']);
    }
}

fclose($output);
exit;
?>
