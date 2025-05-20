<?php
// login.php
session_start();
require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {  
        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Car Dealership</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #ff4d4d 0%, #ff6b6b 100%);
            position: relative;
            overflow: hidden;
        }

        /* Decorative car silhouette background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff15" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,197.3C1248,203,1344,149,1392,122.7L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.7;
        }

        .login-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .login-box {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px 40px 100px; /* Updated padding to account for road height */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
            min-height: 600px; /* Increased height to accommodate road */
        }

        .login-box:hover {
            transform: translateY(-5px);
        }

        .car-icon {
            width: 80px;
            height: 80px;
            margin: 40px auto 30px;
            background: #ff4d4d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(255, 77, 77, 0.3);
        }

        .car-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }

        h1 {
            text-align: center;
            color: #ff4d4d;
            margin-bottom: 40px;
            font-size: 28px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
            color: #333;
        }

        input:focus {
            outline: none;
            border-color: #ff4d4d;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(255, 77, 77, 0.1);
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff4d4d 0%, #ff1a1a 100%);
            color: white;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(255, 77, 77, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ff1a1a 0%, #e60000 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 77, 77, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(255, 77, 77, 0.2);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .alert-danger {
            background-color: #fff5f5;
            color: #ff4d4d;
            border: 1px solid #ffdddd;
        }

        /* Road animation at the bottom */
        .road {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: #333;
            border-top: 4px solid #000;
            overflow: hidden;
        }

        .road::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -100%;
            width: 200%;
            height: 4px;
            background: repeating-linear-gradient(90deg, #fff 0px, #fff 40px, transparent 40px, transparent 80px);
            animation: roadMove 2s linear infinite;
        }

        @keyframes roadMove {
            0% { transform: translateX(0); }
            100% { transform: translateX(40px); }
        }

        /* Car animation on the road */
        .animated-car {
            position: absolute;
            bottom: 55px; 
            right: -50px;
            width: 40px;
            height: 20px;
            animation: carDrive 10s linear infinite;
        }

        .animated-car svg {
            width: 100%;
            height: 100%;
            fill: #ff4d4d;
            opacity: 0.7;
        }

        @keyframes carDrive {
            0% { right: -50px; }
            100% { right: calc(100% + 50px); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <!-- Animated car on the road -->
            <div class="animated-car">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 6l3 4h2c.55 0 1 .45 1 1v3c0 .55-.45 1-1 1h-1.05c-.02.16-.05.32-.1.47.35.39.58.9.58 1.53 0 1.24-1.01 2.25-2.25 2.25s-2.25-1.01-2.25-2.25c0-.63.26-1.16.65-1.56-.04-.1-.07-.21-.1-.32H7.52c-.03.11-.06.22-.1.32.39.4.65.93.65 1.56 0 1.24-1.01 2.25-2.25 2.25s-2.25-1.01-2.25-2.25c0-.63.23-1.14.58-1.53-.05-.15-.08-.31-.1-.47H3c-.55 0-1-.45-1-1v-3c0-.55.45-1 1-1h2l3-4h8m-8 1.5h8L19 10h-14l3-2.5M17.18 17c0-.41.34-.75.75-.75s.75.34.75.75-.34.75-.75.75-.75-.34-.75-.75m-11.36 0c0-.41.34-.75.75-.75s.75.34.75.75-.34.75-.75.75-.75-.34-.75-.75M20 11h-16v2h16v-2z"/>
                </svg>
            </div>
            
            <div class="car-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M16 6l3 4h2c.55 0 1 .45 1 1v3c0 .55-.45 1-1 1h-1.05c-.02.16-.05.32-.1.47.35.39.58.9.58 1.53 0 1.24-1.01 2.25-2.25 2.25s-2.25-1.01-2.25-2.25c0-.63.26-1.16.65-1.56-.04-.1-.07-.21-.1-.32H7.52c-.03.11-.06.22-.1.32.39.4.65.93.65 1.56 0 1.24-1.01 2.25-2.25 2.25s-2.25-1.01-2.25-2.25c0-.63.23-1.14.58-1.53-.05-.15-.08-.31-.1-.47H3c-.55 0-1-.45-1-1v-3c0-.55.45-1 1-1h2l3-4h8m-8 1.5h8L19 10h-14l3-2.5M17.18 17c0-.41.34-.75.75-.75s.75.34.75.75-.34.75-.75.75-.75-.34-.75-.75m-11.36 0c0-.41.34-.75.75-.75s.75.34.75.75-.34.75-.75.75-.75-.34-.75-.75M20 11h-16v2h16v-2z"/>
                </svg>
            </div>
            
            <div class="decorative-dots dots-top-left"></div>
            <div class="decorative-dots dots-top-right"></div>
            <div class="decorative-dots dots-bottom-left"></div>
            <div class="decorative-dots dots-bottom-right"></div>
            
            <h1>AUTOBUBA Dashboard</h1>
            
            <form method="POST">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="road"></div>
        </div>
    </div>
</body>
</html>