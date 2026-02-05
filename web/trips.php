<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Trips - Publix Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🛒 Shopping Trips</h1>
            <p class="subtitle">View items from each shopping trip</p>
        </header>
        
        <nav>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-primary">📊 Dashboard</a>
                <a href="top-items.php" class="btn btn-primary">🏆 Top Items</a>
                <a href="trips.php" class="btn btn-primary active">🛍️ Shopping Trips</a>
                <a href="monthly.php" class="btn btn-primary">📅 Monthly View</a>
                <a href="yearly.php" class="btn btn-primary">📆 Yearly View</a>
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
                
                // Get all unique shopping dates with totals
                $trips_query = "
                    SELECT 
                        purchase_date,
                        COUNT(*) as item_count,
                        SUM(price) as total_spent,
                        SUM(savings) as total_savings,
                        SUM(CASE WHEN on_sale = 1 THEN 1 ELSE 0 END) as sale_items
                    FROM purchases
                    GROUP BY purchase_date
                    ORDER BY purchase_date DESC
                ";
                
                $trips_result = $db->query($trips_query);
                
                if (!$trips_result) {
                    throw new Exception("Query failed: " . $db->lastErrorMsg());
                }
                
                $trip_count = 0;
                $trips = [];
                while ($row = $trips_result->fetchArray(SQLITE3_ASSOC)) {
                    $trips[] = $row;
                    $trip_count++;
                }
                
                if ($trip_count === 0) {
                    echo '<div class="alert alert-info">';
                    echo '<strong>No shopping trips found.</strong> Sync your receipts to get started!';
                    echo '</div>';
                } else {
                    echo '<div class="summary-stats">';
                    echo '<div class="stat-card">';
                    echo '<div class="stat-value">' . $trip_count . '</div>';
                    echo '<div class="stat-label">Total Trips</div>';
                    echo '</div>';
                    echo '</div>';
                    
                    // Display each trip
                    foreach ($trips as $trip) {
                        $date = $trip['purchase_date'];
                        $formatted_date = date('F j, Y', strtotime($date));
                        $day_of_week = date('l', strtotime($date));
                        
                        echo '<div class="trip-container">';
                        echo '<div class="trip-header">';
                        echo '<h2>' . $formatted_date . ' <span style="color: #666; font-size: 0.8em;">(' . $day_of_week . ')</span></h2>';
                        echo '<div class="trip-summary">';
                        echo '<span class="trip-stat"><strong>' . $trip['item_count'] . '</strong> items</span>';
                        echo '<span class="trip-stat"><strong>$' . number_format($trip['total_spent'], 2) . '</strong> spent</span>';
                        if ($trip['total_savings'] > 0) {
                            echo '<span class="trip-stat" style="color: #28a745;"><strong>$' . number_format($trip['total_savings'], 2) . '</strong> saved</span>';
                        }
                        if ($trip['sale_items'] > 0) {
                            echo '<span class="trip-stat" style="color: #ffc107;">' . $trip['sale_items'] . ' on sale</span>';
                        }
                        echo '</div>';
                        echo '</div>';
                        
                        // Get items for this trip
                        $items_query = "
                            SELECT item_name, price, on_sale, taxable, savings
                            FROM purchases
                            WHERE purchase_date = :date
                            ORDER BY created_at ASC
                        ";
                        
                        $stmt = $db->prepare($items_query);
                        $stmt->bindValue(':date', $date, SQLITE3_TEXT);
                        $items_result = $stmt->execute();
                        
                        echo '<table class="data-table">';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Item</th>';
                        echo '<th style="text-align: right;">Price</th>';
                        echo '<th style="text-align: right;">Savings</th>';
                        echo '<th style="text-align: center;">Status</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        
                        while ($item = $items_result->fetchArray(SQLITE3_ASSOC)) {
                            $row_class = $item['on_sale'] ? 'on-sale-row' : '';
                            echo '<tr class="' . $row_class . '">';
                            echo '<td>' . htmlspecialchars($item['item_name']) . '</td>';
                            echo '<td style="text-align: right;">$' . number_format($item['price'], 2) . '</td>';
                            
                            if ($item['savings'] > 0) {
                                echo '<td style="text-align: right; color: #28a745;">$' . number_format($item['savings'], 2) . '</td>';
                            } else {
                                echo '<td style="text-align: right; color: #ccc;">—</td>';
                            }
                            
                            echo '<td style="text-align: center;">';
                            if ($item['on_sale']) {
                                echo '<span class="badge badge-success">SALE</span>';
                            }
                            if ($item['taxable']) {
                                echo ' <span class="badge badge-info">TAX</span>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>';
                    }
                }
                
                $db->close();
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">';
                echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
