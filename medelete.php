<?php
// Database connection parameters
$dbHost = 'localhost';
$db = 'kalinga';  // Use your existing database name
$dbUser  = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$db", $dbUser , $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Connection failed: ' . $e->getMessage(), 3, '/path/to/error.log');
    die('Connection failed. Please try again later.');
}

// Function to delete a medical record by ID
function deleteMedicalRecord($pdo, $id) {
    try {
        // Check if the item exists
        $checkRecordSql = "SELECT * FROM medical_records WHERE record_id = :record_id";
        $stmt = $pdo->prepare($checkRecordSql);
        $stmt->bindParam(':record_id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // If the item exists, proceed with the deletion
        if ($stmt->rowCount() > 0) {
            $deleteSql = "DELETE FROM medical_records WHERE record_id = :record_id";
            $deleteStmt = $pdo->prepare($deleteSql);
            $deleteStmt->bindParam(':record_id', $id, PDO::PARAM_INT);
            $deleteStmt->execute();
            return "Record deleted successfully."; // Confirmation message
        } else {
            return "Item does not exist."; // Message for non-existing item
        }
    } catch (PDOException $e) {
        // Log the error
        error_log('Deletion failed: ' . $e->getMessage(), 3, '/path/to/error.log');
        return "An error occurred while trying to delete the record. Please try again.";
    }
}

// Check if id is set in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Convert to integer for safety
    $message = deleteMedicalRecord($pdo, $id); // Call the delete function
} else {
    $message = "No ID provided."; // Message if no ID is specified
}

// Optionally display the message (could be logged, shown on a page, etc.)
// For now, we will echo it (consider removing this in production)
echo $message;

// Redirect to inventory page
header("Location: medical.php");
exit;
?>