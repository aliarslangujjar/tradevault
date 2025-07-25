<<<<<<< HEAD
<?php
// TradeVault Trades List - v2.4
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$title = "My Trades";
require_once __DIR__ . '/../includes/header.php';

$user_id = get_user_id();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = MAX_TRADES_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Handle trade deletion
if (isset($_GET['delete'])) {
    $trade_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM trades WHERE id = ? AND user_id = ?");
    $stmt->execute([$trade_id, $user_id]);
    header("Location: trades.php?deleted=1");
    exit();
}

// Filtering
$filter_symbol = isset($_GET['symbol']) ? sanitize($_GET['symbol']) : '';
$filter_emotion = isset($_GET['emotion']) ? sanitize($_GET['emotion']) : '';
$filter_type = isset($_GET['type']) ? sanitize($_GET['type']) : '';

// Build WHERE clause
$where = "user_id = ?";
$params = [$user_id];

if ($filter_symbol) {
    $where .= " AND symbol LIKE ?";
    $params[] = "%$filter_symbol%";
}

if ($filter_emotion) {
    $where .= " AND emotions LIKE ?";
    $params[] = "%$filter_emotion%";
}

if ($filter_type) {
    $where .= " AND position_type = ?";
    $params[] = $filter_type;
}

// Get total trades count
$count_sql = "SELECT COUNT(*) FROM trades WHERE $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_trades = $stmt->fetchColumn();
$total_pages = ceil($total_trades / $per_page);

// Get trades with pagination - MARIADB COMPATIBLE VERSION
// Solution 1: Use string concatenation for LIMIT/OFFSET (with proper sanitization)
$sql = "SELECT * FROM trades WHERE $where ORDER BY entry_date DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Solution 2: Alternative approach using bound parameters (if your MariaDB version supports it)
/*
$sql = "SELECT * FROM trades WHERE $where ORDER BY entry_date DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
*/

$trades = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Trades</h2>
        <a href="add-trade.php" class="btn btn-primary">
            <i class="bi bi-plus"></i> Add Trade
        </a>
    </div>
    
    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Trade added successfully!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Trade updated successfully!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Trade deleted successfully!</div>
    <?php endif; ?>
    
    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Symbol</label>
                    <input type="text" name="symbol" class="form-control" value="<?php echo htmlspecialchars($filter_symbol); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Position Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="long" <?php echo $filter_type === 'long' ? 'selected' : ''; ?>>Long</option>
                        <option value="short" <?php echo $filter_type === 'short' ? 'selected' : ''; ?>>Short</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Emotion</label>
                    <select name="emotion" class="form-select">
                        <option value="">All Emotions</option>
                        <?php foreach (ALLOWED_EMOTIONS as $emotion): ?>
                            <option value="<?php echo $emotion; ?>" <?php echo $filter_emotion === $emotion ? 'selected' : ''; ?>>
                                <?php echo ucfirst($emotion); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="trades.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Trades Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Symbol</th>
                            <th>Type</th>
                            <th>Entry</th>
                            <th>Exit</th>
                            <th>Size</th>
                            <th>P&L</th>
                            <th>R:R</th>
                            <th>Emotions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trades)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">No trades found. <a href="add-trade.php">Add your first trade</a></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trades as $trade): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($trade['entry_date'])); ?></td>
                                <td><?php echo htmlspecialchars($trade['symbol']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $trade['position_type'] === 'long' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($trade['position_type']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($trade['entry_price'], 2); ?></td>
                                <td>$<?php echo number_format($trade['exit_price'], 2); ?></td>
                                <td><?php echo number_format($trade['position_size'], 2); ?></td>
                                <td class="<?php echo $trade['pnl'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                    $<?php echo number_format($trade['pnl'], 2); ?>
                                </td>
                                <td><?php echo number_format($trade['risk_reward_ratio'], 2); ?></td>
                                <td>
                                    <?php 
                                    $emotions = explode(',', $trade['emotions']);
                                    foreach ($emotions as $emotion) {
                                        if (!empty($emotion)) {
                                            echo '<span class="badge bg-secondary me-1">'.ucfirst($emotion).'</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="edit-trade.php?id=<?php echo $trade['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="trades.php?delete=<?php echo $trade['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this trade?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&symbol=<?php echo $filter_symbol; ?>&emotion=<?php echo $filter_emotion; ?>&type=<?php echo $filter_type; ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&symbol=<?php echo $filter_symbol; ?>&emotion=<?php echo $filter_emotion; ?>&type=<?php echo $filter_type; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&symbol=<?php echo $filter_symbol; ?>&emotion=<?php echo $filter_emotion; ?>&type=<?php echo $filter_type; ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
=======
<?php
// TradeVault Trades List - v2.4
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$title = "My Trades";
require_once __DIR__ . '/../includes/header.php';

$user_id = get_user_id();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = MAX_TRADES_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Handle trade deletion
if (isset($_GET['delete'])) {
    $trade_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM trades WHERE id = ? AND user_id = ?");
    $stmt->execute([$trade_id, $user_id]);
    header("Location: trades.php?deleted=1");
    exit();
}

// Filtering
$filter_symbol = isset($_GET['symbol']) ? sanitize($_GET['symbol']) : '';
$filter_emotion = isset($_GET['emotion']) ? sanitize($_GET['emotion']) : '';
$filter_type = isset($_GET['type']) ? sanitize($_GET['type']) : '';

// Build WHERE clause
$where = "user_id = ?";
$params = [$user_id];

if ($filter_symbol) {
    $where .= " AND symbol LIKE ?";
    $params[] = "%$filter_symbol%";
}

if ($filter_emotion) {
    $where .= " AND emotions LIKE ?";
    $params[] = "%$filter_emotion%";
}

if ($filter_type) {
    $where .= " AND position_type = ?";
    $params[] = $filter_type;
}

// Get total trades count
$count_sql = "SELECT COUNT(*) FROM trades WHERE $where";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_trades = $stmt->fetchColumn();
$total_pages = ceil($total_trades / $per_page);

// Get trades with pagination - MARIADB COMPATIBLE VERSION
// Solution 1: Use string concatenation for LIMIT/OFFSET (with proper sanitization)
$sql = "SELECT * FROM trades WHERE $where ORDER BY entry_date DESC LIMIT $per_page OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Solution 2: Alternative approach using bound parameters (if your MariaDB version supports it)
/*
$sql = "SELECT * FROM trades WHERE $where ORDER BY entry_date DESC LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
*/

$trades = $stmt->fetchAll();
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Trades</h2>
        <a href="add-trade.php" class="btn btn-primary">
            <i class="bi bi-plus"></i> Add Trade
        </a>
    </div>
    
    <?php if (isset($_GET['added'])): ?>
        <div class="alert alert-success">Trade added successfully!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Trade updated successfully!</div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Trade deleted successfully!</div>
    <?php endif; ?>
    
    <!-- Filter Card -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Symbol</label>
                    <input type="text" name="symbol" class="form-control" value="<?php echo htmlspecialchars($filter_symbol); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Position Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="long" <?php echo $filter_type === 'long' ? 'selected' : ''; ?>>Long</option>
                        <option value="short" <?php echo $filter_type === 'short' ? 'selected' : ''; ?>>Short</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Emotion</label>
                    <select name="emotion" class="form-select">
                        <option value="">All Emotions</option>
                        <?php foreach (ALLOWED_EMOTIONS as $emotion): ?>
                            <option value="<?php echo $emotion; ?>" <?php echo $filter_emotion === $emotion ? 'selected' : ''; ?>>
                                <?php echo ucfirst($emotion); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="trades.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Trades Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Symbol</th>
                            <th>Type</th>
                            <th>Entry</th>
                            <th>Exit</th>
                            <th>Size</th>
                            <th>P&L</th>
                            <th>R:R</th>
                            <th>Emotions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($trades)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">No trades found. <a href="add-trade.php">Add your first trade</a></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($trades as $trade): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($trade['entry_date'])); ?></td>
                                <td><?php echo htmlspecialchars($trade['symbol']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $trade['position_type'] === 'long' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($trade['position_type']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($trade['entry_price'], 2); ?></td>
                                <td>$<?php echo number_format($trade['exit_price'], 2); ?></td>
                                <td><?php echo number_format($trade['position_size'], 2); ?></td>
                                <td class="<?php echo $trade['pnl'] > 0 ? 'text-success' : 'text-danger'; ?>">
                                    $<?php echo number_format($trade['pnl'], 2); ?>
                                </td>
                                <td><?php echo number_format($trade['risk_reward_ratio'], 2); ?></td>
                                <td>
                                    <?php 
                                    $emotions = explode(',', $trade['emotions']);
                                    foreach ($emotions as $emotion) {
                                        if (!empty($emotion)) {
                                            echo '<span class="badge bg-secondary me-1">'.ucfirst($emotion).'</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="edit-trade.php?id=<?php echo $trade['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="trades.php?delete=<?php echo $trade['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           onclick="return confirm('Are you sure you want to delete this trade?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&symbol=<?php echo $filter_symbol; ?>&emotion=<?php echo $filter_emotion; ?>&type=<?php echo $filter_type; ?>">Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&symbol=<?php echo $filter_symbol; ?>&emotion=<?php echo $filter_emotion; ?>&type=<?php echo $filter_type; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&symbol=<?php echo $filter_symbol; ?>&emotion=<?php echo $filter_emotion; ?>&type=<?php echo $filter_type; ?>">Next</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
>>>>>>> 189a777281163ce1a60fd0e2e61beba42a296835
require_once __DIR__ . '/../includes/footer.php';