<?php
// Database connection parameters
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
$inventory_data = [
    "item_id" => "",
    "quantity" => "",
    "unit" => "",
    "description" => "",
    "acquisition_date" => "",
    "expiry_date" => "",
    "brand" => "",
    "category" => "",
    "acquired_via" => ""
];

$error_message = "";
$success_message = "";

// Handle GET request to load inventory data
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
        header("Location: /kalinga/inventory.php");
        exit;
    }

    $id = intval($_GET["id"]);

    // Fetch inventory data from the database
    $stmt = $connection->prepare("SELECT * FROM inventory WHERE item_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $inventory_data = $result->fetch_assoc();

        if (!$inventory_data) {
            header("Location: /kalinga/inventory.php");
            exit;
        }

        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $connection->error;
    }
}

// Handle POST request to update inventory data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($inventory_data as $key => $value) {
        $inventory_data[$key] = $_POST[$key] ?? '';
    }

    // Validate required fields
    if (empty($inventory_data["quantity"]) || empty($inventory_data["unit"]) || 
        empty($inventory_data["description"]) || empty($inventory_data["acquisition_date"]) || 
        empty($inventory_data["expiry_date"]) || empty($inventory_data["brand"]) || 
        empty($inventory_data["category"]) || empty($inventory_data["acquired_via"])) {
        $error_message = "All required fields must be filled.";
    } else {
        // Prepare and bind update statement
        $stmt = $connection->prepare(
            "UPDATE inventory 
            SET quantity = ?, unit = ?, description = ?, acquisition_date = ?, expiry_date = ?, brand = ?, category = ?, acquired_via = ? 
            WHERE item_id = ?"
        );

        if ($stmt) {
            $stmt->bind_param(
                "ssssssssi", 
                $inventory_data["quantity"], 
                $inventory_data["unit"], 
                $inventory_data["description"],
                $inventory_data["acquisition_date"], 
                $inventory_data["expiry_date"], 
                $inventory_data["brand"], 
                $inventory_data["category"], 
                $inventory_data["acquired_via"], 
                $inventory_data["item_id"]
            );

            if ($stmt->execute()) {
                $success_message = "Inventory item updated successfully.";
                header("Location: /kalinga/inventory.php?updated=true");
                exit;
            } else {
                $error_message = "Error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $error_message = "Error preparing update statement: " . $connection->error;
        }
    }
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Inventory Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
        <h2>Edit Inventory Item</h2>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong><?php echo htmlspecialchars($error_message); ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong><?php echo htmlspecialchars($success_message); ?></strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="item_id" value="<?php echo htmlspecialchars($inventory_data['item_id']); ?>">

            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?php echo htmlspecialchars($inventory_data['quantity']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="unit" class="form-label">Unit</label>
                <select id="unit" name="unit" class="form-select" required>
                    <option value="">Select Unit</option>
                    <option value="pcs" <?php echo ($inventory_data['unit'] === 'pcs') ? 'selected' : ''; ?>>pcs</option>
                    <option value="packs" <?php echo ($inventory_data['unit'] === 'packs') ? 'selected' : ''; ?>>packs</option>
                    <option value="L" <?php echo ($inventory_data['unit'] === 'L') ? 'selected' : ''; ?>>L</option>
                    <option value="mL" <?php echo ($inventory_data['unit'] === 'mL') ? 'selected' : ''; ?>>mL</option>
                    <option value="kg" <?php echo ($inventory_data['unit'] === 'kg') ? 'selected' : ''; ?>>kg</option>
                    <option value="g" <?php echo ($inventory_data['unit'] === 'g') ? 'selected' : ''; ?>>g</option>
                    <option value="m" <?php echo ($inventory_data['unit'] === 'm') ? 'selected' : ''; ?>>m</option>
                    <option value="cm" <?php echo ($inventory_data['unit'] === 'cm') ? 'selected' : ''; ?>>cm</option>
                    <option value="m²" <?php echo ($inventory_data['unit'] === 'm²') ? 'selected' : ''; ?>>m²</option>
                    <option value="cm³" <?php echo ($inventory_data['unit'] === 'cm³') ? 'selected' : ''; ?>>cm³</option>
                    <option value="gal" <?php echo ($inventory_data['unit'] === 'gal') ? 'selected' : ''; ?>>gal</option>
                    <option value="t" <?php echo ($inventory_data['unit'] === 't') ? 'selected' : ''; ?>>t</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($inventory_data['description']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="acquisition_date" class="form-label">Acquisition Date</label>
                <input type="date" class="form-control" id="acquisition_date" name="acquisition_date" value="<?php echo htmlspecialchars($inventory_data['acquisition_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="expiry_date" class="form-label">Expiry Date</label>
                <input type="date" class="form-control" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($inventory_data['expiry_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="brand" class="form-label">Brand</label>
                <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($inventory_data['brand']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Category</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="">Select Category</option>
                    <option value="Maintenance Supplies" <?php echo ($inventory_data['category'] === 'Maintenance Supplies') ? 'selected' : ''; ?>>Maintenance Supplies</option>
                    <option value="Sanitation Supplies" <?php echo ($inventory_data['category'] === 'Sanitation Supplies') ? 'selected' : ''; ?>>Sanitation Supplies</option>
                    <option value="Office Supplies" <?php echo ($inventory_data['category'] === 'Office Supplies') ? 'selected' : ''; ?>>Office Supplies</option>
                    <option value="Resident Essentials Supplies" <?php echo ($inventory_data['category'] === 'Resident Essentials Supplies') ? 'selected' : ''; ?>>Resident Essentials Supplies</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="acquired_via" class="form-label">Acquired Via</label>
                <select id="acquired_via" name="acquired_via" class="form-select" required>
                    <option value="">Select Acquisition Method</option>
                    <option value="City Fund" <?php echo ($inventory_data['acquired_via'] === 'City Fund') ? 'selected' : ''; ?>>City Fund</option>
                    <option value="Donation" <?php echo ($inventory_data['acquired_via'] === 'Donation') ? 'selected' : ''; ?>>Donation</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Update Item</button>
            <a href="/kalinga/inventory.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>