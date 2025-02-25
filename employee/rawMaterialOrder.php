<?php
require_once 'session_check.php'; // Adjust path as needed
requireActiveLogin(); // This ensures user is logged in AND has Active status
require_once '../layouts/employeeSidebar.php';
require_once '../layouts/employeeHeader.php';

// Database connection
$host = 'localhost';
$dbname = 'stockport';
$username = 'root';
$password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch all products for the dropdown, including MaterialName, MaterialID and current stock
$productStmt = $pdo->query("
    SELECT p.ProductID, p.ProductName, rm.MaterialID, rm.MaterialName, rm.QuantityInStock 
    FROM products p
    JOIN rawmaterials rm ON p.MaterialID = rm.MaterialID
");
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

// Create product to ratio mapping based on material type and product name
$productRatios = [];
foreach ($products as $product) {
    $productName = strtolower($product['ProductName']);
    $materialName = strtolower($product['MaterialName']);
    
    // Tinplate products
    if (strpos($materialName, 'tinplate') !== false) {
        if (strpos($productName, 'food can') !== false) {
            $productRatios[$product['ProductID']] = 192;
        } elseif (strpos($productName, 'biscuit') !== false) {
            $productRatios[$product['ProductID']] = 115;
        } elseif (strpos($productName, 'paint') !== false) {
            $productRatios[$product['ProductID']] = 82;
        } elseif (strpos($productName, 'baking') !== false || strpos($productName, 'mold') !== false) {
            $productRatios[$product['ProductID']] = 96;
        }
    }
    // Steel products
    elseif (strpos($materialName, 'steel') !== false && strpos($materialName, 'stainless') === false) {
        if (strpos($productName, 'oil') !== false || strpos($productName, 'drum') !== false) {
            $productRatios[$product['ProductID']] = 3;
        } elseif (strpos($productName, 'fuel') !== false || strpos($productName, 'tank') !== false) {
            $productRatios[$product['ProductID']] = 2;
        } elseif (strpos($productName, 'coin') !== false || strpos($productName, 'safe') !== false || strpos($productName, 'bank') !== false) {
            $productRatios[$product['ProductID']] = 11;
        }
    }
    // Aluminum products
    elseif (strpos($materialName, 'aluminum') !== false) {
        if (strpos($productName, 'beverage') !== false) {
            $productRatios[$product['ProductID']] = 823;
        } elseif (strpos($productName, 'food tray') !== false || strpos($productName, 'tray') !== false) {
            $productRatios[$product['ProductID']] = 640;
        } elseif (strpos($productName, 'aerosol') !== false) {
            $productRatios[$product['ProductID']] = 576;
        }
    }
    // Stainless steel products
    elseif (strpos($materialName, 'stainless') !== false) {
        if (strpos($productName, 'storage') !== false || strpos($productName, 'bin') !== false) {
            $productRatios[$product['ProductID']] = 6;
        }
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_order'])) {
        // Create a new raw material order
        $productID = $_POST['ProductID'];
        $employeeID = $_SESSION['employeeID'];
        $startDate = date('Y-m-d');
        $endDate = $_POST['EndDate'];
        $status = 'In Progress';
        $sheetCount = (int)$_POST['SheetCount'];
        
        // Get the product ratio and material information
        $ratio = isset($productRatios[$productID]) ? $productRatios[$productID] : 0;
        
        // Get material ID for this product
        $materialStmt = $pdo->prepare("SELECT MaterialID FROM products WHERE ProductID = :ProductID");
        $materialStmt->execute([':ProductID' => $productID]);
        $materialID = $materialStmt->fetchColumn();
        
        // Check current material stock
        $stockStmt = $pdo->prepare("SELECT QuantityInStock FROM rawmaterials WHERE MaterialID = :MaterialID");
        $stockStmt->execute([':MaterialID' => $materialID]);
        $currentStock = $stockStmt->fetchColumn();
        
        if ($ratio <= 0) {
            echo "<div class='alert alert-error'>Error: No production ratio defined for this product. Please contact administrator.</div>";
        } elseif ($currentStock < $sheetCount) {
            echo "<div class='alert alert-error'>Error: Insufficient material in stock. Available: $currentStock sheets. Required: $sheetCount sheets.</div>";
        } else {
            $quantityOrdered = $ratio * $sheetCount;
            $quantityProduced = 0;

            // Begin transaction to ensure database consistency
            $pdo->beginTransaction();
            
            try {
                // Insert the new order
                $stmt = $pdo->prepare("INSERT INTO productionOrders (ProductID, EmployeeID, StartDate, EndDate, Status, QuantityOrdered, QuantityProduced)
                                VALUES (:ProductID, :EmployeeID, :StartDate, :EndDate, :Status, :QuantityOrdered, :QuantityProduced)");
                $stmt->execute([
                    ':ProductID' => $productID,
                    ':EmployeeID' => $employeeID,
                    ':StartDate' => $startDate,
                    ':EndDate' => $endDate,
                    ':Status' => $status,
                    ':QuantityOrdered' => $quantityOrdered,
                    ':QuantityProduced' => $quantityProduced
                ]);
                
                // Update the material stock
                $updateStmt = $pdo->prepare("UPDATE rawmaterials SET 
                                            QuantityInStock = QuantityInStock - :SheetCount 
                                            WHERE MaterialID = :MaterialID");
                $updateStmt->execute([
                    ':SheetCount' => $sheetCount,
                    ':MaterialID' => $materialID
                ]);
                
                // Commit the transaction
                $pdo->commit();
                
                echo "<div class='alert'>Order created successfully! Ordered " . $quantityOrdered . " units using " . $sheetCount . " material sheets.</div>";
            } catch (Exception $e) {
                // Roll back the transaction if something failed
                $pdo->rollBack();
                echo "<div class='alert alert-error'>Error: " . $e->getMessage() . "</div>";
            }
        }
    } elseif (isset($_POST['update_status'])) {
        // Update the status of an existing order
        $orderID = $_POST['OrderID'];
        $newStatus = $_POST['Status'];
        
        // Get the old status and sheet count for this order
        $orderStmt = $pdo->prepare("
            SELECT po.Status, po.QuantityOrdered, p.ProductID, p.MaterialID 
            FROM productionOrders po
            JOIN products p ON po.ProductID = p.ProductID
            WHERE po.OrderID = :OrderID
        ");
        $orderStmt->execute([':OrderID' => $orderID]);
        $orderInfo = $orderStmt->fetch(PDO::FETCH_ASSOC);
        
        // Begin transaction
        $pdo->beginTransaction();
        
        try {
            // Update the order status
            $stmt = $pdo->prepare("UPDATE productionOrders SET Status = :Status WHERE OrderID = :OrderID");
            $stmt->execute([':Status' => $newStatus, ':OrderID' => $orderID]);
            
            // If changing from 'In Progress' to 'Cancelled', return materials to inventory
            if ($orderInfo['Status'] == 'In Progress' && $newStatus == 'Cancelled') {
                // Calculate sheets used
                $productID = $orderInfo['ProductID'];
                $ratio = isset($productRatios[$productID]) ? $productRatios[$productID] : 0;
                $sheetsUsed = ($ratio > 0) ? round($orderInfo['QuantityOrdered'] / $ratio) : 0;
                
                if ($sheetsUsed > 0) {
                    // Return the materials to stock
                    $updateStmt = $pdo->prepare("UPDATE rawmaterials SET 
                                                QuantityInStock = QuantityInStock + :SheetCount 
                                                WHERE MaterialID = :MaterialID");
                    $updateStmt->execute([
                        ':SheetCount' => $sheetsUsed,
                        ':MaterialID' => $orderInfo['MaterialID']
                    ]);
                }
            }
            
            // Commit the transaction
            $pdo->commit();
            echo "<div class='alert'>Order status updated successfully!</div>";
        } catch (Exception $e) {
            // Roll back the transaction if something failed
            $pdo->rollBack();
            echo "<div class='alert alert-error'>Error: " . $e->getMessage() . "</div>";
        }
    } elseif (isset($_POST['delete_order'])) {
        // Delete an order - similar logic as above for returning materials if needed
        $orderID = $_POST['OrderID'];
        
        // Get order information
        $orderStmt = $pdo->prepare("
            SELECT po.Status, po.QuantityOrdered, p.ProductID, p.MaterialID 
            FROM productionOrders po
            JOIN products p ON po.ProductID = p.ProductID
            WHERE po.OrderID = :OrderID
        ");
        $orderStmt->execute([':OrderID' => $orderID]);
        $orderInfo = $orderStmt->fetch(PDO::FETCH_ASSOC);
        
        // Only return materials to stock if the order was 'In Progress'
        if ($orderInfo['Status'] == 'In Progress') {
            $productID = $orderInfo['ProductID'];
            $ratio = isset($productRatios[$productID]) ? $productRatios[$productID] : 0;
            $sheetsUsed = ($ratio > 0) ? round($orderInfo['QuantityOrdered'] / $ratio) : 0;
            
            if ($sheetsUsed > 0) {
                // Begin transaction
                $pdo->beginTransaction();
                
                try {
                    // Return the materials to stock
                    $updateStmt = $pdo->prepare("UPDATE rawmaterials SET 
                                                QuantityInStock = QuantityInStock + :SheetCount 
                                                WHERE MaterialID = :MaterialID");
                    $updateStmt->execute([
                        ':SheetCount' => $sheetsUsed,
                        ':MaterialID' => $orderInfo['MaterialID']
                    ]);
                    
                    // Delete the order
                    $stmt = $pdo->prepare("DELETE FROM productionOrders WHERE OrderID = :OrderID");
                    $stmt->execute([':OrderID' => $orderID]);
                    
                    // Commit the transaction
                    $pdo->commit();
                    echo "<div class='alert'>Order deleted successfully and materials returned to inventory!</div>";
                } catch (Exception $e) {
                    // Roll back the transaction if something failed
                    $pdo->rollBack();
                    echo "<div class='alert alert-error'>Error: " . $e->getMessage() . "</div>";
                }
            } else {
                // Just delete the order with no inventory adjustment
                $stmt = $pdo->prepare("DELETE FROM productionOrders WHERE OrderID = :OrderID");
                $stmt->execute([':OrderID' => $orderID]);
                echo "<div class='alert'>Order deleted successfully!</div>";
            }
        } else {
            // Just delete the order with no inventory adjustment
            $stmt = $pdo->prepare("DELETE FROM productionOrders WHERE OrderID = :OrderID");
            $stmt->execute([':OrderID' => $orderID]);
            echo "<div class='alert'>Order deleted successfully!</div>";
        }
    }
}

// Fetch all orders with additional details, including raw material image
$orderStmt = $pdo->query("
    SELECT po.OrderID, p.ProductID, p.ProductName, rm.MaterialName, rm.raw_material_img, e.FirstName, e.LastName, 
           po.StartDate, po.EndDate, po.Status, po.QuantityOrdered, po.QuantityProduced
    FROM productionOrders po
    JOIN products p ON po.ProductID = p.ProductID
    JOIN rawmaterials rm ON p.MaterialID = rm.MaterialID
    JOIN employees e ON po.EmployeeID = e.EmployeeID
    ORDER BY po.StartDate DESC
");
$orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);

// Create a JavaScript-friendly version of the product ratios
$jsProductRatios = json_encode($productRatios);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raw Material Order Processing</title>
    <link rel="stylesheet" href="../assets/css/eminventory.css">
    <script>
        // Store product ratios for JavaScript use
        const productRatios = <?= $jsProductRatios ?>;
        
        // JavaScript function to filter table rows based on search input
        function filterTable() {
            const input = document.getElementById('search-bar').value.toLowerCase();
            const tableBody = document.getElementById('order-table-body');
            const rows = tableBody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let matchFound = false;

                for (let j = 0; j < cells.length - 1; j++) { // Exclude the last column (Actions)
                    if (cells[j].textContent.toLowerCase().includes(input)) {
                        matchFound = true;
                        break;
                    }
                }

                rows[i].style.display = matchFound ? '' : 'none';
            }
        }
        
        // JavaScript function to update the quantity based on product selection and sheet count
        function updateQuantity() {
            const productSelect = document.getElementById('ProductID');
            const productID = productSelect.value;
            const productName = productSelect.options[productSelect.selectedIndex].text.split(' (')[0].trim();
            const sheetCount = parseInt(document.getElementById('SheetCount').value) || 1;
            
            // Get the ratio for this product
            const ratio = productRatios[productID] || 0;
            
            // Calculate total quantity
            const totalQuantity = ratio * sheetCount;
            
            // Update the readonly quantity display
            document.getElementById('QuantityDisplay').value = totalQuantity;
            document.getElementById('QuantityOrdered').value = totalQuantity;
            
            // Update the material info display
            if (ratio > 0) {
                document.getElementById('material-info').textContent = 
                    `Each sheet produces ${ratio} units of ${productName}. Total: ${totalQuantity} units.`;
                document.getElementById('material-info').style.color = '#008800';
            } else {
                document.getElementById('material-info').textContent = 
                    `Unknown product ratio. Please contact administrator.`;
                document.getElementById('material-info').style.color = '#FF0000';
            }
            
            // Display current stock information
            const stockInfo = document.getElementById('stock-info');
            const selectedIndex = productSelect.selectedIndex;
            
            if (selectedIndex > 0) {
                const stockText = productSelect.options[selectedIndex].getAttribute('data-stock');
                stockInfo.textContent = `Available stock: ${stockText} sheets`;
                
                // Change color based on if there's enough stock
                if (parseInt(stockText) < sheetCount) {
                    stockInfo.style.color = '#FF0000';
                } else {
                    stockInfo.style.color = '#008800';
                }
            } else {
                stockInfo.textContent = '';
            }
        }
    </script>
</head>
<body>
    <!-- Container -->
    <div class="container">
        <?php renderSidebar('rawMaterialOrder'); // Note different active page ?>

        <div class="main-content">
            <?php renderHeader('Raw Material Order Processing'); ?>
            <!-- Search Bar -->
            <input type="text" id="search-bar" class="search-bar" placeholder="Search orders..." onkeyup="filterTable()">
            
            <!-- Product ratios reference table -->
            <section class="card">
                <h2 class="card-header">Material to Product Ratios</h2>
                <div style="padding: 15px;">
                    <p><strong>Tinplate Sheet Products:</strong> Food Can (192), Biscuit tin (115), Paint can (82), Baking mold (96)</p>
                    <p><strong>Steel Products:</strong> Oil drum (3), Fuel tank (2), CoinBank/Safe (11)</p>
                    <p><strong>Aluminum Sheet Products:</strong> Beverage can (823), Food tray (640), Aerosol can (576)</p>
                    <p><strong>Stainless Steel Products:</strong> Storage bin (6)</p>
                </div>
            </section>
            
            <!-- Materials Inventory Status -->
            <section class="card">
                <h2 class="card-header">Current Raw Material Inventory</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Quantity in Stock (Sheets)</th>
                            <th>Minimum Stock Level</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $materialStmt = $pdo->query("SELECT MaterialName, QuantityInStock, MinimumStock FROM rawmaterials");
                        $materials = $materialStmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($materials as $material):
                            $stockStatus = $material['QuantityInStock'] <= $material['MinimumStock'] ? 'Low' : 'Good';
                            $statusColor = $stockStatus == 'Low' ? '#FF0000' : '#008800';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($material['MaterialName']) ?></td>
                            <td><?= htmlspecialchars($material['QuantityInStock']) ?></td>
                            <td><?= htmlspecialchars($material['MinimumStock']) ?></td>
                            <td style="color: <?= $statusColor ?>;"><?= $stockStatus ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
            
            <!-- Form to create a new order -->
            <section class="card">
                <h2 class="card-header">Create New Order</h2>
                <form method="POST" action="">
                    <label for="ProductID">Select Product:</label>
                    <select id="ProductID" name="ProductID" required class="search-bar" onchange="updateQuantity()">
                        <option value="">-- Select a Product --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= htmlspecialchars($product['ProductID']) ?>" 
                                    data-stock="<?= htmlspecialchars($product['QuantityInStock']) ?>">
                                <?= htmlspecialchars($product['ProductName']) ?> (Material: <?= htmlspecialchars($product['MaterialName']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p id="stock-info" style="margin-top: 5px;"></p>
                    
                    <label for="SheetCount">Number of Material Sheets:</label>
                    <input type="number" id="SheetCount" name="SheetCount" min="1" value="1" required class="search-bar" onchange="updateQuantity()">
                    
                    <label for="QuantityDisplay">Quantity to be Ordered:</label>
                    <input type="text" id="QuantityDisplay" readonly class="search-bar">
                    <input type="hidden" id="QuantityOrdered" name="QuantityOrdered">
                    <p id="material-info" style="font-style: italic; margin-top: 5px;"></p>
                    
                    <label for="EndDate">Expected End Date:</label>
                    <input type="date" id="EndDate" name="EndDate" required class="search-bar">
                    
                    <button type="submit" name="create_order" class="btn">Create Order</button>
                </form>
            </section>
            
            <!-- Display all orders -->
            <section class="card">
                <h2 class="card-header">Current Orders</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product to be Produced</th>
                            <th>Ordered Material</th>
                            <th>Ordered By</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Quantity Ordered</th>
                            <th>Quantity Produced</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="order-table-body">
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['OrderID']) ?></td>
                                <td><?= htmlspecialchars($order['ProductName']) ?></td>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <img src="../assets/imgs/<?= htmlspecialchars($order['raw_material_img']) ?>" alt="Material Image" style="width: 50px; height: 50px; margin-right: 10px;">
                                        <span><?= htmlspecialchars($order['MaterialName']) ?></span>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($order['FirstName'] . ' ' . $order['LastName']) ?></td>
                                <td><?= htmlspecialchars($order['StartDate']) ?></td>
                                <td><?= htmlspecialchars($order['EndDate']) ?></td>
                                <td><?= htmlspecialchars($order['Status']) ?></td>
                                <td>
                                    <?= htmlspecialchars($order['QuantityOrdered']) ?>
                                    <?php
                                    // Calculate how many sheets this represents
                                    $productID = $order['ProductID'];
                                    $ratio = isset($productRatios[$productID]) ? $productRatios[$productID] : 0;
                                    if ($ratio > 0) {
                                        $sheets = $order['QuantityOrdered'] / $ratio;
                                        echo " <span style='color:#666;'>($sheets sheets)</span>";
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($order['QuantityProduced']) ?></td>
                                <td>
                                    <!-- Update status form -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="OrderID" value="<?= $order['OrderID'] ?>">
                                        <select name="Status" class="search-bar">
                                            <option value="In Progress" <?= $order['Status'] === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="Completed" <?= $order['Status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="Cancelled" <?= $order['Status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn">Update</button>
                                    </form>
                                    <!-- Delete order form -->
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="OrderID" value="<?= $order['OrderID'] ?>">
                                        <button type="submit" name="delete_order" onclick="return confirm('Are you sure you want to delete this order?')" class="btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </div>
</body>
</html>