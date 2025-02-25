<?php
session_start();
include '../server/database.php';
require_once 'session_check.php'; // Adjust path as needed
requireActiveLogin(); // This ensures user is logged in AND has Active status
require_once '../layouts/employeeSidebar.php';
require_once '../layouts/employeeHeader.php';

// Fetch raw materials analytics
$rawMaterialsQuery = "SELECT 
    MaterialName,
    QuantityInStock,
    UnitCost,
    MinimumStock,
    LastRestockedDate,
    raw_warehouse,
    (QuantityInStock * UnitCost) as TotalValue
    FROM rawmaterials";
$rawMaterialsResult = $conn->query($rawMaterialsQuery);

// Fetch production orders analytics
$productionQuery = "SELECT 
    p.ProductName,
    po.Status,
    COUNT(*) as OrderCount,
    SUM(po.QuantityOrdered) as TotalOrdered,
    SUM(po.QuantityProduced) as TotalProduced
    FROM productionorders po
    JOIN products p ON po.ProductID = p.ProductID
    GROUP BY p.ProductName, po.Status";
$productionResult = $conn->query($productionQuery);

// Fetch customer orders analytics
$orderQuery = "SELECT 
    Status,
    COUNT(*) as OrderCount,
    SUM(TotalAmount) as TotalRevenue
    FROM customerorders
    GROUP BY Status";
$orderResult = $conn->query($orderQuery);

// Fetch inventory analytics
$inventoryQuery = "SELECT 
    p.ProductName,
    p.Category,
    i.Quantity,
    pw.productWarehouse,
    pw.Section
    FROM inventory i
    JOIN products p ON i.ProductID = p.ProductID
    JOIN products_warehouse pw ON i.LocationID = pw.productLocationID";
$inventoryResult = $conn->query($inventoryQuery);
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
        <?php renderSidebar('overview'); // Note different active page ?>
        
        <div class="main-content">
            <?php renderHeader('Overview'); ?>

            <!-- Quick Stats -->
        <div class="dashboard-grid" style="padding-top: 20px;">
            <!-- Total Raw Materials -->
            <div class="card">
                <div class="card-header">Total Raw Materials</div>
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php echo $rawMaterialsResult->num_rows; ?>
                        </div>
                        <div class="stat-label">Total Items</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            ₱<?php
                                $totalValue = 0;
                                while ($row = $rawMaterialsResult->fetch_assoc()) {
                                    $totalValue += $row['TotalValue'];
                                }
                                echo number_format($totalValue, 2);
                                $rawMaterialsResult->data_seek(0); // Reset pointer for reuse
                            ?>
                        </div>
                        <div class="stat-label">Total Value</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php
                                $lowStock = 0;
                                while ($row = $rawMaterialsResult->fetch_assoc()) {
                                    if ($row['QuantityInStock'] < $row['MinimumStock']) {
                                        $lowStock++;
                                    }
                                }
                                echo $lowStock;
                                $rawMaterialsResult->data_seek(0); // Reset pointer for reuse
                            ?>
                        </div>
                        <div class="stat-label">Low Stock Alerts</div>
                    </div>
                </div>
            </div>

            <!-- Active Orders -->
            <div class="card">
                <div class="card-header">Active Orders</div>
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php
                                $activeOrders = 0;
                                while ($row = $orderResult->fetch_assoc()) {
                                    if ($row['Status'] == 'Processing') {
                                        $activeOrders = $row['OrderCount'];
                                    }
                                }
                                echo $activeOrders;
                                $orderResult->data_seek(0); // Reset pointer for reuse
                            ?>
                        </div>
                        <div class="stat-label">Processing</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php
                                $shippedOrders = 0;
                                while ($row = $orderResult->fetch_assoc()) {
                                    if ($row['Status'] == 'Shipped') {
                                        $shippedOrders = $row['OrderCount'];
                                    }
                                }
                                echo $shippedOrders;
                                $orderResult->data_seek(0); // Reset pointer for reuse
                            ?>
                        </div>
                        <div class="stat-label">Shipped</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Raw Materials Stock Level -->
        <div class="card">
            <div class="card-header">Raw Materials Stock Level</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Material Name</th>
                        <th>Warehouse</th>
                        <th>Quantity</th>
                        <th>Minimum Stock</th>
                        <th>Status</th>
                        <th>Last Restocked</th>
                        <th>Total Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $rawMaterialsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['MaterialName']); ?></td>
                            <td><?php echo htmlspecialchars($row['raw_warehouse']); ?></td>
                            <td><?php echo $row['QuantityInStock']; ?></td>
                            <td><?php echo $row['MinimumStock']; ?></td>
                            <td>
                                <?php
                                    $status = $row['QuantityInStock'] < $row['MinimumStock'] ? 'Low Stock' : 'Adequate';
                                    echo $status;
                                ?>
                            </td>
                            <td><?php echo $row['LastRestockedDate']; ?></td>
                            <td>₱<?php echo number_format($row['TotalValue'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </div>
</body>
</html>