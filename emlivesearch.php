<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "kalinga");

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize sorting variables
$sortBy = isset($_GET['sortBy']) && in_array($_GET['sortBy'], ['employee_id', 'surname', 'first_name', 'designation', 'birthday', 'address', 'years_of_service', 'pag_ibig_no', 'sss_no', 'philhealth_no', 'gsis_no', 'functions_responsibilities', 'precinct_no', 'barangay']) ? $_GET['sortBy'] : 'employee_id';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

if (isset($_POST['input'])) {
    $input = mysqli_real_escape_string($con, $_POST['input']);

    $query = "SELECT employee_id, surname, CONCAT(first_name, ' ', middle_name) AS first_name, middle_name, designation, birthday, address, years_of_service, pag_ibig_no, sss_no, philhealth_no, gsis_no, functions_responsibilities, precinct_no, barangay 
              FROM employees 
              WHERE CONCAT(employee_id, surname, first_name, middle_name, designation, birthday, address, years_of_service, pag_ibig_no, sss_no, philhealth_no, gsis_no, functions_responsibilities, precinct_no, barangay) LIKE ?
              ORDER BY {$sortBy} {$order}";
    
    $stmt = mysqli_prepare($con, $query);
    $searchParam = "%{$input}%";
    mysqli_stmt_bind_param($stmt, "s", $searchParam);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        echo "<h6 class='text-danger text-center mt-3'>Error: " . mysqli_error($con) . "</h6>";
        exit;
    }

    if (mysqli_num_rows($result) > 0) {
        ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <?php
                        $columns = ['employee_id' => 'Employee ID', 'surname' => 'Surname', 'first_name' => 'First Name', 'middle_name' => 'Middle Name', 'designation' => 'Designation', 'birthday' => 'Birthday', 'address' => 'Address', 'years_of_service' => 'Years of Service', 'pag_ibig_no' => 'Pag-IBIG No.', 'sss_no' => 'SSS No.', 'philhealth_no' => 'PhilHealth No.', 'gsis_no' => 'GSIS No.', 'functions_responsibilities' => 'Functions & Responsibilities', 'precinct_no' => 'Precinct No.', 'barangay' => 'Barangay'];
                        foreach ($columns as $column => $label) {
                            echo "<th><a href='?sortBy={$column}&order=" . ($sortBy === $column && $order === 'ASC' ? 'DESC' : 'ASC') . "'>{$label}</a></th>";
                        }
                        ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        foreach ($columns as $column => $label) {
                            $value = $column === 'employee_id' ? 'EMP-' . sprintf('%06d', $row[$column]) : htmlspecialchars($row[$column]);
                            echo "<td>{$value}</td>";
                        }
                        echo "<td>
                                <div class='d-flex justify-content-start'>
                                    <a href='emedit.php?id={$row['employee_id']}' class='btn btn-edit'><i class='bi bi-pencil-fill'></i></a>
                                    <a href='emdelete.php?id={$row['employee_id']}' class='btn btn-delete' onclick='return confirm(\"Are you sure you want to delete this employee?\");'><i class='bi bi-trash-fill'></i></a>
                                </div>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    } else {
        echo "<h6 class='text-danger text-center mt-3'>No data found.</h6>";
    }
}
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
        .table{
            width: 100%;
            table-layout: auto;
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
        text-align: right !important;
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
}
</head>
<body>
    <!-- Your HTML content here -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>