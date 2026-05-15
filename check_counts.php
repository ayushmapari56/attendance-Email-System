<?php
require_once __DIR__ . '/src/Database.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "Students: " . $db->query("SELECT COUNT(*) FROM students")->fetchColumn() . "\n";
    echo "Subjects: " . $db->query("SELECT COUNT(*) FROM subjects")->fetchColumn() . "\n";
    echo "Faculty: " . $db->query("SELECT COUNT(*) FROM faculty")->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>