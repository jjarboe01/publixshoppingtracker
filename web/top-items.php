<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Items - Publix Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏆 Top Purchased Items</h1>
            <p class="subtitle">Your most frequently purchased items</p>
        </header>
        
        <nav>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-primary">📊 Dashboard</a>
                <a href="monthly.php" class="btn btn-primary">📅 Monthly View</a>
                <a href="yearly.php" class="btn btn-primary">📆 Yearly View</a>
                <a href="database.php" class="btn btn-secondary">🗄️ Database</a>
                <a href="sync.php" class="btn btn-warning">🔄 Sync Receipts</a>
                <a href="settings.php" class="btn btn-secondary">⚙️ Settings</a>
            </div>
        </nav>
        
        <div class="content">
            <?php
            $db_file = './data/publix_tracker.db';
            
            // Initialize database if it doesn't exist
            if (!file_exists($db_file)) {
                require_once('init_db.php');
            }
            
            try {
                $db = new SQLite3($db_file);
                
                $query = "SELECT 
                            p.item_name, 
                            COUNT(*) as purchase_count, 
                            AVG(p.price) as avg_price,
                            MIN(p.price) as min_price,
                            MAX(p.price) as max_price,
                            (SELECT price FROM purchases 
                             WHERE item_name = p.item_name 
                             ORDER BY purchase_date DESC, created_at DESC LIMIT 1) as last_price
                          FROM purchases p
                          WHERE on_sale = 0
                          GROUP BY p.item_name
                          ORDER BY purchase_count DESC
                          LIMIT 25";
                
                $result = $db->query($query);
            ?>
            
            <h2>Top 25 Most Purchased Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Item</th>
                        <th>Purchased</th>
                        <th>Low</th>
                        <th>Average</th>
                        <th>High</th>
                        <th>Last</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rank = 1;
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        echo "<tr>";
                        echo "<td><strong>#" . $rank++ . "</strong></td>";
                        echo "<td><strong>" . htmlspecialchars($row['item_name']) . "</strong></td>";
                        echo "<td>" . $row['purchase_count'] . "x</td>";
                        echo "<td class='price'>$" . number_format($row['min_price'], 2) . "</td>";
                        echo "<td class='price'>$" . number_format($row['avg_price'], 2) . "</td>";
                        echo "<td class='price'>$" . number_format($row['max_price'], 2) . "</td>";
                        echo "<td class='price'>$" . number_format($row['last_price'], 2) . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <?php
            } catch (Exception $e) {
                echo '<div class="loading"><p>Error loading data: ' . $e->getMessage() . '</p></div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
