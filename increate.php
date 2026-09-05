<?php
session_start();

// Database credentials
$servername = "localhost";
$username = "root";
$password = ""; // Use your database password
$database = "kalinga";

// Create a connection
$connection = new mysqli($servername, $username, $password, $database);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Initialize variables for form inputs and messages
$quantity = $unit = $description = $acquisition_date = $expiry_date = $brand = $category = $acquired_via = "";
$successMessage = $errorMessage = "";

// Function to validate required inputs
function validate_input($inputs) {
    foreach ($inputs as $input) {
        if (empty($input)) {
            return false;
        }
    }
    return true;
}

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize POST data
    $quantity = isset($_POST['quantity']) ? trim($_POST['quantity']) : null;
    $unit = isset($_POST['unit']) ? trim($_POST['unit']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $acquisition_date = isset($_POST['acquisition_date']) ? trim($_POST['acquisition_date']) : null;
    $expiry_date = isset($_POST['expiry_date']) ? trim($_POST['expiry_date']) : null;
    $brand = isset($_POST['brand']) ? trim($_POST['brand']) : null;
    $category = isset($_POST['category']) ? trim($_POST['category']) : null;
    $acquired_via = isset($_POST['acquired_via']) ? trim($_POST['acquired_via']) : null;

    // Validate required fields (adjust fields as needed)
    if (!validate_input([$quantity, $unit, $description, $category, $acquired_via])) {
        $errorMessage = "All required fields must be filled.";
    } else {
        // Prepare and execute the INSERT statement
        $stmt = $connection->prepare(
            "INSERT INTO inventory (quantity, unit, description, acquisition_date, expiry_date, brand, category, acquired_via) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if ($stmt) {
            // Bind parameters: assuming quantity is integer, adjust types accordingly
            $stmt->bind_param(
                "isssssss", 
                $quantity, $unit, $description, $acquisition_date, $expiry_date, $brand, $category, $acquired_via
            );

            if ($stmt->execute()) {
                $successMessage = "Inventory item added successfully.";
                // Clear input fields
                $quantity = $unit = $description = $acquisition_date = $expiry_date = $brand = $category = $acquired_via = "";
                // Close statement and connection
                $stmt->close();
                $connection->close();
                // Redirect to another page
                header("Location: /kalinga/inventory.php");
                exit;
            } else {
                // Log the error
                error_log("Database Execute Error: " . $stmt->error);
                $errorMessage = "An error occurred while adding the inventory item. Please try again later.";
            }
        } else {
            // Log the error
            error_log("Database Prepare Error: " . $connection->error);
            $errorMessage = "An error occurred while preparing the form. Please try again later.";
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bahay Kalinga | Inventory Management System (Create)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcidslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
        <h2>Add New Inventory Item</h2>

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
            <!-- Quantity input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Quantity</label>
                <div class="col-sm-6">
                    <input type="number" class="form-control" name="quantity" placeholder="Quantity" value="<?php echo htmlspecialchars($quantity); ?>" required>
                </div>
            </div>

            <!-- Unit input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Unit</label>
                <div class="col-sm-6">
                    <select class="form-select" name="unit" required>
                        <option value="">Select Unit</option>
                        <option value="pcs" <?php echo $unit === 'pcs' ? 'selected' : ''; ?>>pcs</option>
                        <option value="packs" <?php echo $unit === 'packs' ? 'selected' : ''; ?>>packs</option>
                        <option value="L" <?php echo $unit === 'L' ? 'selected' : ''; ?>>L</option>
                        <option value="mL" <?php echo $unit === 'mL' ? 'selected' : ''; ?>>mL</option>
                        <option value="kg" <?php echo $unit === 'kg' ? 'selected' : ''; ?>>kg</option>
                        <option value="g" <?php echo $unit === 'g' ? 'selected' : ''; ?>>g</option>
                        <option value="m" <?php echo $unit === 'm' ? 'selected' : ''; ?>>m</option>
                        <option value="cm" <?php echo $unit === 'cm' ? 'selected' : ''; ?>>cm</option>
                        <option value="m²" <?php echo $unit === 'm²' ? 'selected' : ''; ?>>m²</option>
                        <option value="cm³" <?php echo $unit === 'cm³' ? 'selected' : ''; ?>>cm³</option>
                        <option value="gal" <?php echo $unit === 'gal' ? 'selected' : ''; ?>>gal</option>
                        <option value="t" <?php echo $unit === 't' ? 'selected' : ''; ?>>t</option>
                    </select>
                </div>
            </div>

            <!-- Description input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Item Description</label>
                <div class="col-sm-6">
                    <textarea class="form-control" name="description" placeholder="Item Description" required><?php echo htmlspecialchars($description); ?></textarea>
                </div>
            </div>

            <!-- Acquisition Date input (optional) -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Acquisition Date</label>
                <div class="col-sm-6">
                    <input type="date" class="form-control" name="acquisition_date" value="<?php echo htmlspecialchars($acquisition_date); ?>">
                </div>
            </div>

            <!-- Expiry Date input (optional) -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Expiry Date</label>
                <div class="col-sm-6">
                    <input type="date" class="form-control" name="expiry_date" value="<?php echo htmlspecialchars($expiry_date); ?>">
                </div>
            </div>

            <!-- Brand input (optional) -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Brand</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="brand" placeholder="Brand" value="<?php echo htmlspecialchars($brand); ?>">
                </div>
            </div>

            <!-- Category input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Category</label>
                <div class="col-sm-6">
                    <select class="form-select" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Maintenance Supplies" <?php echo $category === 'Maintenance Supplies' ? 'selected' : ''; ?>>Maintenance Supplies</option>
                        <option value="Sanitation Supplies" <?php echo $category === 'Sanitation Supplies' ? 'selected' : ''; ?>>Sanitation Supplies</option>
                        <option value="Office Supplies" <?php echo $category === 'Office Supplies' ? 'selected' : ''; ?>>Office Supplies</option>
                        <option value="Resident Essentials Supplies" <?php echo $category === 'Resident Essentials Supplies' ? 'selected' : ''; ?>>Resident Essentials Supplies</option>
                    </select>
                </div>
            </div>

            <!-- Acquired Via input -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Acquired Via</label>
                <div class="col-sm-6">
                    <select class="form-select" name="acquired_via" required>
                        <option value="">Select Acquisition Means</option>
                        <option value="City Fund" <?php echo $acquired_via === 'City Fund' ? 'selected' : ''; ?>>City Fund</option>
                        <option value="Donation" <?php echo $acquired_via === 'Donation' ? 'selected' : ''; ?>>Donation</option>
                    </select>            
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-primary">Add Item</button>
                    <a href="/kalinga/inventory.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
