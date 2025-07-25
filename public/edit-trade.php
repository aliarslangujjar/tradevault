<?php
// TradeVault Edit Trade - v2.1
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$title = "Edit Trade";
require_once __DIR__ . '/../includes/header.php';

$user_id = get_user_id();
$trade_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch trade data
$stmt = $pdo->prepare("SELECT * FROM trades WHERE id = ? AND user_id = ?");
$stmt->execute([$trade_id, $user_id]);
$trade = $stmt->fetch();

if (!$trade) {
    header("Location: trades.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $symbol = sanitize($_POST['symbol']);
    $entry_price = (float)$_POST['entry_price'];
    $exit_price = (float)$_POST['exit_price'];
    $position_size = (float)$_POST['position_size'];
    $position_type = sanitize($_POST['position_type']);
    $entry_date = sanitize($_POST['entry_date']);
    $exit_date = sanitize($_POST['exit_date']);
    $emotions = isset($_POST['emotions']) ? implode(',', $_POST['emotions']) : '';
    $notes = sanitize($_POST['notes']);
    
    // Recalculate P&L and R:R
    if ($position_type === 'long') {
        $pnl = ($exit_price - $entry_price) * $position_size;
    } else {
        $pnl = ($entry_price - $exit_price) * $position_size;
    }
    
    $risk = abs($entry_price - $exit_price);
    $reward = abs($pnl / $position_size);
    $risk_reward_ratio = $risk > 0 ? $reward / $risk : 0;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE trades SET
                symbol = ?,
                entry_price = ?,
                exit_price = ?,
                position_size = ?,
                position_type = ?,
                entry_date = ?,
                exit_date = ?,
                risk_reward_ratio = ?,
                pnl = ?,
                emotions = ?,
                notes = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $symbol, $entry_price, $exit_price, $position_size, $position_type,
            $entry_date, $exit_date, $risk_reward_ratio, $pnl, $emotions, $notes,
            $trade_id, $user_id
        ]);
        
        header("Location: trades.php?updated=1");
        exit();
    } catch (PDOException $e) {
        $error = "Failed to update trade: " . $e->getMessage();
    }
}

// Convert emotions string to array for checkboxes
$emotions_array = explode(',', $trade['emotions']);
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Trade</h2>
        <a href="trades.php" class="btn btn-outline-secondary">Back to Trades</a>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="post">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="symbol" class="form-control" 
                                   value="<?php echo htmlspecialchars($trade['symbol']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Position Type <span class="text-danger">*</span></label>
                            <select name="position_type" class="form-select" required>
                                <option value="long" <?php echo $trade['position_type'] === 'long' ? 'selected' : ''; ?>>Long</option>
                                <option value="short" <?php echo $trade['position_type'] === 'short' ? 'selected' : ''; ?>>Short</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Entry Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.00001" name="entry_price" class="form-control" 
                                   value="<?php echo htmlspecialchars($trade['entry_price']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Exit Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.00001" name="exit_price" class="form-control" 
                                   value="<?php echo htmlspecialchars($trade['exit_price']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Position Size <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" name="position_size" class="form-control" 
                                   value="<?php echo htmlspecialchars($trade['position_size']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Entry Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="entry_date" class="form-control" 
                                   value="<?php echo date('Y-m-d\TH:i', strtotime($trade['entry_date'])); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Exit Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="exit_date" class="form-control" 
                                   value="<?php echo date('Y-m-d\TH:i', strtotime($trade['exit_date'])); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Emotions</label>
                            <div class="row">
                                <?php foreach (array_chunk(ALLOWED_EMOTIONS, 2) as $emotion_chunk): ?>
                                    <div class="col-6">
                                        <?php foreach ($emotion_chunk as $emotion): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="emotions[]" 
                                                       value="<?php echo $emotion; ?>" id="emotion_<?php echo $emotion; ?>"
                                                       <?php echo in_array($emotion, $emotions_array) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="emotion_<?php echo $emotion; ?>">
                                                    <?php echo ucfirst($emotion); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($trade['notes']); ?></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>P&L:</strong> 
                        <span id="pnl-display" class="<?php echo $trade['pnl'] > 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo number_format($trade['pnl'], 2); ?>
                        </span>
                    </div>
                    <div>
                        <strong>Risk/Reward:</strong> 
                        <span><?php echo number_format($trade['risk_reward_ratio'], 2); ?></span>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">Update Trade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live P&L calculation
    const calculatePnL = () => {
        const entry = parseFloat(document.querySelector('input[name="entry_price"]').value) || 0;
        const exit = parseFloat(document.querySelector('input[name="exit_price"]').value) || 0;
        const size = parseFloat(document.querySelector('input[name="position_size"]').value) || 0;
        const type = document.querySelector('select[name="position_type"]').value;
        
        let pnl;
        if (type === 'long') {
            pnl = (exit - entry) * size;
        } else {
            pnl = (entry - exit) * size;
        }
        
        const pnlDisplay = document.getElementById('pnl-display');
        if (pnlDisplay) {
            pnlDisplay.textContent = pnl.toFixed(2);
            pnlDisplay.className = pnl >= 0 ? 'text-success' : 'text-danger';
        }
    };
    
    // Add event listeners
    ['entry_price', 'exit_price', 'position_size', 'position_type'].forEach(field => {
        document.querySelector(`[name="${field}"]`).addEventListener('input', calculatePnL);
    });
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>