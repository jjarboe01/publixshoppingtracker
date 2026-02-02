<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yearly View - Publix Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📆 Yearly Spending Analysis</h1>
            <p class="subtitle">Track your annual shopping patterns</p>
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
            
            // Initialize database if it doesn't exist
            if (!file_exists($db_file)) {
                require_once('init_db.php');
            }
            
            try {
                $db = new SQLite3($db_file);
                
                // Get selected year from URL parameter, default to current year
                $selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
                
                // Get all available years
                $years_query = "
                    SELECT DISTINCT strftime('%Y', purchase_date) as year 
                    FROM purchases 
                    ORDER BY year DESC
                ";
                $years_result = $db->query($years_query);
                $available_years = [];
                while ($row = $years_result->fetchArray(SQLITE3_ASSOC)) {
                    if ($row['year']) {
                        $available_years[] = $row['year'];
                    }
                }
                
                // Get yearly totals
                $yearly_query = "
                    SELECT 
                        COUNT(*) as total_items,
                        SUM(CASE WHEN on_sale = 0 THEN price ELSE 0 END) as total_spent,
                        SUM(savings) as total_savings,
                        COUNT(DISTINCT purchase_date) as total_trips,
                        COUNT(DISTINCT item_name) as unique_items
                    FROM purchases
                    WHERE strftime('%Y', purchase_date) = ?
                ";
                
                $stmt = $db->prepare($yearly_query);
                $stmt->bindValue(1, $selected_year, SQLITE3_TEXT);
                $yearly_result = $stmt->execute();
                $yearly_data = $yearly_result->fetchArray(SQLITE3_ASSOC);
                
                ?>
                
                <!-- Year Selector -->
                <div style="margin-bottom: 30px; text-align: center;">
                    <form method="GET" style="display: inline-block;">
                        <label for="year" style="font-size: 1.2em; font-weight: 600; margin-right: 10px;">Select Year:</label>
                        <select name="year" id="year" onchange="this.form.submit()" style="padding: 10px 20px; font-size: 1.1em; border: 2px solid #00753e; border-radius: 8px; background: white; cursor: pointer;">
                            <?php
                            if (empty($available_years)) {
                                echo '<option value="' . date('Y') . '">' . date('Y') . '</option>';
                            } else {
                                foreach ($available_years as $year) {
                                    $selected = ($year == $selected_year) ? 'selected' : '';
                                    echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </form>
                </div>
                
                <?php if ($yearly_data && $yearly_data['total_items'] > 0): ?>
                
                <!-- Yearly Summary Cards -->
                <div class="summary-grid">
                    <div class="summary-card">
                        <h3>Total Spent</h3>
                        <div class="value">$<?php echo number_format($yearly_data['total_spent'], 2); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Total Savings</h3>
                        <div class="value">$<?php echo number_format($yearly_data['total_savings'], 2); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Shopping Trips</h3>
                        <div class="value"><?php echo number_format($yearly_data['total_trips']); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Items Purchased</h3>
                        <div class="value"><?php echo number_format($yearly_data['total_items']); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Unique Items</h3>
                        <div class="value"><?php echo number_format($yearly_data['unique_items']); ?></div>
                    </div>
                    <div class="summary-card">
                        <h3>Avg Per Trip</h3>
                        <div class="value">$<?php 
                            $avg_per_trip = $yearly_data['total_trips'] > 0 
                                ? $yearly_data['total_spent'] / $yearly_data['total_trips'] 
                                : 0;
                            echo number_format($avg_per_trip, 2); 
                        ?></div>
                    </div>
                </div>
                
                <h2>Monthly Breakdown for <?php echo $selected_year; ?></h2>
                
                <!-- Monthly Data Table -->
                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Shopping Trips</th>
                            <th>Items Purchased</th>
                            <th>Total Spent</th>
                            <th>Total Savings</th>
                            <th>Avg Per Trip</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get monthly breakdown
                        $monthly_query = "
                            SELECT 
                                strftime('%m', purchase_date) as month,
                                COUNT(DISTINCT purchase_date) as trip_count,
                                COUNT(*) as item_count,
                                SUM(CASE WHEN on_sale = 0 THEN price ELSE 0 END) as total_spent,
                                SUM(savings) as total_savings
                            FROM purchases
                            WHERE strftime('%Y', purchase_date) = ?
                            GROUP BY month
                            ORDER BY month ASC
                        ";
                        
                        $stmt = $db->prepare($monthly_query);
                        $stmt->bindValue(1, $selected_year, SQLITE3_TEXT);
                        $monthly_result = $stmt->execute();
                        
                        $months = [
                            '01' => 'January', '02' => 'February', '03' => 'March',
                            '04' => 'April', '05' => 'May', '06' => 'June',
                            '07' => 'July', '08' => 'August', '09' => 'September',
                            '10' => 'October', '11' => 'November', '12' => 'December'
                        ];
                        
                        $monthly_data = [];
                        while ($row = $monthly_result->fetchArray(SQLITE3_ASSOC)) {
                            $monthly_data[$row['month']] = $row;
                        }
                        
                        // Display all 12 months
                        foreach ($months as $month_num => $month_name) {
                            if (isset($monthly_data[$month_num])) {
                                $data = $monthly_data[$month_num];
                                $avg_per_trip = $data['trip_count'] > 0 
                                    ? $data['total_spent'] / $data['trip_count'] 
                                    : 0;
                                
                                echo '<tr>';
                                echo '<td><strong>' . $month_name . '</strong></td>';
                                echo '<td>' . $data['trip_count'] . '</td>';
                                echo '<td>' . number_format($data['item_count']) . '</td>';
                                echo '<td class="price">$' . number_format($data['total_spent'], 2) . '</td>';
                                echo '<td class="price">$' . number_format($data['total_savings'], 2) . '</td>';
                                echo '<td class="price">$' . number_format($avg_per_trip, 2) . '</td>';
                                echo '</tr>';
                            } else {
                                // Show zero values for months with no data
                                echo '<tr style="opacity: 0.5;">';
                                echo '<td><strong>' . $month_name . '</strong></td>';
                                echo '<td>0</td>';
                                echo '<td>0</td>';
                                echo '<td class="price">$0.00</td>';
                                echo '<td class="price">$0.00</td>';
                                echo '<td class="price">$0.00</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
                
                <?php else: ?>
                
                <div class="no-data">
                    <h3>No data found for <?php echo $selected_year; ?></h3>
                    <p>Sync your receipts to see spending statistics.</p>
                    <p style="margin-top: 20px;">
                        <a href="sync.php" class="btn btn-warning">🔄 Sync Receipts</a>
                    </p>
                </div>
                
                <?php endif; ?>
                
                <?php
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
