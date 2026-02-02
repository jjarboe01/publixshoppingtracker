<?php
require_once 'init_db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raw Database - Publix Tracker</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .db-table {
            font-size: 0.9em;
            width: 100%;
            overflow-x: auto;
        }
        .db-table table {
            min-width: 1200px;
        }
        .db-table th {
            position: sticky;
            top: 0;
            background: #00753e;
            z-index: 10;
        }
        .db-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-box h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 0.9em;
            font-weight: 600;
        }
        .stat-box .value {
            font-size: 1.5em;
            font-weight: bold;
            color: #00753e;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🗄️ Raw Database View</h1>
        </header>
        
        <nav>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="top-items.php" class="btn btn-primary">🏆 Top Items</a>
                <a href="monthly.php" class="btn btn-primary">📅 Monthly View</a>
                <a href="yearly.php" class="btn btn-primary">📆 Yearly View</a>
                <a href="sync.php" class="btn btn-warning">🔄 Sync Receipts</a>
                <a href="settings.php" class="btn btn-secondary">⚙️ Settings</a>
            </div>
        </nav>
        
        <div class="content">
            <?php
            // Database connection
            $db_file = './data/publix_tracker.db';
            
            try {
                $db = new SQLite3($db_file);
                
                // Get database statistics
                $total_records = $db->querySingle("SELECT COUNT(*) FROM purchases");
                $total_spent = $db->querySingle("SELECT SUM(price) FROM purchases WHERE on_sale = 0");
                $total_savings = $db->querySingle("SELECT SUM(savings) FROM purchases");
                $unique_items = $db->querySingle("SELECT COUNT(DISTINCT item_name) FROM purchases");
                $date_range_min = $db->querySingle("SELECT MIN(purchase_date) FROM purchases");
                $date_range_max = $db->querySingle("SELECT MAX(purchase_date) FROM purchases");
                $unique_dates = $db->querySingle("SELECT COUNT(DISTINCT purchase_date) FROM purchases");
                $on_sale_items = $db->querySingle("SELECT COUNT(*) FROM purchases WHERE on_sale = 1");
                
                ?>
                
                <div class="db-stats">
                    <div class="stat-box">
                        <h4>Total Records</h4>
                        <div class="value"><?php echo number_format($total_records); ?></div>
                    </div>
                    <div class="stat-box">
                        <h4>Total Spent</h4>
                        <div class="value">$<?php echo number_format($total_spent, 2); ?></div>
                    </div>
                    <div class="stat-box">
                        <h4>Total Savings</h4>
                        <div class="value" style="color: #00753e;">$<?php echo number_format($total_savings, 2); ?></div>
                    </div>
                    <div class="stat-box">
                        <h4>Unique Items</h4>
                        <div class="value"><?php echo number_format($unique_items); ?></div>
                    </div>
                    <div class="stat-box">
                        <h4>Shopping Trips</h4>
                        <div class="value"><?php echo number_format($unique_dates); ?></div>
                    </div>
                    <div class="stat-box">
                        <h4>Items On Sale</h4>
                        <div class="value"><?php echo number_format($on_sale_items); ?></div>
                    </div>
                </div>
                
                <h2>📊 Database Records</h2>
                <p style="color: #666; margin-bottom: 20px;">
                    <?php 
                    if ($date_range_min && $date_range_max) {
                        echo "Date Range: " . date('M d, Y', strtotime($date_range_min)) . " - " . date('M d, Y', strtotime($date_range_max));
                    }
                    ?>
                </p>
                
                <div class="db-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Purchase Date</th>
                                <th>Item Name</th>
                                <th>Price</th>
                                <th>On Sale</th>
                                <th>Taxable</th>
                                <th>Savings</th>
                                <th>Email ID</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get all purchases
                            $query = "SELECT * FROM purchases ORDER BY purchase_date DESC, id DESC";
                            $result = $db->query($query);
                            
                            if ($result) {
                                $row_count = 0;
                                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                                    $row_count++;
                                    
                                    // Format values
                                    $on_sale = $row['on_sale'] ? '✓' : '';
                                    $taxable = $row['taxable'] === null ? '?' : ($row['taxable'] ? '✓' : '');
                                    $price = $row['price'] > 0 ? '$' . number_format($row['price'], 2) : '$0.00';
                                    $savings = $row['savings'] > 0 ? '$' . number_format($row['savings'], 2) : '-';
                                    $purchase_date = date('Y-m-d', strtotime($row['purchase_date']));
                                    $created_at = $row['created_at'] ? date('Y-m-d H:i:s', strtotime($row['created_at'])) : '-';
                                    $email_id = $row['email_id'] ? substr($row['email_id'], 0, 10) . '...' : '-';
                                    
                                    // Highlight on sale items
                                    $row_class = $row['on_sale'] ? 'style="background-color: #e8f5e9;"' : '';
                                    
                                    echo "<tr $row_class>";
                                    echo "<td>{$row['id']}</td>";
                                    echo "<td>$purchase_date</td>";
                                    echo "<td>{$row['item_name']}</td>";
                                    echo "<td style='text-align: right;'>$price</td>";
                                    echo "<td style='text-align: center;'>$on_sale</td>";
                                    echo "<td style='text-align: center;'>$taxable</td>";
                                    echo "<td style='text-align: right; color: #00753e; font-weight: 600;'>$savings</td>";
                                    echo "<td style='font-size: 0.8em; color: #666;'>$email_id</td>";
                                    echo "<td style='font-size: 0.8em;'>$created_at</td>";
                                    echo "</tr>";
                                }
                                
                                if ($row_count === 0) {
                                    echo '<tr><td colspan="9" style="text-align: center; padding: 40px; color: #999;">No data found</td></tr>';
                                }
                            } else {
                                echo '<tr><td colspan="9" style="text-align: center; padding: 40px; color: #f44336;">Error querying database</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <?php
                $db->close();
            } catch (Exception $e) {
                echo '<div style="background: #ffebee; color: #c62828; padding: 20px; border-radius: 8px; margin: 20px 0;">';
                echo '<h3>Database Error</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
