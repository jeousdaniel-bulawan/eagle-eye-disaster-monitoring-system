<?php
// Start the session
session_start();

// MySQL database connection parameters
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "kalinga";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to validate input data
function validate_input($data) {
    if (empty($data)) {
        return false;
    }
    return true;
}

// Handle Sign-In and Sign-Up actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action == 'sign_in') {
            // Handle Sign-In
            $email = sanitize_input($_POST['email']);
            $password = $_POST['password'];
            $role = sanitize_input($_POST['role']);

            // Validate inputs
            if (!validate_input($email) || !validate_input($password) || !validate_input($role)) {
                throw new Exception("All fields are required for sign-in.");
            }

            // Prepare SQL statement to prevent SQL injection
            $sql = "SELECT id, username, email, role, password 
                    FROM users 
                    WHERE email = ? AND role = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $role);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $users = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $users['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $users['id'];
                    $_SESSION['username'] = $users['username'];
                    $_SESSION['role'] = $users['role'];

                    // Redirect based on role
                    if ($users['role'] == 'Admin') {
                        header("Location: admin.php");
                    } elseif ($users['role'] == 'Social Worker') {
                        header("Location: dashboard.php");
                    } else {
                        header("Location: user_dashboard.php");
                    }
                    exit();
                } else {
                    throw new Exception("Invalid password. Please try again.");
                }
            } else {
                throw new Exception("User  not found. Please sign up.");
            }

            $stmt->close();
        } elseif ($action == 'sign_up') {
            // Handle Sign-Up
            $username = sanitize_input($_POST['name']);
            $email = sanitize_input($_POST['email']);
            $password = $_POST['password'];
            $role = sanitize_input($_POST['role']);

            // Validate inputs
            if (!validate_input($username) || !validate_input($email) || !validate_input($password) || !validate_input($role)) {
                throw new Exception("All fields are required for sign-up.");
            }

            // Check if email or username already exists
            $check_sql = "SELECT id FROM users WHERE email = ? OR username = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ss", $email, $username);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                throw new Exception("Email or Username already exists. Please choose another.");
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_ARGON2I);

                // Insert new user into users table
                $insert_sql = "INSERT INTO users (username, email, role, password, created_at) VALUES (?, ?, ?, ?, NOW())";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("ssss", $username, $email, $role, $hashed_password);

                if ($insert_stmt->execute()) {
                    echo "<script>alert('Registration successful! You can now log in.');</script>";
                } else {
                    throw new Exception("Registration failed. Please try again.");
                }

                $insert_stmt->close();
            }

            $check_stmt->close();
        }
    } catch (Exception $e) {
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            height: 100vh;
            text-align: center;
        }

        .container {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
        }

        .container p {
            font-size: 14px;
            line-height: 20px;
            letter-spacing: 0.3px;
            margin: 10px 0; /* Reduced margin for better spacing */
        }

        .container span {
            font-size: 12px;
        }

        .container a {
            color: #000;
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
            display: block;
        }

        .container button {
            background-color: #16423C;
            color: #fff;
            font-size: 12px;
            padding: 10px 45px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 10px;
            cursor: pointer;
        }

        .container button.hidden {
            background-color: transparent;
            border-color: #f0f0f0;
        }

        .container form {
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
        }

        .container input, .container select {
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 10px 15px;
            font-size: 13px;
            border-radius: 8px;
            width: 100%;
            outline: none;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in {
            left: 0;
            width: 50%;
            z-index: 2;
        }

        .container.active .sign-in {
            transform: translateX(100%);
        }

        .sign-up {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: move 0.6s;
        }

        @keyframes move {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 150px 0 0 100px;
            z-index: 1000;
        }

        .container.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 0 150px 100px 0;
        }

        .toggle {
            background-color: #16423C;
            height: 100%;
            background: linear-gradient(to right, #16423C, #16423D);
            color: #fff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .container.active .toggle {
            transform: translateX(50%);
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 30px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .toggle-left {
            transform: translateX(-200%);
        }

        .container.active .toggle-left {
            transform: translateX(0);
        }

        .toggle-right {
            right: 0;
            transform: translateX(0);
        }

        .container.active .toggle-right {
            transform: translateX(200%);
        }

        /* Revised margin adjustments for text elements */
        .toggle-panel h1 {
            margin: -15px 0; /* Increased margin for better separation from other elements */
        }

        .toggle-panel p {
            margin: 15px 0; /* Adjusted margin for better alignment */
        }

        .toggle-panel button.hidden {
            margin-top: 10px;
        }
    </style>
    <title>Bahay Kalinga Portal | Log-in</title>
</head>

<body>
    <div class="container" id="container">
        <div class="form-container sign-up">
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="sign_up">
                <h1>Create Account</h1>
                <span>or use your email for registration</span>
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="Admin">Admin</option>
                    <option value="Social Worker">Social Worker</option>
                </select>
                <button type="submit">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form method="POST" action="index.php">
                <input type="hidden" name="action" value="sign_in">
                <h1>Sign In</h1>
                <span>or use your email password</span>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                    <select name="role" required>
                        <option value="Admin">Admin</option>
                        <option value="Social Worker">Social Worker</option>
                    </select>
                <a href="change_password.php">Forgot Your Password?</a>
                <button type="submit">Sign In</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Hello, Friend</h1>
                    <p>this is Bahay Kalinga - Parañaque Portal</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Welcome Back</h1>
                    <p>to Bahay Kalinga - Parañaque Portal</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const container = document.getElementById('container');
        const registerBtn = document.getElementById('register');
        const loginBtn = document.getElementById('login');

        registerBtn.addEventListener('click', () => {
            container.classList.add("active");
        });

        loginBtn.addEventListener('click', () => {
            container.classList.remove("active");
        });
    </script>
</body>

</html>