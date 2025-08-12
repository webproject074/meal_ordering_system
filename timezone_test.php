<?php
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sri Lanka Timezone Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #16a085; font-weight: bold; }
        .info { background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .test-result { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🇱🇰 Sri Lanka Timezone Test</h1>
        
        <div class="info">
            <h3>Current System Status:</h3>
            <p><strong>Timezone:</strong> <span class="success"><?php echo date_default_timezone_get(); ?></span></p>
            <p><strong>Current Time:</strong> <span class="success"><?php echo date('Y-m-d H:i:s'); ?></span></p>
            <p><strong>Current Date:</strong> <span class="success"><?php echo date('Y-m-d'); ?></span></p>
        </div>

        <div class="test-result">
            <h3>Function Tests:</h3>
            <p><strong>getCurrentTimestamp():</strong> <?php echo getCurrentTimestamp(); ?></p>
            <p><strong>getCurrentDate():</strong> <?php echo getCurrentDate(); ?></p>
        </div>

        <div class="info">
            <h3>Expected Results:</h3>
            <p>✅ Timezone should be: <strong>Asia/Colombo</strong></p>
            <p>✅ Time should match your current Sri Lankan time (around 10:00 AM)</p>
            <p>✅ When you place orders, timestamps should use this time</p>
        </div>

        <div class="test-result">
            <h3>Order Timestamp Preview:</h3>
            <p>If you place an order right now, it will be saved with timestamp:</p>
            <p><strong><?php echo getCurrentTimestamp(); ?></strong></p>
        </div>

        <hr>
        <p><a href="department/dashboard.php" style="color: #007bff;">← Back to Department Dashboard</a></p>
        <p><a href="department/place_order.php" style="color: #28a745;">🍽️ Test Order Placement</a></p>
    </div>
</body>
</html>
