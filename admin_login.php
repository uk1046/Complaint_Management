<?php

$servername = "localhost";
$username = "college";
$password = "vinu123";
$dbname = "complaint";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admin01 WHERE name = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        header("Location: dashboard.php"); 
        exit();
    } else {
        $error_message = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
            max-width: 350px; 
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 50px auto 20px; 
        }

        .container h2 {
            color: #333;
            margin-bottom: 20px;
        }

        .container p {
            color: #666;
            line-height: 1.6;
            text-align: center;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between; 
            align-items: center; 
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: calc(100% - 22px); 
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box; 
        }

        .form-group input[type="submit"] {
            background-color: #333;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group input[type="submit"]:hover {
            background-color: #555;
        }

        .back-button a {
            text-decoration: none;
            color: #fff;
            background-color: #333;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button a:hover {
            background-color: #555;
        }
        
    </style>
</head>
<body>
    <header>
        <h1>Complaint Management System</h1>
    </header>

    <div class="container">
        <h2>Admin Login</h2>
        <?php if(isset($error_message)) { ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php } ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                
            </div>
            <div class="form-group">
            <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                
            </div>
            <div class="form-group">
            <input type="password" id="password" name="password" required>
        </div>
        <center>
            <div class="form-group">
                <input type="submit" value="Login">
            </div>
            </center>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Complaint Management System</p>
    </footer>
</body>
</html>


