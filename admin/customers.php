<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stockport";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debug: Log the entire $_POST array
    error_log("Received POST data: " . print_r($_POST, true));

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
                $address = $_POST['address'];

                // Validate inputs (example - add more validation as needed)
                if (empty($name) || empty($phone) || empty($email) || empty($address)) {
                    echo "<script>alert('All fields are required for adding a customer.');</script>";
                    break; // Exit the switch case
                }

                $sql = "INSERT INTO customers (CustomerName, Phone, Email, Address) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    error_log("Error preparing statement (add): " . $conn->error);
                    echo "<script>alert('Error preparing statement: " . htmlspecialchars($conn->error) . "');</script>";
                    break; // Exit the switch case
                }

                $stmt->bind_param("ssss", $name, $phone, $email, $address);

                if ($stmt->execute()) {
                    echo "<script>alert('New customer added successfully'); window.location.href = 'customers.php';</script>";
                } else {
                    error_log("Error executing statement (add): " . $stmt->error);
                    echo "<script>alert('Error adding customer: " . htmlspecialchars($stmt->error) . "');</script>";
                }

                $stmt->close();
                break;

            case 'edit':
                $id = $_POST['customerid'];
                $name = $_POST['name'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
                $address = $_POST['address'];

                // Validate inputs (example - add more validation as needed)
                if (empty($id) || empty($name) || empty($phone) || empty($email) || empty($address)) {
                    echo "<script>alert('All fields are required for editing a customer.');</script>";
                    break; // Exit the switch case
                }

                $sql = "UPDATE customers SET CustomerName=?, Phone=?, Email=?, Address=? WHERE CustomerID=?";
                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    error_log("Error preparing statement (edit): " . $conn->error);
                    echo "<script>alert('Error preparing statement: " . htmlspecialchars($conn->error) . "');</script>";
                    break; // Exit the switch case
                }

                $stmt->bind_param("ssssi", $name, $phone, $email, $address, $id);

                if ($stmt->execute()) {
                    echo "<script>alert('Customer updated successfully'); window.location.href = 'customers.php';</script>";
                } else {
                    error_log("Error executing statement (edit): " . $stmt->error);
                    echo "<script>alert('Error updating customer: " . htmlspecialchars($stmt->error) . "');</script>";
                }

                $stmt->close();
                break;

            case 'delete':
                if (isset($_POST['customerid']) && !empty($_POST['customerid'])) {
                    $id = $_POST['customerid'];

                    $sql = "DELETE FROM customers WHERE CustomerID=?";
                    $stmt = $conn->prepare($sql);

                    if ($stmt === false) {
                        error_log("Error preparing statement (delete): " . $conn->error);
                        echo "<script>alert('Error preparing statement: " . htmlspecialchars($conn->error) . "');</script>";
                        break; // Exit the switch case
                    }

                    $stmt->bind_param("i", $id);

                    if ($stmt->execute()) {
                        echo "<script>alert('Customer deleted successfully'); window.location.href = 'customers.php';</script>";
                    } else {
                        error_log("Error executing statement (delete): " . $stmt->error);
                        echo "<script>alert('Error deleting customer: " . htmlspecialchars($stmt->error) . "');</script>";
                    }

                    $stmt->close();
                } else {
                    echo "<script>alert('Customer ID not provided for deletion.');</script>";
                }
                break;
        }
    }
}

$sql = "SELECT * FROM customers";
$result = $conn->query($sql);
$customers = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Warehouse System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../layouts/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Customer Management</h1>
            </header>

            <div class="content">
                <button id="btnAddCustomer" class="btn-add-Customer">
                    <i class="fas fa-plus"></i> Add New Customer
                </button>

                <div class="Customer-table-container">
                    <table class="customer-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['CustomerID']); ?></td>
                                <td><?php echo htmlspecialchars($customer['CustomerName']); ?></td>
                                <td><?php echo htmlspecialchars($customer['Phone']); ?></td>
                                <td><?php echo htmlspecialchars($customer['Email']); ?></td>
                                <td><?php echo htmlspecialchars($customer['Address']); ?></td>
                                <td>
                                    <button class="btn-edit" data-customerid="<?php echo htmlspecialchars($customer['CustomerID']); ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn-delete" data-customerid="<?php echo htmlspecialchars($customer['CustomerID']); ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="customerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Add New Customer</h2>
            <form id="customerForm" class="customer-form" method="post" action="customers.php">
                <input type="hidden" id="customerId" name="customerid" value="">
                <input type="hidden" id="formAction" name="action" value="add">

                <div class="form-group">
                    <label for="name">Customer Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Submit</button>
                    <button type="button" class="btn-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 style="color: black;">Confirm Deletion</h2>
            <p style="color: black;">Are you sure you want to delete this customer? This action cannot be undone.</p>
            <form method="post" action="customers.php" id="deleteForm">
                <input type="hidden" id="deleteId" name="customerid" value="">
                <input type="hidden" name="action" value="delete">
                <div class="form-actions">
                    <button type="submit" class="btn-delete-confirm">Delete</button>
                    <button type="button" class="btn-cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize delete modal and buttons *after* the DOM is fully loaded
            const deleteModal = document.getElementById('deleteModal');
            const deleteButtons = document.querySelectorAll('.btn-delete');
            const customerModal = document.getElementById('customerModal');
            const editButtons = document.querySelectorAll('.btn-edit');

            //DELETE
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent default form submission

                    const customerId = this.dataset.customerid;
                    console.log("Deleting customer ID:", customerId); // Debugging

                    // Set value of hidden input field
                    document.getElementById('deleteId').value = customerId;

                    // Show the delete confirmation modal
                    deleteModal.style.display = 'block';
                });
            });

            // EDIT/CREATE
            editButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    document.getElementById('modalTitle').textContent = 'Edit Customer';
                    document.getElementById('formAction').value = 'edit';

                    const customerId = this.dataset.customerid;
                    document.getElementById('customerId').value = customerId;

                    const customer = <?php echo json_encode($customers); ?>.find(s => s.CustomerID == customerId);

                    document.getElementById('name').value = customer.CustomerName;
                    document.getElementById('phone').value = customer.Phone;
                    document.getElementById('email').value = customer.Email;
                    document.getElementById('address').value = customer.Address;

                    customerModal.style.display = 'block';
                });
            });

            document.getElementById('btnAddCustomer').addEventListener('click', function() {
                document.getElementById('modalTitle').textContent = 'Add New Customer';
                document.getElementById('formAction').value = 'add';
                document.getElementById('customerId').value = '';
                document.getElementById('customerForm').reset();
                customerModal.style.display = 'block';
            });

            // Handle the delete confirmation button click
            document.querySelector('.btn-delete-confirm').addEventListener('click', function(event) {
                event.preventDefault();

                // Submit the form programmatically
                document.getElementById('deleteForm').submit();
            });

            // Close the modal
            document.querySelectorAll('.close, .btn-cancel').forEach(closeBtn => {
                closeBtn.addEventListener('click', () => {
                    deleteModal.style.display = 'none';
                    customerModal.style.display = 'none';
                });
            });

            // Close modal when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target === deleteModal || event.target === customerModal) {
                    deleteModal.style.display = 'none';
                    customerModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>