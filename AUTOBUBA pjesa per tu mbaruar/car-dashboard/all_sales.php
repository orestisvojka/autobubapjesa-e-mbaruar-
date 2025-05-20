<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $query = "
        SELECT s.*, v.make, v.model, c.name AS customer_name 
        FROM sales s
        JOIN vehicles v ON s.vehicle_id = v.id
        JOIN customers c ON s.customer_id = c.id
        WHERE YEAR(s.sale_date) = :year
    ";
    $params = [':year' => $year];

    if ($month) {
        $query .= " AND MONTH(s.sale_date) = :month";
        $params[':month'] = $month;
    }

    if (!empty($search)) {
        $query .= " AND (c.name LIKE :search OR v.make LIKE :search OR v.model LIKE :search)";
        $params[':search'] = "%$search%";
    }

    $query .= " ORDER BY s.sale_date DESC";
    $sales = getAll($query, $params);
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "Unable to load sales data.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Sales | <?= htmlspecialchars(SITE_NAME) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .table-hover tbody tr:hover { background-color: rgba(255, 77, 77, 0.05); }
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content" style="margin-left: 250px; padding: 20px;">
        <?php include 'includes/topnav.php'; ?>

        <div class="container-fluid mt-4">
            <h1 class="h3 mb-4 text-danger"><i class="fas fa-receipt me-2"></i>All Sales</h1>

            <!-- Filter Form -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search customers or vehicles" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="year" class="form-select">
                        <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                            <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="month" class="form-select">
                        <option value="">All Months</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-danger w-100">Filter</button>
                </div>
            </form>

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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sales as $sale): ?>
                                    <tr>
                                        <td><?= htmlspecialchars(date('M j, Y', strtotime($sale['sale_date']))) ?></td>
                                        <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($sale['make'] . ' ' . $sale['model']) ?></td>
                                        <td><?= htmlspecialchars($sale['city'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($sale['state'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($sale['zip'] ?? 'N/A') ?></td>
                                        <td>$<?= number_format($sale['sale_price'], 2) ?></td>
                                        <td><?= ucfirst(htmlspecialchars($sale['payment_method'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>