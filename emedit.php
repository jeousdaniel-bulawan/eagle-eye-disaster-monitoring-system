<?php
// Database connection parameters
$host = 'localhost';
$db = 'kalinga';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Update the path below to a valid writable path on your server
    error_log('Connection failed: ' . $e->getMessage(), 3, __DIR__ . '/error.log');
    die('Connection failed. Please try again later.');
}

$error_message = '';
$success_message = '';

// Check if an ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: employees.php");
    exit();
}

$employee_id = (int)$_GET['id'];

// Fetch employee data
$stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
$stmt->execute([$employee_id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
    header("Location: employees.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and assign the form values
    $first_name = trim($_POST['first_name']);
    $surname = trim($_POST['surname']);
    $middle_name = trim($_POST['middle_name']);
    $designation = trim($_POST['designation']);
    $birthday = $_POST['birthday'];
    $address = trim($_POST['address']);
    $years_of_service = intval($_POST['years_of_service']);
    $pag_ibig_no = trim($_POST['pag_ibig_no']);
    
    // Handle additional fields as text inputs
    $sss_no = trim($_POST['sss_no']);
    $philhealth_no = trim($_POST['philhealth_no']);
    $gsis_no = trim($_POST['gsis_no']);
    $functions_responsibilities = trim($_POST['functions_responsibilities']);
    $precinct_no = trim($_POST['precinct_no']);
    $barangay = trim($_POST['barangay']);

    // Basic validation (you can expand this as needed)
    if (empty($first_name) || empty($surname) || empty($designation) || empty($birthday) || empty($address) || empty($years_of_service) || empty($pag_ibig_no) || empty($sss_no) || empty($philhealth_no) || empty($gsis_no) || empty($functions_responsibilities) || empty($precinct_no) || empty($barangay)) {
        $error_message = "Please fill in all required fields.";
    } else {
        // Update the employee record in the database
        $stmt = $pdo->prepare("UPDATE employees SET 
            first_name = ?, 
            surname = ?, 
            middle_name = ?, 
            designation = ?, 
            birthday = ?, 
            address = ?, 
            years_of_service = ?, 
            pag_ibig_no = ?, 
            sss_no = ?, 
            philhealth_no = ?,
            gsis_no = ?,
            functions_responsibilities = ?,
            precinct_no = ?,
            barangay = ?
            WHERE employee_id = ?");

        $params = [
            $first_name,
            $surname,
            $middle_name,
            $designation,
            $birthday,
            $address,
            $years_of_service,
            $pag_ibig_no,
            $sss_no,
            $philhealth_no,
            $gsis_no,
            $functions_responsibilities,
            $precinct_no,
            $barangay,
            $employee_id
        ];

        try {
            $stmt->execute($params);
            // Redirect with a success flag
            header("Location: employees.php?updated=true");
            exit();
        } catch (PDOException $e) {
            error_log('Update failed: ' . $e->getMessage(), 3, __DIR__ . '/error.log');
            $error_message = "Failed to update employee information. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .form-check {
            margin-bottom: 15px;
        }
        .form-check-input {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Employee Information</h2>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success_message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($employee['first_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="surname" class="form-label">Last Name<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="surname" name="surname" value="<?= htmlspecialchars($employee['surname']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= htmlspecialchars($employee['middle_name']) ?>">
            </div>
            <div class="mb-3">
                <label for="designation" class="form-label">Designation<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="designation" name="designation" value="<?= htmlspecialchars($employee['designation']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="birthday" class="form-label">Birthday<span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="birthday" name="birthday" value="<?= htmlspecialchars($employee['birthday']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($employee['address']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="years_of_service" class="form-label">Years of Service<span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="years_of_service" name="years_of_service" value="<?= htmlspecialchars($employee['years_of_service']) ?>" min="0" required>
            </div>
            <div class="mb-3">
                <label for="pag_ibig_no" class="form-label">Pag-IBIG No<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="pag_ibig_no" name="pag_ibig_no" value="<?= htmlspecialchars($employee['pag_ibig_no']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="sss_no" class="form-label">SSS No<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="sss_no" name="sss_no" value="<?= htmlspecialchars($employee['sss_no']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="philhealth_no" class="form-label">PhilHealth No<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="philhealth_no" name="philhealth_no" value="<?= htmlspecialchars($employee['philhealth_no']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="gsis_no" class="form-label">GSIS No<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="gsis_no" name="gsis_no" value="<?= htmlspecialchars($employee['gsis_no']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="functions_responsibilities" class="form-label">Functions & Responsibilities<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="functions_responsibilities" name="functions_responsibilities" value="<?= htmlspecialchars($employee['functions_responsibilities']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="precinct_no" class="form-label">Precinct No<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="precinct_no" name="precinct_no" value="<?= htmlspecialchars($employee['precinct_no']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="barangay" class="form-label">Barangay<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="barangay" name="barangay" value="<?= htmlspecialchars($employee['barangay']) ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="employees.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
