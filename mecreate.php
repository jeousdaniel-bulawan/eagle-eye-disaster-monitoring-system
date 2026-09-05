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
$prescription = $doctor_order = $laboratory = $medicine = $daily_progress_notes = "";
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
    $prescription = sanitize_input($_POST["prescription"] ?? "", $connection);
    $doctor_order = sanitize_input($_POST["doctor_order"] ?? "", $connection);
    $laboratory = sanitize_input($_POST["laboratory"] ?? "", $connection);
    $medicine = sanitize_input($_POST["medicine"] ?? "", $connection);
    $daily_progress_notes = sanitize_input($_POST["daily_progress_notes"] ?? "", $connection);

    // Validate required fields
    if (!validate_input([$prescription, $doctor_order, $laboratory, $medicine, $daily_progress_notes])) {
        $errorMessage = "All required fields must be filled.";
    } else {
        // Prepare and execute the INSERT statement
        $stmt = $connection->prepare(
            "INSERT INTO medical_records (prescription, doctor_order, laboratory, medicine, daily_progress_notes) 
            VALUES (?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            // Bind parameters: s for strings
            $stmt->bind_param("sssss", $prescription, $doctor_order, $laboratory, $medicine, $daily_progress_notes);

            if ($stmt->execute()) {
                $successMessage = "Employee record added successfully.";
                // Clear input fields
                $prescription = $doctor_order = $laboratory = $medicine = $daily_progress_notes = "";
                // Close statement
                $stmt->close();
                // Redirect to another page if necessary
                header("Location: /kalinga/medical.php");
                exit;
            } else {
                $errorMessage = "Error executing statement: " . $stmt->error;
            }
        } else {
            $errorMessage = "Error preparing statement: " . $connection->error;
        }
    }
}

// Close connection
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahay Kalinga | Medical Management System (Create)</title>
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
        <h2>Add New Medication</h2>

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

        <form method="POST" action="">
            <!-- Prescription input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Prescription</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="prescription" placeholder="Prescription" value="<?php echo htmlspecialchars($prescription); ?>" required>
                </div>
            </div>

            <!-- Doctor Order input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Doctor Order</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="doctor_order" placeholder="Doctor Order" value="<?php echo htmlspecialchars($doctor_order); ?>" required>
                </div>
            </div>

            <!-- Laboratory input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Laboratory</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="laboratory" placeholder="Laboratory" value="<?php echo htmlspecialchars($laboratory); ?>" required>
                </div>
            </div>

            <!-- Medicine input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Medicine</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="medicine" placeholder="Medicine" value="<?php echo htmlspecialchars($medicine); ?>" required>
                </div>
            </div>

            <!-- Daily Progress Notes input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Daily Progress Notes</label>
                <div class="col-sm-6">
                    <textarea class="form-control" name="daily_progress_notes" placeholder="Daily Progress Notes" required><?php echo htmlspecialchars($daily_progress_notes); ?></textarea>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-3"></div>
                <div class="col-sm-5">
                    <button type="submit" class="btn btn-primary">Add New Medication</button>
                    <a href="medical.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
