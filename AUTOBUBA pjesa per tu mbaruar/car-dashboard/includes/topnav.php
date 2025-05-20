<?php defined('BASE_URL') or exit('No direct script access allowed'); ?>
<header class="top-nav">
    <div class="nav-container">
        <!-- Left-aligned controls -->
        <div class="nav-section left-controls">
            <!-- Sidebar Toggle for Mobile -->
            <button class="toggle-sidebar d-md-none">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Page Title with Time-Based Greeting -->
            <div class="page-title">
                <?php
                $current_hour = date('H');
                
                if ($current_hour >= 5 && $current_hour < 12) {
                    $greeting = "Good Morning";
                } elseif ($current_hour >= 12 && $current_hour < 18) {
                    $greeting = "Good Afternoon";
                } elseif ($current_hour >= 18 && $current_hour < 22) {
                    $greeting = "Good Evening";
                } else {
                    $greeting = "Good Night";
                }
                
                $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
                
                echo "<h1>{$greeting}, <span class=\"user-greeting\">{$username}</span></h1>";
                ?>
            </div>
        </div>

        <!-- Middle section - Status Indicators -->
        <div class="nav-section status-group" style="margin-left: auto; margin-right: 2rem;">
            <div class="system-status">
                <div class="status-indicator online" title="System Online">
                    <i class="fas fa-server"></i>
                </div>
                <div class="status-indicator" id="db-status" title="Database Status">
                    <i class="fas fa-database"></i>
                </div>
                <div class="status-indicator" id="cpu-status" title="CPU Usage">
                    <i class="fas fa-microchip"></i>
                </div>
            </div>
        </div>

        <!-- Right-aligned controls -->
        <div class="nav-section controls-group">
            <!-- Date/Time -->
            <div class="datetime-display">
                <div class="time-container">
                    <span id="current-date"></span>
                    <span id="current-time"></span>
                </div>
            </div>
        
            
            <!-- User Info -->
            <div class="user-info">
                <div class="user" id="user-profile">
                    <img src="img/user-icon.png" alt="User">
                    <span><?= $_SESSION['username'] ?? 'Guest' ?></span>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- CSS Files -->
<link rel="stylesheet" href="assets/css/topnav.css">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script>
    // Update date and time in real-time
    function updateDateTime() {
        const now = new Date();
        document.getElementById('current-date').textContent = now.toLocaleDateString('en-US', { 
            weekday: 'short', 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
        document.getElementById('current-time').textContent = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    setInterval(updateDateTime, 1000);
    updateDateTime(); // Initial call

    
    // Simulate system status checks with more realistic behavior
    function checkSystemStatus() {
        // Simulate DB status (95% chance of being online)
        const dbOnline = Math.random() > 0.05;
        const dbStatus = document.getElementById('db-status');
        dbStatus.classList.toggle('online', dbOnline);
        dbStatus.classList.toggle('offline', !dbOnline);
        dbStatus.title = dbOnline ? 'Database Online' : 'Database Offline';
        
        // Simulate CPU status with more realistic fluctuations
        const cpuStatus = document.getElementById('cpu-status');
        const currentLoad = parseFloat(cpuStatus.title?.match(/[\d.]+/) || 50);
        
        // Random walk algorithm for CPU load (more realistic fluctuations)
        let cpuLoad = currentLoad + (Math.random() * 10 - 5); // fluctuate by ±5%
        cpuLoad = Math.max(10, Math.min(95, cpuLoad)); // Keep between 10% and 95%
        
        cpuStatus.classList.remove('online', 'warning', 'offline');
        
        if (cpuLoad < 60) {
            cpuStatus.classList.add('online');
        } else if (cpuLoad < 85) {
            cpuStatus.classList.add('warning');
        } else {
            cpuStatus.classList.add('offline');
        }
        cpuStatus.title = `CPU Usage: ${cpuLoad.toFixed(1)}%`;
    }
    
    // Check status immediately and every 30 seconds
    checkSystemStatus();
    setInterval(checkSystemStatus, 30000);

    // Enhanced Notification System
    document.addEventListener('DOMContentLoaded', function() {
        const notificationBell = document.getElementById('notification-bell');
        const notificationDropdown = document.getElementById('notification-dropdown');
        const notificationCount = document.getElementById('notification-count');
        const notificationList = document.getElementById('notification-list');
        const markAllReadBtn = document.getElementById('mark-all-read');

        

        // Toggle dropdown visibility with improved animation
        notificationBell.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleNotificationDropdown();
        });

        function toggleNotificationDropdown(force = null) {
            const isVisible = notificationDropdown.classList.contains('show');
            
            if (force === false || (force === null && isVisible)) {
                notificationDropdown.classList.remove('show');
                setTimeout(() => {
                    notificationDropdown.style.display = 'none';
                }, 250);
            } else {
                notificationDropdown.style.display = 'block';
                setTimeout(() => {
                    notificationDropdown.classList.add('show');
                }, 10);
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            toggleNotificationDropdown(false);
        });

        // Prevent dropdown from closing when clicking inside it
        notificationDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // Mark all notifications as read with animation
        markAllReadBtn.addEventListener('click', function() {
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            
            unreadItems.forEach((item, index) => {
                setTimeout(() => {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    
                    // Update icon in action button
                    const actionBtn = item.querySelector('.notification-action');
                    if (actionBtn) {
                        actionBtn.title = 'Mark as unread';
                        actionBtn.innerHTML = '<i class="fas fa-envelope"></i>';
                    }
                }, index * 100);
            });
            
            setTimeout(() => {
                notifications = notifications.map(notif => ({ ...notif, read: true }));
                updateNotificationCount();
            }, unreadItems.length * 100);
        });

        // Function to update notification count with animation
        function updateNotificationCount() {
            const unreadCount = notifications.filter(notif => !notif.read).length;
            const oldCount = parseInt(notificationCount.textContent);
            
            if (unreadCount !== oldCount) {
                notificationCount.classList.add('update');
                setTimeout(() => {
                    notificationCount.textContent = unreadCount;
                    notificationCount.style.display = unreadCount > 0 ? 'flex' : 'none';
                    notificationCount.classList.remove('update');
                }, 300);
            } else {
                notificationCount.textContent = unreadCount;
                notificationCount.style.display = unreadCount > 0 ? 'flex' : 'none';
            }
        }

        // Function to render notifications with improved styling
        function renderNotifications() {
            if (notifications.length === 0) {
                notificationList.innerHTML = '<div class="notification-empty">No new notifications</div>';
                return;
            }

            notificationList.innerHTML = notifications.map(notif => `
                <div class="notification-item ${notif.read ? 'read' : 'unread'}" data-id="${notif.id}">
                    <div class="notification-content">
                        <div class="notification-message">${notif.message}</div>
                        <div class="notification-time">
                            <i class="far fa-clock"></i> ${notif.time}
                        </div>
                    </div>
                    <button class="notification-action" title="Mark as ${notif.read ? 'unread' : 'read'}">
                        <i class="fas fa-${notif.read ? 'envelope' : 'check'}"></i>
                    </button>
                </div>
            `).join('');

            // Add click handler to mark individual notifications as read
            document.querySelectorAll('.notification-item').forEach(item => {
                const actionBtn = item.querySelector('.notification-action');
                
                actionBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const id = parseInt(item.getAttribute('data-id'));
                    const notif = notifications.find(n => n.id === id);
                    
                    if (notif) {
                        notif.read = !notif.read;
                        item.classList.toggle('read');
                        item.classList.toggle('unread');
                        this.title = `Mark as ${notif.read ? 'unread' : 'read'}`;
                        this.innerHTML = `<i class="fas fa-${notif.read ? 'envelope' : 'check'}"></i>`;
                        updateNotificationCount();
                    }
                });
            });
        }

        // Simulate receiving new notifications with improved relevance to an auto dealership
        function simulateNewNotifications() {
            const messages = [
                'New customer inquiry for test drive',
                'Vehicle #A2543 has arrived in inventory',
                'Maintenance reminder: 3 vehicles due for service',
                'Sales target achieved for this month!',
                'Price update applied to 7 vehicles',
                'Customer John Smith approved for financing',
                'Low inventory alert: SUV category',
                'New review: 5 stars from customer',
                'Scheduled test drive in 30 minutes',
                'Trade-in vehicle needs inspection'
            ];
            
            // Only add new notification 30% of the time for more realistic behavior
            if (Math.random() > 0.7) {
                const newNotif = {
                    id: Date.now(),
                    message: messages[Math.floor(Math.random() * messages.length)],
                    time: 'Just now',
                    read: false
                };
                
                notifications.unshift(newNotif);
                if (notifications.length > 10) notifications.pop();
                
                updateNotificationCount();
                renderNotifications();
                
                // Enhanced visual cue animation when new notification arrives
                notificationBell.classList.add('new-notification');
                setTimeout(() => {
                    notificationBell.classList.remove('new-notification');
                }, 1000);
            }
        }

        // Initialize
        updateNotificationCount();
        renderNotifications();

        // Simulate new notifications every 45-90 seconds (less frequent for realism)
        setInterval(simulateNewNotifications, Math.random() * 45000 + 45000);
        
        // User profile menu toggle
        const userProfile = document.getElementById('user-profile');
        const userMenu = document.getElementById('user-menu');
        
        userProfile.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenu.classList.toggle('show');
        });
        
        document.addEventListener('click', function() {
            userMenu.classList.remove('show');
        });
        
        userMenu.addEventListener('click', function(e) {
            e.stopPropagation();
        });
        
        // Logout button functionality
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function() {
                window.location.href = 'logout.php';
            });
        }
    });
    
    // Mobile navigation enhancement
    const toggleSidebarBtn = document.querySelector('.toggle-sidebar');
    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        });
    }
</script>