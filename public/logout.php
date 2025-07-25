<?php
// TradeVault Logout - v1.1
session_start();
session_unset();
session_destroy();
header("Location: login.php");
exit();
?>