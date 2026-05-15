<?php
require_once __DIR__ . '/../src/Database.php';

echo "Cleaning up database tables...\n";

try {
    $db = Database::getInstance()->getConnection();

    // Disable foreign key checks for truncation
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    $tables = ['attendance', 'students', 'subjects'];

    foreach ($tables as $table) {
        echo "Truncating $table...\n";
        $db->exec("TRUNCATE TABLE $table");
    }

    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "Done! Database is now empty (except users/faculty).\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>