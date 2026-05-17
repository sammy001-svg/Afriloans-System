<?php 
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ZETA CREDIT</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 260px;
        }
        body { background: #f1f5f9; display: flex; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: #1e293b;
            color: white;
            position: fixed;
            height: 100vh;
            padding: 2rem 0;
            display: flex;
            flex-direction: column;
        }
        .sidebar-logo { padding: 0 2rem 2rem; border-bottom: 1px solid #334155; margin-bottom: 1rem; }
        .sidebar-menu { list-style: none; flex: 1; }
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 2rem;
            color: #94a3b8;
            text-decoration: none;
            transition: 0.3s;
        }
        .sidebar-menu li a:hover, .sidebar-menu li a.active {
            color: white;
            background: #334155;
            border-left: 4px solid var(--primary);
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 2rem;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }
        
        /* Dashboard Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Tables */
        .table-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }
        .table-header { padding: 1.5rem 2rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f8fafc; padding: 1rem 2rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; }
        td { padding: 1rem 2rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
        
        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #dcfce7; color: #166534; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-logo">
            <a href="dashboard.php" class="logo" style="color: white;">
                <i class="fas fa-hand-holding-dollar"></i>
                ZETA <span>CREDIT</span>
            </a>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="loans.php" class="<?= basename($_SERVER['PHP_SELF']) == 'loans.php' ? 'active' : '' ?>"><i class="fas fa-file-invoice-dollar"></i> Loan Apps</a></li>
            <li><a href="clients.php" class="<?= basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> Clients</a></li>
            <li><a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="payments.php" class="<?= basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : '' ?>"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="top-bar">
            <h2><?= str_replace('.php', '', ucfirst(basename($_SERVER['PHP_SELF']))) ?></h2>
            <div class="user-info">
                <span>Welcome, <strong><?= $_SESSION['full_name'] ?></strong></span>
            </div>
        </div>
