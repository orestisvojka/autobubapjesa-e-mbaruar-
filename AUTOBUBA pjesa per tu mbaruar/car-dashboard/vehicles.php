<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

// Get current user data
$user = currentUser();

// Handle vehicle actions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_vehicle'])) {
        $make = trim($_POST['make']);
        $model = trim($_POST['model']);
        $year = intval($_POST['year']);
        $price = floatval($_POST['price']);
        $mileage = intval($_POST['mileage']);
        $color = trim($_POST['color']);
        $vin = trim($_POST['vin']);

        if (empty($make) || empty($model) || $year < 1900) {
            $error_message = "Please fill required fields (Make, Model, Year)";
        } else {
            $result = addVehicle([
                'make' => $make,
                'model' => $model,
                'year' => $year,
                'price' => $price,
                'mileage' => $mileage,
                'color' => $color,
                'vin' => $vin
            ]);
            
            if ($result['success']) {
                $success_message = "Vehicle added successfully!";
            } else {
                $error_message = $result['message'];
            }
        }
    } elseif (isset($_POST['update_vehicle'])) {
        $id = intval($_POST['vehicle_id']);
        $make = trim($_POST['make']);
        $model = trim($_POST['model']);
        $year = intval($_POST['year']);
        
        if (empty($make) || empty($model) || $year < 1900) {
            $error_message = "Please fill required fields (Make, Model, Year)";
        } else {
            $result = updateVehicle($id, [
                'make' => $make,
                'model' => $model,
                'year' => $year,
                'price' => floatval($_POST['price']),
                'mileage' => intval($_POST['mileage']),
                'color' => trim($_POST['color']),
                'vin' => trim($_POST['vin'])
            ]);
            
            if ($result['success']) {
                $success_message = "Vehicle updated successfully!";
            } else {
                $error_message = $result['message'];
            }
        }
    } elseif (isset($_POST['delete_vehicle'])) {
        $id = intval($_POST['vehicle_id']);
        $result = deleteVehicle($id);
        
        if ($result['success']) {
            $success_message = "Vehicle deleted successfully!";
        } else {
            $error_message = $result['message'];
        }
    }
}

// vehicles.php functions
function getVehicles() {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM vehicles ORDER BY year DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function validateVehicleData($data) {
    $errors = [];

    // Required fields
    if (empty(trim($data['make']))) {
        $errors[] = "Make is required";
    }
    if (empty(trim($data['model']))) {
        $errors[] = "Model is required";
    }
    if (empty($data['year']) || $data['year'] < 1900 || $data['year'] > date('Y') + 1) {
        $errors[] = "Valid year is required";
    }

    // Optional fields validation
    if (!empty($data['price']) && !is_numeric($data['price'])) {
        $errors[] = "Price must be a number";
    }
    if (!empty($data['mileage']) && !is_numeric($data['mileage'])) {
        $errors[] = "Mileage must be a number";
    }

    return $errors;
}

function addVehicle($data) {
    $validationErrors = validateVehicleData($data);
    if (!empty($validationErrors)) {
        return ['success' => false, 'message' => implode(', ', $validationErrors)];
    }

    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO vehicles 
            (make, model, year, price, mileage, color, vin)
            VALUES (:make, :model, :year, :price, :mileage, :color, :vin)");

        $stmt->execute([
            ':make'    => htmlspecialchars(trim($data['make'])),
            ':model'   => htmlspecialchars(trim($data['model'])),
            ':year'    => intval($data['year']),
            ':price'   => !empty($data['price']) ? floatval($data['price']) : null,
            ':mileage' => !empty($data['mileage']) ? intval($data['mileage']) : null,
            ':color'  => !empty($data['color']) ? htmlspecialchars(trim($data['color'])) : null,
            ':vin'     => !empty($data['vin']) ? htmlspecialchars(trim($data['vin'])) : null
        ]);

        return ['success' => true, 'vehicle_id' => $pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to add vehicle'];
    }
}

function updateVehicle($id, $data) {
    $validationErrors = validateVehicleData($data);
    if (!empty($validationErrors)) {
        return ['success' => false, 'message' => implode(', ', $validationErrors)];
    }

    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE vehicles SET
            make = :make,
            model = :model,
            year = :year,
            price = :price,
            mileage = :mileage,
            color = :color,
            vin = :vin
            WHERE id = :id");

        $stmt->execute([
            ':id'      => intval($id),
            ':make'   => htmlspecialchars(trim($data['make'])),
            ':model'  => htmlspecialchars(trim($data['model'])),
            ':year'   => intval($data['year']),
            ':price'  => !empty($data['price']) ? floatval($data['price']) : null,
            ':mileage'=> !empty($data['mileage']) ? intval($data['mileage']) : null,
            ':color'  => !empty($data['color']) ? htmlspecialchars(trim($data['color'])) : null,
            ':vin'    => !empty($data['vin']) ? htmlspecialchars(trim($data['vin'])) : null
        ]);

        return ['success' => true];
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to update vehicle'];
    }
}

function deleteVehicle($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM vehicles WHERE id = ?");
        $stmt->execute([intval($id)]);
        return ['success' => $stmt->rowCount() > 0];
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete vehicle'];
    }
}

// Before the loop:
    $vehicles = getVehicles(); // Fetch vehicles from the database
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Inventory | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <?php include 'includes/sidebar.php'; ?>

        <div class="main-content">
            <?php include 'includes/topnav.php'; ?>

            <div class="content">
                <div class="page-header mb-4">
                    <h1 class="mb-1">Vehicle Inventory</h1>
                    <p class="text-muted">Manage your vehicle listings</p>
                    <div class="mt-3">
                        <a href="all_vehicles.php" class="btn btn-primary me-2">
                            <i class="fas fa-car me-2"></i>View All Vehicles
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
                            <i class="fas fa-plus me-2"></i>Add Vehicle
                        </button>
                    </div>
                </div>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Vehicle List</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Make</th>
                                    <th>Model</th>
                                    <th>Year</th>
                                    <th>Price</th>
                                    <th>Mileage</th>
                                    <th>Color</th>
                                    <th>VIN</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td><?= htmlspecialchars($vehicle['make']) ?></td>
                                    <td><?= htmlspecialchars($vehicle['model']) ?></td>
                                    <td><?= $vehicle['year'] ?></td>
                                    <td>$<?= number_format($vehicle['price'], 2) ?></td>
                                    <td><?= number_format($vehicle['mileage']) ?> mi</td>
                                    <td><?= htmlspecialchars($vehicle['color']) ?></td>
                                    <td><?= htmlspecialchars($vehicle['vin']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning edit-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editVehicleModal"
                                                data-id="<?= $vehicle['id'] ?>"
                                                data-make="<?= htmlspecialchars($vehicle['make']) ?>"
                                                data-model="<?= htmlspecialchars($vehicle['model']) ?>"
                                                data-year="<?= $vehicle['year'] ?>"
                                                data-price="<?= $vehicle['price'] ?>"
                                                data-mileage="<?= $vehicle['mileage'] ?>"
                                                data-color="<?= htmlspecialchars($vehicle['color']) ?>"
                                                data-vin="<?= htmlspecialchars($vehicle['vin']) ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                                            <button type="submit" name="delete_vehicle" class="btn btn-sm btn-danger" 
                                                    onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
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
    </div>

    <!-- Add Vehicle Modal -->
    <div class="modal fade" id="addVehicleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Vehicle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Make *</label>
                            <input type="text" class="form-control" name="make" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Model *</label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Year *</label>
                                <input type="number" class="form-control" name="year" min="1900" max="<?= date('Y') + 1 ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="price" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mileage</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="mileage">
                                    <span class="input-group-text">mi</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="color">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">VIN</label>
                            <input type="text" class="form-control" name="vin">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_vehicle" class="btn btn-primary">Add Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Vehicle Modal -->
    <div class="modal fade" id="editVehicleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="vehicle_id" id="edit_vehicle_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Vehicle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Make *</label>
                            <input type="text" class="form-control" name="make" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Model *</label>
                            <input type="text" class="form-control" name="model" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Year *</label>
                                <input type="number" class="form-control" name="year" min="1900" max="<?= date('Y') + 1 ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="price" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mileage</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="mileage">
                                    <span class="input-group-text">mi</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Color</label>
                                <input type="text" class="form-control" name="color">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">VIN</label>
                            <input type="text" class="form-control" name="vin">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_vehicle" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit vehicle modal handler
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const modal = document.querySelector('#editVehicleModal');
                
                // Populate all edit form fields
                modal.querySelector('#edit_vehicle_id').value = this.dataset.id;
                modal.querySelector('[name="make"]').value = this.dataset.make;
                modal.querySelector('[name="model"]').value = this.dataset.model;
                modal.querySelector('[name="year"]').value = this.dataset.year;
                modal.querySelector('[name="price"]').value = this.dataset.price;
                modal.querySelector('[name="mileage"]').value = this.dataset.mileage;
                modal.querySelector('[name="color"]').value = this.dataset.color;
                modal.querySelector('[name="vin"]').value = this.dataset.vin;
            });
        });

        // Add vehicle form reset handler
        document.getElementById('addVehicleModal').addEventListener('show.bs.modal', function () {
            this.querySelector('form').reset();
        });

        // Prevent form submission if invalid (for both forms)
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                this.classList.add('was-validated');
            }, false);
        });
    });
    </script>
</body>
</html>