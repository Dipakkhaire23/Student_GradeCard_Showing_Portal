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

$success_message = "";
$error_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    $designation = $_POST['designation'];
    $college_name = $_POST['college_name'];
    $password = $_POST['password'];
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Simple input validation
    if (!empty($name) && !empty($email) && !empty($phone_no) && !empty($designation) && !empty($college_name) && !empty($password)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Insert new user into the database
            $stmt = $conn->prepare("INSERT INTO user (name, email, phone_no, designation, college_name, password) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $name, $email, $phone_no, $designation, $college_name, $hashed_password);
            if ($stmt->execute()) {
                $success_message = "User registered successfully!";
            } else {
                $error_message = "Error: Could not register user.";
            }
        } else {
            $error_message = "Email already exists!";
        }
    } else {
        $error_message = "All fields are required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - MyV Result</title>
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

    <!-- Signup Form -->
    <section class="main-content">
        <div class="signup-container">
            <h2>Register for MyV Result</h2>

            <!-- Display success or error message -->
            <?php if (!empty($success_message)) { ?>
                <p class="success-message"><?php echo $success_message; ?></p>
            <?php } elseif (!empty($error_message)) { ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php } ?>

            <form method="POST" action="signup.php">
                <div class="input-group">
                    <label for="name">Name:</label>
                    <input type="text" name="name" id="name" required>
                </div>
                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                </div>
                <div class="input-group">
                    <label for="phone_no">Phone No:</label>
                    <input type="text" name="phone_no" id="phone_no" required>
                </div>
                <div class="input-group">
                    <label for="designation">Designation:</label>
                    <input type="text" name="designation" id="designation" required>
                </div>
                <div class="input-group">
                    <label for="college_name">College Name:</label>
                    <input type="text" name="college_name" id="college_name" required>
                </div>
                <div class="input-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <button type="submit" class="btn-signup">Signup</button>
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
