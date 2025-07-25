<?php
// TradeVault Add Trade - v2.1
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

$title = "Add Trade";
require_once __DIR__ . '/../includes/header.php';

$user_id = get_user_id();

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
    
    // Calculate P&L
    if ($position_type === 'long') {
        $pnl = ($exit_price - $entry_price) * $position_size;
    } else {
        $pnl = ($entry_price - $exit_price) * $position_size;
    }
    
    // Calculate risk-reward ratio
    $risk = abs($entry_price - $exit_price);
    $reward = abs($pnl / $position_size);
    $risk_reward_ratio = $risk > 0 ? $reward / $risk : 0;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO trades 
            (user_id, symbol, entry_price, exit_price, position_size, position_type, 
             entry_date, exit_date, risk_reward_ratio, pnl, emotions, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id, $symbol, $entry_price, $exit_price, $position_size, $position_type,
            $entry_date, $exit_date, $risk_reward_ratio, $pnl, $emotions, $notes
        ]);
        
        header("Location: trades.php?added=1");
        exit();
    } catch (PDOException $e) {
        $error = "Failed to add trade: " . $e->getMessage();
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Add New Trade</h2>
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
                            <input type="text" name="symbol" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Position Type <span class="text-danger">*</span></label>
                            <select name="position_type" class="form-select" required>
                                <option value="long">Long</option>
                                <option value="short">Short</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Entry Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.00001" name="entry_price" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Exit Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.00001" name="exit_price" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Position Size <span class="text-danger">*</span></label>
                            <input type="number" step="0.001" name="position_size" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Entry Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="entry_date" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Exit Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="exit_date" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Emotions</label>
                            <div class="row">
                                <?php foreach (array_chunk(ALLOWED_EMOTIONS, 2) as $emotion_chunk): ?>
                                    <div class="col-6">
                                        <?php foreach ($emotion_chunk as $emotion): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="emotions[]" 
                                                       value="<?php echo $emotion; ?>" id="emotion_<?php echo $emotion; ?>">
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
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Save Trade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default dates
    const now = new Date();
    const timezoneOffset = now.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(now - timezoneOffset)).toISOString().slice(0, -8);
    
    document.querySelector('input[name="entry_date"]').value = localISOTime;
    document.querySelector('input[name="exit_date"]').value = localISOTime;
    
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
    
    // Initial calculation
    calculatePnL();
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>