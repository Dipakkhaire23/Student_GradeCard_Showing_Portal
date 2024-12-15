<?php
// Start session
session_start();

// Database connection
$servername = "localhost";  // Change if different
$username = "root";         // Change to your MySQL username
$password = "";             // Change to your MySQL password
$dbname = "myv_result_db";  // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Simple input validation
    if (!empty($email) && !empty($password)) {
        // Prepare SQL query
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch user data
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name']; // Store user's name for greeting

                // Redirect to the main page (welcome page)
                header("Location: welcome.php");
                exit;
            } else {
                $error_message = "Invalid email or password!";
            }
        } else {
            $error_message = "User not found!";
        }
    } else {
        $error_message = "Please enter both email and password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyV Result</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">
            <a href="#">MyV Result</a>
        </div>
        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Signup</a></li>
            <li><a href="show.php">See Result</a></li>
            <li><a href="#about-us">About Us</a></li>
            <li><a href="#contact-us">Contact Us</a></li>
        </ul>
    </nav>

    <!-- Login Form -->
    <section class="main-content">
        <div class="login-container">
            <h2>Login to MyV Result</h2>

            <?php if (!empty($error_message)) { ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php } ?>

            <form method="POST" action="login.php">
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div id="about-us" class="footer-section">
            <h3>About Us</h3>
            <p>MyV Result is an online platform that helps students to view their academic results easily and securely. We aim to provide a seamless user experience to students, helping them access their grades in a few clicks.</p>
        </div>
        <div id="contact-us" class="footer-section">
            <h3>Contact Us</h3>
            <p>If you have any questions, feel free to reach out at: <br>
               Email: support@myvresult.com<br>
               Phone: +123 456 7890
            </p>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 MyV Result. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
