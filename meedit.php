<?php
// Database connection parameters
$host = 'localhost';
$db = 'kalinga';
$user = 'root';
$pass = '';

try {
    // Establish a PDO connection with UTF-8 encoding
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Log the error to a file in the same directory
    error_log('Connection failed: ' . $e->getMessage(), 3, __DIR__ . '/error.log');
    die('Connection failed. Please try again later.');
}

$errorMessage = '';
$successMessage = '';

// Initialize variables
$prescription = $doctor_order = $laboratory = $medicine = $daily_progress_notes = "";
$id = 0; // Initialize the ID variable

// Check if id is set in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Convert to integer for safety

    // Fetch the existing record
    $stmt = $pdo->prepare("SELECT * FROM medical_records WHERE record_id = :record_id");
    $stmt->bindParam(':record_id', $id, PDO::PARAM_INT); // Use $id here
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        // Populate fields with existing record data
        $prescription = $record['prescription'];
        $doctor_order = $record['doctor_order'];
        $laboratory = $record['laboratory'];
        $medicine = $record['medicine'];
        $daily_progress_notes = $record['daily_progress_notes'];
    } else {
        $errorMessage = "Medical record not found.";
    }
}

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
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
    $prescription = sanitize_input($_POST["prescription"] ?? "");
    $doctor_order = sanitize_input($_POST["doctor_order"] ?? "");
    $laboratory = sanitize_input($_POST["laboratory"] ?? "");
    $medicine = sanitize_input($_POST["medicine"] ?? "");
    $daily_progress_notes = sanitize_input($_POST["daily_progress_notes"] ?? "");

    // Validate required fields
    if (!validate_input([$prescription, $doctor_order, $laboratory, $medicine, $daily_progress_notes])) {
        $errorMessage = "All required fields must be filled.";
    } else {
        try {
            // Prepare the UPDATE statement using named placeholders
            $stmt = $pdo->prepare(
                "UPDATE medical_records SET prescription = :prescription, doctor_order = :doctor_order, 
                laboratory = :laboratory, medicine = :medicine, daily_progress_notes = :daily_progress_notes 
                WHERE record_id = :id" // Ensure you use the correct column name
            );

            // Bind parameters
            $stmt->bindParam(':prescription', $prescription, PDO::PARAM_STR);
            $stmt->bindParam(':doctor_order', $doctor_order, PDO::PARAM_STR);
            $stmt->bindParam(':laboratory', $laboratory, PDO::PARAM_STR);
            $stmt->bindParam(':medicine', $medicine, PDO::PARAM_STR);
            $stmt->bindParam(':daily_progress_notes', $daily_progress_notes, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Use $id here

            // Execute the statement
            if ($stmt->execute()) {
                $successMessage = "Medical record updated successfully.";
                // Clear input fields
                $prescription = $doctor_order = $laboratory = $medicine = $daily_progress_notes = "";
                // Redirect to another page if necessary
                header("Location: /kalinga/medical.php?updated=true");
                exit();
            } else {
                $errorMessage = "Failed to update medical record.";
            }
        } catch (PDOException $e) {
            // Log the error and set an error message
            error_log('Update failed: ' . $e->getMessage(), 3, __DIR__ . '/error.log');
            $errorMessage = "An error occurred while updating the medical record. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahay Kalinga | Medical Management System (Edit)</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .btn-primary, .btn-secondary {
            background-color: #16423C;
            color: white;
            border: none;        
        }
        .btn-primary:hover, .btn-secondary:hover {
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
        <h2>Edit Medical Record</h2>

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
                <label for="prescription" class="col-sm-3 col-form-label">Prescription<span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" id="prescription" name="prescription" placeholder="Enter prescription" value="<?php echo htmlspecialchars($prescription); ?>" required>
                </div>
            </div>

            <!-- Doctor Order input -->
            <div class="row mb-3">
                <label for="doctor_order" class="col-sm-3 col-form-label">Doctor Order<span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" id="doctor_order" name="doctor_order" placeholder="Enter doctor's order" value="<?php echo htmlspecialchars($doctor_order); ?>" required>
                </div>
            </div>

            <!-- Laboratory input -->
            <div class="row mb-3">
                <label for="laboratory" class="col-sm-3 col-form-label">Laboratory<span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" id="laboratory" name="laboratory" placeholder="Enter laboratory details" value="<?php echo htmlspecialchars($laboratory); ?>" required>
                </div>
            </div>

            <!-- Medicine input -->
            <div class="row mb-3">
                <label for="medicine" class="col-sm-3 col-form-label">Medicine<span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" id="medicine" name="medicine" placeholder="Enter medicine details" value="<?php echo htmlspecialchars($medicine); ?>" required>
                </div>
            </div>

            <!-- Daily Progress Notes input -->
            <div class="row mb-3">
                <label for="daily_progress_notes" class="col-sm-3 col-form-label">Daily Progress Notes<span class="text-danger">*</span></label>
                <div class="col-sm-6">
                    <textarea class="form-control" id="daily_progress_notes" name="daily_progress_notes" rows="3" placeholder="Enter daily progress notes" required><?php echo htmlspecialchars($daily_progress_notes); ?></textarea>
                </div>
            </div>

            <!-- Submit button -->
            <div class="row mb-3">
                <div class="col-sm-6 offset-sm-3">
                    <button type="submit" class="btn btn-primary">Update Record</button>
                    <a href="medical.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>
