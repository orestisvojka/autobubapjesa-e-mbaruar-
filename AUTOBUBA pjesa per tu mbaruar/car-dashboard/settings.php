<?php
session_start();
require_once 'includes/config.php'; 
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/settings-functions.php';
requireAuth();

$user = currentUser();

// Handle form submissions
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $result = updateUserProfile($_POST);
        if ($result['success']) {
            $success_message = "Profile updated successfully!";
            $user = currentUser(); // Refresh user data
        } else {
            $error_message = $result['message'];
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match.";
        } else {
            $result = changeUserPassword($user['id'], $current_password, $new_password);
            if ($result['success']) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = $result['message'];
            }
        }
    } elseif (isset($_POST['update_preferences'])) {
        // Update preferences (dark mode, timezone)
        $dark_mode = isset($_POST['dark_mode']) ? 1 : 0;
        $timezone = $_POST['timezone'];
        
        $result = updateUserPreferences($user['id'], $dark_mode, $timezone);
        if ($result['success']) {
            $success_message = "Preferences updated successfully!";
            $user = currentUser(); // Refresh user data
            
            // If dark mode was changed, set cookie for immediate effect
            setcookie('dark_mode', $dark_mode, time() + (86400 * 30), "/"); // 30 days
        } else {
            $error_message = $result['message'];
        }
    } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        // Handle avatar upload
        $result = updateUserAvatar($user['id'], $_FILES['avatar']);
        if ($result['success']) {
            $success_message = "Avatar updated successfully!";
            $user = currentUser(); // Refresh user data
        } else {
            $error_message = $result['message'];
        }
    } elseif (isset($_POST['remove_avatar'])) {
        // Remove avatar
        $result = removeUserAvatar($user['id']);
        if ($result['success']) {
            $success_message = "Avatar removed successfully!";
            $user = currentUser(); // Refresh user data
        } else {
            $error_message = $result['message'];
        }
    }
}

// Get user preferences or defaults
$dark_mode = isset($user['dark_mode']) ? $user['dark_mode'] : 0;
$timezone = isset($user['timezone']) ? $user['timezone'] : 'UTC';

// List of common timezones
$timezones = [
    'UTC' => 'UTC (Coordinated Universal Time)',
    'America/New_York' => 'Eastern Time (US & Canada)',
    'America/Chicago' => 'Central Time (US & Canada)',
    'America/Denver' => 'Mountain Time (US & Canada)',
    'America/Los_Angeles' => 'Pacific Time (US & Canada)',
    'America/Anchorage' => 'Alaska',
    'Pacific/Honolulu' => 'Hawaii',
    'Europe/London' => 'London',
    'Europe/Paris' => 'Paris, Berlin, Rome, Madrid',
    'Europe/Moscow' => 'Moscow',
    'Asia/Dubai' => 'Dubai',
    'Asia/Kolkata' => 'Mumbai, New Delhi',
    'Asia/Shanghai' => 'Beijing, Shanghai',
    'Asia/Tokyo' => 'Tokyo',
    'Australia/Sydney' => 'Sydney',
    'Pacific/Auckland' => 'Auckland'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | <?= SITE_NAME ?></title>
    <link rel="icon" href="img/tab-logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e11d48;
            --primary-dark: #be123c;
            --primary-light: #fecdd3;
            --primary-lighter: #ffe4e6;
            --secondary-color: #6b7280;
            --text-color: #1f2937;
            --light-text: #6b7280;
            --border-color: #e5e7eb;
            --background: #f9fafb;
            --white: #ffffff;
        }
        
        /* Dark Mode Variables */
        .dark-mode {
            --text-color: #f3f4f6;
            --light-text: #d1d5db;
            --border-color: #374151;
            --background: #111827;
            --white: #1f2937;
        }
        
        body {
            background-color: var(--background);
            color: var(--text-color);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .settings-container {
            max-width: 100%;
            margin-bottom: 2rem;
        }
        
        .settings-nav {
            background-color: var(--white);
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .settings-nav .nav-link {
            border-radius: 0;
            padding: 1rem 1.5rem;
            color: var(--text-color);
            border-left: 3px solid transparent;
        }
        
        .settings-nav .nav-link.active {
            background-color: var(--primary-lighter);
            border-left: 3px solid var(--primary-color);
            color: var(--primary-dark);
        }
        
        .settings-content {
            background-color: var(--white);
            border-radius: 0.5rem;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .settings-header {
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .avatar-container {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            margin-bottom: 1rem;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .avatar-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.5);
            padding: 0.25rem;
            font-size: 0.75rem;
            color: var(--white);
            text-align: center;
            cursor: pointer;
        }
        
        /* Form Control in Dark Mode */
        .dark-mode .form-control,
        .dark-mode .form-select {
            background-color: #374151;
            border-color: #4b5563;
            color: #f3f4f6;
        }
        
        .dark-mode .form-control:focus,
        .dark-mode .form-select:focus {
            background-color: #374151;
            border-color: var(--primary-color);
            color: #f3f4f6;
        }
        
        /* Toggle Switch for Dark Mode */
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
        }
        
        .form-switch .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
    </style>
</head>
<body class="<?= $dark_mode ? 'dark-mode' : '' ?>">
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
                        <h1 class="mb-1">Settings</h1>
                        <p class="text-muted mb-0">Manage your account settings</p>
                    </div>
                </div>

                <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?= $success_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error_message ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <!-- Settings Layout -->
                <div class="settings-container">
                    <div class="d-flex flex-column flex-lg-row settings-layout">
                        <!-- Settings Navigation -->
                        <div class="settings-nav me-0 me-lg-4 mb-4 mb-lg-0" style="min-width: 220px;">
                            <div class="nav flex-column nav-pills">
                                <button class="nav-link active" id="profile-tab" data-bs-toggle="pill" data-bs-target="#profile" type="button">
                                    <i class="fas fa-user me-2"></i> Profile
                                </button>
                                <button class="nav-link" id="security-tab" data-bs-toggle="pill" data-bs-target="#security" type="button">
                                    <i class="fas fa-shield-alt me-2"></i> Security
                                </button>
                                <button class="nav-link" id="preferences-tab" data-bs-toggle="pill" data-bs-target="#preferences" type="button">
                                    <i class="fas fa-sliders-h me-2"></i> Preferences
                                </button>
                            </div>
                        </div>

                        <!-- Settings Content -->
                        <div class="settings-content flex-grow-1">
                            <div class="tab-content">
                                <!-- Profile Tab -->
                                <div class="tab-pane fade show active" id="profile">
                                    <div class="settings-header">
                                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Profile Information</h4>
                                    </div>
                                    
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-4 mb-4 mb-md-0">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="avatar-container">
                                                        <?php if (!empty($user['avatar'])): ?>
                                                            <img src="<?= $user['avatar'] ?>" alt="Profile">
                                                        <?php else: ?>
                                                            <?= substr($user['first_name'] ?? 'U', 0, 1) ?><?= substr($user['last_name'] ?? 'S', 0, 1) ?>
                                                        <?php endif; ?>
                                                        <div class="avatar-overlay">
                                                            <i class="fas fa-camera"></i> Change
                                                        </div>
                                                    </div>
                                                    <input type="file" id="avatar-upload" name="avatar" class="d-none" accept="image/*">
                                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="remove-avatar-btn">
                                                        <i class="fas fa-trash-alt me-1"></i> Remove
                                                    </button>
                                                    <input type="hidden" name="remove_avatar" id="remove_avatar" value="0">
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="first_name" class="form-label">First Name</label>
                                                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="last_name" class="form-label">Last Name</label>
                                                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email Address</label>
                                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">Phone Number</label>
                                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="text-end mt-4">
                                            <button type="submit" name="update_profile" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Security Tab -->
                                <div class="tab-pane fade" id="security">
                                    <div class="settings-header">
                                        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Settings</h4>
                                    </div>
                                    
                                    <!-- Change Password Form -->
                                    <form action="" method="POST">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            <div class="mt-2" id="password-strength"></div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                            <div class="mt-2" id="password-match"></div>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" name="change_password" class="btn btn-primary">
                                                <i class="fas fa-key me-1"></i> Change Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- Preferences Tab -->
                                <div class="tab-pane fade" id="preferences">
                                    <div class="settings-header">
                                        <h4 class="mb-0"><i class="fas fa-sliders-h me-2"></i>User Preferences</h4>
                                    </div>
                                    
                                    <!-- Preferences Form -->
                                    <form action="" method="POST">
                                        <!-- Dark Mode Toggle -->
                                        <div class="mb-4">
                                            <label class="form-label fw-bold">Appearance</label>
                                            <div class="form-check form-switch mb-2">
                                                <input class="form-check-input" type="checkbox" id="dark_mode" name="dark_mode" <?= $dark_mode ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="dark_mode">
                                                    <i class="fas fa-moon me-2"></i>Dark Mode
                                                </label>
                                            </div>
                                            <small class="text-muted">Enable dark mode for a more comfortable viewing experience in low-light environments.</small>
                                        </div>
                                        
                                        <!-- Timezone Selection -->
                                        <div class="mb-4">
                                            <label for="timezone" class="form-label fw-bold">Timezone</label>
                                            <select class="form-select" id="timezone" name="timezone">
                                                <?php foreach ($timezones as $tz_value => $tz_label): ?>
                                                <option value="<?= $tz_value ?>" <?= $timezone == $tz_value ? 'selected' : '' ?>>
                                                    <?= $tz_label ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted mt-2 d-block">
                                                Current time in selected timezone: 
                                                <span id="current_timezone_time">
                                                    <?php 
                                                    $date = new DateTime();
                                                    $date->setTimezone(new DateTimeZone($timezone));
                                                    echo $date->format('F j, Y - g:i A');
                                                    ?>
                                                </span>
                                            </small>
                                        </div>
                                        
                                        <div class="text-end">
                                            <button type="submit" name="update_preferences" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Save Preferences
                                            </button>
                                        </div>
                                    </form>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle avatar upload
        const avatarContainer = document.querySelector('.avatar-container');
        const avatarOverlay = document.querySelector('.avatar-overlay');
        const avatarUpload = document.getElementById('avatar-upload');
        const removeAvatarBtn = document.getElementById('remove-avatar-btn');
        const removeAvatarInput = document.getElementById('remove_avatar');
        
        if (avatarContainer && avatarOverlay && avatarUpload) {
            avatarOverlay.addEventListener('click', function() {
                avatarUpload.click();
            });
            
            avatarUpload.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let img = avatarContainer.querySelector('img');
                        if (!img) {
                            img = document.createElement('img');
                            avatarContainer.innerHTML = '';
                            avatarContainer.appendChild(img);
                            avatarContainer.appendChild(avatarOverlay);
                        }
                        img.src = e.target.result;
                        
                        // Reset remove avatar flag if user uploads new avatar
                        removeAvatarInput.value = '0';
                    }
                    reader.readAsDataURL(this.files[0]);
                    
                    // Automatically submit the form when file is selected
                    // Create and submit the form
                    const form = avatarUpload.closest('form');
                    form.submit();
                }
            });
        }
        
        // Remove avatar
        if (removeAvatarBtn) {
            removeAvatarBtn.addEventListener('click', function() {
                const avatarContainer = document.querySelector('.avatar-container');
                const img = avatarContainer.querySelector('img');
                if (img) {
                    img.remove();
                    const firstInitial = document.getElementById('first_name').value.charAt(0) || 'U';
                    const lastInitial = document.getElementById('last_name').value.charAt(0) || 'S';
                    avatarContainer.innerHTML = firstInitial + lastInitial;
                    avatarContainer.appendChild(document.querySelector('.avatar-overlay'));
                    
                    // Set the flag to remove avatar in the database
                    removeAvatarInput.value = '1';
                    
                    // Submit the form
                    const form = removeAvatarBtn.closest('form');
                    form.submit();
                }
            });
        }
        
        // Handle dark mode toggle
        const darkModeToggle = document.getElementById('dark_mode');
        if (darkModeToggle) {
            darkModeToggle.addEventListener('change', function() {
                document.body.classList.toggle('dark-mode', this.checked);
            });
        }
        
        // Update timezone preview when changed
        const timezoneSelect = document.getElementById('timezone');
        const currentTimezoneTime = document.getElementById('current_timezone_time');
        
        if (timezoneSelect && currentTimezoneTime) {
            timezoneSelect.addEventListener('change', function() {
                fetchCurrentTimeForTimezone(this.value);
            });
        }
        
        // Function to fetch current time for a timezone
        function fetchCurrentTimeForTimezone(timezone) {
            fetch('includes/get_timezone_time.php?timezone=' + encodeURIComponent(timezone))
                .then(response => response.text())
                .then(data => {
                    currentTimezoneTime.textContent = data;
                })
                .catch(error => {
                    console.error('Error fetching timezone:', error);
                    
                    // Fallback method using client-side
                    try {
                        const now = new Date();
                        const options = { 
                            timeZone: timezone,
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric',
                            hour: 'numeric',
                            minute: 'numeric',
                            hour12: true
                        };
                        
                        const formatter = new Intl.DateTimeFormat('en-US', options);
                        currentTimezoneTime.textContent = formatter.format(now);
                    } catch (error) {
                        console.error('Error formatting time:', error);
                        currentTimezoneTime.textContent = 'Unable to display time for this timezone';
                    }
                });
        }
        
        // Password strength check
        const passwordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStrengthDiv = document.getElementById('password-strength');
        const passwordMatchDiv = document.getElementById('password-match');
        
        if (passwordInput && passwordStrengthDiv) {
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
        }
        
        if (confirmPasswordInput && passwordInput && passwordMatchDiv) {
            confirmPasswordInput.addEventListener('input', function() {
                checkPasswordMatch(passwordInput.value, this.value);
            });
        }
        
        function checkPasswordStrength(password) {
            let strength = 0;
            let feedback = '';
            
            if (password.length < 6) {
                feedback = 'Too short';
            } else {
                // Check for length
                if (password.length >= 8) strength += 1;
                
                // Check for mixed case
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
                
                // Check for numbers
                if (password.match(/\d/)) strength += 1;
                
                // Check for special characters
                if (password.match(/[^a-zA-Z\d]/)) strength += 1;
                
                // Calculate strength
                if (strength < 2) {
                    feedback = '<span class="text-danger">Weak password</span>';
                } else if (strength < 4) {
                    feedback = '<span class="text-warning">Moderate password</span>';
                } else {
                    feedback = '<span class="text-success">Strong password</span>';
                }
            }
            
            passwordStrengthDiv.innerHTML = feedback;
        }
        
        function checkPasswordMatch(password, confirmPassword) {
            if (!confirmPassword) {
                passwordMatchDiv.innerHTML = '';
                return;
            }
            
            if (password === confirmPassword) {
                passwordMatchDiv.innerHTML = '<span class="text-success">Passwords match</span>';
            } else {
                passwordMatchDiv.innerHTML = '<span class="text-danger">Passwords do not match</span>';
            }
        }
        
        // Auto-select tab based on hash
        const url = new URL(window.location.href);
        const hash = url.hash;
        
        if (hash) {
            const tab = document.querySelector(`[data-bs-target="${hash}"]`);
            if (tab) {
                const tabInstance = new bootstrap.Tab(tab);
                tabInstance.show();
            }
        }
    });
    </script>
</body>
</html>