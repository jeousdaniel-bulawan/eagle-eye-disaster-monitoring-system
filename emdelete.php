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
    $checkClientSql = "SELECT * FROM employees WHERE employee_id=?"; // Use client_id instead of id
    $stmt = $connection->prepare($checkClientSql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the client exists, proceed with the request and deletion
    if ($result->num_rows > 0) {

        $sql = "DELETE FROM employees WHERE employee_id=?"; // Use client_id instead of id
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } else {
        echo "Employee does not exist.";
    }

    // Close statement and connection
    $stmt->close();
    $connection->close();
}

// Redirect to dashboard
header("location: /kalinga/employees.php");
exit;
?>
