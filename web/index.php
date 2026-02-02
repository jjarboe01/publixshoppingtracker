<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publix Shopping Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🛒 Publix Shopping Tracker</h1>
            <p class="subtitle">Track your purchases, analyze your spending</p>
        </header>
        
        <nav>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-primary">📊 Dashboard</a>
                <a href="top-items.php" class="btn btn-primary">🏆 Top Items</a>
                <a href="monthly.php" class="btn btn-primary">📅 Monthly View</a>
                <a href="search.php" class="btn btn-secondary">🔍 Search</a>
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
                
                // Get statistics
                $total_items = $db->querySingle("SELECT COUNT(*) FROM purchases");
                $total_spent = $db->querySingle("SELECT SUM(price) FROM purchases WHERE on_sale = 0");
                $unique_items = $db->querySingle("SELECT COUNT(DISTINCT item_name) FROM purchases");
                $shopping_trips = $db->querySingle("SELECT COUNT(DISTINCT purchase_date) FROM purchases");
                
                // Get recent purchases
                $recent_query = "SELECT purchase_date, item_name, price, on_sale, taxable 
                                FROM purchases 
                                ORDER BY purchase_date DESC, created_at DESC 
                                LIMIT 20";
                $recent_result = $db->query($recent_query);
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Items Purchased</h3>
                    <div class="value"><?php echo number_format($total_items); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Spent</h3>
                    <div class="value">$<?php echo number_format($total_spent, 2); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Unique Items</h3>
                    <div class="value"><?php echo number_format($unique_items); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Shopping Trips</h3>
                    <div class="value"><?php echo number_format($shopping_trips); ?></div>
                </div>
            </div>
            
            <h2>📦 Recent Purchases</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Tax</th>
                        <th>Sale</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $recent_result->fetchArray(SQLITE3_ASSOC)) {
                        $price_display = $row['on_sale'] ? 'SALE' : '$' . number_format($row['price'], 2);
                        $tax_display = $row['taxable'] ? '🟢' : '🔵';
                        $sale_display = $row['on_sale'] ? '💰' : '';
                        
                        echo "<tr>";
                        echo "<td class='date'>" . htmlspecialchars($row['purchase_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
                        echo "<td class='price'>" . $price_display . "</td>";
                        echo "<td>" . $tax_display . "</td>";
                        echo "<td>" . $sale_display . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <?php
            } catch (Exception $e) {
                echo '<div class="loading">';
                echo '<p>Database not found or no data yet.</p>';
                echo '<p>Please run the receipt sync to populate data.</p>';
                echo '</div>';
            }
            ?>
        </div>
        
        <footer>
            <p>&copy; 2026 Publix Shopping Tracker | Built with PHP & Python</p>
        </footer>
    </div>
</body>
</html>
