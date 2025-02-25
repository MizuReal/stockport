<?php
function renderSidebar($activePage = '') {
?>
    <div class="sidebar">
        <div class="logo">WMS Dashboard</div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="overview.php" class="nav-link <?php echo ($activePage === 'overview') ? 'active' : ''; ?>">
                    Overview
                </a>
            </li>
            <li class="nav-item">
                <a href="inventory.php" class="nav-link <?php echo ($activePage === 'inventory') ? 'active' : ''; ?>">
                    Inventory Management
                </a>
            </li>
            <li class="nav-item">
                <a href="rawMaterialOrder.php" class="nav-link <?php echo ($activePage === 'rawMaterialOrder') ? 'active' : ''; ?>">
                    Raw Material Processing
                </a>
            </li>
            <li class="nav-item">
                <a href="rawMaterialTracker.php" class="nav-link <?php echo ($activePage === 'rawMaterialTracker') ? 'active' : ''; ?>">
                    Raw Material Tracker
                </a>
            </li>
            <li class="nav-item">
                <a href="CustomerOrder.php" class="nav-link <?php echo ($activePage === 'customerOrder') ? 'active' : ''; ?>">
                    Request Customer Order
                </a>
            </li>
            <li class="nav-item">
                <a href="Customer Order Tracker.php" class="nav-link <?php echo ($activePage === 'customerOrderTracker') ? 'active' : ''; ?>">
                    Customer Order Tracker
                </a>
            </li>
            <li class="nav-item">
                <a href="DeliverProcessedProduct.php" class="nav-link <?php echo ($activePage === 'DeliverProcessedProduct.php') ? 'active' : ''; ?>">
                    Deliver Processed Product
                </a>
            </li>
            <li class="nav-item">
                <a href="warehouse.php" class="nav-link <?php echo ($activePage === 'warehouse') ? 'active' : ''; ?>">
                    Warehouse Operations
                </a>
            </li>
            <li class="nav-item">
                <a href="reports_analytics.php" class="nav-link <?php echo ($activePage === 'reports_analytics') ? 'active' : ''; ?>">
                    Reports & Analytics
                </a>
            </li>
            <li class="nav-item">
                <a href="employee_profile.php" class="nav-link <?php echo ($activePage === 'employee_profile') ? 'active' : ''; ?>">
                    Employee Profile
                </a>
            </li>
            <li class="nav-item">
                <a href="email_support_form.php" class="nav-link <?php echo ($activePage === 'email_support_form') ? 'active' : ''; ?>">
                    Email Support
                </a>
            </li>
        </ul>
    </div>
<?php
}
?>