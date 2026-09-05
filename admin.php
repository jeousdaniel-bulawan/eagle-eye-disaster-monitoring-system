<?php
// db.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kalinga";

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

function countEntries($con, $dbname) {
    $counts = [];
    $tables = $con->query("SHOW TABLES FROM $dbname");
    while ($row = $tables->fetch_assoc()) {
        $tableName = $row["Tables_in_" . $dbname]; // Corrected concatenation
        $countQuery = "SELECT COUNT(*) as count FROM $tableName";
        $stmt = $con->prepare($countQuery); // Prepared statement
        $stmt->execute();
        $countResult = $stmt->get_result();
        if ($countResult) {
            $countRow = $countResult->fetch_assoc();
            $counts[$tableName] = $countRow['count'];
        } else {
            $counts[$tableName] = "Error: " . $con->error;
        }
    }
    return $counts;
}

function fetchLatestEntries($con, $table) {
    $primaryKey = '';
    switch ($table) {
        case 'inventory':
            $primaryKey = 'item_id';
            break;
        case 'employees':
            $primaryKey = 'employee_id';
            break;
        case 'medical_records':
            $primaryKey = 'record_id';
            break;
        case 'clients':
            $primaryKey = 'client_id';
            break;
        default:
            $primaryKey = 'id';
            break;
    }
    $query = "SELECT * FROM $table ORDER BY $primaryKey ASC LIMIT 10"; // Changed to ASC for ascending order
    $stmt = $con->prepare($query); // Prepared statement
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        error_log("MySQL Error: " . $con->error);
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}


// Get counts of entries
$entryCounts = countEntries($con, $dbname);

// Fetch latest entries for each tab
$latestInventory = fetchLatestEntries($con, 'inventory');
$latestEmployees = fetchLatestEntries($con, 'employees');
$latestMedical = fetchLatestEntries($con, 'medical_records');
$latestDashboard = fetchLatestEntries($con, 'clients');

// Close connection
$con->close();
?>

<?php
// admin.php
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahay Kalinga [Admin] | Homepage</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
/* Base Styles */
body {
    margin: 0;
    background-color: #f5f5f5;
}
.table thead {
            font-size: 13px;
        }
/* Navbar Styles */
.navbar-custom {
    background-color: #fff; /* Corrected hex code */
    padding-top: 15px;
            padding-left: 20px;
            padding-right: 20px;
    position: sticky; 
    top: 0;
    z-index: 1030;
    margin-bottom: 0;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-brand img {
    height: 1.25rem;
    margin-right: 0.5rem;
    vertical-align: middle;
}

.navbar-brand-title {
    font-size: 0.75rem;
    margin-left: 0.5rem;
    font-weight: bold;
    color: black;
}

.profile-icon i {
    font-size: 1.5rem;
    color: black;
    cursor: pointer;
}

.navbar-small {
    background-color: #16423C;
    padding: 10px 30px;
    min-height: 35px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 3.5rem;
    z-index: 1029;
    display: flex;
    align-items: center;
}

.navbar-small a {
    color: #ffffff; /* White text color for better contrast against dark background */
    text-decoration: none; /* Remove underline from links */
    padding: 3px 20px; /* Add padding for clickable area */
    font-size: 13px; /* Set a comfortable font size */
    transition: color 0.3s, background-color 0.3s; /* Smooth transition for hover effects */
    border-radius: 4px; /* Slightly rounded corners */
    margin: 0 5px; /* Space between links */
}

/* Dashboard Title */
.dashboard-title {
    text-align: center;
    margin: 20px 0;
    font-size: 2rem;
    font-weight: bold;
    color: #16423C;
}

/* Combined CSS for Tabbed Interface */
body, html {
    margin: 0;
    padding: 0;
    height: 100%;
    width: 100%;
}

.tabContainer {
    display: flex;
    flex-direction: column;
    align-items: center; /* Center the tab container horizontally */
    margin: 0; /* Remove the margin */
    padding: 0; /* Remove the padding */
}

.tabContainer .buttonContainer {
    width: 100%;
    display: flex;
    flex-direction: row;
    align-items: flex-end; /* Align buttons to the start (left) */
    margin-left: 350px;
    gap: 0px; /* Optional: Adds space between buttons */
}

/* Optional: If you want the buttons centered within the container on larger screens */


.tabContainer .buttonContainer button {
    flex: none; /* Prevent buttons from stretching */
    padding: 7px 20px;
    border: 0px solid transparent;
    cursor: pointer;
    font-family: sans-serif;
    font-size: 12px;
    background-color: #16423C;
    color: white;
    transition: background-color 0.3s, color 0.3s, border-color 0.3s;
    max-width: 220px; /* Optional: Limit button width */
}

.tabContainer .buttonContainer button:first-child {
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
}

.tabContainer .buttonContainer button:last-child {
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
}

.tabContainer .buttonContainer button.active {
    background-color: white;
    border-top: 1.75px solid #16423C;
    color: black;
}

.tabContainer .tabPanel {
    flex-grow: 1;
    width: 100%;
    background-color: #fff;
    color: #212529;
    margin-bottom: 5px;
    padding: 20px;
    box-sizing: border-box;
    display: none; /* Hidden by default */
    border: 1px solid #dee;
    margin: 0 auto; /* Center the panel */
    box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Optional: Add shadow */
    overflow-y: auto;
    margin-right: auto;
    
}

.tabContainer .tabPanel.active {
    display: block; /* Show active panel */

}

/* Table Header */
.table thead th {
    background-color: white;
    border-top: 4px solid #16423C;
    color: black;
}

/* Container Adjustments */
.container {
    margin-left: 0;
    margin-right: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .tabContainer {
        margin: 10px;
    }
    .tabContainer .buttonContainer {
        flex-direction: row;
        width: 100%;
        margin-left: 0;
    }
}
</style>
</head>
<body>
    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="d-flex align-items-center" style="width: 100%;">
            <a class="navbar-brand d-flex align-items-center text-grey" href="admin.php">
                <img src="cityhall.png" alt="City Hall">
                <img src="bago.png" alt="Bago">
                <img src="dswd.png" alt="DSWD">
                <img src="kalingalogo.png" alt="Kalinga Logo">
                <span class="navbar-brand-title">BAHAY KALINGA PARAÑAQUE INVENTORY SYSTEM [ADMIN]</span>
            </a>
            <div class="d-flex ms-auto align-items-center">
                <div class="dropdown profile-icon">
                    <a href="#" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-fill"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="change_password.php">Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php">Sign Out</a></li>
                    </ul>
                </div>    
        </div>
    </nav>
 

    <!-- Secondary Navigation Bar -->

    <nav class="navbar navbar-expand-lg navbar-light navbar-small d-flex justify-content-center align-items-center">
        <a class="d-flex" href="admin.php">Homepage</a>
        <a class="d-flex" href="inventory.php">Inventory</a>
        <a class="d-flex" href="employees.php">Employees</a>
        <a class="d-flex" href="medical.php">Medical</a>
    </nav>

    <!-- Dashboard Title -->
    <div class="dashboard-title">
        STATUS BOARD
    </div>

    <!-- Container for Tabbed Interface -->
    <div class="d-flex justify-content-center">
        <!-- Tabbed Interface -->
        <div class="tabContainer">
            <div class="buttonContainer">
                <button class="active" onclick="showPanel(0)">Inventory</button>
                <button onclick="showPanel(1)">Employees</button>
                <button onclick="showPanel(2)">Medical</button>
                <button onclick="showPanel(3)">Dashboard</button>
                <button onclick="showPanel(4)">Admin Panel</button>
            </div>

            <div class="tabPanel active">
                <h3>Latest Inventory Entries</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item No</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Acquisition Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestInventory as $entry): ?>
                            <tr>
                                <td><?= 'ITEM-' . sprintf('%06d', $entry['item_id']) ?></td>
                                <td><?php echo htmlspecialchars($entry['description']); ?></td>
                                <td><?php echo htmlspecialchars($entry['quantity']); ?></td>
                                <td><?php echo htmlspecialchars($entry['acquisition_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="tabPanel">
                <h3>Latest Employee Entries</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Surname</th>
                            <th>First Name</th>
                            <th>Designation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestEmployees as $entry): ?>
                            <tr>
                                <td><?= 'EMP-' . sprintf('%06d', $entry['employee_id']) ?></td>
                                <td><?php echo htmlspecialchars($entry['surname']); ?></td>
                                <td><?php echo htmlspecialchars($entry['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['designation']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="tabPanel">
                <h3>Latest Medical Records</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Record ID</th>
                            <th>Prescription</th>
                            <th>Doctor Order</th>
                            <th>Medicine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestMedical as $entry): ?>
                            <tr>
                                <td><?= 'MED-' . sprintf('%06d', $entry['record_id']) ?></td>
                                <td><?php echo htmlspecialchars($entry['prescription']); ?></td>
                                <td><?php echo htmlspecialchars($entry['doctor_order']); ?></td>
                                <td><?php echo htmlspecialchars($entry['medicine']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="tabPanel">
                <h3>Latest Client Entries</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Name</th>
                            <th>Admission Date</th>
                            <th>Discharge Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestDashboard as $entry): ?>
                            <tr>
                                <td><?= 'CLI-' . sprintf('%06d', $entry['client_id']) ?></td>
                                <td><?php echo htmlspecialchars($entry['name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['admission_date']); ?></td>
                                <td><?php echo htmlspecialchars($entry['discharge_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="tabPanel">
                <h3>Admin Panel</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Entry Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Define the tables you want to display
                        $desiredTables = ['clients', 'employees', 'inventory', 'medical_records'];

                        foreach ($desiredTables as $table) {
                            // Check if the table exists in the counts
                            if (array_key_exists($table, $entryCounts)) {
                                // Transform table name: Replace underscores with spaces and capitalize words
                                $displayName = ucwords(str_replace('_', ' ', $table));
                                $count = $entryCounts[$table];
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($displayName) . "</td>";
                                echo "<td>" . htmlspecialchars($count) . "</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <a href="http://localhost/phpmyadmin/index.php" class="btn btn-success mt-3" style="background-color: #16423C; color: white;">Browse Raw Database</a>
            </div>
        </div>
    </div>

    <script>
        var tabButtons = document.querySelectorAll(".tabContainer .buttonContainer button");
        var tabPanels = document.querySelectorAll(".tabContainer .tabPanel");

        function showPanel(panelIndex) {
            tabButtons.forEach(function(node){
                node.classList.remove("active");
            });
            tabButtons[panelIndex].classList.add("active");
            tabPanels.forEach(function(node){
                node.style.display = "none";
            });
            tabPanels[panelIndex].style.display = "block";
        }
        showPanel(0); // Show the first panel by default
    </script>
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
