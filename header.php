<?php
// header.php - Komponen Header yang dapat digunakan di semua halaman

// Pastikan session sudah dimulai dan user sudah login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Sportory' : 'Sportory - Sistem Manajemen Alat Olahraga'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            min-height: 100vh;
            padding: 20px;
            color: #1f2937;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .header {
            background: white;
            padding: 24px 32px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            margin-bottom: 32px;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-brand img {
            height: 60px;
            width: auto;
            transition: all 0.3s ease;
        }

        .header-brand img:hover {
            transform: scale(1.05);
        }

        .header-title h1 {
            color: #2563eb;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .header-subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }

        .user-info-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            background: #f3f4f6;
            border-radius: 12px;
        }

        .user-info span {
            color: #1f2937;
            font-weight: 500;
            font-size: 14px;
        }

        .badge {
            background: #2563eb;
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Navigation */
        .header-nav {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 20px;
            border-top: 2px solid #f3f4f6;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .btn-info {
            background: #3b82f6;
            color: white;
        }

        .btn-secondary {
            background: #8b5cf6;
            color: white;
        }

        .btn-warning {
            background: #f59e0b;
            color: white;
        }

        .btn.active {
            background: #1e40af;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }

        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideInDown 0.4s ease;
            font-size: 14px;
            font-weight: 500;
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: all 0.3s ease;
            padding: 0;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .header-top {
                flex-direction: column;
                text-align: center;
            }

            .header-brand {
                flex-direction: column;
            }

            .user-info-section {
                width: 100%;
                justify-content: center;
            }

            .header-nav {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .header {
                padding: 20px;
            }

            .header-title h1 {
                font-size: 24px;
            }

            .header-nav {
                flex-direction: column;
                gap: 8px;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .user-info {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <!-- Top Section: Brand & User Info -->
            <div class="header-top">
                <div class="header-brand">
                    <a href="dashboard.php">
                        <img src="img/logo.png" alt="Sportory Logo">
                    </a>
                    <div class="header-title">
                        <h1><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                        <p class="header-subtitle">Sistem Manajemen Alat Olahraga</p>
                    </div>
                </div>
                <div class="user-info-section">
                    <div class="user-info">
                        <span>👤 <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
                        <span class="badge"><?php echo htmlspecialchars($_SESSION['role']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Navigation Section -->
            <div class="header-nav">
                <a href="dashboard.php" class="btn btn-primary <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
                    🏠 Dashboard
                </a>
                 <a href="profile.php" class="btn btn-warning <?php echo $current_page == 'profile' ? 'active' : ''; ?>">
                    👤 Profile
                </a>
                <a href="daftar_rekomendasi.php" class="btn btn-secondary <?php echo $current_page == 'daftar_rekomendasi' ? 'active' : ''; ?>">
                    💡 Rekomendasi
                </a>
                <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="manage_users.php" class="btn btn-info <?php echo $current_page == 'manage_users' ? 'active' : ''; ?>">
                    👥 Kelola User
                </a>
                <?php endif; ?>
                <button class="btn btn-danger" onclick="logout()">
                    🚪 Logout
                </button>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <span>✅ <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
            <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <span>⚠️ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
        </div>
        <?php endif; ?>

        <script>
            function logout() {
                if (confirm('Apakah Anda yakin ingin logout?')) {
                    window.location.href = 'logout.php';
                }
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                });
            }, 5000);
        </script>