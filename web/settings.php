<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Publix Tracker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>⚙️ Settings</h1>
            <p>Manage your Gmail account configuration</p>
        </header>
        
        <nav>
            <div class="nav-buttons">
                <a href="index.php" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="monthly.php" class="btn btn-primary">📅 Monthly View</a>
                <a href="yearly.php" class="btn btn-primary">📆 Yearly View</a>
                <a href="database.php" class="btn btn-secondary">🗄️ Database</a>
                <a href="sync.php" class="btn btn-primary">🔄 Sync Receipts</a>
            </div>
        </nav>
        
        <div class="content">
            <?php
            $config_file = './data/config.php';
            $config_dir = './data';
            
            // Create data directory if it doesn't exist
            if (!is_dir($config_dir)) {
                mkdir($config_dir, 0755, true);
            }
            
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                
                // Load existing config if password is blank (to keep existing password)
                $existing_password = '';
                if (file_exists($config_file)) {
                    include $config_file;
                    if (defined('GMAIL_PASSWORD')) {
                        $existing_password = GMAIL_PASSWORD;
                    }
                }
                
                // Use existing password if new one not provided
                if (empty($password) && !empty($existing_password)) {
                    $password = $existing_password;
                }
                
                if ($email && $password) {
                    $config_content = "<?php\n";
                    $config_content .= "// Gmail Configuration\n";
                    $config_content .= "// Generated: " . date('Y-m-d H:i:s') . "\n\n";
                    $config_content .= "define('GMAIL_EMAIL', '" . addslashes($email) . "');\n";
                    $config_content .= "define('GMAIL_PASSWORD', '" . addslashes($password) . "');\n";
                    
                    if (file_put_contents($config_file, $config_content)) {
                        echo '<div class="alert alert-success">';
                        echo '<strong>✓ Success!</strong> Your Gmail credentials have been saved securely.';
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-danger">';
                        echo '<strong>✗ Error!</strong> Could not save configuration file. Check directory permissions.';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="alert alert-danger">';
                    echo '<strong>✗ Error!</strong> Email is required. Password is required for new configurations.';
                    echo '</div>';
                }
            }
            
            // Load existing config
            $current_email = '';
            $config_exists = false;
            
            if (file_exists($config_file)) {
                include $config_file;
                $current_email = defined('GMAIL_EMAIL') ? GMAIL_EMAIL : '';
                $config_exists = defined('GMAIL_EMAIL') && defined('GMAIL_PASSWORD');
            }
            
            // Display current configuration
            if ($config_exists) {
                echo '<div class="current-config">';
                echo '<h3>Current Configuration</h3>';
                echo '<div class="config-item">';
                echo '<span class="config-label">Email Address:</span>';
                echo '<span class="config-value">' . htmlspecialchars($current_email) . '</span>';
                echo '</div>';
                echo '<div class="config-item">';
                echo '<span class="config-label">Password:</span>';
                echo '<span class="config-value">••••••••••••••••</span>';
                echo '</div>';
                echo '<div class="config-item">';
                echo '<span class="config-label">Status:</span>';
                echo '<span class="config-value" style="color: #28a745;">✓ Configured</span>';
                echo '</div>';
                echo '</div>';
            }
            ?>
            
            <div class="alert alert-warning">
                <h3>⚠️ Important Security Information</h3>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <li>Use a Gmail <strong>App Password</strong>, not your regular password</li>
                    <li>Create an App Password at: <a href="https://myaccount.google.com/apppasswords" target="_blank">https://myaccount.google.com/apppasswords</a></li>
                    <li>Your credentials are stored in a PHP file on the server</li>
                    <li>Make sure the data directory is not publicly accessible</li>
                </ul>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Gmail Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($current_email); ?>" 
                           required 
                           placeholder="your-email@gmail.com">
                    <div class="help-text">The Gmail account that receives Publix receipts</div>
                </div>
                
                <div class="form-group">
                    <label for="password">Gmail App Password</label>
                    <input type="password" id="password" name="password" 
                           <?php echo !$config_exists ? 'required' : ''; ?>
                           placeholder="<?php echo $config_exists ? 'Leave blank to keep current password' : 'Enter your 16-character app password'; ?>">
                    <div class="help-text">
                        <?php if ($config_exists): ?>
                            Leave blank to keep current password, or enter new password to update
                        <?php else: ?>
                            16-character password from Google App Passwords
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px;">
                    <?php echo $config_exists ? '🔄 Update' : '💾 Save'; ?> Configuration
                </button>
            </form>
            
            <div class="info-box">
                <h3>📝 How to get a Gmail App Password:</h3>
                <ol style="margin-left: 20px; line-height: 1.8;">
                    <li>Go to your Google Account: <a href="https://myaccount.google.com" target="_blank">myaccount.google.com</a></li>
                    <li>Click on "Security" in the left menu</li>
                    <li>Under "How you sign in to Google," select "2-Step Verification" (you must enable this first)</li>
                    <li>At the bottom, click "App passwords"</li>
                    <li>Select "Mail" and your device</li>
                    <li>Copy the 16-character password and paste it above</li>
                </ol>
            </div>
            
            <?php if ($config_exists): ?>
            <div style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #dee2e6;">
                <a href="sync.php" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px; text-align: center;">
                    🔄 Sync Receipts Now
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
