<?php
if (isset($_GET["id"])) {
    $id = $_GET["id"];

    $servername = "localhost";
    $username = "root";
    $password = "";
    $database = "kalinga"; 

    // Create a connection
    $connection = new mysqli($servername, $username, $password, $database);

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Check if the item exists
    $checkItemSql = "SELECT * FROM inventory WHERE item_id = ?";
    $stmt = $connection->prepare($checkItemSql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        // If the item exists, proceed with the deletion
        if ($result->num_rows > 0) {
            $deleteSql = "DELETE FROM inventory WHERE item_id = ?";
            $deleteStmt = $connection->prepare($deleteSql);
            if ($deleteStmt) {
                $deleteStmt->bind_param("i", $id);
                $deleteStmt->execute();
                $deleteStmt->close();
            }
        } else {
            echo "Item does not exist.";
        }

        $stmt->close();
    }

    $connection->close();
}

// Redirect to inventory page
header("Location: /kalinga/inventory.php");
exit;
?>
