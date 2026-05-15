<?php
require_once __DIR__ . '/src/Database.php';

$csvFile = __DIR__ . '/public/faculty_members.csv';

if (!file_exists($csvFile)) {
    die("Error: faculty_members.csv not found in " . $csvFile);
}

try {
    $db = Database::getInstance()->getConnection();

    $file = fopen($csvFile, 'r');

    // Skip the header row
    fgetcsv($file);

    $successCount = 0;
    $stmt = $db->prepare("INSERT INTO faculty (faculty_name, email, department) VALUES (:name, :email, :department)");

    while (($row = fgetcsv($file)) !== false) {
        if (count($row) >= 3) {
            $stmt->execute([
                'name' => trim($row[0]),
                'email' => trim($row[1]),
                'department' => trim($row[2])
            ]);
            $successCount++;
        }
    }

    fclose($file);
    echo "Successfully inserted $successCount faculty members into the database.\n";

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
