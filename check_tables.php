<?php
include_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h2>Checking Database Tables</h2>";
echo "<pre>";

try {
    // Get all tables
    $query = "SHOW TABLES";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Total tables found: " . count($tables) . "\n\n";
    echo "Tables in database 'spot':\n";
    echo str_repeat("-", 50) . "\n";

    foreach ($tables as $table) {
        echo "✓ " . $table . "\n";
    }

    echo "\n" . str_repeat("-", 50) . "\n";

    // Check specifically for news_images
    if (in_array('news_images', $tables)) {
        echo "\n✓ news_images table EXISTS\n";

        // Get column info
        $query = "DESCRIBE news_images";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "\nColumns in news_images:\n";
        foreach ($columns as $col) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "\n✗ news_images table DOES NOT EXIST\n";
        echo "\nYou need to create this table manually.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "</pre>";

