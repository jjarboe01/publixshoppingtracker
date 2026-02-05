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
            <p>Manage your email account configuration</p>
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
                $provider = $_POST['provider'] ?? 'auto';
                
                // Load existing config if password is blank (to keep existing password)
                $existing_password = '';
                $existing_provider = 'auto';
                if (file_exists($config_file)) {
                    include $config_file;
                    if (defined('GMAIL_PASSWORD')) {
                        $existing_password = GMAIL_PASSWORD;
                    }
                    if (defined('EMAIL_PROVIDER')) {
                        $existing_provider = EMAIL_PROVIDER;
                    }
                }
                
                // Use existing password if new one not provided
                if (empty($password) && !empty($existing_password)) {
                    $password = $existing_password;
                }
                
                if ($email && $password) {
                    $config_content = "<?php\n";
                    $config_content .= "// Email Configuration\n";
                    $config_content .= "// Generated: " . date('Y-m-d H:i:s') . "\n\n";
                    $config_content .= "define('GMAIL_EMAIL', '" . addslashes($email) . "');\n";
                    $config_content .= "define('GMAIL_PASSWORD', '" . addslashes($password) . "');\n";
                    $config_content .= "define('EMAIL_PROVIDER', '" . addslashes($provider) . "');\n";
                    
                    if (file_put_contents($config_file, $config_content)) {
                        echo '<div class="alert alert-success">';
                        echo '<strong>✓ Success!</strong> Your email credentials have been saved securely.';
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
            $current_provider = 'auto';
            $config_exists = false;
            
            if (file_exists($config_file)) {
                include $config_file;
                $current_email = defined('GMAIL_EMAIL') ? GMAIL_EMAIL : '';
                $current_provider = defined('EMAIL_PROVIDER') ? EMAIL_PROVIDER : 'auto';
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
                    <li>Use an <strong>App Password</strong>, not your regular password</li>
                    <li>See instructions below for your specific email provider</li>
                    <li>Your credentials are stored in a PHP file on the server</li>
                    <li>Make sure the data directory is not publicly accessible</li>
                </ul>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($current_email); ?>" 
                           required 
                           placeholder="your-email@example.com">
                    <div class="help-text">The email account that receives Publix receipts</div>
                </div>
                
                <div class="form-group">
                    <label for="provider">Email Provider</label>
                    <select id="provider" name="provider" required>
                        <option value="auto" <?php echo $current_provider === 'auto' ? 'selected' : ''; ?>>Auto-detect from email domain</option>
                        <option value="gmail" <?php echo $current_provider === 'gmail' ? 'selected' : ''; ?>>Gmail</option>
                        <option value="outlook" <?php echo $current_provider === 'outlook' ? 'selected' : ''; ?>>Outlook / Hotmail / Live</option>
                        <option value="yahoo" <?php echo $current_provider === 'yahoo' ? 'selected' : ''; ?>>Yahoo Mail</option>
                        <option value="icloud" <?php echo $current_provider === 'icloud' ? 'selected' : ''; ?>>iCloud Mail</option>
                        <option value="aol" <?php echo $current_provider === 'aol' ? 'selected' : ''; ?>>AOL Mail</option>
                        <option value="custom" <?php echo $current_provider === 'custom' ? 'selected' : ''; ?>>Custom IMAP Server</option>
                    </select>
                    <div class="help-text">Select your email provider (auto-detect recommended)</div>
                </div>
                
                <div class="form-group">
                    <label for="password">App Password</label>
                    <input type="password" id="password" name="password" 
                           <?php echo !$config_exists ? 'required' : ''; ?>
                           placeholder="<?php echo $config_exists ? 'Leave blank to keep current password' : 'Enter your app password'; ?>">
                    <div class="help-text">
                        <?php if ($config_exists): ?>
                            Leave blank to keep current password, or enter new password to update
                        <?php else: ?>
                            App-specific password from your email provider
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 18px;">
                    <?php echo $config_exists ? '🔄 Update' : '💾 Save'; ?> Configuration
                </button>
            </form>
            
            <div class="info-box">
                <h3>📝 How to get an App Password:</h3>
                
                <h4 style="margin-top: 15px; color: #007bff;">📧 Gmail:</h4>
                <ol style="margin-left: 20px; line-height: 1.8;">
                    <li>Go to <a href="https://myaccount.google.com/apppasswords" target="_blank">Google App Passwords</a></li>
                    <li>You must have 2-Step Verification enabled first</li>
                    <li>Select "Mail" and your device</li>
                    <li>Copy the 16-character password</li>
                </ol>
                
                <h4 style="margin-top: 15px; color: #0078d4;">📧 Outlook / Hotmail / Live:</h4>
                <ol style="margin-left: 20px; line-height: 1.8;">
                    <li>Go to <a href="https://account.microsoft.com/security" target="_blank">Microsoft Security Settings</a></li>
                    <li>Enable "Two-step verification"</li>
                    <li>Under "App passwords", create a new password</li>
                    <li>Copy the generated password</li>
                </ol>
                
                <h4 style="margin-top: 15px; color: #6001d2;">📧 Yahoo Mail:</h4>
                <ol style="margin-left: 20px; line-height: 1.8;">
                    <li>Go to <a href="https://login.yahoo.com/account/security" target="_blank">Yahoo Account Security</a></li>
                    <li>Click "Generate app password"</li>
                    <li>Select "Other app" and enter a name</li>
                    <li>Copy the generated password</li>
                </ol>
                
                <h4 style="margin-top: 15px; color: #000;">📧 iCloud Mail:</h4>
                <ol style="margin-left: 20px; line-height: 1.8;">
                    <li>Go to <a href="https://appleid.apple.com/" target="_blank">Apple ID Settings</a></li>
                    <li>Sign in and go to Security section</li>
                    <li>Under "App-Specific Passwords", click "Generate Password"</li>
                    <li>Enter a label and copy the password</li>
                </ol>
                
                <h4 style="margin-top: 15px; color: #ff0000;">📧 AOL Mail:</h4>
                <ol style="margin-left: 20px; line-height: 1.8;">
                    <li>Go to <a href="https://login.aol.com/account/security" target="_blank">AOL Account Security</a></li>
                    <li>Click "Generate app password"</li>
                    <li>Select "Other app" and enter a name</li>
                    <li>Copy the generated password</li>
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
