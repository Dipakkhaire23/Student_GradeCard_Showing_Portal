<?php
// Start session
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Change to your MySQL username
$password = ""; // Change to your MySQL password
$dbname = "myv_result_db"; // Your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$prn = "";
$student_data = [];
$cgpa = null;
$error_message = "";
$copy_case_message = "";

// Handle form submission for fetching result
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prn = $_POST['prn'];

    // Fetch student data by PRN
    $stmt = $conn->prepare("SELECT * FROM res WHERE prn = ?");
    $stmt->bind_param("s", $prn);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student_data = $result->fetch_assoc();

        // Check for copy case (-1 in any marks)
        $copy_cases = [];
        if ($student_data['dbms'] == -1) {
            $copy_cases[] = "DBMS";
        }
        if ($student_data['ads'] == -1) {
            $copy_cases[] = "ADS";
        }
        if ($student_data['dm'] == -1) {
            $copy_cases[] = "DM";
        }
        if ($student_data['dt'] == -1) {
            $copy_cases[] = "DT";
        }
        if ($student_data['qsp'] == -1) {
            $copy_cases[] = "QSP";
        }

        // If there are any copy cases, show message
        if (count($copy_cases) > 0) {
            $copy_case_message = "You have a copy case on " . implode(", ", $copy_cases) . ". Please meet the principal.";
        } else {
            // Calculate CGPA based on marks and subject credits (Assuming total credits 20)
            $total_marks = $student_data['dbms'] + $student_data['ads'] + $student_data['dm'] + $student_data['dt'] + $student_data['qsp'];
            $cgpa = ($total_marks / 500) * 10; // Total marks out of 500, CGPA out of 10
        }
    } else {
        $error_message = "No record found for the provided PRN.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result Card - MyV Result</title>
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->
    <style>
        /* Internal styling specific to result */
        .result-container {
            width: 80%;
            margin: auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 50px;
        }
        .subject-marks {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .subject-marks th, .subject-marks td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .input-group {
            margin: 20px 0;
            text-align: center;
        }
        .btn-print {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-print:hover {
            background-color: #0056b3;
        }
        .error-message, .copy-case-message {
            color: red;
            text-align: center;
        }
    </style>
    <script>
        function printResult() {
            window.print();
        }
    </script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="logo">
        <a href="index.php">MyV Result</a>
    </div>
    <ul class="nav-links">
        <li><a href="welcome.php">Home</a></li>
    </ul>
</nav>

<div class="result-container">
    <div class="header">
        <center>
            <h2>Vishwakarma Institute of Information Technology</h2>
            <h3>Department of Artificial Intelligence and Data Science</h3>
            <h4>Result Card</h4>
        </center>
    </div>

    <form method="POST" action="show.php">
        <div class="input-group">
            <label for="prn">Enter PRN:</label>
            <input type="text" name="prn" id="prn" required>
            <button type="submit">Show Result</button>
        </div>
    </form>

    <?php if (!empty($copy_case_message)) { ?>
        <!-- Show copy case message if -1 is found -->
        <p class="copy-case-message"><?php echo $copy_case_message; ?></p>

    <?php } elseif (!empty($student_data)) { ?>
    <div class="result-details">
        <p><strong>PRN:</strong> <?php echo $student_data['prn']; ?></p>
        <p><strong>Name:</strong> <?php echo $student_data['name']; ?></p>

        <table class="subject-marks">
            <tr>
                <th>Subject</th>
                <th>Marks Obtained</th>
                <th>Maximum Marks</th>
                <th>Credits</th>
            </tr>
            <tr>
                <td>DBMS</td>
                <td><?php echo $student_data['dbms']; ?></td>
                <td>100</td>
                <td>4</td>
            </tr>
            <tr>
                <td>ADS</td>
                <td><?php echo $student_data['ads']; ?></td>
                <td>100</td>
                <td>4</td>
            </tr>
            <tr>
                <td>DM</td>
                <td><?php echo $student_data['dm']; ?></td>
                <td>100</td>
                <td>4</td>
            </tr>
            <tr>
                <td>DT</td>
                <td><?php echo $student_data['dt']; ?></td>
                <td>100</td>
                <td>4</td>
            </tr>
            <tr>
                <td>QSP</td>
                <td><?php echo $student_data['qsp']; ?></td>
                <td>100</td>
                <td>4</td>
            </tr>
        </table>

        <p><strong>Total Marks:</strong> <?php echo $total_marks = $student_data['dbms'] + $student_data['ads'] + $student_data['dm'] + $student_data['dt'] + $student_data['qsp']; ?> / 500</p>
        <p><strong>CGPA:</strong> <?php echo number_format($cgpa, 2); ?></p>

        <button class="btn-print" onclick="printResult()">Print Result</button>
    </div>

    <?php } elseif (!empty($error_message)) { ?>
        <!-- Show error message if no record found -->
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php } ?>
	
    <div class="footer"></div>
</div>

<h4>&copy; 2024 MyV Result. All rights reserved.</h4>
</body>
</html>
