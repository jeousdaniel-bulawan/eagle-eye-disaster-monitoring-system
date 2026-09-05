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

    // Check if the client exists
    $checkClientSql = "SELECT * FROM clients WHERE client_id=?"; // Use client_id instead of id
    $stmt = $connection->prepare($checkClientSql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the client exists, proceed with the request and deletion
    if ($result->num_rows > 0) {
        // Prepare to insert the delete request
        $request_data = 'Client deleted from the system'; // Customize this message as needed
        $insertRequestSql = "INSERT INTO request (client_id, request_type, request_data) VALUES (?, 'delete', ?)";
        $stmt = $connection->prepare($insertRequestSql);
        $stmt->bind_param("is", $id, $request_data);
        $stmt->execute();

        // Delete the client record
        $sql = "DELETE FROM clients WHERE client_id=?"; // Use client_id instead of id
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } else {
        echo "Client does not exist.";
    }

    // Close statement and connection
    $stmt->close();
    $connection->close();
}

// Redirect to dashboard
header("location: /kalinga/dashboard.php");
exit;
?>
