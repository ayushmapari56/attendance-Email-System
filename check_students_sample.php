<?php
require_once __DIR__ . '/src/Database.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "Sample student record:\n";
    $stmt = $db->query("SELECT * FROM students LIMIT 1");
    print_r($stmt->fetch(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>