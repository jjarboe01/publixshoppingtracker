<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publix Shopping Tracker</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            font-size: 1em;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .stat-card .value {
            font-size: 2.5em;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        th {
            background: #00753e;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .price {
            color: #00753e;
            font-weight: 600;
        }
        
        .date {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            border-top: 2px solid #dee2e6;
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
    </style>
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
