<?php
/**
 * Initialize the database and create tables if they don't exist.
 */

$db_file = './data/publix_tracker.db';
$data_dir = './data';

// Create data directory if it doesn't exist
if (!file_exists($data_dir)) {
    mkdir($data_dir, 0775, true);
    chown($data_dir, 'www-data');
    chgrp($data_dir, 'www-data');
}

try {
    $db = new SQLite3($db_file);
    
    // Create purchases table
    $db->exec('
        CREATE TABLE IF NOT EXISTS purchases (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            purchase_date TEXT NOT NULL,
            item_name TEXT NOT NULL,
            price REAL NOT NULL,
            on_sale BOOLEAN NOT NULL,
            taxable BOOLEAN,
            email_id TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ');
    
    // Create indexes for faster queries
    $db->exec('
        CREATE INDEX IF NOT EXISTS idx_purchase_date ON purchases(purchase_date)
    ');
    
    $db->exec('
        CREATE INDEX IF NOT EXISTS idx_item_name ON purchases(item_name)
    ');
    
    $db->close();
    
    // Set proper permissions
    if (file_exists($db_file)) {
        chmod($db_file, 0664);
        chown($db_file, 'www-data');
        chgrp($db_file, 'www-data');
    }
    
    return true;
} catch (Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
    return false;
}
