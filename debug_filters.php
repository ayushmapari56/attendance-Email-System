<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check students table distinct department and semester
    $stmt = $db->query("SELECT DISTINCT department, semester FROM students");
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current student groups in database:\n";
    foreach ($groups as $g) {
        echo "Dept: '" . $g['department'] . "' | Sem: '" . $g['semester'] . "'\n";
    }
    
    // Check subjects table distinct department and semester
    $stmt2 = $db->query("SELECT DISTINCT department, semester FROM subjects");
    $groups2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nCurrent subject groups in database:\n";
    foreach ($groups2 as $g) {
        echo "Dept: '" . $g['department'] . "' | Sem: '" . $g['semester'] . "'\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
