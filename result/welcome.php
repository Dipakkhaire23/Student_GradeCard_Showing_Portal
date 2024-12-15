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

// Redirect to login if user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Logout functionality
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$success_message = "";
$error_message = "";

// Handle form submission to insert, update, or delete data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prn = $_POST['prn'];
    $name = $_POST['name'];
    $dbms = $_POST['dbms'];
    $ads = $_POST['ads'];
    $dm = $_POST['dm'];
    $dt = $_POST['dt'];
    $qsp = $_POST['qsp'];

    // If any of the marks is less than -1, set an error message
    if ($dbms < -1 || $ads < -1 || $dm < -1 || $dt < -1 || $qsp < -1) {
        $error_message = "Marks cannot be less than -1. Enter -1 for a copy case.";
    } else {
        if (isset($_POST['submit'])) {
            // Check if record exists for the PRN
            $check_prn = $conn->prepare("SELECT * FROM res WHERE prn = ?");
            $check_prn->bind_param("s", $prn);
            $check_prn->execute();
            $result = $check_prn->get_result();

            if ($result->num_rows > 0) {
                // Update record if exists
                $update = $conn->prepare("UPDATE res SET name = ?, dbms = ?, ads = ?, dm = ?, dt = ?, qsp = ? WHERE prn = ?");
                $update->bind_param("siiiiis", $name, $dbms, $ads, $dm, $dt, $qsp, $prn);
                if ($update->execute()) {
                    $success_message = "Record updated successfully!";
                } else {
                    $error_message = "Error updating record.";
                }
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO res (prn, name, dbms, ads, dm, dt, qsp) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssiiiii", $prn, $name, $dbms, $ads, $dm, $dt, $qsp);
                if ($stmt->execute()) {
                    $success_message = "Record saved successfully!";
                } else {
                    $error_message = "Error saving record.";
                }
            }
        } elseif (isset($_POST['delete'])) {
            // Delete record based on PRN
            $delete = $conn->prepare("DELETE FROM res WHERE prn = ?");
            $delete->bind_param("s", $prn);
            if ($delete->execute()) {
                $success_message = "Record deleted successfully!";
            } else {
                $error_message = "Error deleting record.";
            }
        } elseif (isset($_POST['update'])) {
            // Update existing record based on PRN
            $update = $conn->prepare("UPDATE res SET name = ?, dbms = ?, ads = ?, dm = ?, dt = ?, qsp = ? WHERE prn = ?");
            $update->bind_param("siiiiis", $name, $dbms, $ads, $dm, $dt, $qsp, $prn);
            if ($update->execute()) {
                $success_message = "Record updated successfully!";
            } else {
                $error_message = "Error updating record.";
            }
        }
    }
}

// Fetch all records for displaying
$all_students_query = "SELECT * FROM res";
$all_students_result = $conn->query($all_students_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - MyV Result</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        .navbar {
            background-color: #007bff;
            padding: 1rem;
            color: #fff;
        }
        .navbar .logo a {
            color: white;
            text-decoration: none;
            font-size: 1.5rem;
        }
        .navbar .nav-links {
            list-style-type: none;
        }
        .navbar .nav-links li {
            display: inline;
            margin-right: 10px;
        }
        .navbar .nav-links li a {
            color: white;
            text-decoration: none;
        }
        .main-content {
            padding: 2rem;
        }
        .input-group {
            margin: 15px 0;
        }
        .input-group label {
            display: block;
            font-weight: bold;
        }
        .input-group input {
            padding: 0.5rem;
            width: 100%;
            box-sizing: border-box;
        }
        .btn-submit, .btn-update, .btn-delete {
            padding: 0.7rem 1.2rem;
            background-color: #007bff;
            color: white;
            border: none;
            margin-right: 10px;
            cursor: pointer;
        }
        .success-message {
            color: green;
        }
        .error-message {
            color: red;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .instructions {
            margin-top: 15px;
            font-weight: bold;
        }
        .records {
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="logo">
            <a href="#">MyV Result</a>
        </div>
        <ul class="nav-links">
            <li><a href="welcome.php">Home</a></li>
            <li>
                <form method="POST" action="welcome.php" style="display:inline;">
                    <button type="submit" name="logout" class="btn-logout" style="background:none; color:white; border:none; cursor:pointer;">Logout</button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Welcome Section -->
    <section class="main-content">
        <div class="welcome-container">
            <h2>Welcome, <?php echo $_SESSION['name']; ?>!</h2>

            <!-- Display success or error messages -->
            <?php if (!empty($success_message)) { ?>
                <p class="success-message"><?php echo $success_message; ?></p>
            <?php } elseif (!empty($error_message)) { ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php } ?>

            <!-- Instructions -->
            <p class="instructions">Please enter marks for each subject. If a candidate has a copy case, enter "-1" for that subject.</p>

            <!-- Form to enter PRN, name, and marks for subjects -->
            <form method="POST" action="welcome.php">
                <div class="input-group">
                    <label for="prn">PRN:</label>
                    <input type="text" name="prn" id="prn" required>
                </div>
                <div class="input-group">
                    <label for="name">Name:</label>
                    <input type="text" name="name" id="name" required>
                </div>
                
                <!-- Subject Marks Inputs -->
                <div class="input-group">
                    <label for="dbms">DBMS Marks:</label>
                    <input type="number" name="dbms" id="dbms" required>
                </div>
                <div class="input-group">
                    <label for="ads">ADS Marks:</label>
                    <input type="number" name="ads" id="ads" required>
                </div>
                <div class="input-group">
                    <label for="dm">DM Marks:</label>
                    <input type="number" name="dm" id="dm" required>
                </div>
                <div class="input-group">
                    <label for="dt">DT Marks:</label>
                    <input type="number" name="dt" id="dt" required>
                </div>
                <div class="input-group">
                    <label for="qsp">QSP Marks:</label>
                    <input type="number" name="qsp" id="qsp" required>
                </div>

                <!-- Submit, Update and Delete Buttons -->
                <div class="input-group">
                    <button type="submit" name="submit" class="btn-submit">Submit</button>
                    <button type="submit" name="update" class="btn-update">Update</button>
                    <button type="submit" name="delete" class="btn-delete">Delete</button>
                </div>
            </form>
        </div>

        <!-- Separate div for displaying all records -->
        <div class="records">
            <h3>All Student Records</h3>
            <?php if ($all_students_result->num_rows > 0) { ?>
                <table>
                    <thead>
                        <tr>
                            <th>PRN</th>
                            <th>Name</th>
                            <th>DBMS</th>
                            <th>ADS</th>
                            <th>DM</th>
                            <th>DT</th>
                            <th>QSP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $all_students_result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $row['prn']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['dbms']; ?></td>
                                <td><?php echo $row['ads']; ?></td>
                                <td><?php echo $row['dm']; ?></td>
                                <td><?php echo $row['dt']; ?></td>
                                <td><?php echo $row['qsp']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <p>No records found.</p>
            <?php } ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 MyV Result. All rights reserved.</p>
    </footer>

</body>
</html>
