<?php
// TradeVault Dashboard - v2.1
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$title = "Dashboard";
require_once __DIR__ . '/../includes/header.php';

$user_id = get_user_id();

// Get statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_trades,
        SUM(CASE WHEN pnl > 0 THEN 1 ELSE 0 END) as winning_trades,
        SUM(pnl) as total_pnl,
        AVG(risk_reward_ratio) as avg_rr,
        MAX(pnl) as best_trade,
        MIN(pnl) as worst_trade
    FROM trades 
    WHERE user_id = ?
");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Get monthly performance data
$stmt = $pdo->prepare("
    SELECT 
        DATE_FORMAT(entry_date, '%Y-%m') as month,
        COUNT(*) as trade_count,
        SUM(pnl) as monthly_pnl
    FROM trades
    WHERE user_id = ?
    GROUP BY month
    ORDER BY month
");
$stmt->execute([$user_id]);
$monthly_data = $stmt->fetchAll();

// Get recent trades
$stmt = $pdo->prepare("SELECT * FROM trades WHERE user_id = ? ORDER BY entry_date DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_trades = $stmt->fetchAll();
?>

<div class="dashboard-container">
    <h2 class="mb-4">Dashboard Overview</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h5>Total Trades</h5>
            <p class="display-4"><?php echo $stats['total_trades'] ?? 0; ?></p>
        </div>
        <div class="stat-card">
            <h5>Win Rate</h5>
            <p class="display-4">
                <?php echo ($stats['total_trades'] > 0) 
                    ? round(($stats['winning_trades'] / $stats['total_trades']) * 100) . '%' 
                    : '0%'; ?>
            </p>
        </div>
        <div class="stat-card">
            <h5>Avg R:R</h5>
            <p class="display-4"><?php echo round($stats['avg_rr'] ?? 0, 2); ?></p>
        </div>
        <div class="stat-card">
            <h5>Total P&L</h5>
            <p class="display-4">$<?php echo number_format($stats['total_pnl'] ?? 0, 2); ?></p>
        </div>
    </div>
    
    <div class="chart-container mb-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Monthly Performance</h5>
                <canvas id="monthlyChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="recent-trades">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Recent Trades</h4>
            <a href="trades.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Symbol</th>
                        <th>Type</th>
                        <th>Entry</th>
                        <th>Exit</th>
                        <th>P&L</th>
                        <th>R:R</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_trades as $trade): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($trade['symbol']); ?></td>
                        <td><span class="badge bg-<?php echo $trade['position_type'] === 'long' ? 'success' : 'danger'; ?>">
                            <?php echo ucfirst($trade['position_type']); ?>
                        </span></td>
                        <td>$<?php echo number_format($trade['entry_price'], 2); ?></td>
                        <td>$<?php echo number_format($trade['exit_price'], 2); ?></td>
                        <td class="<?php echo $trade['pnl'] > 0 ? 'text-success' : 'text-danger'; ?>">
                            $<?php echo number_format($trade['pnl'], 2); ?>
                        </td>
                        <td><?php echo number_format($trade['risk_reward_ratio'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Performance Chart
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($monthly_data, 'month')); ?>,
        datasets: [{
            label: 'Profit/Loss',
            data: <?php echo json_encode(array_column($monthly_data, 'monthly_pnl')); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: false,
                title: {
                    display: true,
                    text: 'Amount ($)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Month'
                }
            }
        }
    }
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>