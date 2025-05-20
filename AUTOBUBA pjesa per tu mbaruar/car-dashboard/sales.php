<?php
// Enable strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

// Initialize messages
$success_message = '';
$error_message = '';

// Handle Add Sale submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sale'])) {
    try {
        // Validate required fields
        $required = ['vehicle_id', 'customer_id', 'sale_date', 'sale_price'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        $data = [
            'vehicle_id' => (int)$_POST['vehicle_id'],
            'customer_id' => (int)$_POST['customer_id'],
            'sale_date' => $_POST['sale_date'],
            'sale_price' => (float)$_POST['sale_price'],
            'payment_method' => filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING),
            'city' => filter_var($_POST['city'] ?? '', FILTER_SANITIZE_STRING),
            'state' => filter_var($_POST['state'] ?? '', FILTER_SANITIZE_STRING),
            'zip' => filter_var($_POST['zip'] ?? '', FILTER_SANITIZE_STRING)
        ];

        $pdo->beginTransaction();

        // Insert new sale
        $stmt = $pdo->prepare("INSERT INTO sales 
            (vehicle_id, customer_id, sale_date, sale_price, payment_method, city, state, zip)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['vehicle_id'],
            $data['customer_id'],
            $data['sale_date'],
            $data['sale_price'],
            $data['payment_method'],
            $data['city'],
            $data['state'],
            $data['zip']
        ]);

        // Update vehicle status to 'sold'
        $pdo->prepare("UPDATE vehicles SET status = 'sold' WHERE id = ?")
            ->execute([$data['vehicle_id']]);

        $pdo->commit();

        // Redirect to prevent form resubmission
        $_SESSION['success_message'] = 'Sale added successfully!';
        header('Location: sales.php');
        exit;

    } catch (Throwable $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = 'Error adding sale: ' . $e->getMessage();
        header('Location: sales.php');
        exit;
    }
}

// Handle AJAX requests first
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_sale'])) {
    try {
        // Validate and sanitize input
        $required = ['sale_id', 'vehicle_id', 'customer_id', 'sale_date'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        $data = [
            'sale_id' => (int)$_POST['sale_id'],
            'vehicle_id' => (int)$_POST['vehicle_id'],
            'customer_id' => (int)$_POST['customer_id'],
            'sale_date' => $_POST['sale_date'],
            'sale_price' => (float)$_POST['sale_price'],
            'payment_method' => filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING),
            'city' => filter_var($_POST['city'] ?? '', FILTER_SANITIZE_STRING),
            'state' => filter_var($_POST['state'] ?? '', FILTER_SANITIZE_STRING),
            'zip' => filter_var($_POST['zip'] ?? '', FILTER_SANITIZE_STRING)
        ];

        $pdo->beginTransaction();

        // Get original vehicle
        $stmt = $pdo->prepare("SELECT vehicle_id FROM sales WHERE id = ?");
        $stmt->execute([$data['sale_id']]);
        $original_vehicle_id = $stmt->fetchColumn();

        // Update sale
        $stmt = $pdo->prepare("UPDATE sales SET
            vehicle_id = ?,
            customer_id = ?,
            sale_date = ?,
            sale_price = ?,
            payment_method = ?,
            city = ?,
            state = ?,
            zip = ?
            WHERE id = ?");
        $stmt->execute([
            $data['vehicle_id'],
            $data['customer_id'],
            $data['sale_date'],
            $data['sale_price'],
            $data['payment_method'],
            $data['city'],
            $data['state'],
            $data['zip'],
            $data['sale_id']
        ]);

        // Update vehicle statuses
        if ($original_vehicle_id != $data['vehicle_id']) {
            $pdo->prepare("UPDATE vehicles SET status = 'available' WHERE id = ?")
                ->execute([$original_vehicle_id]);
            $pdo->prepare("UPDATE vehicles SET status = 'sold' WHERE id = ?")
                ->execute([$data['vehicle_id']]);
        }

        $pdo->commit();

        // Get updated sale data
        $stmt = $pdo->prepare("
            SELECT s.*, v.make, v.model, c.name AS customer_name 
            FROM sales s
            JOIN vehicles v ON s.vehicle_id = v.id
            JOIN customers c ON s.customer_id = c.id
            WHERE s.id = ?
        ");
        $stmt->execute([$data['sale_id']]);
        $updatedSale = $stmt->fetch(PDO::FETCH_ASSOC);

        // Send clean JSON response
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'sale' => $updatedSale,
            'vehicle' => $updatedSale['make'] . ' ' . $updatedSale['model']
        ]);
        exit;

    } catch (Throwable $e) {
        $pdo->rollBack();
        ob_end_clean();
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Server error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Handle regular page load
try {
    // Fetch vehicles (corrected query)
    $vehicles = $pdo->query("
        SELECT id, CONCAT(make, ' ', model) AS name, status 
        FROM vehicles 
        WHERE status != 'sold'
    ")->fetchAll();

    // Show sales from the last 30 days (adjust interval as needed)
    $sales = $pdo->query("
        SELECT s.*, v.make, v.model, c.name AS customer_name 
        FROM sales s
        JOIN vehicles v ON s.vehicle_id = v.id
        JOIN customers c ON s.customer_id = c.id
        WHERE s.sale_date >= CURDATE() - INTERVAL 30 DAY
        ORDER BY s.sale_date DESC
    ")->fetchAll();

    // Fetch vehicles and customers
    $vehicles = $pdo->query("
        SELECT id, CONCAT(make, ' ', model) AS name, status 
        FROM vehicles 
        WHERE status != 'sold' OR id IN (SELECT vehicle_id FROM sales)
    ")->fetchAll();

    $customers = $pdo->query("SELECT id, name FROM customers")->fetchAll();

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "Unable to load data. Please try again later.";
}

// Clean output buffer before HTML
ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Management | <?= htmlspecialchars(SITE_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body class="bg-light">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content" style="margin-left: 250px; padding: 20px;">
        <?php include 'includes/topnav.php'; ?>

        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-danger"><i class="fas fa-receipt me-2"></i> Sales Records</h1>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                    <i class="fas fa-plus me-2"></i> Add Sale
                </button>
            </div>

           <!-- Messages -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['success_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_SESSION['error_message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <!-- Sales Table -->
            <div class="card shadow">
                <div class="card-body">
                    <?php if (empty($sales)): ?>
                        <div class="alert alert-info mb-0">No sales records found.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-danger">
                                    <tr>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Vehicle</th>
                                        <th>City</th>
                                        <th>State</th>
                                        <th>Zip</th>
                                        <th>Sale Price</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sales as $sale): ?>
                                    <tr data-id="<?= $sale['id'] ?>"> 
                                        <td><?= htmlspecialchars(date('M j, Y', strtotime($sale['sale_date']))) ?></td>
                                        <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($sale['make'] . ' ' . $sale['model']) ?></td>
                                        <td><?= htmlspecialchars($sale['city'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($sale['state'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($sale['zip'] ?? 'N/A') ?></td>
                                        <td>$<?= number_format($sale['sale_price'], 2) ?></td>
                                        <td><?= ucfirst(htmlspecialchars($sale['payment_method'])) ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="view-sale.php?id=<?= $sale['id'] ?>" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-sale-btn"
                                                    data-id="<?= $sale['id'] ?>"
                                                    data-customer-id="<?= $sale['customer_id'] ?>"
                                                    data-vehicle-id="<?= $sale['vehicle_id'] ?>"
                                                    data-sale-date="<?= htmlspecialchars($sale['sale_date']) ?>"
                                                    data-sale-price="<?= $sale['sale_price'] ?>"
                                                    data-payment-method="<?= htmlspecialchars($sale['payment_method'] ?? '') ?>"
                                                    data-city="<?= htmlspecialchars($sale['city'] ?? '') ?>"
                                                    data-state="<?= htmlspecialchars($sale['state'] ?? '') ?>"
                                                    data-zip="<?= htmlspecialchars($sale['zip'] ?? '') ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="invoice.php?id=<?= $sale['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
                <div class="mt-3 text-center">
                    <a href="all_sales.php" class="btn btn-outline-danger">View All Sales This Year</a>
                </div>
        </div>

        <!-- Add Sale Modal -->
        <div class="modal fade" id="addSaleModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title"><i class="fas fa-plus me-2"></i>New Sale</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Customer</label>
                                        <select class="form-select" name="customer_id" required>
                                        <?php if (empty($customers)): ?>
                                            <option value="" disabled>No customers found</option>
                                        <?php else: ?>
                                            <option value="">Select Customer</option>
                                            <?php foreach ($customers as $c): ?>
                                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Vehicle</label>
                                    <select class="form-select" name="vehicle_id" required>
                                        <?php if (empty($vehicles)): ?>
                                            <option value="" disabled>No available vehicles</option>
                                        <?php else: ?>
                                            <option value="">Select Vehicle</option>
                                            <?php foreach ($vehicles as $vehicle): ?>
                                                <option value="<?= $vehicle['id'] ?>" <?= $vehicle['status'] === 'sold' ? 'disabled' : '' ?>>
                                                    <?= htmlspecialchars($vehicle['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Sale Date</label>
                                    <input type="date" class="form-control" name="sale_date" 
                                           value="<?= date('Y-m-d') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Sale Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control" 
                                               name="sale_price" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Zip</label>
                                    <input type="text" class="form-control" name="zip" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Payment Method</label>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="cash">Cash</option>
                                        <option value="credit">Credit Card</option>
                                        <option value="finance">Bank Finance</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="add_sale" class="btn btn-danger">
                                <i class="fas fa-save me-2"></i>Save Sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Sale Modal -->
        <div class="modal fade" id="editSaleModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Sale</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="sale_id" id="edit_sale_id">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Customer</label>
                                    <select class="form-select" name="customer_id" id="edit_customer_id" required>
                                        <?php foreach ($customers as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Vehicle</label>
                                    <select class="form-select" name="vehicle_id" id="edit_vehicle_id" required>
                                        <?php foreach ($vehicles as $v): ?>
                                            <option value="<?= $v['id'] ?>" <?= $v['status'] === 'sold' ? 'data-sold="true"' : '' ?>>
                                                <?= htmlspecialchars($v['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Sale Date</label>
                                    <input type="date" class="form-control" name="sale_date" id="edit_sale_date" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Sale Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control" 
                                            name="sale_price" id="edit_sale_price" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" id="edit_city">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state" id="edit_state">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Zip</label>
                                    <input type="text" class="form-control" name="zip" id="edit_zip">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Payment Method</label>
                                    <select class="form-select" name="payment_method" id="edit_payment_method" required>
                                        <option value="cash">Cash</option>
                                        <option value="credit">Credit Card</option>
                                        <option value="finance">Bank Finance</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" name="edit_sale" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Sale
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = new bootstrap.Modal('#editSaleModal');

        // Edit button handlers
        document.querySelectorAll('.edit-sale-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Populate modal fields
                document.getElementById('edit_sale_id').value = this.dataset.id;
                document.getElementById('edit_customer_id').value = this.dataset.customerId;
                document.getElementById('edit_vehicle_id').value = this.dataset.vehicleId;
                document.getElementById('edit_sale_date').value = this.dataset.saleDate;
                document.getElementById('edit_sale_price').value = parseFloat(this.dataset.salePrice).toFixed(2);
                document.getElementById('edit_city').value = this.dataset.city;
                document.getElementById('edit_state').value = this.dataset.state;
                document.getElementById('edit_zip').value = this.dataset.zip;
                document.getElementById('edit_payment_method').value = this.dataset.paymentMethod;

                // Handle vehicle dropdown
                const vehicleSelect = document.getElementById('edit_vehicle_id');
                Array.from(vehicleSelect.options).forEach(option => {
                    if (option.value === this.dataset.vehicleId) {
                        option.selected = true;
                        option.disabled = false;
                    } else if (option.dataset.sold === 'true') {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });

                editModal.show();
            });
        });
    });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced AJAX handling
        document.querySelector('#editSaleModal form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            try {
                const response = await fetch('sales.php', {
                    method: 'POST',
                    body: new FormData(e.target),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                
                const data = await response.json();
                
                if (!data.success) throw new Error(data.error || 'Unknown error');
                
                // Update table row
                const row = document.querySelector(`tr[data-id="${data.sale.id}"]`);
                if (row) {
                    row.cells[0].textContent = new Date(data.sale.sale_date).toLocaleDateString('en-US', { 
                        month: 'short', day: 'numeric', year: 'numeric' 
                    });
                    row.cells[1].textContent = data.sale.customer_name;
                    row.cells[2].textContent = data.vehicle;
                    row.cells[3].textContent = data.sale.city || 'N/A';
                    row.cells[4].textContent = data.sale.state || 'N/A';
                    row.cells[5].textContent = data.sale.zip || 'N/A';
                    row.cells[6].textContent = `$${parseFloat(data.sale.sale_price).toFixed(2)}`;
                    row.cells[7].textContent = data.sale.payment_method.charAt(0).toUpperCase() + 
                                              data.sale.payment_method.slice(1);
                }
                
                bootstrap.Modal.getInstance(document.getElementById('editSaleModal')).hide();
                
            } catch (error) {
                console.error('Error:', error);
                alert(`Operation failed: ${error.message}`);
            }
        });
    });
    </script>
</body>
</html>