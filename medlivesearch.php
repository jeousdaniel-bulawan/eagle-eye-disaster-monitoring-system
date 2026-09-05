<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "kalinga");

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize sorting variables
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'record_id';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

// Whitelist for sortable columns to prevent SQL injection
$sortableColumns = ['record_id', 'prescription', 'doctor_order', 'laboratory', 'medicine', 'daily_progress_notes'];
if (!in_array($sortBy, $sortableColumns)) {
    $sortBy = 'record_id';
}

if ($order !== 'ASC' && $order !== 'DESC') {
    $order = 'ASC';
}

if (isset($_POST['input'])) {
    $input = mysqli_real_escape_string($con, $_POST['input']);

    // Modified query to search across multiple columns
    $query = "SELECT record_id, prescription, doctor_order, laboratory, medicine, daily_progress_notes 
              FROM medical_records 
              WHERE record_id LIKE '%{$input}%' 
                 OR prescription LIKE '%{$input}%'
                 OR doctor_order LIKE '%{$input}%'
                 OR laboratory LIKE '%{$input}%'
                 OR medicine LIKE '%{$input}%'
                 OR daily_progress_notes LIKE '%{$input}%'
              ORDER BY {$sortBy} {$order}";
    
    $result = mysqli_query($con, $query);

    if (!$result) {
        // Handle query error
        echo "<h6 class='text-danger text-center mt-3'>Error: " . mysqli_error($con) . "</h6>";
        exit;
    }

    if (mysqli_num_rows($result) > 0) {
        ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><a href="?sortBy=record_id&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Record ID</a></th>
                        <th><a href="?sortBy=prescription&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Prescription</a></th>
                        <th><a href="?sortBy=doctor_order&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Doctor Order</a></th>
                        <th><a href="?sortBy=laboratory&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Laboratory</a></th>
                        <th><a href="?sortBy=medicine&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Medicine</a></th>
                        <th><a href="?sortBy=daily_progress_notes&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Daily Progress Notes</a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?= 'MED-' . sprintf('%06d', htmlspecialchars($row['record_id'])) ?></td>
                            <td><?= htmlspecialchars($row['prescription']) ?></td>
                            <td><?= htmlspecialchars($row['doctor_order']) ?></td>
                            <td><?= htmlspecialchars($row['laboratory']) ?></td>
                            <td><?= htmlspecialchars($row['medicine']) ?></td>
                            <td><?= htmlspecialchars($row['daily_progress_notes']) ?></td>
                            <td>
                                <div class="d-flex justify-content-start">
                                    <a href="meedit.php?id=<?= urlencode($row['record_id']) ?>" class="btn btn-edit" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="medelete.php?id=<?= urlencode($row['record_id']) ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this medication?');" title="Delete"><i class="bi bi-trash-fill"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php
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
</script>
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
            font-size: 8px;
            margin-right: 2px;    
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