<?php
echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "SQLite3 available: " . (class_exists('SQLite3') ? 'Yes' : 'No') . "<br>";
echo "PDO available: " . (class_exists('PDO') ? 'Yes' : 'No') . "<br>";
echo "PDO SQLite driver: " . (in_array('sqlite', PDO::getAvailableDrivers()) ? 'Yes' : 'No') . "<br>";

$db_file = './data/publix_tracker.db';
echo "<br>Database file path: " . realpath($db_file) . "<br>";
echo "Database file exists: " . (file_exists($db_file) ? 'Yes' : 'No') . "<br>";
echo "Data directory exists: " . (is_dir('./data') ? 'Yes' : 'No') . "<br>";
echo "Data directory writable: " . (is_writable('./data') ? 'Yes' : 'No') . "<br>";

if (is_dir('./data')) {
    echo "<br>Contents of data directory:<br>";
    $files = scandir('./data');
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo " - $file<br>";
        }
    }
}
?>
