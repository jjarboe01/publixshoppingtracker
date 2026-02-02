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
            
            // Get limit from query parameter, default to 10
            $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
            // Validate limit is one of the allowed values
            if (!in_array($limit, [10, 15, 20, 25])) {
                $limit = 10;
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
                          LIMIT " . $limit;
                
                $result = $db->query($query);
            ?>
            
            <!-- Item Count Selector -->
            <div style="margin-bottom: 20px; text-align: center;">
                <form method="GET" style="display: inline-block;">
                    <label for="limit" style="font-size: 1.1em; font-weight: 600; margin-right: 10px;">Show Top:</label>
                    <select name="limit" id="limit" onchange="this.form.submit()" style="padding: 8px 15px; font-size: 1em; border: 2px solid #00753e; border-radius: 8px; background: white; cursor: pointer;">
                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10 Items</option>
                        <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15 Items</option>
                        <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20 Items</option>
                        <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25 Items</option>
                    </select>
                </form>
            </div>
            
            <h2>Top <?php echo $limit; ?> Most Purchased Items</h2>
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
