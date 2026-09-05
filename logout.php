<?php
session_start(); // Start the session

// Check if a session exists
if (isset($_SESSION['user_id'])) {
    // Destroy the session to log out the user
    session_unset(); // Free all session variables
    session_destroy(); // Destroy the session data

    // Optionally, delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, 
            $params["path"], 
            $params["domain"], 
            $params["secure"], 
            $params["httponly"]
        );
    }
}

// Redirect to the login page or home page
header("Location: index.php"); // Change 'login.php' to the appropriate page
exit();
?>
