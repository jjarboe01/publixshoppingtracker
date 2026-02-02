<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sync Receipts - Publix Tracker</title>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔄 Sync Gmail Receipts</h1>
        </header>
        
        <nav>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="top-items.php" class="btn btn-primary">🏆 Top Items</a>
                <a href="settings.php" class="btn btn-secondary">⚙️ Settings</a>
            </div>
        </nav>
        
        <div class="content">
            <?php
            // Check if credentials are saved in config
            $config_file = './data/config.php';
            $has_saved_credentials = false;
            $saved_email = '';
            
            if (file_exists($config_file)) {
                include $config_file;
                if (defined('GMAIL_EMAIL') && defined('GMAIL_PASSWORD')) {
                    $has_saved_credentials = true;
                    $saved_email = GMAIL_EMAIL;
                }
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Check if using saved credentials or manual entry
                $use_saved = isset($_POST['use_saved']) && $_POST['use_saved'] === '1';
                
                if ($use_saved && $has_saved_credentials) {
                    $email = constant('GMAIL_EMAIL');
                    $password = constant('GMAIL_PASSWORD');
                } else {
                    $email = $_POST['email'] ?? '';
                    $password = $_POST['password'] ?? '';
                }
                
                if ($email && $password) {
                    // Execute Python script - it will automatically use web config if available
                    $command = 'cd /app && python3 GetReciepts.py 2>&1';
                    
                    echo '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">';
                    echo '<h3>Syncing receipts...</h3>';
                    echo '<pre style="background: #fff; padding: 15px; border-radius: 5px; overflow-x: auto; max-height: 500px; overflow-y: auto;">';
                    
                    $handle = popen($command, 'r');
                    while (!feof($handle)) {
                        echo htmlspecialchars(fgets($handle));
                        flush();
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                    }
                    pclose($handle);
                    
                    echo '</pre>';
                    echo '<p><a href="index.php" class="btn btn-primary">View Updated Dashboard</a></p>';
                    echo '</div>';
                } else {
                    echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 20px 0;">';
                    echo '<strong>Error:</strong> Please provide both email and password.';
                    echo '</div>';
                }
            } else {
            ?>
            
            <div style="max-width: 600px; margin: 40px auto;">
                <?php if ($has_saved_credentials): ?>
                <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                    <h3>✓ Credentials Configured</h3>
                    <p>Using saved credentials for: <strong><?php echo htmlspecialchars($saved_email); ?></strong></p>
                    <p style="margin-top: 10px; font-size: 0.9em;">Click the button below to sync your receipts. To change credentials, visit <a href="settings.php" style="color: #155724; text-decoration: underline;">Settings</a>.</p>
                </div>
                
                <form method="POST" style="background: #f8f9fa; padding: 30px; border-radius: 10px; text-align: center;">
                    <input type="hidden" name="use_saved" value="1">
                    <button type="submit" class="btn btn-warning" style="width: 100%; padding: 15px; font-size: 18px;">
                        🔄 Sync Receipts Now
                    </button>
                </form>
                <?php else: ?>
                <div style="background: #fff3cd; color: #856404; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                    <h3>⚠️ No Credentials Configured</h3>
                    <p>Please configure your Gmail credentials in <a href="settings.php" style="color: #856404; text-decoration: underline;">Settings</a> first.</p>
                    <p style="margin-top: 15px;">
                        <a href="settings.php" class="btn btn-primary">Go to Settings →</a>
                    </p>
                </div>
                <?php endif; ?>
                </form>
            </div>
            
            <?php
            }
            ?>
        </div>
    </div>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
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
        
        h1 { margin: 0; }
        
        nav {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
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
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #000;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .content {
            padding: 30px;
        }
    </style>
</body>
</html>
