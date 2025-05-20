<?php defined('BASE_URL') or exit('No direct script access allowed'); ?>
<aside class="sidebar">
    <div class="logo">
        <i style="color:red;" class="fas fa-car"></i>
        <span style="color: red;">AUTOBUBA</span>
    </div>
    <nav class="menu">
        <ul>
            <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'index.php' ? 'active' : '' ?>">
                <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
            </li>
            <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'vehicles.php' ? 'active' : '' ?>">
                <a href="vehicles.php"><i class="fas fa-car"></i> <span>Inventory</span></a>
            </li>
            <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'sales.php' ? 'active' : '' ?>">
                <a href="sales.php"><i class="fas fa-receipt"></i> <span>Sales</span></a>
            </li>
            <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'customers.php' ? 'active' : '' ?>">
                <a href="customers.php"><i class="fas fa-users"></i> <span>Customers</span></a>
            </li>
            <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'calendar.php' ? 'active' : '' ?>">
                <a href="calendar.php"><i class="fas fa-calendar"></i> <span>Calendar</span></a>
            </li>
            <li class="<?= basename($_SERVER['SCRIPT_NAME']) === 'settings.php' ? 'active' : '' ?>">
                <a href="settings.php"><i class="fas fa-cog"></i> <span>Settings</span></a>
            </li>
        </ul>
    </nav>
    <div class="sidebar-footer">
        
    <div class="logout">
    <a href="includes/logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </div>
    </div>
</aside>

