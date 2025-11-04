<?php
// Script to run the database schema
require_once 'database.php';

try {
    $pdo = getDB();

    // Read the schema file
    $schema = file_get_contents(__DIR__ . '/database_fixed.schema');

    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                // Skip duplicate key errors for indexes and duplicate entries
                if (($e->getCode() == '42000' && preg_match('/Duplicate key name/', $e->getMessage())) ||
                    ($e->getCode() == '23000' && preg_match('/Duplicate entry/', $e->getMessage()))) {
                    echo "Skipping duplicate: " . substr($statement, 0, 50) . "\n";
                } else {
                    throw $e;
                }
            }
        }
    }

    echo "Database schema executed successfully!\n";

} catch (Exception $e) {
    echo "Error executing schema: " . $e->getMessage() . "\n";
}
?>
