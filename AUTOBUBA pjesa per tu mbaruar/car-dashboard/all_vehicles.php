<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

// Filter parameters
$filters = [
    'make' => $_GET['make'] ?? '',
    'model' => $_GET['model'] ?? '',
    'min_year' => $_GET['min_year'] ?? '',
    'max_year' => $_GET['max_year'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'color' => $_GET['color'] ?? ''
];

// Build query
$where = [];
$params = [];

if (!empty($filters['make'])) {
    $where[] = "make LIKE ?";
    $params[] = "%{$filters['make']}%";
}

if (!empty($filters['model'])) {
    $where[] = "model LIKE ?";
    $params[] = "%{$filters['model']}%";
}

if (!empty($filters['min_year']) && is_numeric($filters['min_year'])) {
    $where[] = "year >= ?";
    $params[] = $filters['min_year'];
}

if (!empty($filters['max_year']) && is_numeric($filters['max_year'])) {
    $where[] = "year <= ?";
    $params[] = $filters['max_year'];
}

if (!empty($filters['min_price']) && is_numeric($filters['min_price'])) {
    $where[] = "price >= ?";
    $params[] = $filters['min_price'];
}

if (!empty($filters['max_price']) && is_numeric($filters['max_price'])) {
    $where[] = "price <= ?";
    $params[] = $filters['max_price'];
}

if (!empty($filters['color'])) {
    $where[] = "color LIKE ?";
    $params[] = "%{$filters['color']}%";
}

$query = "SELECT * FROM vehicles";
if (!empty($where)) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$query .= " ORDER BY year DESC, make ASC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "Error loading vehicles";
    $vehicles = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Vehicle Inventory | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                    <h1>Vehicle Search</h1>
                    <a href="vehicles.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Inventory
                    </a>
                </div>

                <!-- Filter Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="make" placeholder="Make" 
                                    value="<?= htmlspecialchars($filters['make']) ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="model" placeholder="Model" 
                                    value="<?= htmlspecialchars($filters['model']) ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="min_year" placeholder="Min Year" 
                                    value="<?= htmlspecialchars($filters['min_year']) ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="number" class="form-control" name="max_year" placeholder="Max Year" 
                                    value="<?= htmlspecialchars($filters['max_year']) ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="min_price" placeholder="Min Price" 
                                    value="<?= htmlspecialchars($filters['min_price']) ?>">
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="max_price" placeholder="Max Price" 
                                    value="<?= htmlspecialchars($filters['max_price']) ?>">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="color" placeholder="Color" 
                                    value="<?= htmlspecialchars($filters['color']) ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>Search
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="all_vehicles.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-2"></i>Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
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
                                            <a href="vehicles.php?edit=<?= $vehicle['id'] ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if (empty($vehicles)): ?>
                                <div class="alert alert-info m-3">No vehicles found matching your criteria</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit vehicle modal handler
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_vehicle_id').value = this.dataset.id;
                // Populate other fields similarly
                // Example:
                document.querySelector('#editVehicleModal [name="make"]').value = this.dataset.make;
                // Repeat for other fields...
            });
        });
    });
    </script>
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