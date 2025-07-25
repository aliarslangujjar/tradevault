<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'TradeVault'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <?php if (isset($hide_nav)): ?>
        <link href="../assets/css/auth.css" rel="stylesheet">
    <?php else: ?>
        <link href="../assets/css/style.css" rel="stylesheet">
    <?php endif; ?>
    <?php if (isset($charts) && $charts): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
</head>
<body>
    <?php if (!isset($hide_nav)): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-safe"></i> TradeVault
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="trades.php">
                            <i class="bi bi-list-check"></i> Trades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add-trade.php">
                            <i class="bi bi-plus-circle"></i> Add Trade
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['username'] ?? ''; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <main class="<?php echo isset($hide_nav) ? 'auth-main' : 'container py-4'; ?>">