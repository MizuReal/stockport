<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Database connection
$host = "127.0.0.1";
$username = "root";
$password = "";
$database = "stockport";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    try {
        $stmt = $conn->prepare("UPDATE employees SET Status = ? WHERE EmployeeID = ?");
        $stmt->execute([$_POST['status'], $_POST['employee_id']]);
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch(PDOException $e) {
        echo "Error updating status: " . $e->getMessage();
    }
}

// Handle employee deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_employee'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM employees WHERE EmployeeID = ?");
        $stmt->execute([$_POST['employee_id']]);
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch(PDOException $e) {
        echo "Error deleting employee: " . $e->getMessage();
    }
}

// Handle employee update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_employee'])) {
    try {
        $stmt = $conn->prepare("UPDATE employees SET 
                                FirstName = ?, 
                                LastName = ?, 
                                employeeEmail = ?, 
                                Phone = ?, 
                                Role = ?, 
                                HireDate = ?, 
                                Status = ? 
                                WHERE EmployeeID = ?");
        
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['role'],
            $_POST['hire_date'],
            $_POST['status'],
            $_POST['employee_id']
        ]);
        
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch(PDOException $e) {
        echo "Error updating employee: " . $e->getMessage();
    }
}

// Fetch employee data for edit form
$edit_employee = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM employees WHERE EmployeeID = ?");
        $stmt->execute([$_GET['edit_id']]);
        $edit_employee = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error fetching employee data: " . $e->getMessage();
    }
}

// Fetch employees
try {
    $stmt = $conn->query("SELECT * FROM employees ORDER BY EmployeeID");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Status - Warehouse System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .status-select {
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .status-active {
            color: #4CAF50;
            font-weight: bold;
        }
        .status-inactive {
            color: #f44336;
            font-weight: bold;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-save {
            background-color: #4CAF50;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-save:hover {
            background-color: #45a049;
        }
        .status-form {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 60%;
            max-width: 700px;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn-container {
            margin-top: 20px;
            text-align: right;
        }
        .btn-update {
            background-color: #2196F3;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-update:hover {
            background-color: #0b7dda;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include '../layouts/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Employee Status</h1>
            </header>
            <div class="content">
                <div class="employee-status-table-container">
                    <table class="employee-status-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Hire Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($employees as $employee): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($employee['EmployeeID']); ?></td>
                                <td><?php echo htmlspecialchars($employee['FirstName'] . ' ' . $employee['LastName']); ?></td>
                                <td><?php echo htmlspecialchars($employee['employeeEmail']); ?></td>
                                <td><?php echo htmlspecialchars($employee['Phone']); ?></td>
                                <td><?php echo htmlspecialchars($employee['Role']); ?></td>
                                <td><?php echo htmlspecialchars($employee['HireDate']); ?></td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="employee_id" value="<?php echo $employee['EmployeeID']; ?>">
                                        <select name="status" class="status-select" 
                                                onchange="this.form.querySelector('.btn-save').style.display = 'inline-block';">
                                            <option value="Active" <?php echo ($employee['Status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>
                                                Active
                                            </option>
                                            <option value="Inactive" <?php echo ($employee['Status'] ?? 'Active') === 'Inactive' ? 'selected' : ''; ?>>
                                                Inactive
                                            </option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn-save" style="display: none;">
                                            <i class="fas fa-save"></i> Save
                                        </button>
                                    </form>
                                </td>
                                <td class="action-buttons">
                                    <button class="btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($employee)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                        <input type="hidden" name="employee_id" value="<?php echo $employee['EmployeeID']; ?>">
                                        <button type="submit" name="delete_employee" class="btn-delete">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Edit Employee</h2>
            <form id="editForm" method="POST">
                <input type="hidden" name="employee_id" id="edit_employee_id">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <input type="text" id="role" name="role" required>
                </div>
                <div class="form-group">
                    <label for="hire_date">Hire Date</label>
                    <input type="date" id="hire_date" name="hire_date" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="btn-container">
                    <button type="submit" name="edit_employee" class="btn-update">Update Employee</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        const modal = document.getElementById('editModal');
        const form = document.getElementById('editForm');
        
        function openEditModal(employee) {
            // Populate form fields
            document.getElementById('edit_employee_id').value = employee.EmployeeID;
            document.getElementById('first_name').value = employee.FirstName;
            document.getElementById('last_name').value = employee.LastName;
            document.getElementById('email').value = employee.employeeEmail;
            document.getElementById('phone').value = employee.Phone;
            document.getElementById('role').value = employee.Role;
            document.getElementById('hire_date').value = employee.HireDate;
            document.getElementById('status').value = employee.Status || 'Active';
            
            // Show modal
            modal.style.display = 'block';
        }
        
        function closeEditModal() {
            modal.style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>