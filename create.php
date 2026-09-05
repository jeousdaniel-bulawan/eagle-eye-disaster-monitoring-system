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
$admission_date = $discharge_date = $name = $birthdate = $age = $gender = $address = "";
$civil_status = $case_category = $medical_condition = $service_rendered = $social_worker = "";
$reintegration = $shelter_transfer = $balik_probinsiya = $remarks = "";

$errorMessage = "";
$successMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $admission_date = $connection->real_escape_string(trim($_POST["admission_date"] ?? ""));
    $discharge_date = $connection->real_escape_string(trim($_POST["discharge_date"] ?? ""));
    $name = $connection->real_escape_string(trim($_POST["name"] ?? ""));
    $birthdate = $connection->real_escape_string(trim($_POST["birthdate"] ?? ""));
    $age = $connection->real_escape_string(trim($_POST["age"] ?? ""));
    $gender = $connection->real_escape_string(trim($_POST["gender"] ?? ""));
    $address = $connection->real_escape_string(trim($_POST["address"] ?? ""));
    $civil_status = $connection->real_escape_string(trim($_POST["civil_status"] ?? ""));
    $case_category = $connection->real_escape_string(trim($_POST["case_category"] ?? ""));
    $medical_condition = $connection->real_escape_string(trim($_POST["medical_condition"] ?? ""));
    $service_rendered = $connection->real_escape_string(trim($_POST["service_rendered"] ?? ""));
    $social_worker = $connection->real_escape_string(trim($_POST["social_worker"] ?? ""));
    $reintegration = $connection->real_escape_string(trim($_POST["reintegration"] ?? ""));
    $shelter_transfer = $connection->real_escape_string(trim($_POST["shelter_transfer"] ?? ""));
    $balik_probinsiya = $connection->real_escape_string(trim($_POST["balik_probinsiya"] ?? ""));
    $remarks = $connection->real_escape_string(trim($_POST["remarks"] ?? ""));

    // Validate required fields
    if (empty($admission_date) || empty($discharge_date) || empty($name) || empty($birthdate) || empty($age) || empty($gender) || empty($address)) {
        $errorMessage = "All required fields must be filled.";
    } else {
        // Prepare and execute the INSERT statement
        $stmt = $connection->prepare(
            "INSERT INTO clients (admission_date, discharge_date, name, birthdate, age, gender, address, civil_status, case_category, medical_condition, service_rendered, social_worker, reintegration, shelter_transfer, balik_probinsiya, remarks) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            // Bind parameters: s for strings
            $stmt->bind_param(
                "ssssisssssssssss", 
                $admission_date, $discharge_date, $name, $birthdate, $age, $gender, $address, $civil_status, $case_category, $medical_condition, $service_rendered, $social_worker, $reintegration, $shelter_transfer, $balik_probinsiya, $remarks
            );

            if ($stmt->execute()) {
                $successMessage = "Client added successfully.";
                // Clear input fields
                $admission_date = $discharge_date = $name = $birthdate = $age = $gender = $address = $civil_status = $case_category = $medical_condition = $service_rendered = $social_worker = $reintegration = $shelter_transfer = $balik_probinsiya = $remarks = "";
                // Close statement and connection
                $stmt->close();
                $connection->close();
                // Redirect to another page
                header("Location: /kalinga/dashboard.php");
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
    <title>Add New Inventory Item</title>
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
            border: none;
        }
        .btn-primary:hover {
            background-color: #1c5e5a;
        }
        .btn-secondary {
            background-color: #16423C;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .btn-cancel {
            background-color: #16423C /* Green color */
            border: none;
            color: white; /* Text color */
        }
        .btn-cancel:hover {
            background-color: #218838; /* Darker green on hover */
        }
        .alert {
            margin-bottom: 20px;
        }
        .btn-close {
            box-shadow: none;
        }
    </style>
<body>
    <div class="container my-5">
        <h2>New Client</h2>

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
            <!-- Admission Date Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Admission Date</label>
                <div class="col-sm-6">
                    <input type="date" class="form-control" name="admission_date" value="<?php echo htmlspecialchars($admission_date); ?>">
                </div>
            </div>

            <!-- Discharge Date Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Discharge Date</label>
                <div class="col-sm-6">
                    <input type="date" class="form-control" name="discharge_date" value="<?php echo htmlspecialchars($discharge_date); ?>">
                </div>
            </div>

            <!-- Name Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>">
                </div>
            </div>

            <!-- Birthdate Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Birthdate</label>
                <div class="col-sm-6">
                    <input type="date" class="form-control" name="birthdate" value="<?php echo htmlspecialchars($birthdate); ?>">
                </div>
            </div>

            <!-- Age Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Age</label>
                <div class="col-sm-6">
                    <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($age); ?>">
                </div>
            </div>

            <!-- Gender Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Gender</label>
                <div class="col-sm-6">
                    <select class="form-control" name="gender">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="LGBTQIA+" <?php echo $gender === 'LGBTQIA+' ? 'selected' : ''; ?>>LGBTQIA+</option>
                    </select>
                </div>
            </div>

            <!-- Address Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Address</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($address); ?>">
                </div>
            </div>

            <!-- Civil Status Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Civil Status</label>
                <div class="col-sm-6">
                    <select class="form-control" name="civil_status">
                        <option value="">Select Civil Status</option>
                        <option value="Single" <?php echo $civil_status === 'Single' ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo $civil_status === 'Married' ? 'selected' : ''; ?>>Married</option>
                        <option value="Divorced" <?php echo $civil_status === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                        <option value="Widowed" <?php echo $civil_status === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Separated" <?php echo $civil_status === 'Separated' ? 'selected' : ''; ?>>Separated</option>
                        <option value="Domestic Partnership/Civil Union" <?php echo $civil_status === 'Domestic Partnership/Civil Union' ? 'selected' : ''; ?>>Domestic Partnership/Civil Union</option>
                        <option value="Registered Partnership" <?php echo $civil_status === 'Registered Partnership' ? 'selected' : ''; ?>>Registered Partnership</option>
                        <option value="Common-Law Marriage" <?php echo $civil_status === 'Common-Law Marriage' ? 'selected' : ''; ?>>Common-Law Marriage</option>
                        <option value="Annulled" <?php echo $civil_status === 'Annulled' ? 'selected' : ''; ?>>Annulled</option>
                    </select>
                </div>
            </div>

            <!-- Case Category Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Case Category</label>
                <div class="col-sm-6">
                    <select class="form-control" name="case_category">
                        <option value="">Select Case Category</option>
                        <option value="Abandoned" <?php echo $case_category === 'Abandoned' ? 'selected' : ''; ?>>Abandoned</option>
                        <option value="Abused" <?php echo $case_category === 'Abused' ? 'selected' : ''; ?>>Abused</option>
                        <option value="Homeless" <?php echo $case_category === 'Homeless' ? 'selected' : ''; ?>>Homeless</option>
                        <option value="Neglected" <?php echo $case_category === 'Neglected' ? 'selected' : ''; ?>>Neglected</option>
                        <option value="Unattached" <?php echo $case_category === 'Unattached' ? 'selected' : ''; ?>>Unattached</option>
                    </select>
                </div>
            </div>

            <!-- Medical Condition Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Medical Condition</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="medical_condition" value="<?php echo htmlspecialchars($medical_condition); ?>">
                </div>
            </div>

            <!-- Service Rendered Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Service Rendered</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="service_rendered" value="<?php echo htmlspecialchars($service_rendered); ?>">
                </div>
            </div>

            <!-- Social Worker In-Charge Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Social Worker In-Charge</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="social_worker" value="<?php echo htmlspecialchars($social_worker); ?>">
                </div>
            </div>

            <!-- Reintegration Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Reintegration</label>
                <div class="col-sm-6">
                    <select class="form-control" name="reintegration">
                        <option value="">Select Reintegration</option>
                        <option value="Yes" <?php echo $reintegration === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                        <option value="No" <?php echo $reintegration === 'No' ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
            </div>

            <!-- Transfer to Shelter Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Transfer to Shelter</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="shelter_transfer" value="<?php echo htmlspecialchars($shelter_transfer); ?>">
                </div>
            </div>

            <!-- Balik-Probinsiya Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Balik-Probinsiya</label>
                <div class="col-sm-6">
                    <select class="form-control" name="balik_probinsiya">
                        <option value="">Select Balik-Probinsiya</option>
                        <option value="Yes" <?php echo $balik_probinsiya === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                        <option value="No" <?php echo $balik_probinsiya === 'No' ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>
            </div>

            <!-- Remarks Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Remarks</label>
                <div class="col-sm-6">
                    <textarea class="form-control" name="remarks"><?php echo htmlspecialchars($remarks); ?></textarea>
                </div>
            </div>

            <!-- Submit and Cancel Buttons -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label"></label>
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a href="/kalinga/dashboard.php" class="btn btn-primary">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
