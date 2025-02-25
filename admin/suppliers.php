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
                $contact = $_POST['contact'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
                $address = $_POST['address'];

                // Validate inputs (example - add more validation as needed)
                if (empty($name) || empty($contact) || empty($phone) || empty($email) || empty($address)) {
                    echo "<script>alert('All fields are required for adding a supplier.');</script>";
                    break; // Exit the switch case
                }

                $sql = "INSERT INTO suppliers (SupplierName, ContactPerson, Phone, Email, Address) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    error_log("Error preparing statement (add): " . $conn->error);
                    echo "<script>alert('Error preparing statement: " . htmlspecialchars($conn->error) . "');</script>";
                    break; // Exit the switch case
                }

                $stmt->bind_param("sssss", $name, $contact, $phone, $email, $address);

                if ($stmt->execute()) {
                    echo "<script>alert('New supplier added successfully'); window.location.href = 'suppliers.php';</script>";
                } else {
                    error_log("Error executing statement (add): " . $stmt->error);
                    echo "<script>alert('Error adding supplier: " . htmlspecialchars($stmt->error) . "');</script>";
                }

                $stmt->close();
                break;

            case 'edit':
                $id = $_POST['supplierid'];
                $name = $_POST['name'];
                $contact = $_POST['contact'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
                $address = $_POST['address'];

                // Validate inputs (example - add more validation as needed)
                if (empty($id) || empty($name) || empty($contact) || empty($phone) || empty($email) || empty($address)) {
                    echo "<script>alert('All fields are required for editing a supplier.');</script>";
                    break; // Exit the switch case
                }

                $sql = "UPDATE suppliers SET SupplierName=?, ContactPerson=?, Phone=?, Email=?, Address=? WHERE SupplierID=?";
                $stmt = $conn->prepare($sql);

                if ($stmt === false) {
                    error_log("Error preparing statement (edit): " . $conn->error);
                    echo "<script>alert('Error preparing statement: " . htmlspecialchars($conn->error) . "');</script>";
                    break; // Exit the switch case
                }

                $stmt->bind_param("sssssi", $name, $contact, $phone, $email, $address, $id);

                if ($stmt->execute()) {
                    echo "<script>alert('Supplier updated successfully'); window.location.href = 'suppliers.php';</script>";
                } else {
                    error_log("Error executing statement (edit): " . $stmt->error);
                    echo "<script>alert('Error updating supplier: " . htmlspecialchars($stmt->error) . "');</script>";
                }

                $stmt->close();
                break;

            case 'delete':
                if (isset($_POST['supplierid']) && !empty($_POST['supplierid'])) {
                    $id = $_POST['supplierid'];

                    $sql = "DELETE FROM suppliers WHERE SupplierID=?";
                    $stmt = $conn->prepare($sql);

                    if ($stmt === false) {
                        error_log("Error preparing statement (delete): " . $conn->error);
                        echo "<script>alert('Error preparing statement: " . htmlspecialchars($conn->error) . "');</script>";
                        break; // Exit the switch case
                    }

                    $stmt->bind_param("i", $id);

                    if ($stmt->execute()) {
                        echo "<script>alert('Supplier deleted successfully'); window.location.href = 'suppliers.php';</script>";
                    } else {
                        error_log("Error executing statement (delete): " . $stmt->error);
                        echo "<script>alert('Error deleting supplier: " . htmlspecialchars($stmt->error) . "');</script>";
                    }

                    $stmt->close();
                } else {
                    echo "<script>alert('Supplier ID not provided for deletion.');</script>";
                }
                break;
        }
    }
}

// Retrieve all suppliers
$sql = "SELECT * FROM suppliers";
$result = $conn->query($sql);
$suppliers = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $suppliers[] = $row;
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
    <title>Supplier Management - Warehouse System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/supplier.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../layouts/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Supplier Management</h1>
            </header>

            <div class="content">
                <button id="btnAddSupplier" class="btn-add-supplier">
                    <i class="fas fa-plus"></i> Add New Supplier
                </button>

                <div class="supplier-table-container">
                    <table class="supplier-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Supplier Name</th>
                                <th>Contact Person</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['SupplierID']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['SupplierName']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['ContactPerson']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['Phone']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['Email']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['Address']); ?></td>
                                <td>
                                    <button class="btn-edit" data-supplierid="<?php echo htmlspecialchars($supplier['SupplierID']); ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn-delete" data-supplierid="<?php echo htmlspecialchars($supplier['SupplierID']); ?>">
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

    <!-- Add/Edit Supplier Modal -->
    <div id="supplierModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Add New Supplier</h2>
            <form id="supplierForm" class="supplier-form" method="post" action="suppliers.php">
                <input type="hidden" id="supplierId" name="supplierid" value="">
                <input type="hidden" id="formAction" name="action" value="add">

                <div class="form-group">
                    <label for="name">Supplier Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="contact">Contact Person</label>
                    <input type="text" id="contact" name="contact" required>
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
            <p style="color: black;">Are you sure you want to delete this supplier? This action cannot be undone.</p>
            <form method="post" action="suppliers.php" id="deleteForm">
                <input type="hidden" id="deleteId" name="supplierid" value="">
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
            const supplierModal = document.getElementById('supplierModal');
            const editButtons = document.querySelectorAll('.btn-edit');

            //DELETE
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevent default form submission

                    const supplierId = this.dataset.supplierid;
                    console.log("Deleting supplier ID:", supplierId); // Debugging

                    // Set value of hidden input field
                    document.getElementById('deleteId').value = supplierId;

                    // Show the delete confirmation modal
                    deleteModal.style.display = 'block';
                });
            });

            // EDIT/CREATE
            editButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    document.getElementById('modalTitle').textContent = 'Edit Supplier';
                    document.getElementById('formAction').value = 'edit';

                    const supplierId = this.dataset.supplierid;
                    document.getElementById('supplierId').value = supplierId;

                     // Fetch supplier data based on ID from our data array
                     const supplier = <?php echo json_encode($suppliers); ?>.find(s => s.SupplierID == supplierId);

                     // Populate the modal's fields with the supplier data
                     document.getElementById('name').value = supplier.SupplierName;
                     document.getElementById('contact').value = supplier.ContactPerson;
                     document.getElementById('phone').value = supplier.Phone;
                     document.getElementById('email').value = supplier.Email;
                     document.getElementById('address').value = supplier.Address;

                     supplierModal.style.display = 'block';
                });
            });

             // Add new supplier button
            document.getElementById('btnAddSupplier').addEventListener('click', function() {
                document.getElementById('modalTitle').textContent = 'Add New Supplier';
                document.getElementById('formAction').value = 'add';
                document.getElementById('supplierId').value = '';
                document.getElementById('supplierForm').reset();
                supplierModal.style.display = 'block';
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
                    supplierModal.style.display = 'none';
                });
            });

            // Close modal when clicking outside
            window.addEventListener('click', (event) => {
                if (event.target === deleteModal || event.target === supplierModal) {
                    deleteModal.style.display = 'none';
                    supplierModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
