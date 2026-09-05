<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "kalinga");

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize sorting variables
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'item_id'; // Default to item_id
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

// Whitelist for sortable columns to prevent SQL injection
$sortableColumns = ['item_id', 'quantity', 'unit', 'description', 'acquisition_date', 'expiry_date', 'brand', 'category', 'acquired_via'];
if (!in_array($sortBy, $sortableColumns)) {
    $sortBy = 'item_id'; // Default to item_id if not valid
}

if ($order !== 'ASC' && $order !== 'DESC') {
    $order = 'ASC';
}

if (isset($_POST['input'])) {
    $input = mysqli_real_escape_string($con, $_POST['input']);

    // Modified query to search across multiple columns
    $query = "SELECT item_id, quantity, unit, description, acquisition_date, expiry_date, brand, category, acquired_via 
              FROM inventory 
              WHERE item_id LIKE '%{$input}%' 
                 OR description LIKE '%{$input}%'
                 OR brand LIKE '%{$input}%'
                 OR category LIKE '%{$input}%'
                 OR acquired_via LIKE '%{$input}%'
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
                        <th><a href="?sortBy=item_id&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Item ID</a></th>
                        <th><a href="?sortBy=quantity&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Quantity</a></th>
                        <th><a href="?sortBy=unit&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Unit</a></th>
                        <th><a href="?sortBy=description&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Description</a></th>
                        <th><a href="?sortBy=acquisition_date&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Acquisition Date</a></th>
                        <th><a href="?sortBy=expiry_date&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Expiry Date</a></th>
                        <th><a href="?sortBy=brand&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Brand</a></th>
                        <th><a href="?sortBy=category&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Category</a></th>
                        <th><a href="?sortBy=acquired_via&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Acquired Via</a></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['item_id']) ?></td>
                            <td><?= htmlspecialchars($row['quantity']) ?></td>
                            <td><?= htmlspecialchars($row['unit']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= htmlspecialchars($row['acquisition_date']) ?></td>
                            <td><?= htmlspecialchars($row['expiry_date']) ?></td>
                            <td><?= htmlspecialchars($row['brand']) ?></td>
                            <td><?= htmlspecialchars($row['category']) ?></td>
                            <td><?= htmlspecialchars($row['acquired_via']) ?></td>
                            <td>
                                <div class="d-flex justify-content-start">
                                    <a href="inedit.php?id=<?= $row['item_id']; ?>" class="btn btn-edit"><i class="bi bi-pencil-fill"></i></a>
                                    <a href="indelete.php?id=<?= $row['item_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this item?');"><i class="bi bi-trash-fill"></i></a>
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
            padding: 15px 20px;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .table {
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
        table th a {
            color: #16423C; /* Change the color of the linked headers to green */
        }
        table th a:hover {
            color: #6A9C89; /* Optional: Change the color on hover for better visibility */
        }
        .navbar-small {
            background-color: #16423C;
            padding: 0 30px;
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
            margin: 0 70px;
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
        .btn-edit, .btn-delete {
            background-color: #16423C;
            color: white;
            border: none;
        }
        .btn-edit:hover, .btn-delete:hover {
            background-color: #6A9C89;
        }
        .search-bar {
            margin-bottom: 5px; /* Set bottom margin to 5px */
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
                text-align: left !important; /* Ensure table displays correctly */
            }
        }
    </style>
</head>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
