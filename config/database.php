<?php
// TradeVault Database Configuration - v2.6
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tradevault');

try {
    // First connect without selecting database
    $pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS ".DB_NAME);
    $pdo->exec("USE ".DB_NAME);
    
    // Create tables - properly formatted SQL
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS trades (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            symbol VARCHAR(20) NOT NULL,
            entry_price DECIMAL(15,5) NOT NULL,
            exit_price DECIMAL(15,5) NOT NULL,
            position_size DECIMAL(15,5) NOT NULL,
            position_type ENUM('long', 'short') NOT NULL,
            entry_date DATETIME NOT NULL,
            exit_date DATETIME NOT NULL,
            risk_reward_ratio DECIMAL(10,2),
            pnl DECIMAL(15,5),
            emotions VARCHAR(255),
            notes TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX (email),
            INDEX (token)
        );
        
        CREATE TABLE IF NOT EXISTS trade_attachments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            trade_id INT NOT NULL,
            user_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            filepath VARCHAR(255) NOT NULL,
            filetype VARCHAR(50) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (trade_id) REFERENCES trades(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
    ";
    
    // Execute the SQL in one go
    $pdo->exec($sql);
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>