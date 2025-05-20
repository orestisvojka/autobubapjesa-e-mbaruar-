<?php
session_start();
require_once 'includes/config.php'; 
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Check if user is logged in
requireAuth();

$user = currentUser();

// Handle event deletion
if (isset($_POST['delete_event']) && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id = ?");
    $stmt->execute([$event_id]);
    
    // Redirect to prevent form resubmission
    header("Location: all-events.php?deleted=1");
    exit;
}

// Set default filter and sort values
$event_type_filter = isset($_GET['event_type']) ? $_GET['event_type'] : 'all';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'start_time';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'ASC';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the SQL query based on filters
$sql = "SELECT e.*, c.name as customer_name, CONCAT(v.make, ' ', v.model) as vehicle_name 
        FROM calendar_events e
        LEFT JOIN customers c ON e.customer_id = c.id
        LEFT JOIN vehicles v ON e.vehicle_id = v.id
        WHERE 1=1";

$params = [];

// Apply event type filter
if ($event_type_filter != 'all') {
    $sql .= " AND e.event_type = ?";
    $params[] = $event_type_filter;
}

// Apply date filter
if ($date_filter != 'all') {
    switch ($date_filter) {
        case 'today':
            $sql .= " AND DATE(e.start_time) = CURDATE()";
            break;
        case 'tomorrow':
            $sql .= " AND DATE(e.start_time) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'this_week':
            $sql .= " AND YEARWEEK(e.start_time, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'next_week':
            $sql .= " AND YEARWEEK(e.start_time, 1) = YEARWEEK(DATE_ADD(CURDATE(), INTERVAL 1 WEEK), 1)";
            break;
        case 'this_month':
            $sql .= " AND YEAR(e.start_time) = YEAR(CURDATE()) AND MONTH(e.start_time) = MONTH(CURDATE())";
            break;
        case 'upcoming':
            $sql .= " AND e.start_time >= CURDATE()";
            break;
        case 'past':
            $sql .= " AND e.start_time < CURDATE()";
            break;
    }
}

// Apply search filter
if (!empty($search)) {
    $sql .= " AND (e.title LIKE ? OR c.name LIKE ? OR CONCAT(v.make, ' ', v.model) LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

// Apply sorting
$sql .= " ORDER BY e.$sort_by $sort_order";

// Prepare and execute the query
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get event counts by type
$eventCounts = $pdo->query("
    SELECT event_type, COUNT(*) as count 
    FROM calendar_events 
    GROUP BY event_type
")->fetchAll(PDO::FETCH_KEY_PAIR);

// Get total event count
$totalEvents = $pdo->query("SELECT COUNT(*) FROM calendar_events")->fetchColumn();

// Get upcoming events count
$upcomingEvents = $pdo->query("SELECT COUNT(*) FROM calendar_events WHERE start_time >= CURDATE()")->fetchColumn();

// Get today's events count
$todayEvents = $pdo->query("SELECT COUNT(*) FROM calendar_events WHERE DATE(start_time) = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events | <?= SITE_NAME ?></title>
    <link rel="icon" href="img/tab-logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Card styling */
        .card {
            background-color: #ffffff;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f1f1;
            padding: 1.25rem 1.5rem;
        }

        .card-header h5 {
            color: #e11d48;
            font-weight: 600;
        }

        .card-header i {
            color: #e11d48;
        }

        .card-body {
            padding: 1.25rem 1.5rem;
        }

        /* Stats section */
        .stats-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #e11d48;
            color: white;
            font-size: 1.2rem;
            border-radius: 8px;
        }

        /* Stats cards */
        .stats-card {
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Button styling */
        .btn-primary {
            background-color: #e11d48;
            border-color: #e11d48;
        }

        .btn-primary:hover {
            background-color: #be123c;
            border-color: #be123c;
        }

        .btn-outline-primary {
            color: #e11d48;
            border-color: #e11d48;
        }

        .btn-outline-primary:hover {
            background-color: #e11d48;
            color: #ffffff;
        }

        /* Event type badges */
        .badge-test_drive {
            background-color: #000000;
            color: white;
        }

        .badge-service {
            background-color: #e11d48;
            color: white;
        }

        .badge-follow_up {
            background-color: #6b7280;
            color: white;
        }

        /* Table styling */
        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #f9fafb;
            color: #4b5563;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 0.75rem 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #e5e7eb;
        }

        .table tr:hover {
            background-color: #f9fafb;
        }

        /* Event status indicators */
        .event-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-upcoming {
            background-color: #10b981;
        }

        .status-past {
            background-color: #6b7280;
        }

        .status-today {
            background-color: #3b82f6;
        }

        /* Filter section */
        .filter-section {
            background-color: #f9fafb;
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        /* Pagination styling */
        .pagination .page-item.active .page-link {
            background-color: #e11d48;
            border-color: #e11d48;
        }

        .pagination .page-link {
            color: #e11d48;
        }

        .pagination .page-link:hover {
            background-color: #f9fafb;
        }

        /* Sort indicators */
        .sort-icon {
            margin-left: 5px;
            font-size: 0.75rem;
        }

        /* Event actions */
        .event-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        /* Search box */
        .search-box {
            position: relative;
        }

        .search-box .form-control {
            padding-left: 2.5rem;
            border-radius: 20px;
        }

        .search-box .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }

        /* No events message */
        .no-events {
            text-align: center;
            padding: 3rem 0;
        }

        .no-events i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        /* Toast notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        .toast {
            background-color: #ffffff;
            border-left: 4px solid #10b981;
        }

        .toast-header {
            background-color: #ffffff;
            border-bottom: 1px solid #f1f1f1;
        }

        .toast-body {
            padding: 0.75rem 1rem;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <?php include 'includes/topnav.php'; ?>

            <!-- Content -->
            <div class="content">
                <!-- Page Header -->
                <div class="page-header mb-4">
                    <div>
                        <h4 class="mb-1">All Events</h4>
                        <p class="text-muted mb-0">View and manage all calendar events</p>
                    </div>
                    <div>
                        <a href="calendar.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-calendar me-2"></i>Calendar View
                        </a>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="fas fa-plus me-2"></i>Add Event
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="stats-icon">
                                            <i class="fas fa-calendar-alt fa-fw"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Total Events</div>
                                        <div class="h3 mb-0"><?= $totalEvents ?></div>
                                        <div class="small text-muted">All time</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-0 shadow stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="stats-icon">
                                            <i class="fas fa-clock fa-fw"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Today's Events</div>
                                        <div class="h3 mb-0"><?= $todayEvents ?></div>
                                        <div class="small text-muted">Scheduled for today</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-0 shadow stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="stats-icon">
                                            <i class="fas fa-car-side fa-fw"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Test Drives</div>
                                        <div class="h3 mb-0"><?= $eventCounts['test_drive'] ?? 0 ?></div>
                                        <div class="small text-muted">Scheduled appointments</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-0 shadow stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="stats-icon">
                                            <i class="fas fa-wrench fa-fw"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Service Appointments</div>
                                        <div class="h3 mb-0"><?= $eventCounts['service'] ?? 0 ?></div>
                                        <div class="small text-muted">Maintenance & repairs</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <form action="" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="event_type" class="form-label">Event Type</label>
                            <select name="event_type" id="event_type" class="form-select">
                                <option value="all" <?= $event_type_filter == 'all' ? 'selected' : '' ?>>All Types</option>
                                <option value="test_drive" <?= $event_type_filter == 'test_drive' ? 'selected' : '' ?>>Test Drive</option>
                                <option value="service" <?= $event_type_filter == 'service' ? 'selected' : '' ?>>Service</option>
                                <option value="follow_up" <?= $event_type_filter == 'follow_up' ? 'selected' : '' ?>>Follow Up</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date_filter" class="form-label">Date Range</label>
                            <select name="date_filter" id="date_filter" class="form-select">
                                <option value="all" <?= $date_filter == 'all' ? 'selected' : '' ?>>All Dates</option>
                                <option value="today" <?= $date_filter == 'today' ? 'selected' : '' ?>>Today</option>
                                <option value="tomorrow" <?= $date_filter == 'tomorrow' ? 'selected' : '' ?>>Tomorrow</option>
                                <option value="this_week" <?= $date_filter == 'this_week' ? 'selected' : '' ?>>This Week</option>
                                <option value="next_week" <?= $date_filter == 'next_week' ? 'selected' : '' ?>>Next Week</option>
                                <option value="this_month" <?= $date_filter == 'this_month' ? 'selected' : '' ?>>This Month</option>
                                <option value="upcoming" <?= $date_filter == 'upcoming' ? 'selected' : '' ?>>Upcoming</option>
                                <option value="past" <?= $date_filter == 'past' ? 'selected' : '' ?>>Past</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_by" class="form-label">Sort By</label>
                            <select name="sort_by" id="sort_by" class="form-select">
                                <option value="start_time" <?= $sort_by == 'start_time' ? 'selected' : '' ?>>Date</option>
                                <option value="title" <?= $sort_by == 'title' ? 'selected' : '' ?>>Title</option>
                                <option value="event_type" <?= $sort_by == 'event_type' ? 'selected' : '' ?>>Event Type</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_order" class="form-label">Order</label>
                            <select name="sort_order" id="sort_order" class="form-select">
                                <option value="ASC" <?= $sort_order == 'ASC' ? 'selected' : '' ?>>Ascending</option>
                                <option value="DESC" <?= $sort_order == 'DESC' ? 'selected' : '' ?>>Descending</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" name="search" class="form-control" placeholder="Search events, customers, or vehicles..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Events Table -->
                <div class="card border-0 shadow mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i> Events List</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-file-csv me-2"></i>CSV</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i>Print</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (count($events) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">#</th>
                                            <th>
                                                <a href="?event_type=<?= $event_type_filter ?>&date_filter=<?= $date_filter ?>&sort_by=title&sort_order=<?= $sort_by == 'title' && $sort_order == 'ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>" class="text-decoration-none text-reset">
                                                    Title
                                                    <?php if ($sort_by == 'title'): ?>
                                                        <i class="fas fa-sort-<?= $sort_order == 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th>
                                                <a href="?event_type=<?= $event_type_filter ?>&date_filter=<?= $date_filter ?>&sort_by=event_type&sort_order=<?= $sort_by == 'event_type' && $sort_order == 'ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>" class="text-decoration-none text-reset">
                                                    Type
                                                    <?php if ($sort_by == 'event_type'): ?>
                                                        <i class="fas fa-sort-<?= $sort_order == 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th>
                                                <a href="?event_type=<?= $event_type_filter ?>&date_filter=<?= $date_filter ?>&sort_by=start_time&sort_order=<?= $sort_by == 'start_time' && $sort_order == 'ASC' ? 'DESC' : 'ASC' ?>&search=<?= urlencode($search) ?>" class="text-decoration-none text-reset">
                                                    Date & Time
                                                    <?php if ($sort_by == 'start_time'): ?>
                                                        <i class="fas fa-sort-<?= $sort_order == 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                                                    <?php endif; ?>
                                                </a>
                                            </th>
                                            <th>Customer</th>
                                            <th>Vehicle</th>
                                            <th style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($events as $index => $event): ?>
                                            <?php 
                                                $event_date = new DateTime($event['start_time']);
                                                $today = new DateTime('today');
                                                
                                                if ($event_date->format('Y-m-d') == $today->format('Y-m-d')) {
                                                    $status_class = 'status-today';
                                                    $status_text = 'Today';
                                                } elseif ($event_date > $today) {
                                                    $status_class = 'status-upcoming';
                                                    $status_text = 'Upcoming';
                                                } else {
                                                    $status_class = 'status-past';
                                                    $status_text = 'Past';
                                                }
                                            ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="event-status <?= $status_class ?>" title="<?= $status_text ?>"></span>
                                                        <?= sanitize($event['title']) ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?= $event['event_type'] ?>">
                                                        <?= ucfirst(str_replace('_', ' ', $event['event_type'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div><?= date('M j, Y', strtotime($event['start_time'])) ?></div>
                                                    <small class="text-muted">
                                                        <?= date('g:i A', strtotime($event['start_time'])) ?> - 
                                                        <?= date('g:i A', strtotime($event['end_time'])) ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if (!empty($event['customer_name'])): ?>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-user-circle text-muted me-2"></i>
                                                            <?= sanitize($event['customer_name']) ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($event['vehicle_name'])): ?>
                                                        <div class="d-flex align-items-center">
                                                            <i class="fas fa-car text-muted me-2"></i>
                                                            <?= sanitize($event['vehicle_name']) ?>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="event-actions">
                                                        <button class="btn btn-sm btn-outline-primary view-event" data-event-id="<?= $event['id'] ?>" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="edit-event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-outline-danger delete-event" data-event-id="<?= $event['id'] ?>" data-event-title="<?= sanitize($event['title']) ?>" title="Delete">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="no-events">
                                <i class="fas fa-calendar-xmark"></i>
                                <h5>No events found</h5>
                                <p class="text-muted">Try changing your filters or create a new event</p>
                                <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addEventModal">
                                    <i class="fas fa-plus me-2"></i>Add New Event
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($events) > 0): ?>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">Showing <?= count($events) ?> events</small>
                            </div>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="eventDetailsTitle">Event Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="eventDetailsContent">
                    <!-- Dynamic content inserted here -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading event details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id="editEventBtn">
                        <i class="fas fa-edit me-1"></i> Edit Event
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the event: <strong id="deleteEventTitle"></strong>?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="event_id" id="deleteEventId">
                        <input type="hidden" name="delete_event" value="1">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash-alt me-1"></i> Delete Event
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div class="modal fade" id="addEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="calendar.php">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Add Calendar Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Event Type</label>
                            <select name="event_type" class="form-select" required>
                                <option value="test_drive">Test Drive</option>
                                <option value="service">Service</option>
                                <option value="follow_up">Follow Up</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label>Start Time</label>
                                <input type="datetime-local" name="start_time" class="form-control" required>
                            </div>
                            <div class="col">
                                <label>End Time</label>
                                <input type="datetime-local" name="end_time" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Customer</label>
                            <select name="customer_id" class="form-select">
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= sanitize($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Vehicle</label>
                            <select name="vehicle_id" class="form-select">
                                <option value="">Select Vehicle</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?= $v['id'] ?>"><?= sanitize($v['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
    <div class="toast-container">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="fas fa-check-circle text-success me-2"></i>
                <strong class="me-auto">Success</strong>
                <small>Just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Event has been successfully deleted.
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize toasts
        var toastElList = [].slice.call(document.querySelectorAll('.toast'));
        var toastList = toastElList.map(function(toastEl) {
            var toast = new bootstrap.Toast(toastEl, {
                autohide: true,
                delay: 5000
            });
            return toast;
        });

        // Handle view event button clicks
        const viewEventButtons = document.querySelectorAll('.view-event');
        viewEventButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                
                // In a real application, you would fetch the event details via AJAX
                // For this example, we'll simulate it with a timeout
                const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));
                modal.show();
                
                // Set the edit button URL
                document.getElementById('editEventBtn').href = 'edit-event.php?id=' + eventId;
                
                // Simulate loading event details
                setTimeout(function() {
                    // This would normally be an AJAX call to get event details
                    // For demonstration, we'll just populate with sample data
                    const eventRow = button.closest('tr');
                    const title = eventRow.querySelector('td:nth-child(2)').textContent.trim();
                    const type = eventRow.querySelector('td:nth-child(3) .badge').textContent.trim();
                    const date = eventRow.querySelector('td:nth-child(4) div').textContent.trim();
                    const time = eventRow.querySelector('td:nth-child(4) small').textContent.trim();
                    const customer = eventRow.querySelector('td:nth-child(5)').textContent.trim();
                    const vehicle = eventRow.querySelector('td:nth-child(6)').textContent.trim();
                    
                    // Get badge class
                    const badgeClass = eventRow.querySelector('td:nth-child(3) .badge').classList[1];
                    
                    // Update modal content
                    document.getElementById('eventDetailsTitle').textContent = 'Event Details: ' + title;
                    
                    const detailsHtml = `
                        <div class="event-header mb-4">
                            <h4>${title}</h4>
                            <span class="badge ${badgeClass}">
                                ${type}
                            </span>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="fw-bold">Date</label>
                                    <p><i class="far fa-calendar-alt me-2"></i> ${date}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Time</label>
                                    <p><i class="far fa-clock me-2"></i> ${time}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="fw-bold">Customer</label>
                                    <p><i class="fas fa-user me-2"></i> ${customer !== '-' ? customer : 'No customer'}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Vehicle</label>
                                    <p><i class="fas fa-car me-2"></i> ${vehicle !== '-' ? vehicle : 'No vehicle'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="fw-bold">Notes</label>
                            <div class="p-3 bg-light rounded">
                                <p class="mb-0">Sample notes for this event. In a real application, this would be fetched from the database.</p>
                            </div>
                        </div>
                    `;
                    
                    document.getElementById('eventDetailsContent').innerHTML = detailsHtml;
                }, 500);
            });
        });

        // Handle delete event button clicks
        const deleteEventButtons = document.querySelectorAll('.delete-event');
        deleteEventButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-event-id');
                const eventTitle = this.getAttribute('data-event-title');
                
                document.getElementById('deleteEventId').value = eventId;
                document.getElementById('deleteEventTitle').textContent = eventTitle;
                
                const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
                modal.show();
            });
        });

        // Set default date/time values for the add event form
        const now = new Date();
        const startTime = new Date(now);
        startTime.setHours(now.getHours() + 1, 0, 0, 0); // Next hour, on the hour
        
        const endTime = new Date(startTime);
        endTime.setHours(endTime.getHours() + 1); // 1 hour later
        
        const startInput = document.querySelector('#addEventModal [name="start_time"]');
        const endInput = document.querySelector('#addEventModal [name="end_time"]');
        
        if (startInput && endInput) {
            startInput.value = formatDateTimeForInput(startTime);
            endInput.value = formatDateTimeForInput(endTime);
        }
        
        // Helper function for datetime formatting for input fields
        function formatDateTimeForInput(date) {
            return date.toISOString().slice(0, 16);
        }
    });
    </script>
</body>
</html>
