<!-- File: app/views/layouts/dashboard_header.php (UPDATED) -->
<?php
$auth = Auth::getInstance();
$currentUser = $auth->user();

// Get unread notification count
$notificationRepo = new NotificationRepository();
$unreadCount = $notificationRepo->countUnread($auth->id());
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Dashboard' ?> - Sistem Reservasi Badminton</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            position: relative;
        }
        
        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            padding-left: 30px;
        }
        
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid white;
        }
        
        /* 🔔 Notification Badge */
        .notification-badge {
            background: #FF5252;
            color: white;
            font-size: 0.7rem;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 20px;
            text-align: center;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 260px;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: white;
            color: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .user-details {
            flex: 1;
        }
        
        .user-details .name {
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .user-details .role {
            font-size: 0.8rem;
            opacity: 0.8;
            text-transform: capitalize;
        }
        
        .btn-logout {
            width: 100%;
            padding: 10px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }
        
        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .top-bar h1 {
            color: #333;
            font-size: 1.8rem;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .stat-card .value {
            color: #333;
            font-size: 2rem;
            font-weight: bold;
        }
        
        /* Content Card */
        .content-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .content-card h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .badge-admin {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-manager {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .badge-staff {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🏸 Badminton MVC</h2>
                <p>Role-Based System</p>
            </div>
            
            <nav class="sidebar-menu">
                <a href="<?= $base_url ?>?c=dashboard&a=index">
                    📊 Dashboard
                </a>
                
                <a href="<?= $base_url ?>?c=reservation&a=index">
                    📅 Reservasi
                </a>
                
                <?php if ($auth->hasRole('manager') || $auth->hasRole('admin')): ?>
                <a href="<?= $base_url ?>?c=reservation&a=managerPending">
                    <span>⏳ Pending Approvals</span>
                    <?php 
                    $reservationRepo = new ReservationRepository();
                    $pendingCount = count($reservationRepo->getPendingForManager());
                    if ($pendingCount > 0): 
                    ?>
                    <span class="notification-badge"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
                
                <a href="<?= $base_url ?>?c=reservation&a=pendingCancel">
                    <span>🗑️ Pending Cancellations</span>
                    <?php 
                    $cancelCount = count($reservationRepo->getPendingCancel());
                    if ($cancelCount > 0): 
                    ?>
                    <span class="notification-badge"><?= $cancelCount ?></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                
                <?php if ($auth->hasPermission('manage_courts')): ?>
                <a href="<?= $base_url ?>?c=court&a=index">
                    🏟️ Lapangan
                </a>
                <?php endif; ?>
                
                <?php if ($auth->hasPermission('view_all_data')): ?>
                <a href="<?= $base_url ?>?c=recyclebin&a=index">
                    🗑️ Recycle Bin
                </a>
                <?php endif; ?>
                
                <?php if ($auth->isAdmin()): ?>
                <a href="<?= $base_url ?>?c=user&a=index">
                    👥 Kelola User
                </a>
                <?php endif; ?>
                
                <a href="<?= $base_url ?>?c=notification&a=index">
                    <span>🔔 Notifikasi</span>
                    <?php if ($unreadCount > 0): ?>
                    <span class="notification-badge"><?= $unreadCount ?></span>
                    <?php endif; ?>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($currentUser->getFullName(), 0, 1)) ?>
                    </div>
                    <div class="user-details">
                        <div class="name"><?= htmlspecialchars($currentUser->getFullName()) ?></div>
                        <div class="role"><?= htmlspecialchars($currentUser->getRole()) ?></div>
                    </div>
                </div>
                <form method="POST" action="<?= $base_url ?>?c=auth&a=logout">
                    <button type="submit" class="btn-logout">🚪 Logout</button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>