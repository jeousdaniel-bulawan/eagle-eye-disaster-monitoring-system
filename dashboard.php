<?php
// Database connection parameters
$host = 'localhost';
$db = 'kalinga';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Connection failed: ' . $e->getMessage(), 3, '/path/to/error.log');
    die('Connection failed. Please try again later.');
}

// Determine sorting order
$sortBy = isset($_GET['sortBy']) ? htmlspecialchars($_GET['sortBy']) : 'client_id';
$order = isset($_GET['order']) ? strtoupper(htmlspecialchars($_GET['order'])) : 'ASC';
$allowedSortColumns = ['client_id', 'admission_date', 'discharge_date', 'name', 'birthdate', 'age', 'gender', 'address', 'civil_status', 'case_category', 'medical_condition', 'service_rendered', 'social_worker', 'reintegration', 'shelter_transfer', 'balik_probinsiya', 'remarks'];
$allowedOrders = ['ASC', 'DESC'];

if (!in_array($sortBy, $allowedSortColumns)) {
    $sortBy = 'client_id'; // Default sorting column
}

if (!in_array($order, $allowedOrders)) {
    $order = 'ASC'; // Default sorting order
}

$query = "SELECT * FROM clients ORDER BY $sortBy $order";
// Define how many employees you want to display per page
$clientsPerPage = 10; // Adjust this as needed

// Calculate the total number of employees (fetch the count from the database)
$totalclientsQuery = "SELECT COUNT(*) FROM clients";
$totalclientsResult = $pdo->query($totalclientsQuery);
$totalclients = $totalclientsResult->fetchColumn();

// Calculate the total number of pages
$totalPages = ceil($totalclients / $clientsPerPage);

// Now you can use $totalPages safely in your code.

// Fetch medical records
$clients = [];
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Reset IDs if there are gaps
    if (!empty($clients)) {
        $pdo->exec("SET @i := 0; UPDATE clients SET client_id = @i := @i + 1 ORDER BY client_id");
    }
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error fetching clients: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// Records per page
$recordsPerPage = 10;

// Calculate total records
$totalRecords = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$totalPages = ceil($totalRecords / $recordsPerPage);

// Calculate offset for SQL query
$offset = ($page - 1) * $recordsPerPage;

// Fetch records for the current page
$query = "SELECT * FROM clients LIMIT $recordsPerPage OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahay Kalinga | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            background-color: #f5f5f5;
        }
        .table {
            width: 100%;
        }
        .table thead th {
            background-color: white;
            border-top: 3px solid #16423C;
            color: black;
        }
        .table thead {
            font-size: 13px;
        }
        .navbar-custom {
            background-color: #fff;
            padding-top: 15px;
            padding-left: 20px;
            padding-right: 20px;
            position: sticky;
            top: 0;
            z-index: 1030;
            margin-bottom: 0;
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
        .dashboard-title {
            text-align: center;
            margin: 20px 0 5px;
            margin-bottom:5px;
            font-size: 2rem;
            font-weight: bold;
            color: #16423C;
        }
        .profile-icon img {
            height: 1.5rem;
            border-radius: 50%;
            cursor: pointer;
        }
        table th a {
           color: #16423C; /* Change the color of the linked headers to green */
        }
        table th a:hover {
            color: #6A9C89; /* Optional: Change the color on hover for better visibility */
        }
        .navbar-small {
            background-color: #16423C;
            padding-left: 30px;
            padding-right: 30px;
            height: 35px; /* Fixed height for the navbar */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 3.5rem;
            z-index: 1029;
            display: flex;
            align-items: center; /* Center content vertically */
        }
        .profile-icon i {
            font-size: 1.5rem;
            color: black;
            cursor: pointer;
        }

        .navbar-small .navbar-brand {
            font-size: 12px;
            color: white;
            margin-left: 70px;
            margin-right: 70px;
        }
        .btn-group {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
        }
        .btn-group .btn {
            font-size: 10px;
            text-transform: uppercase;
            background-color: #16423C;
            color: white;
            border: none;
        }
        .btn-group .btn:hover {
            background-color: #6A9C89;
        }
        table .btn {
            font-size: 10px;
            margin: 0.5px;
        }
        .btn-edit {
            background-color: #16423C;
            color: white;
            border: none;
        }
        .btn-edit:hover {
            background-color: #6A9C89;
        }
        .btn-delete {
            background-color: #16423C;
            color: white;
            border: none;
        }
        .btn-delete:hover {
            background-color: #6A9C89;
        }
        .search-bar {
        margin-bottom: 0.5px ; /* Set top margin to 5px */
        }
        .pagination .page-link {
            color: black; /* Set text color to black */
        }

        .pagination .page-link:hover {
            color: white; /* Ensure text color remains black on hover */
            background-color: #16423C; /* Optional: Dark background on hover */
        }

        .pagination .page-item.active .page-link {
            color: white; /* Active page text color */
            background-color: #16423C; /* Maintain existing background color */
            border-color: #16423C; /* Maintain existing border color */
        }

        .pagination .page-item.disabled .page-link {
            color: #16423C; /* Optional: Gray color for disabled buttons */
            pointer-events: none;
            cursor: not-allowed;
            background-color: #fff;
            border-color: #dee2e6;
        }        

        /* Print Styles */
        @media print {
    .navbar-custom, .navbar-small, .btn-group, .search-bar {
        display: none !important; /* Hide search bar and other elements */
    }
    .table .btn {
        display: none !important;
    }
    .table th:last-child, .table td:last-child {
        display: none !important;
    }
    .container {
        margin: 0 !important;
        padding: 0 !important;
        text-align: center !important;
    }
    .container .table {
        display: inline-table !important;
        text-align: left !important;
    }
    
/* Print Styles */
@media print {
    .navbar-custom, 
    .navbar-small, 
    .btn-group, 
    .search-bar, 
    .dropdown, 
    .profile-icon {
        display: none !important; /* Hide the search bar and other elements */
    }
    
    .table .btn {
        display: none !important; /* Hide buttons in the table */
    }
    .table th:last-child, 
    .table td:last-child {
        display: none !important; /* Hide the last column */
    }
    .container {
        margin: 0 !important;
        padding: 0 !important;
        text-align: center !important; /* Center content */
    }
    .container .table {
        display: inline-table !important; /* Ensure table displays correctly */
        text-align: left !important;
    }
    @media (max-width: 768px) {
    .navbar-custom, .navbar-small {
        flex-direction: column; /* Stack items vertically */
        padding: 10px;
    }

    .navbar-brand-title {
        font-size: 0.9rem; /* Adjust font size for mobile */
    }

    .dashboard-title {
        font-size: 1.5rem; /* Reduce title size */
    }

    .btn-group {
        flex-direction: column; /* Stack buttons vertically on small screens */
        align-items: center; /* Center buttons */
    }

    .btn-group .btn {
        width: 100%; /* Full width buttons */
        margin: 5px 0; /* Margin between buttons */
    }

    .table {
        font-size: 12px; /* Reduce table font size */
    }

    .search-bar {
        width: 100%; /* Full width search bar */
    }
}

}


        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="d-flex align-items-center" style="width: 100%;">
            <a class="navbar-brand d-flex align-items-center text-grey" href="#">
                <img src="cityhall.png" alt="City Hall">
                <img src="bago.png" alt="Bago">
                <img src="dswd.png" alt="DSWD">
                <img src="kalingalogo.png" alt="Kalinga Logo">
                <span class="navbar-brand-title">BAHAY KALINGA PARAÑAQUE INVENTORY SYSTEM</span>
            </a>
            <div class="d-flex ms-auto align-items-center">
            <input type="text" class="form-control" id="live_search" autocomplete="off" placeholder="Search" font-size="10px" style="margin-right: 5px;"id="searchresult">

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
        </div>
    </nav>

    <nav class="navbar navbar-expand-lg navbar-light navbar-small d-flex justify-content-center align-items-center">
    </nav>

    <!-- Dashboard Title -->
    <div class="title-container">
        <div class="dashboard-title">
            CLIENT DASHBOARD
        </div>
    </div>

<!-- Search Form -->
<div class="container" style="max-width: 50%; margin-bottom: 20px;">
</div>
<div id="searchresult"></div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){

            $("#live_search").keyup(function(){
                var input = $(this).val();
                //alert(input);

                if(input != ""){
                    $.ajax({
                        url:"dashlivesearch.php",
                        method:"POST",
                        data:{input:input},

                        success:function(data){
                            $("#searchresult").html(data);
                        }
                    })
                }else{
                    $("#searchresult").css("display","none");
                }
            })
        })
    </script>
    
    <!-- Buttons -->
    <div class="btn-group mb-3 mx-3">
        <a href="create.php" class="btn btn-custom">New Client</a>  
        <a href="#" class="btn btn-custom" onclick="window.print()">Print</a>
    </div>

    <!-- Inventory Table -->
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th><a href="?sortBy=client_id&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Client ID</a></th>
                <th><a href="?sortBy=admission_date&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Admission Date</a></th>
                <th><a href="?sortBy=discharge_date&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Discharge Date</a></th>
                <th><a href="?sortBy=name&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Name</a></th>
                <th><a href="?sortBy=birthdate&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Birthdate</a></th>
                <th><a href="?sortBy=age&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Age</a></th>
                <th><a href="?sortBy=gender&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Gender</a></th>
                <th><a href="?sortBy=address&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Address</a></th>
                <th><a href="?sortBy=civil_status&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Civil Status</a></th>
                <th><a href="?sortBy=case_category&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Case Category</a></th>
                <th><a href="?sortBy=medical_condition&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Medical Condition</a></th>
                <th><a href="?sortBy=service_rendered&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Service Rendered</a></th>
                <th><a href="?sortBy=social_worker&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Social Worker</a></th>
                <th><a href="?sortBy=reintegration&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Reintegration</a></th>
                <th><a href="?sortBy=shelter_transfer&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Shelter Transfer</a></th>
                <th><a href="?sortBy=balik_probinsiya&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Balik Probinsiya</a></th>
                <th><a href="?sortBy=remarks&order=<?php echo $order === 'ASC' ? 'DESC' : 'ASC'; ?>">Remarks</a></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= 'CLI-' . sprintf('%06d', $client['client_id']) ?></td>
                    <td><?php echo htmlspecialchars($client['admission_date']); ?></td>
                    <td><?php echo htmlspecialchars($client['discharge_date']); ?></td>
                    <td><?php echo htmlspecialchars($client['name']); ?></td>
                    <td><?php echo htmlspecialchars($client['birthdate']); ?></td>
                    <td><?php echo htmlspecialchars($client['age']); ?></td>
                    <td><?php echo htmlspecialchars($client['gender']); ?></td>
                    <td><?php echo htmlspecialchars($client['address']); ?></td>
                    <td><?php echo htmlspecialchars($client['civil_status']); ?></td>
                    <td><?php echo htmlspecialchars($client['case_category']); ?></td>
                    <td><?php echo htmlspecialchars($client['medical_condition']); ?></td>
                    <td><?php echo htmlspecialchars($client['service_rendered']); ?></td>
                    <td><?php echo htmlspecialchars($client['social_worker']); ?></td>
                    <td><?php echo htmlspecialchars($client['reintegration']); ?></td>
                    <td><?php echo htmlspecialchars($client['shelter_transfer']); ?></td>
                    <td><?php echo htmlspecialchars($client['balik_probinsiya']); ?></td>
                    <td><?php echo htmlspecialchars($client['remarks']); ?></td>
                    <td>
                        <div class="d-flex justify-content-around">
                            <a href="edit.php?id=<?php echo $client['client_id']; ?>" class="btn btn-edit"><i class="bi bi-pencil-fill"></i></a>
                            <a href="delete.php?id=<?php echo $client['client_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this client?');"><i class="bi bi-trash-fill"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
        <div class="container-fluid">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end">
                    <!-- Previous Button -->
                    <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="?<?php 
                            $prevPage = max(1, $page - 1);
                            echo http_build_query(array_merge($_GET, ['page' => $prevPage])); 
                        ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php
                        // Determine the range of pages to show
                        $maxLinks = 5;
                        $start = max(1, $page - floor($maxLinks / 2));
                        $end = min($totalPages, $start + $maxLinks - 1);

                        // Adjust start if we are near the end
                        if (($end - $start) < ($maxLinks - 1)) {
                            $start = max(1, $end - $maxLinks + 1);
                        }

                        for ($i = $start; $i <= $end; $i++):
                    ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                        <a class="page-link" href="?<?php 
                            $nextPage = min($totalPages, $page + 1);
                            echo http_build_query(array_merge($_GET, ['page' => $nextPage])); 
                        ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
