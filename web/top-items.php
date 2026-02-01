<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Items - Publix Tracker</title>
    <link rel="stylesheet" href="index.php">
    <style>
        <?php include 'index.php'; ?>
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🏆 Top Purchased Items</h1>
        </header>
        
        <nav>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="monthly.php" class="btn btn-primary">📅 Monthly View</a>                <a href="settings.php" class="btn btn-secondary">⚙️ Settings</a>            </div>
        </nav>
        
        <div class="content">
            <?php
            $db_file = './data/publix_tracker.db';
            
            try {
                $db = new SQLite3($db_file);
                
                $query = "SELECT 
                            p.item_name, 
                            COUNT(*) as purchase_count, 
                            SUM(p.price) as total_spent,
                            AVG(p.price) as avg_price,
                            MIN(p.price) as min_price,
                            MAX(p.price) as max_price,
                            (SELECT price FROM purchases 
                             WHERE item_name = p.item_name 
                             ORDER BY purchase_date DESC, created_at DESC LIMIT 1) as last_price,
                            (SELECT purchase_date FROM purchases 
                             WHERE item_name = p.item_name 
                             ORDER BY purchase_date DESC, created_at DESC LIMIT 1) as last_date
                          FROM purchases p
                          GROUP BY p.item_name
                          ORDER BY purchase_count DESC
                          LIMIT 50";
                
                $result = $db->query($query);
            ?>
            
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Count</th>
                        <th>Total</th>
                        <th>Avg</th>
                        <th>Low</th>
                        <th>High</th>
                        <th>Last Price</th>
                        <th>Last Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($row['item_name']) . "</strong></td>";
                        echo "<td>" . $row['purchase_count'] . "</td>";
                        echo "<td class='price'>$" . number_format($row['total_spent'], 2) . "</td>";
                        echo "<td class='price'>$" . number_format($row['avg_price'], 2) . "</td>";
                        echo "<td class='price'>$" . number_format($row['min_price'], 2) . "</td>";
                        echo "<td class='price'>$" . number_format($row['max_price'], 2) . "</td>";
                        echo "<td class='price'>$" . number_format($row['last_price'], 2) . "</td>";
                        echo "<td class='date'>" . htmlspecialchars($row['last_date']) . "</td>";
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
