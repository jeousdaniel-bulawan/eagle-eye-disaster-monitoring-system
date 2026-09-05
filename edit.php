<?php
// Database connection parameters
$host = 'localhost';
$db = 'kalinga';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log('Connection failed: ' . $e->getMessage(), 3, '/path/to/error.log');
    die('Connection failed. Please try again later.');
}

$error_message = '';
$success_message = '';

// Check if an ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$client_id = $_GET['id'];

// Fetch client data
$stmt = $pdo->prepare("SELECT * FROM clients WHERE client_id = ?");
$stmt->execute([$client_id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    header("Location: dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize input
    $admission_date = $_POST['admission_date'] ?? '';
    $discharge_date = $_POST['discharge_date'] ?? '';
    $name = $_POST['name'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $age = $_POST['age'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = $_POST['address'] ?? '';
    $civil_status = $_POST['civil_status'] ?? '';
    $case_category = $_POST['case_category'] ?? '';
    $medical_condition = $_POST['medical_condition'] ?? '';
    $service_rendered = $_POST['service_rendered'] ?? '';
    $social_worker = $_POST['social_worker'] ?? '';
    $reintegration = $_POST['reintegration'] ?? '';
    $shelter_transfer = $_POST['shelter_transfer'] ?? '';
    $balik_probinsiya = $_POST['balik_probinsiya'] ?? '';
    $remarks = $_POST['remarks'] ?? '';

    // Perform validation here
    // ...

    // Update the database
    $stmt = $pdo->prepare("UPDATE clients SET 
        admission_date = ?, discharge_date = ?, name = ?, birthdate = ?, 
        age = ?, gender = ?, address = ?, civil_status = ?, case_category = ?, 
        medical_condition = ?, service_rendered = ?, social_worker = ?, 
        reintegration = ?, shelter_transfer = ?, balik_probinsiya = ?, remarks = ? 
        WHERE client_id = ?");

    try {
        $stmt->execute([
            $admission_date, $discharge_date, $name, $birthdate, 
            $age, $gender, $address, $civil_status, $case_category, 
            $medical_condition, $service_rendered, $social_worker, 
            $reintegration, $shelter_transfer, $balik_probinsiya, $remarks, 
            $client_id
        ]);
        $success_message = "Client updated successfully.";
    } catch (PDOException $e) {
        $error_message = "Error updating client: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client | Bahay Kalinga</title>
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
    <div class="container">
        <h2>Edit Client</h2>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="post">
            <!-- Admission Date Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Admission Date</label>
                <div class="col-sm-6">
                    <input type="date" class="form-control" name="admission_date" value="<?php echo htmlspecialchars($client['admission_date'] ?? ''); ?>">
                </div>
            </div>

            <!-- Discharge Date Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Discharge Date</label>
                <div class="col-sm-6">
                    <input type="date" class="form-control" name="discharge_date" value="<?php echo htmlspecialchars($client['discharge_date'] ?? ''); ?>">
                </div>
            </div>

            <!-- Name Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($client['name'] ?? ''); ?>">
                </div>
            </div>

            <!-- Birthdate Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Birthdate</label>
                <div class="col-sm-6">
                    <input type="date" class="form-control" name="birthdate" value="<?php echo htmlspecialchars($client['birthdate'] ?? ''); ?>">
                </div>
            </div>

            <!-- Age Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Age</label>
                <div class="col-sm-6">
                    <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($client['age'] ?? ''); ?>">
                </div>
            </div>

            <!-- Gender Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Gender</label>
                <div class="col-sm-6">
                    <select class="form-control" name="gender">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo $client['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $client['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="LGBTQIA+" <?php echo $client['gender'] === 'LGBTQIA+' ? 'selected' : ''; ?>>LGBTQIA+</option>
                    </select>
                </div>
            </div>

            <!-- Address Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Address</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($client['address'] ?? ''); ?>">
                </div>
            </div>

            <!-- Civil Status Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Civil Status</label>
                <div class="col-sm-6">
                    <select class="form-control" name="civil_status">
                        <option value="">Select Civil Status</option>
                        <option value="Single" <?php echo $client['civil_status'] === 'Single' ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo $client['civil_status'] === 'Married' ? 'selected' : ''; ?>>Married</option>
                        <option value="Divorced" <?php echo $client['civil_status'] === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                        <option value="Widowed" <?php echo $client['civil_status'] === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Separated" <?php echo $client['civil_status'] === 'Separated' ? 'selected' : ''; ?>>Separated</option>
                        <option value="Domestic Partnership/Civil Union" <?php echo $client['civil_status'] === 'Domestic Partnership/Civil Union' ? 'selected' : ''; ?>>Domestic Partnership/Civil Union</option>
                        <option value="Registered Partnership" <?php echo $client['civil_status'] === 'Registered Partnership' ? 'selected' : ''; ?>>Registered Partnership</option>
                    </select>
                </div>
            </div>

            <!-- Case Category Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Case Category</label>
                <div class="col-sm-6">
                    <select class="form-control" name="case_category">
                        <option value="">Select Case Category</option>
                        <option value="Survivors of Domestic Violence" <?php echo $client['case_category'] === 'Survivors of Domestic Violence' ? 'selected' : ''; ?>>Survivors of Domestic Violence</option>
                        <option value="Children in Conflict with the Law" <?php echo $client['case_category'] === 'Children in Conflict with the Law' ? 'selected' : ''; ?>>Children in Conflict with the Law</option>
                        <option value="Victims of Abuse" <?php echo $client['case_category'] === 'Victims of Abuse' ? 'selected' : ''; ?>>Victims of Abuse</option>
                        <option value="Trafficking Victims" <?php echo $client['case_category'] === 'Trafficking Victims' ? 'selected' : ''; ?>>Trafficking Victims</option>
                        <option value="Others" <?php echo $client['case_category'] === 'Others' ? 'selected' : ''; ?>>Others</option>
                    </select>
                </div>
            </div>

            <!-- Medical Condition Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Medical Condition</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="medical_condition" value="<?php echo htmlspecialchars($client['medical_condition'] ?? ''); ?>">
                </div>
            </div>

            <!-- Service Rendered Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Service Rendered</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="service_rendered" value="<?php echo htmlspecialchars($client['service_rendered'] ?? ''); ?>">
                </div>
            </div>

            <!-- Social Worker Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Social Worker</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="social_worker" value="<?php echo htmlspecialchars($client['social_worker'] ?? ''); ?>">
                </div>
            </div>

            <!-- Reintegration Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Reintegration</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="reintegration" value="<?php echo htmlspecialchars($client['reintegration'] ?? ''); ?>">
                </div>
            </div>

            <!-- Shelter Transfer Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Shelter Transfer</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="shelter_transfer" value="<?php echo htmlspecialchars($client['shelter_transfer'] ?? ''); ?>">
                </div>
            </div>

            <!-- Balik Probinsiya Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Balik Probinsiya</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="balik_probinsiya" value="<?php echo htmlspecialchars($client['balik_probinsiya'] ?? ''); ?>">
                </div>
            </div>

            <!-- Remarks Field -->
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Remarks</label>
                <div class="col-sm-6">
                    <textarea class="form-control" name="remarks" rows="3"><?php echo htmlspecialchars($client['remarks'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-3"></div>
                <div class="col-sm-6">
                    <button type="submit" class="btn btn-primary">Update Client</button>
                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
