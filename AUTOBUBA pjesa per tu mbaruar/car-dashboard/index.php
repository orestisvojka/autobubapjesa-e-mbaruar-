<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireAuth();

$user = currentUser();

// Get dashboard data
$stats = getDashboardStats();
$transactions = getRecentTransactions(5);
$topModels = getTopModels(5);
$newArrivals = getNewArrivals(5); 
$dueRentals = getDueRentals();
$salesChartData = getSalesChartData();

// Get calendar events for the current month
$start = date('Y-m-01');
$end = date('Y-m-t');
$calendarEvents = getCalendarEvents($start, $end);

// Current month and year
$currentMonth = date('F Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?= SITE_NAME ?></title>
    <link rel="icon" href="img/tab-logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <style>
    /* Dashboard Layout */
    .dashboard-layout {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .stats-column {
      width: 30%;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .chart-column {
      width: 35%;
    }

    .stats-card {
      margin-bottom: 0 !important;
      height: calc(25% - 12px);
      transition: all 0.3s ease;
    }

    .stats-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .chart-card {
    height: 100%;
    max-height: none; 
    min-height: 516px; /* Ensure minimum height */
}

    .chart-column {
      width: 70%;
      height: 100%;
      min-height: 516px0px;
    }

    .chart-container {
      display: flex;
      flex-direction: column;
    }

    .chart-body {
    flex: 1;
    height: 100%;
    min-height: 500px; 
    position: relative;
}

    .card {
      background-color: #ffffff;
      border: none;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .card-header {
      background-color: #ffffff;
      border-bottom: 1px solid #f1f1f1;
    }

    .card-header h5 {
      color: #e11d48;
      font-weight: 600;
    }

    .card-header i {
      color: #e11d48;
    }

    .btn-outline-primary {
      color: #e11d48;
      border-color: #e11d48;
    }

    .btn-outline-primary:hover {
      background-color: #e11d48;
      color: #ffffff;
    }

    .progress-bar {
      background-color: #e11d48;
    }

    .table-dark {
      background-color: #e11d48;
    }

    .badge.bg-primary {
      background-color: #e11d48 !important;
    }

    .stats-icon {
      background-color: #e11d48;
    }

    /* Stats section */
    .stats-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        color: white;
        font-size: 1.2rem;
    }

    /* Card styling improvements */
    .card {
        transition: all 0.3s ease;
        border: none;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        border-bottom: none;
        padding: 1.25rem 1.5rem;
    }

    .card-body {
        padding: 1.25rem 1.5rem;
    }

    /* Chart styling to make it fill the container */
    .chart-body {
      flex: 1;
      height: 100%;
      min-height: 300px;
      position: relative;
    }
    
    /* CALENDAR FIXES - START */
    /* Calendar container to prevent overflow */
    #calendar-container {
        width: 100%;
        height: 660px;
        max-height: 660px;
        position: relative;
    }

    #calendar-mini {
        width: 100%;
        height: 100%;
    }

    /* Calendar styling with red and white palette */
    .fc-theme-standard .fc-scrollgrid {
        border-color: #f1f1f1;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: rgba(225, 29, 72, 0.1) !important;
    }

    .fc .fc-button-primary {
        background-color: #e11d48;
        border-color: #e11d48;
    }

    .fc .fc-button-primary:hover {
        background-color: #be123c;
        border-color: #be123c;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active, 
    .fc .fc-button-primary:not(:disabled):active {
        background-color: #9f1239;
        border-color: #9f1239;
    }

    .fc .fc-toolbar-title {
        color: #e11d48;
        font-weight: 600;
    }

    .fc .fc-col-header-cell-cushion {
        color: #e11d48;
        font-weight: 600;
    }

    .fc .fc-daygrid-day-number {
        color: #333;
    }

    /* Event colors - black, red, and grey */
    .fc-event.event-test_drive {
        background-color: #000000;
        border-color: #000000;
        color: #ffffff;
    }

    .fc-event.event-service {
        background-color: #e11d48;
        border-color: #e11d48;
        color: #ffffff;
    }

    .fc-event.event-follow_up {
        background-color: #6b7280;
        border-color: #6b7280;
        color: #ffffff;
    }

    /* Calendar container adjustments to fix overflow */
    .fc-view-harness {
        height: auto !important;
    }

    .fc-daygrid-body {
        width: 100% !important;
    }

    .fc-scroller {
        overflow: hidden !important;
    }

    .fc-scrollgrid-sync-table {
        width: 100% !important;
    }

    /* Day cell styling */
    .fc .fc-daygrid-day-frame {
        min-height: 60px;
        padding: 2px;
    }

    .fc .fc-daygrid-day-top {
        flex-direction: row;
        padding: 2px;
    }

    /* Event display improvements */
    .fc-event {
        font-size: 11px;
        padding: 2px 4px;
        margin: 1px 2px;
        border-radius: 3px;
        cursor: pointer;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .fc-daygrid-event {
        margin: 1px 2px;
    }

    /* More link styling */
    .fc-daygrid-more-link {
        font-size: 11px;
        color: #e11d48;
        font-weight: 600;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .fc .fc-toolbar {
            flex-direction: column;
            gap: 10px;
            align-items: center;
        }
        
        .fc .fc-toolbar-title {
            font-size: 1.2em;
        }
        
        .fc .fc-daygrid-day-frame {
            min-height: 40px;
        }
        
        .fc-event {
            font-size: 9px;
            padding: 1px 2px;
        }
    }
    /* CALENDAR FIXES - END */
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

            <!-- Dashboard Content -->
            <div class="content">
                <!-- Page Header -->
                <div class="page-header mb-4">
                    <div>
                        <h4 class="mb-1">Dashboard</h4>
                        <p class="text-muted mb-0">Welcome back, <?= !empty($user['name']) ? sanitize($user['name']) : 'User' ?>!</p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <!-- Replace the existing dashboard grid with this new layout -->
                <!-- Dashboard Layout - Stats and Chart side by side -->
                <div class="dashboard-layout">
                    <!-- Stats Column -->
                    <div class="stats-column">
                        <!-- Stats Cards -->
                        <div class="card border-0 shadow stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="stats-icon">
                                            <i class="fas fa-car fa-fw"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Total Vehicles</div>
                                        <div class="h3 mb-0"><?= $stats['vehicles']['total'] ?></div>
                                        <div class="small text-muted"><?= $stats['vehicles']['available'] ?> available</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="stats-icon">
                                            <i class="fas fa-dollar-sign fa-fw"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Monthly Revenue</div>
                                        <div class="h3 mb-0">$<?= number_format($stats['sales']['monthly_revenue'] ?? 0, 2) ?></div>
                                        <div class="small text-muted"><?= $currentMonth ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="stats-icon">
                                            <i class="fas fa-calendar-check fa-fw"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Active Rentals</div>
                                        <div class="h3 mb-0"><?= $stats['rentals']['active'] ?></div>
                                        <div class="small text-muted"><?= $stats['rentals']['due_today'] ?> due today</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card border-0 shadow stats-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="stats-icon">
                                            <i class="fas fa-users fa-fw"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-muted small">Total Customers</div>
                                        <div class="h3 mb-0"><?= $stats['customers']['total'] ?></div>
                                        <div class="small text-muted">Potential buyers</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart Column -->
                    <div class="chart-column">
                        <div class="card border-0 shadow chart-card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Sales Performance</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                            id="chartPeriodDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Last 6 Months
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="chartPeriodDropdown">
                                        <li><a class="dropdown-item" href="#" data-period="3">Last 3 Months</a></li>
                                        <li><a class="dropdown-item" href="#" data-period="6">Last 6 Months</a></li>
                                        <li><a class="dropdown-item" href="#" data-period="12">This Year</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body chart-body">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Models and Arrivals Row -->
                <div class="row mb-4">
                    <!-- Top Models Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i> Top Models</h5>
                                <a href="reports.php?report=top_models" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-chart-bar me-1"></i> Report
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="ps-3">#</th>
                                                <th>Model</th>
                                                <th>Sales</th>
                                                <th style="width: 35%">Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($topModels as $index => $model): ?>
                                            <tr>
                                                <td class="ps-3"><?= $index + 1 ?></td>
                                                <td>
                                                    <strong><?= sanitize($model['make']) ?></strong>
                                                    <div class="text-muted small"><?= sanitize($model['model']) ?></div>
                                                </td>
                                                <td><?= $model['sales_count'] ?> <small class="text-muted">(<?= $model['percentage'] ?>%)</small></td>
                                                <td>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar" 
                                                            style="width: <?= $model['percentage'] ?>%"></div>
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

                    <!-- New Arrivals Section -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-star me-2"></i> New Arrivals</h5>
                                <a href="vehicles.php?filter=new_arrivals" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-car me-1"></i> View All
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th class="ps-3">Vehicle</th>
                                                <th>Details</th>
                                                <th>Price</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($newArrivals as $vehicle): ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <strong><?= sanitize($vehicle['make']) ?> <?= sanitize($vehicle['model']) ?></strong>
                                                    <div class="text-muted small">
                                                        <?= $vehicle['year'] ?> | <?= number_format($vehicle['mileage']) ?> mi
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary"><?= sanitize($vehicle['color']) ?></span>
                                                    <div class="text-muted small">VIN: <?= sanitize($vehicle['vin']) ?></div>
                                                </td>
                                                <td>$<?= number_format($vehicle['price'], 2) ?></td>
                                                <td>
                                                    <?php 
                                                    $statusClass = [
                                                        'available' => 'bg-success',
                                                        'sold' => 'bg-danger',
                                                        'rented' => 'bg-info',
                                                        'maintenance' => 'bg-warning'
                                                    ];
                                                    ?>
                                                    <span class="badge <?= $statusClass[$vehicle['status']] ?? 'bg-secondary' ?>">
                                                        <?= ucfirst($vehicle['status']) ?>
                                                    </span>
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

                <!-- Calendar Section (Full Width) - FIXED CALENDAR -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-calendar me-2"></i> Calendar</h5>
                                <a href="calendar.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-expand me-1"></i> Full View
                                </a>
                            </div>
                            <div class="card-body p-0">
                                <!-- Calendar container with fixed height and proper overflow handling -->
                                <div id="calendar-container">
                                    <div id="calendar-mini"></div>
                                </div>
                            </div>
                            <div class="card-footer bg-light p-2">
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="d-flex align-items-center">
                                        <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background-color: #000000;"></span>
                                        <small>Test Drive</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background-color: #e11d48;"></span>
                                        <small>Service</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="d-inline-block rounded-circle me-2" style="width: 12px; height: 12px; background-color: #6b7280;"></span>
                                        <small>Follow Up</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    
    <!-- Initialize Dashboard Charts and Calendar -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Sales Chart
        const salesChartCanvas = document.getElementById('salesChart');
        if (salesChartCanvas) {
            const salesChartData = <?= json_encode($salesChartData) ?>;
            window.salesChartData = salesChartData; // Make it globally accessible
            
            // Create a function to filter data based on period
            window.filterChartData = function(data, months) {
                // If we have less data than requested months, return all data
                if (data.length <= months) return data;
                
                // Otherwise return the last X months
                return data.slice(-months);
            }
            
            // Initial data (last 6 months by default)
            let filteredData = filterChartData(salesChartData, 6);
            
            window.salesChart = new Chart(salesChartCanvas, {
                type: 'line',
                data: {
                    labels: filteredData.map(item => item.month),
                    datasets: [
                        {
                            label: 'Sales Count',
                            data: filteredData.map(item => item.sales),
                            borderColor: '#e11d48',
                            backgroundColor: 'rgba(225, 29, 72, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#e11d48',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Revenue ($)',
                            data: filteredData.map(item => item.revenue),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2,
                            pointBackgroundColor: '#ffffff',
                            pointBorderColor: '#3b82f6',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 10,
                                font: {
                                    size: 10,
                                    weight: '600'
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#1f2937',
                            bodyColor: '#4b5563',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: {
                                size: 13,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.datasetIndex === 1) {
                                        label += '$' + Number(context.raw).toLocaleString();
                                    } else {
                                        label += context.raw;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: {
                                drawBorder: false,
                                lineWidth: 1
                            },
                            ticks: {
                                font: {
                                    size: 10
                                }
                            },
                            min: 0
                        }
                    }
                }
            });
        }

        // Add event listeners for chart period dropdown
        const chartPeriodDropdown = document.getElementById('chartPeriodDropdown');
        const chartPeriodOptions = document.querySelectorAll('[data-period]');

        if (chartPeriodOptions.length > 0) {
            chartPeriodOptions.forEach(option => {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    const period = parseInt(this.getAttribute('data-period'));
                    chartPeriodDropdown.textContent = this.textContent;
                    
                    // Update chart data based on selected period
                    if (window.salesChart) {
                        // Filter data based on selected period
                        const filteredData = filterChartData(window.salesChartData, period);
                        
                        // Update chart data
                        window.salesChart.data.labels = filteredData.map(item => item.month);
                        window.salesChart.data.datasets[0].data = filteredData.map(item => item.sales);
                        window.salesChart.data.datasets[1].data = filteredData.map(item => item.revenue);
                        
                        // Update the chart
                        window.salesChart.update();
                    }
                });
            });
        }
    });
    </script>

    <!-- Initialize Dashboard Charts and Calendar -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Calendar
        const calendarEl = document.getElementById('calendar-mini');
        if (calendarEl) {
            const calendarEvents = <?= json_encode($calendarEvents ?? []) ?>;
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                },
                height: 'auto',
                contentHeight: 'auto',
                aspectRatio: 1.5,
                events: calendarEvents,
                eventClassNames: function(arg) {
                    return ['calendar-event', 'event-' + arg.event.extendedProps.type];
                },
                dayMaxEvents: 3, // Show "more" link when more than 3 events
                eventDisplay: 'block',
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: 'short'
                },
                eventDidMount: function(info) {
                    // Add tooltip for events
                    const tooltip = new bootstrap.Tooltip(info.el, {
                        title: info.event.title,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                    
                    // Apply custom event colors
                    const eventType = info.event.extendedProps.type;
                    if (eventType === 'test_drive') {
                        info.el.style.backgroundColor = '#000000';
                        info.el.style.borderColor = '#000000';
                    } else if (eventType === 'service') {
                        info.el.style.backgroundColor = '#e11d48';
                        info.el.style.borderColor = '#e11d48';
                    } else if (eventType === 'follow_up') {
                        info.el.style.backgroundColor = '#6b7280';
                        info.el.style.borderColor = '#6b7280';
                    }
                }
            });
            
            calendar.render();
            
            // Adjust calendar size after initial render
            setTimeout(() => {
                calendar.updateSize();
            }, 100);
            
            // Handle window resize
            window.addEventListener('resize', () => {
                calendar.updateSize();
            });
        }
    });
    </script>
</body>
</html>