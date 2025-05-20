<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

// Get filter parameters
$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'added_at_desc';

// Determine sort order
$sortOptions = [
    'added_at_desc' => 'v.added_at DESC',
    'added_at_asc' => 'v.added_at ASC',
    'price_desc' => 'v.price DESC',
    'price_asc' => 'v.price ASC',
    'mileage_desc' => 'v.mileage DESC',
    'mileage_asc' => 'v.mileage ASC',
    'year_desc' => 'v.year DESC',
    'year_asc' => 'v.year ASC'
];
$orderBy = $sortOptions[$sort] ?? 'v.added_at DESC';

// Get inventory data
$vehicles = getAll("
    SELECT v.*, 
           COUNT(r.id) as rental_count,
           DATEDIFF(CURRENT_DATE, v.added_at) as days_in_inventory
    FROM vehicles v
    LEFT JOIN rentals r ON v.id = r.vehicle_id AND r.status = 'Completed'
    WHERE (v.make LIKE ? OR v.model LIKE ? OR v.vin LIKE ?)
    ".($filter === 'available' ? "AND v.status = 'In Stock'" : "")."
    ".($filter === 'sold' ? "AND v.status = 'Sold'" : "")."
    ".($filter === 'rented' ? "AND v.status = 'Rented'" : "")."
    ".($filter === 'oldest' ? "AND v.status = 'In Stock'" : "")."
    GROUP BY v.id
    ORDER BY $orderBy
", ["%$search%", "%$search%", "%$search%"]);

$stats = getDashboardStats();
$user = currentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= ASSETS_PATH ?>/css/style.css">
    <link rel="icon" href="img/autobuba-high-resolution-logo.png">
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
                    <h1>Vehicle Inventory</h1>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="add-vehicle.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Vehicle
                        </a>
                        <form class="d-flex" method="get">
                            <input type="text" name="search" class="form-control" placeholder="Search inventory..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body p-2">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="inventory.php" class="btn btn-sm btn-outline-secondary <?= empty($filter) ? 'active' : '' ?>">
                                All Vehicles
                            </a>
                            <a href="inventory.php?filter=available" class="btn btn-sm btn-outline-success <?= $filter === 'available' ? 'active' : '' ?>">
                                <i class="fas fa-car"></i> Available
                            </a>
                            <a href="inventory.php?filter=sold" class="btn btn-sm btn-outline-danger <?= $filter === 'sold' ? 'active' : '' ?>">
                                <i class="fas fa-check"></i> Sold
                            </a>
                            <a href="inventory.php?filter=rented" class="btn btn-sm btn-outline-info <?= $filter === 'rented' ? 'active' : '' ?>">
                                <i class="fas fa-calendar"></i> Rented
                            </a>
                            <a href="inventory.php?filter=oldest" class="btn btn-sm btn-outline-warning <?= $filter === 'oldest' ? 'active' : '' ?>">
                                <i class="fas fa-clock"></i> Oldest Stock
                            </a>
                            <div class="dropdown ms-auto">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-sort"></i> Sort By
                                </button>
                                <ul class="dropdown-menu">
                                    <li><h6 class="dropdown-header">Date Added</h6></li>
                                    <li><a class="dropdown-item <?= $sort === 'added_at_desc' ? 'active' : '' ?>" href="?sort=added_at_desc">Newest First</a></li>
                                    <li><a class="dropdown-item <?= $sort === 'added_at_asc' ? 'active' : '' ?>" href="?sort=added_at_asc">Oldest First</a></li>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Price</h6></li>
                                    <li><a class="dropdown-item <?= $sort === 'price_desc' ? 'active' : '' ?>" href="?sort=price_desc">Highest First</a></li>
                                    <li><a class="dropdown-item <?= $sort === 'price_asc' ? 'active' : '' ?>" href="?sort=price_asc">Lowest First</a></li>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Mileage</h6></li>
                                    <li><a class="dropdown-item <?= $sort === 'mileage_desc' ? 'active' : '' ?>" href="?sort=mileage_desc">Highest First</a></li>
                                    <li><a class="dropdown-item <?= $sort === 'mileage_asc' ? 'active' : '' ?>" href="?sort=mileage_asc">Lowest First</a></li>
                                    
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Year</h6></li>
                                    <li><a class="dropdown-item <?= $sort === 'year_desc' ? 'active' : '' ?>" href="?sort=year_desc">Newest First</a></li>
                                    <li><a class="dropdown-item <?= $sort === 'year_asc' ? 'active' : '' ?>" href="?sort=year_asc">Oldest First</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th class="text-end">Price</th>
                                        <th>Mileage</th>
                                        <th>Status</th>
                                        <th>Inventory</th>
                                        <th>Rentals</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicles as $vehicle): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= $vehicle['image_path'] ? 'uploads/'.$vehicle['image_path'] : 'assets/images/car-placeholder.jpg' ?>" 
                                                     class="vehicle-thumbnail me-3" alt="<?= $vehicle['make'].' '.$vehicle['model'] ?>">
                                                <div>
                                                    <strong><?= sanitize($vehicle['make']) ?> <?= sanitize($vehicle['model']) ?></strong>
                                                    <div class="text-muted small">
                                                        <?= $vehicle['year'] ?> • <?= sanitize($vehicle['color']) ?>
                                                        <?php if (!empty($vehicle['vin'])): ?>
                                                            <br>VIN: <?= sanitize($vehicle['vin']) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <strong>$<?= number_format($vehicle['price'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <?= number_format($vehicle['mileage']) ?> mi
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $vehicle['status'] === 'Sold' ? 'danger' : 
                                                ($vehicle['status'] === 'Rented' ? 'info' : 'success') 
                                            ?>">
                                                <?= $vehicle['status'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-muted small">
                                                <?= $vehicle['days_in_inventory'] ?> days
                                            </div>
                                        </td>
                                        <td>
                                            <?= $vehicle['rental_count'] ?> rentals
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="view-vehicle.php?id=<?= $vehicle['id'] ?>" class="btn btn-sm btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-vehicle.php?id=<?= $vehicle['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($vehicle['status'] === 'In Stock'): ?>
                                                    <a href="add-rental.php?vehicle_id=<?= $vehicle['id'] ?>" class="btn btn-sm btn-outline-info" title="Rent">
                                                        <i class="fas fa-calendar"></i>
                                                    </a>
                                                    <a href="add-sale.php?vehicle_id=<?= $vehicle['id'] ?>" class="btn btn-sm btn-outline-success" title="Sell">
                                                        <i class="fas fa-dollar-sign"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>