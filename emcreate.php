<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "kalinga";

// Create connection
$connection = new mysqli($servername, $username, $password, $database);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Initialize variables
$surname = $first_name = $middle_name = $designation = $birthday = $address = $years_of_service = $pag_ibig_no = $sss_no = $philhealth_no = $gsis_no = $functions_responsibilities = $precinct_no = $barangay = "";
$errorMessage = "";
$successMessage = "";

// Function to sanitize input
function sanitize_input($data, $connection) {
    return $connection->real_escape_string(trim($data));
}

// Function to validate input
function validate_input($inputs) {
    foreach ($inputs as $input) {
        if (empty($input)) {
            return false;
        }
    }
    return true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $surname = sanitize_input($_POST["surname"] ?? "", $connection);
    $first_name = sanitize_input($_POST["first_name"] ?? "", $connection);
    $middle_name = sanitize_input($_POST["middle_name"] ?? "", $connection);
    $designation = sanitize_input($_POST["designation"] ?? "", $connection);
    $birthday = sanitize_input($_POST["birthday"] ?? "", $connection);
    $address = sanitize_input($_POST["address"] ?? "", $connection);
    $years_of_service = sanitize_input($_POST["years_of_service"] ?? "", $connection);
    $pag_ibig_no = sanitize_input($_POST["pag_ibig_no"] ?? "", $connection);
    $sss_no = sanitize_input($_POST["sss_no"] ?? "", $connection);
    $philhealth_no = sanitize_input($_POST["philhealth_no"] ?? "", $connection);
    $gsis_no = sanitize_input($_POST["gsis_no"] ?? "", $connection);
    $functions_responsibilities = sanitize_input($_POST["functions_responsibilities"] ?? "", $connection);
    $precinct_no = sanitize_input($_POST["precinct_no"] ?? "", $connection);
    $barangay = sanitize_input($_POST["barangay"] ?? "", $connection);

    // Validate required fields
    if (!validate_input([$surname, $first_name, $middle_name, $designation, $birthday, $address, $years_of_service, $pag_ibig_no, $sss_no, $philhealth_no, $gsis_no, $functions_responsibilities, $precinct_no, $barangay])) {
        $errorMessage = "All required fields must be filled.";
    } else {
        // Prepare and execute the INSERT statement
        $stmt = $connection->prepare(
            "INSERT INTO employees (surname, first_name, middle_name, designation, birthday, address, years_of_service, pag_ibig_no, sss_no, philhealth_no, gsis_no, functions_responsibilities, precinct_no, barangay) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            // Bind parameters: s for strings, and i for integers
            $stmt->bind_param(
                "ssssisssssssss", 
                $surname, $first_name, $middle_name, $designation, $birthday, $address, $years_of_service, $pag_ibig_no, $sss_no, $philhealth_no, $gsis_no, $functions_responsibilities, $precinct_no, $barangay
            );

            if ($stmt->execute()) {
                $successMessage = "Employee added successfully.";
                // Clear input fields
                $surname = $first_name = $middle_name = $designation = $birthday = $address = $years_of_service = $pag_ibig_no = $sss_no = $philhealth_no = $gsis_no = $functions_responsibilities = $precinct_no = $barangay = "";
                // Close statement and connection
                $stmt->close();
                $connection->close();
                // Redirect to another page
                header("Location: /kalinga/employees.php");
                exit;
            } else {
                $errorMessage = "Error executing statement: " . $stmt->error;
            }
        } else {
            $errorMessage = "Error preparing statement: " . $connection->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahay Kalinga | Employee Management System (Create)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
            background-color: #f4f4f4;
            padding-top: 20px;
        }
        .container {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-control, .form-select {
            font-size: 14px;
        }
        .form-control::placeholder {
            font-style: italic;
        }
        .btn-primary {
            background-color: #16423C;
            color: white;
            border: none;        
        }
        .btn-primary:hover {
            background-color: #6A9C89;
        }
        .btn-secondary {
            background-color: #16423C;
            color: white;
            border: none;        
        }
        .btn-secondary:hover {
            background-color: #6A9C89;
        }
        .alert {
            margin-bottom: 20px;
        }
        .btn-close {
            box-shadow: none;
        }    
    </style>
</head>
<body>
    <div class="container my-5">
        <h2>Add New Employee</h2>

        <?php if ($errorMessage): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong><?php echo htmlspecialchars($errorMessage); ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><?php echo htmlspecialchars($successMessage); ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="post">
            <!-- Surname input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Surname</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="surname" placeholder="Surname" value="<?php echo htmlspecialchars($surname); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">First Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="first_name" placeholder="First Name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                </div>
            </div>

            <!-- Middle Name input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Middle Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="middle_name" placeholder="Middle Name" value="<?php echo htmlspecialchars($middle_name); ?>" required>
                </div>
            </div>

            <!-- Designation input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Designation</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="designation" placeholder="Designation" value="<?php echo htmlspecialchars($designation); ?>" required>
                </div>
            </div>

            <!-- Birthday input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Birthday</label>
                <div class="col-sm-6">
                    <input type="date" class="form-control" name="birthday" value="<?php echo htmlspecialchars($birthday); ?>" required>
                </div>
            </div>

            <!-- Address input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Address</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="address" placeholder="Address" value="<?php echo htmlspecialchars($address); ?>" required>
                </div>
            </div>

            <!-- Years of Service input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Years of Service</label>
                <div class="col-sm-6">
                    <input type="number" class="form-control" name="years_of_service" placeholder="Years of Service" value="<?php echo htmlspecialchars($years_of_service); ?>" required>
                </div>
            </div>

            <!-- Pag-Ibig Number input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Pag-Ibig No.</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="pag_ibig_no" placeholder="Pag-Ibig No." value="<?php echo htmlspecialchars($pag_ibig_no); ?>" required>
                </div>
            </div>

            <!-- SSS Number input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">SSS No.</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="sss_no" placeholder="SSS No." value="<?php echo htmlspecialchars($sss_no); ?>" required>
                </div>
            </div>

            <!-- PhilHealth Number input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">PhilHealth No.</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="philhealth_no" placeholder="PhilHealth No." value="<?php echo htmlspecialchars($philhealth_no); ?>" required>
                </div>
            </div>

            <!-- GSIS Number input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">GSIS No.</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="gsis_no" placeholder="GSIS No." value="<?php echo htmlspecialchars($gsis_no); ?>" required>
                </div>
            </div>

            <!-- Functions and Responsibilities input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Functions & Responsibilities</label>
                <div class="col-sm-6">
                    <textarea class="form-control" name="functions_responsibilities" placeholder="Functions & Responsibilities" required><?php echo htmlspecialchars($functions_responsibilities); ?></textarea>
                </div>
            </div>

            <!-- Precinct No input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Precinct No.</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="precinct_no" placeholder="Precinct No." value="<?php echo htmlspecialchars($precinct_no); ?>" required>
                </div>
            </div>

            <!-- Barangay input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Barangay</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="barangay" placeholder="Barangay" value="<?php echo htmlspecialchars($barangay); ?>" required>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="row mb-3">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                    <a href="employees.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
