<?php
// Database connection
$con = mysqli_connect("localhost", "root", "", "kalinga");

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

if (isset($_POST['input'])) {
    $input = $_POST['input'];
    $query = "SELECT client_id, admission_date, discharge_date, name, birthdate, age, gender, address, civil_status, case_category, medical_condition, service_rendered, social_worker, reintegration, shelter_transfer, balik_probinsiya, remarks 
    FROM clients 
    WHERE name LIKE '{$input}%'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) { ?>
        <div class="table-responsive">
        <table class="table table-bordered table-striped">
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
                <?php
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <tr>
                    <td><?= 'CLI-' . sprintf('%06d', $client['client_id']) ?></td>
                        <td><?php echo htmlspecialchars($row['admission_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['discharge_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['birthdate']); ?></td>
                        <td><?php echo htmlspecialchars($row['age']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['civil_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['case_category']); ?></td>
                        <td><?php echo htmlspecialchars($row['medical_condition']); ?></td>
                        <td><?php echo htmlspecialchars($row['service_rendered']); ?></td>
                        <td><?php echo htmlspecialchars($row['social_worker']); ?></td>
                        <td><?php echo htmlspecialchars($row['reintegration']); ?></td>
                        <td><?php echo htmlspecialchars($row['shelter_transfer']); ?></td>
                        <td><?php echo htmlspecialchars($row['balik_probinsiya']); ?></td>
                        <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                        <td>
                            <div class="d-flex justify-content-around">
                                <a href="edit.php?id=<?php echo $client['id']; ?>" class="btn btn-edit"><i class="bi bi-pencil-fill"></i></a>
                                <a href="delete.php?id=<?php echo $client['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this client?');"><i class="bi bi-trash-fill"></i></a>
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
<script type="text/javascript">
    $(document).ready(function(){

        $("#live_search").keyup(function(){
            var input = $(this).val();

            if(input != ""){
                $.ajax({
                    url: "dashlivesearch.php",
                    method: "POST",
                    data: {input: input},

                    success: function(data){
                        $("#searchresult").css("display", "block").html(data);
                    }
                });
            } else {
                $("#searchresult").css("display", "none").html(""); // Clear the result when input is empty
            }
        });
    });
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
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>