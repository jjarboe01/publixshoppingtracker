<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly View - Publix Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📅 Monthly Purchase View</h1>
            <p class="subtitle">Track your spending by month</p>
        </header>
        
        <nav>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="top-items.php" class="btn btn-primary">🏆 Top Items</a>
                <a href="yearly.php" class="btn btn-primary">📆 Yearly View</a>
                <a href="sync.php" class="btn btn-warning">🔄 Sync Receipts</a>
                <a href="settings.php" class="btn btn-secondary">⚙️ Settings</a>
            </div>
        </nav>
        
        <div class="content">
            <?php
            // Database connection
            $db_file = './data/publix_tracker.db';
            
            // Initialize database if it doesn't exist
            if (!file_exists($db_file)) {
                require_once('init_db.php');
            }
            
            try {
                $db = new SQLite3($db_file);
                
                // Get overall statistics
                $total_months = $db->querySingle("
                    SELECT COUNT(DISTINCT strftime('%Y-%m', purchase_date)) 
                    FROM purchases
                ");
                
                $total_spent = $db->querySingle("SELECT SUM(price) FROM purchases WHERE on_sale = 0");
                $total_savings = $db->querySingle("SELECT SUM(savings) FROM purchases");
                $avg_monthly = $total_months > 0 ? $total_spent / $total_months : 0;
                
                $total_items = $db->querySingle("SELECT COUNT(*) FROM purchases");
                
                ?>
                
                <div class="summary-grid">
                    <div class="summary-card">
                        <h3>Total Months</h3>
                        <div class="value"><?php echo number_format($total_months); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Total Spent</h3>
                        <div class="value">$<?php echo number_format($total_spent, 2); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Total Savings</h3>
                        <div class="value" style="color: #00753e;">$<?php echo number_format($total_savings, 2); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Average/Month</h3>
                        <div class="value">$<?php echo number_format($avg_monthly, 2); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Total Items</h3>
                        <div class="value"><?php echo number_format($total_items); ?></div>
                    </div>
                </div>
                
                <?php
                // Get monthly data
                $monthly_query = "
                    SELECT 
                        strftime('%Y-%m', purchase_date) as month,
                        COUNT(*) as item_count,
                        SUM(CASE WHEN on_sale = 0 THEN price ELSE 0 END) as total_spent,
                        SUM(savings) as total_savings,
                        COUNT(DISTINCT item_name) as unique_items,
                        COUNT(DISTINCT purchase_date) as shopping_trips
                    FROM purchases
                    GROUP BY month
                    ORDER BY month DESC
                ";
                
                $monthly_result = $db->query($monthly_query);
                
                if (!$monthly_result) {
                    echo '<div class="no-data">';
                    echo '<h3>No purchase data found</h3>';
                    echo '<p>Sync your receipts to see monthly statistics.</p>';
                    echo '</div>';
                } else {
                    $has_data = false;
                    
                    while ($month_data = $monthly_result->fetchArray(SQLITE3_ASSOC)) {
                        $has_data = true;
                        $month = $month_data['month'];
                        $month_formatted = date('F Y', strtotime($month . '-01'));
                        
                        // Get top items for this month
                        $top_items_query = "
                            SELECT 
                                item_name,
                                COUNT(*) as purchase_count,
                                AVG(price) as avg_price,
                                SUM(CASE WHEN on_sale = 0 THEN price ELSE 0 END) as total_spent
                            FROM purchases
                            WHERE strftime('%Y-%m', purchase_date) = ?
                            GROUP BY item_name
                            ORDER BY purchase_count DESC, total_spent DESC
                            LIMIT 10
                        ";
                        
                        $stmt = $db->prepare($top_items_query);
                        $stmt->bindValue(1, $month, SQLITE3_TEXT);
                        $top_items = $stmt->execute();
                        
                        ?>
                        
                        <div class="month-section">
                            <div class="month-header">
                                <div class="month-title"><?php echo $month_formatted; ?></div>
                            </div>
                            
                            <div class="month-stats">
                                <div class="stat-card">
                                    <h4>Items Purchased</h4>
                                    <div class="value"><?php echo number_format($month_data['item_count']); ?></div>
                                </div>
                                <div class="stat-card">
                                    <h4>Total Spent</h4>
                                    <div class="value">$<?php echo number_format($month_data['total_spent'], 2); ?></div>
                                </div>
                                <div class="stat-card">
                                    <h4>Savings</h4>
                                    <div class="value">$<?php echo number_format($month_data['total_savings'], 2); ?></div>
                                </div>
                                <div class="stat-card">
                                    <h4>Unique Items</h4>
                                    <div class="value"><?php echo number_format($month_data['unique_items']); ?></div>
                                </div>
                                <div class="stat-card">
                                    <h4>Shopping Trips</h4>
                                    <div class="value"><?php echo number_format($month_data['shopping_trips']); ?></div>
                                </div>
                            </div>
                            
                            <h3 style="color: #00753e; margin-top: 20px; margin-bottom: 10px;">Top Items</h3>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Item</th>
                                        <th>Purchases</th>
                                        <th>Avg Price</th>
                                        <th>Total Spent</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    while ($item = $top_items->fetchArray(SQLITE3_ASSOC)) { 
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $rank++; ?></strong></td>
                                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                        <td><?php echo $item['purchase_count']; ?>x</td>
                                        <td class="price">$<?php echo number_format($item['avg_price'], 2); ?></td>
                                        <td class="price">$<?php echo number_format($item['total_spent'], 2); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php
                    }
                    
                    if (!$has_data) {
                        echo '<div class="no-data">';
                        echo '<h3>No purchase data found</h3>';
                        echo '<p>Sync your receipts to see monthly statistics.</p>';
                        echo '</div>';
                    }
                }
                
                $db->close();
                
            } catch (Exception $e) {
                echo '<div class="no-data">';
                echo '<h3>Error loading data</h3>';
                echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
