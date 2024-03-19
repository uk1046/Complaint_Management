<?php
$servername = "localhost";
$username = "college"; 
$password = "vinu123"; 
$dbname = "complaint";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $email = $mobile = $password = $confirm_password = $department = $class_year = "";
$name_err = $email_err = $mobile_err = $password_err = $confirm_password_err = $department_err = $class_year_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your full name.";
    } else {
        $name = trim($_POST["name"]);
    }

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email address.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);

            $param_email = trim($_POST["email"]);

            if ($stmt->execute()) {
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $email_err = "This email is already taken.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            $stmt->close();
        }
    }

    if (empty(trim($_POST["mobile"]))) {
        $mobile_err = "Please enter your mobile number.";
    } elseif (!preg_match("/^[0-9]{10}$/", trim($_POST["mobile"]))) {
        $mobile_err = "Invalid mobile number.";
    } else {
        $mobile = trim($_POST["mobile"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    if (empty(trim($_POST["department"]))) {
        $department_err = "Please select your department.";
    } else {
        $department = trim($_POST["department"]);
    }

    if (empty(trim($_POST["class_year"]))) {
        $class_year_err = "Please select your class year.";
    } else {
        $class_year = trim($_POST["class_year"]);
    }

    if (empty($name_err) && empty($email_err) && empty($mobile_err) && empty($password_err) && empty($confirm_password_err) && empty($department_err) && empty($class_year_err)) {
   
        $sql = "INSERT INTO users (name, email, mobile, password, department, class_year) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssss", $param_name, $param_email, $param_mobile, $param_password, $param_department, $param_class_year);

            $param_name = $name;
            $param_email = $email;
            $param_mobile = $mobile;
            $param_password = password_hash($password, PASSWORD_DEFAULT); 
            $param_department = $department;
            $param_class_year = $class_year;

            if ($stmt->execute()) {
                $complaint_table_name = "complaints_" . $conn->insert_id;
                $sql_create_table = "CREATE TABLE $complaint_table_name (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    category VARCHAR(255) NOT NULL,
                    subcategory VARCHAR(255) NOT NULL,
                    subject VARCHAR(255) NOT NULL,
                    description TEXT NOT NULL,
                    status VARCHAR(255) NOT NULL,
                    note VARCHAR(255) NOT NULL,
                    date VARCHAR(255) NOT NULL,
                    time VARCHAR(255) NOT NULL
                )";
                if ($conn->query($sql_create_table) === TRUE) {
                } else {
                    echo "Error creating table: " . $conn->error;
                }
                header("location: login.php");
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }
        
        header, footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 20px 0;
        }
        
        footer {
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
        }
        
        footer p {
            margin: 0;
        }

        .container {
            max-width: 400px;
            margin: 25px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group select {
            width: calc(100% - 22px);
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .form-group input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group input[type="submit"]:hover {
            background-color: #555;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <h1>Complaint Management System</h1>
    </header>

    <div class="container">
        <h2>User Registration</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo $name; ?>">
                <span class="error-message"><?php echo $name_err; ?></span>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $email; ?>">
                <span class="error-message"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <label>Mobile Number</label>
                <input type="text" name="mobile" value="<?php echo $mobile; ?>">
                <span class="error-message"><?php echo $mobile_err; ?></span>
            </div>
            <div class="form-group">
                <label>Department</label>
                <select name="department">
                    <option value="">Select Department</option>
                    <option value="Computer Engineering">Computer Engineering</option>
                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                    <option value="Electrical Engineering">Electrical Engineering</option>
                    <option value="Civil Engineering">Civil Engineering</option>
                    <option value="Computer and Electronics Engineering">Computer and Electronics Engineering</option>
                    <option value="Diploma in Medical Laboratory Technology(DMLT)">Diploma in Medical Laboratory Technology(DMLT)</option>
                    <option value="Mechatronics Engineering">Mechatronics Engineering</option>
                </select>
                <span class="error-message"><?php echo $department_err; ?></span>
            </div>
            <div class="form-group">
                <label>Class Year</label>
                <select name="class_year">
                    <option value="">Select Class Year</option>
                    <option value="FY">First Year</option>
                    <option value="SY">Second Year</option>
                    <option value="TY">Third Year</option>
                </select>
                <span class="error-message"><?php echo $class_year_err; ?></span>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" value="<?php echo $password; ?>">
                <span class="error-message"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" value="<?php echo $confirm_password; ?>">
                <span class="error-message"><?php echo $confirm_password_err; ?></span>
            </div>
            
            <div class="form-group">
                <input type="submit" value="Register">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Complaint Management System</p>
    </footer>
</body>
</html>
