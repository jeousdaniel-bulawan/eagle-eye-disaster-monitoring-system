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


// Define how many employees you want to display per page
$limit = 10; // Number of records per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

// Determine sorting order
$sortBy = isset($_GET['sortBy']) ? htmlspecialchars($_GET['sortBy']) : 'employee_id';
$order = isset($_GET['order']) ? strtoupper(htmlspecialchars($_GET['order'])) : 'ASC';
$allowedSortColumns = ['employee_id', 'surname', 'name', 'middle_name', 'designation', 'birthday', 'address', 'years_of_service', 'pag_ibig_no', 'sss_no', 'philhealth_no', 'gsis_no', 'functions_responsibilities', 'precinct_no', 'barangay'];
$allowedOrders = ['ASC', 'DESC'];

if (!in_array($sortBy, $allowedSortColumns)) {
    $sortBy = 'employee_id'; // Default sorting column
}

if (!in_array($order, $allowedOrders)) {
    $order = 'ASC'; // Default sorting order
}

// Build the query
$query = "SELECT * FROM employees ORDER BY $sortBy $order LIMIT :limit OFFSET :offset";

try {
    // Prepare and execute the statement
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error fetching employees records: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Calculate total records for pagination
$totalRecordsQuery = "SELECT COUNT(*) FROM employees";
$totalRecords = $pdo->query($totalRecordsQuery)->fetchColumn();
$totalPages = ceil($totalRecords / $limit);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahay Kalinga | Employee Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.5/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            background-color: #f5f5f5;
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
        table .btn {
            font-size: 10px;
            margin: 0.5px;
        }
        .navbar-brand-title {
            font-size: 0.75rem;
            margin-left: 0.5rem;
            font-weight: bold;
            color: black;
        }
        .dashboard-title {
            text-align: center;
            margin: 20px 0;
            font-size: 2rem;
            font-weight: bold;
            color: #16423C;
        }
        .profile-icon img {
            height: 1.5rem;
            border-radius: 50%;
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
        .table thead th {
            background-color: white;
            border-top: 3px solid #16423C;
            color: black;
        }
        .table thead {
            font-size: 13px;
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
        .btn-edit {
            background-color: #16423C;
            color: white;
            border: none;
        }
        .btn-edit:hover {
            background-color: #6A9C89;
        }
        table th a {
           color: #16423C; /* Change the color of the linked headers to green */
        }
        table th a:hover {
            color: #6A9C89; /* Optional: Change the color on hover for better visibility */
        }
        .btn-delete {
            background-color: #16423C;
            color: white;
            border: none;
        }
        .btn-delete:hover {
            background-color: #6A9C89;
        }
        /* Print Styles */
        @media print {
            .navbar-custom, .navbar-small, .btn-group {
                display: none !important;
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
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="d-flex align-items-center" style="width: 100%;">
            <a class="navbar-brand d-flex align-items-center text-grey" href="admin.php">
                <img src="cityhall.png" alt="City Hall">
                <img src="bago.png" alt="Bago">
                <img src="dswd.png" alt="DSWD">
                <img src="kalingalogo.png" alt="Kalinga Logo">
                <span class="navbar-brand-title">BAHAY KALINGA PARAÑAQUE EMPLOYEE SYSTEM [ADMIN]</span>
            </a>
            <div class="d-flex ms-auto align-items-center">
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

    </nav>
    <nav class="navbar navbar-expand-lg navbar-light navbar-small d-flex justify-content-center align-items-center">
        <a class="d-flex" href="admin.php">Homepage</a>
        <a class="d-flex" href="inventory.php">Inventory</a>
        <a class="d-flex" href="employees.php">Employees</a>
        <a class="d-flex" href="medical.php">Medical</a>
    </nav>    </nav>

        <div class="title-container mb-3">
            <div class="dashboard-title">
            EMPLOYEE MANAGEMENT
            </div>
        </div>     
 <!-- Search Form -->
 <div class="container" style="max-width: 50%; margin-bottom: 20px;">
</div>
<div id="searchresult"></div>
        <div class="btn-group mb-3 mx-3">
                <a href="emcreate.php" class="btn btn-custom">New Employee</a>
                <a href="#" class="btn btn-custom" onclick="window.print()">Print</a>
            </div>


        <!-- Employee Table -->
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th><a href="?sortBy=employee_id&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Employee ID</a></th>
                    <th><a href="?sortBy=surname&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Surname</a></th>
                    <th><a href="?sortBy=name&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Name</a></th>
                    <th><a href="?sortBy=middle_name&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Middle Name</a></th>
                    <th><a href="?sortBy=designation&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Designation</a></th>
                    <th><a href="?sortBy=birthday&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Birthday</a></th>
                    <th><a href="?sortBy=address&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Address</a></th>
                    <th><a href="?sortBy=years_of_service&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Years of Service</a></th>
                    <th><a href="?sortBy=pag_ibig_no&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Pag-IBIG No.</a></th>
                    <th><a href="?sortBy=sss_no&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">SSS No.</a></th>
                    <th><a href="?sortBy=philhealth_no&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">PhilHealth No.</a></th>
                    <th><a href="?sortBy=gsis_no&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">GSIS No.</a></th>
                    <th><a href="?sortBy=functions_responsibilities&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Functions & Responsibilities</a></th>
                    <th><a href="?sortBy=precinct_no&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Precinct No.</a></th>
                    <th><a href="?sortBy=barangay&order=<?= ($order === 'ASC') ? 'DESC' : 'ASC' ?>">Barangay</a></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?= 'EMP-' . sprintf('%06d', $employee['employee_id']) ?></td>
                    <td><?= htmlspecialchars($employee['surname']) ?></td>
                    <td><?= htmlspecialchars($employee['first_name']) ?></td>
                    <td><?= htmlspecialchars($employee['middle_name']) ?></td>
                    <td><?= htmlspecialchars($employee['designation']) ?></td>
                    <td><?= htmlspecialchars($employee['birthday']) ?></td>
                    <td><?= htmlspecialchars($employee['address']) ?></td>
                    <td><?= htmlspecialchars($employee['years_of_service']) ?></td>
                    <td><?= htmlspecialchars($employee['pag_ibig_no']) ?></td>
                    <td><?= htmlspecialchars($employee['sss_no']) ?></td>
                    <td><?= htmlspecialchars($employee['philhealth_no']) ?></td>
                    <td><?= htmlspecialchars($employee['gsis_no']) ?></td>
                    <td><?= htmlspecialchars($employee['functions_responsibilities']) ?></td>
                    <td><?= htmlspecialchars($employee['precinct_no']) ?></td>
                    <td><?= htmlspecialchars($employee['barangay']) ?></td>
                    <td>
                    <div class="d-flex justify-content-start">
                    <a href="emedit.php?id=<?php echo $employee['employee_id']; ?>" class="btn btn-edit"><i class="bi bi-pencil-fill"></i></a>
                    <a href="emdelete.php?id=<?php echo $employee['employee_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this employee?');"><i class="bi bi-trash-fill"></i></a>
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
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#live_search").keyup(function(){
            var input = $(this).val();
            if(input != ""){
                $.ajax({
                    url: "emlivesearch.php",
                    method: "POST",
                    data: {input: input},
                    success: function(data){
                        $("#searchresult").html(data).css("display", "block");
                    },
                    error: function(){
                        $("#searchresult").html('<div class="alert alert-danger">Error performing live search.</div>').css("display", "block");
                    }
                })
            } else {
                $("#searchresult").css("display", "none").html('');
            }
        })
    })
</script>

    <!-- Include Bootstrap JS and its dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
