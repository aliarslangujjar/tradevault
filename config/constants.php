<?php
// TradeVault Constants - v2.1
define('APP_NAME', 'TradeVault');
define('APP_VERSION', '2.1');
define('MAX_TRADES_PER_PAGE', 20);
define('ALLOWED_EMOTIONS', ['confident', 'fearful', 'greedy', 'patient', 'impulsive', 'disciplined']);

// Date format constants
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Path constants
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('ASSETS_PATH', BASE_PATH . '/assets');
?>