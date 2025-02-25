
<?php
require_once 'session_check.php'; // Adjust path as needed
requireActiveLogin(); // This ensures user is logged in AND has Active status
require_once '../employee/session_check.php';
require_once '../layouts/employeeSidebar.php';
require_once '../layouts/employeeHeader.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/eminventory.css">
    <title>Inventory Management</title>
</head>
<body>
    <div class="container">
        <?php renderSidebar('inventory'); // Note different active page ?>
        
        <div class="main-content">
            <?php renderHeader('Inventory Management'); ?>
        

            <!-- Quick Stats -->
            <div class="dashboard-grid">
                <div class="card">
                    <div class="card-header">Current Inventory Status</div>
                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-value">2,500</div>
                            <div class="stat-label">Total Stock</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">15</div>
                            <div class="stat-label">Low Stock Alerts</div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">Pending Orders</div>
                    <div class="stat-grid">
                        <div class="stat-card">
                            <div class="stat-value">42</div>
                            <div class="stat-label">Processing</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-value">28</div>
                            <div class="stat-label">Shipped</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Alerts Section -->
            <div class="card">
                <div class="card-header">Recent Alerts</div>
                <div class="alert">
                    Low stock alert: SKU-123 below threshold
                </div>
                <div class="alert">
                    Delayed shipment: Order #456
                </div>
            </div>
            <!-- Recent Orders Table -->
            <div class="card">
                <div class="card-header">Recent Orders</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>ORD-001</td>
                            <td>John Doe</td>
                            <td>Processing</td>
                            <td>2024-02-16</td>
                            <td>
                                <a href="view_order.php?id=ORD-001" class="btn">View</a>
                            </td>
                        </tr>
                        <tr>
                            <td>ORD-002</td>
                            <td>Jane Smith</td>
                            <td>Shipped</td>
                            <td>2024-02-16</td>
                            <td>
                                <a href="view_order.php?id=ORD-002" class="btn">View</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>