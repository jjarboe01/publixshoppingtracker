<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly View - Publix Tracker</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        header {
            background: #00753e;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        h2 {
            color: #00753e;
            margin: 30px 0 20px 0;
            font-size: 1.8em;
        }
        
        .subtitle {
            font-size: 1.2em;
            opacity: 0.9;
        }
        
        nav {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #00753e;
            color: white;
        }
        
        .btn-primary:hover {
            background: #005a2f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,117,62,0.3);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #000;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .content {
            padding: 30px;
        }
        
        .month-section {
            margin-bottom: 40px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .month-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #00753e;
        }
        
        .month-title {
            font-size: 1.8em;
            color: #00753e;
            font-weight: bold;
        }
        
        .month-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .stat-card h4 {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 8px;
        }
        
        .stat-card .value {
            font-size: 1.8em;
            font-weight: bold;
            color: #00753e;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        th {
            background: #00753e;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95em;
        }
        
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .price {
            font-weight: 600;
            color: #00753e;
        }
        
        .sale-badge {
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }
        
        .taxable-badge {
            background: #17a2b8;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.85em;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .summary-card h3 {
            font-size: 1em;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .summary-card .value {
            font-size: 2.5em;
            font-weight: bold;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .no-data h3 {
            font-size: 1.5em;
            margin-bottom: 15px;
        }
    </style>
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
                        SUM(CASE WHEN on_sale = 1 THEN price ELSE 0 END) as total_savings,
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
