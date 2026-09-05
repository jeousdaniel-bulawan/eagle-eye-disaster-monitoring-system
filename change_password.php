<?php
// Database connection parameters
$host = 'localhost';
$db = 'kalinga';
$user = 'root';
$pass = '';

try {
    // Establish database connection using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Initialize message variable
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form input values
    $email = $_POST['email'];
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Fetch user by email
    $stmt = $pdo->prepare("SELECT password FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "Email not found.";
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $message = "Current password is incorrect.";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "New password and confirmation do not match.";
    } else {
        // Hash the new password before storing
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $updateStmt = $pdo->prepare("UPDATE users SET password = :newPassword WHERE email = :email");
        $updateStmt->execute([
            'newPassword' => $hashedPassword,
            'email' => $email
        ]);

        // Redirect to index.php after successful password change
        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .change-password-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }        
        body {
            background-color: #f0f0f0;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .btn-primary {
            background-color: #16423C;
            border-color: #16423C;
        }
        .btn-primary:hover {
            background-color: #0f302a;
        }
        .form-control:focus {
            border-color: #16423C;
        }
        .alert {
            margin-top: 15px;
        }
        .back-button {
            background-color: #f0f0f0;
            border: none;
            color: #000;
        }
        .back-button:hover {
            background-color: #16423C;
        }
    </style>
</head>
<body>
    <div class="change-password-container">
        <h2>Change Password</h2>
        <?php if ($message): ?>
            <div class="alert <?= strpos($message, 'successfully') !== false ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="currentPassword" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
            </div>
            <div class="mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
            </div>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn back-button" onclick="window.location.href='admin.php';">Back</button>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </div>
        </form>
    </div>
</body>
</html>
