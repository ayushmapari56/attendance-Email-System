<?php
require_once __DIR__ . '/src/Database.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "Subjects table content:\n";
    $stmt = $db->query("SELECT * FROM subjects");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>