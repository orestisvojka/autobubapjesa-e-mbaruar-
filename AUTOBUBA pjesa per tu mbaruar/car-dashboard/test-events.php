<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Set timezone (adjust to your location)
date_default_timezone_set('Europe/Tirane');

// 1. SAMPLE TEST DATA
$testEvents = [
    [
        'title' => 'Test Drive: Toyota Corolla',
        'event_type' => 'test_drive',
        'start_time' => date('Y-m-d 14:00:00', strtotime('+1 day')),
        'end_time' => date('Y-m-d 14:30:00', strtotime('+1 day')),
        'vehicle_id' => 1, // MUST EXIST IN YOUR VEHICLES TABLE
        'customer_id' => 1, // MUST EXIST IN YOUR CUSTOMERS TABLE
        'status' => 'confirmed',
        'notes' => 'Interested in hybrid model'
    ],
    [
        'title' => 'Engine Service',
        'event_type' => 'service',
        'start_time' => date('Y-m-d 09:00:00', strtotime('+2 days')),
        'end_time' => date('Y-m-d 11:00:00', strtotime('+2 days')),
        'vehicle_id' => 2,
        'customer_id' => 2,
        'status' => 'pending',
        'notes' => 'Full maintenance check'
    ],
    [
        'title' => 'Follow-Up: Financing Options',
        'event_type' => 'follow_up',
        'start_time' => date('Y-m-d 10:00:00', strtotime('+3 days')),
        'end_time' => date('Y-m-d 10:30:00', strtotime('+3 days')),
        'customer_id' => 3,
        'status' => 'confirmed'
    ]
];

// 2. INSERT TEST DATA
foreach ($testEvents as $event) {
    try {
        $result = createEvent($event);
        echo "<p>✅ Created event: {$event['title']}</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Failed to create '{$event['title']}': {$e->getMessage()}</p>";
    }
}

// 3. FETCH AND DISPLAY EVENTS
echo "<h2>Upcoming Events (Next 30 Days)</h2>";

$events = getEvents(date('Y-m-d'), date('Y-m-d', strtotime('+30 days')));

if (empty($events)) {
    echo "<p>No events found. Check database connections.</p>";
} else {
    // Format as a styled table
    echo "
    <style>
        .event-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .event-table th, .event-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .event-table th {
            background-color: #f2f2f2;
        }
        .test-drive { background-color: #ffe4e6; }
        .service { background-color: #e0f2fe; }
        .follow-up { background-color: #f0fdf4; }
    </style>
    
    <table class='event-table'>
        <tr>
            <th>Type</th>
            <th>Title</th>
            <th>Date/Time</th>
            <th>Customer</th>
            <th>Vehicle</th>
            <th>Status</th>
        </tr>";

    foreach ($events as $event) {
        $vehicle = $event['vehicle_id'] 
            ? "{$event['make']} {$event['model']}" 
            : "N/A";
        
        $rowClass = str_replace('_', '-', $event['event_type']);
        
        echo "<tr class='$rowClass'>
            <td>{$event['event_type']}</td>
            <td><strong>{$event['title']}</strong><br>{$event['notes']}</td>
            <td>" . date('D, M j g:i A', strtotime($event['start_time'])) . "</td>
            <td>{$event['first_name']} {$event['last_name']}<br>{$event['phone']}</td>
            <td>$vehicle</td>
            <td>{$event['status']}</td>
        </tr>";
    }
    echo "</table>";
}

// 4. CLEANUP INSTRUCTIONS
echo "
<div style='margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;'>
    <h3>Debugging Tips</h3>
    <ul>
        <li>Check vehicle IDs exist: <code>SELECT id, make, model FROM vehicles WHERE id IN (1,2);</code></li>
        <li>Verify customer IDs: <code>SELECT id, first_name FROM customers WHERE id IN (1,2,3);</code></li>
        <li>View all events: <code>SELECT * FROM events;</code></li>
    </ul>
    <p>To reset: <code>TRUNCATE TABLE events;</code></p>
</div>";
?>