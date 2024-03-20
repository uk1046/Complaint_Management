<?php
session_start();

// Database configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'college');
define('DB_PASSWORD', 'vinu123');
define('DB_NAME', 'complaint');

// Establish a connection to the database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["status"], $_POST["note"], $_POST["complaint_id"], $_POST["userid"])) {
    // Retrieve complaint_id, status, and note
    $complaint_id = $_POST["complaint_id"];
    $status = $_POST["status"];
    $note = $_POST["note"];
    $userid = $_POST["userid"]; 

    // Construct the table name
    $complaints_table = "complaints_" . $userid;

    // Construct the update query
    $sql = "UPDATE $complaints_table SET status='$status', note='$note' WHERE id=$complaint_id";

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Complaint updated successfully.');</script>";
    } else {
        echo "<script>alert('Error updating complaint: " . $conn->error . "');</script>";
    }
}

// Check if delete request is received
if (isset($_GET['delete'])) {
    $userId = $_GET['delete'];

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully.');</script>";
    } else {
        echo "<script>alert('Failed to delete user.');</script>";
    }

    $stmt->close();
}

// Fetch users from the database in descending order
$sql_users = "SELECT id, name, email, mobile, department, class_year FROM users ORDER BY name DESC";
$result_users = $conn->query($sql_users);

// Store all complaints sorted by date and time in descending order
$allComplaints = array();
if ($result_users->num_rows > 0) {
    while ($row_users = $result_users->fetch_assoc()) {
        $userid = $row_users['id'];
        $complaints_table = "complaints_" . $userid;
        $selectedDayRange = isset($_POST["dayrange"]) ? intval($_POST["dayrange"]) : 10000;
        $selectedStatus = isset($_POST["complaintstatus01"]) ? $_POST["complaintstatus01"] : "Pending";

        
        // Construct SQL query with day range
        $interval = "-$selectedDayRange DAY";
        $sql_complaints = "SELECT * FROM $complaints_table WHERE date >= DATE_SUB(NOW(), INTERVAL $selectedDayRange DAY) AND status = '$selectedStatus' ORDER BY date DESC, time DESC";
        $result_complaints = $conn->query($sql_complaints);
        if ($result_complaints->num_rows > 0) {
            while ($row_complaints = $result_complaints->fetch_assoc()) {
                $allComplaints[] = array(
                    'id' => $row_complaints['id'], // Adding ID to the array
                    'userid' => $userid,
                    'username' => $row_users['name'],
                    'department' => $row_users['department'],
                    'class_year' => $row_users['class_year'],
                    'category' => $row_complaints['category'],
                    'subcategory' => $row_complaints['subcategory'],
                    'subject' => $row_complaints['subject'],
                    'description' => $row_complaints['description'],
                    'status' => $row_complaints['status'],
                    'date' => $row_complaints['date'],
                    'time' => $row_complaints['time']
                );
            }
        }
    }
}

// Sort all complaints by date and time in descending order
usort($allComplaints, function ($a, $b) {
    return strtotime($b['date'] . ' ' . $b['time']) - strtotime($a['date'] . ' ' . $a['time']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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

        nav {
            background-color: #333;
            color: #fff;
            text-align: right;
            padding: 10px 0;
            padding: 20px;
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
            max-width: 1200px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto 90px auto;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #333;
            color: #fff;
        }

        .delete-btn {
            background-color: #ff3333;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 5px 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-btn:hover {
            background-color: #ff6666;
        }

        .dialog-overlay {
            display: none;
            position: fixed;
            z-index: 999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .dialog-box {
            display: none;
            position: fixed;
            z-index: 1000;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80%;
            height: 70%;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.
            1);
            padding: 20px;
            overflow: auto;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            border: 1px solid #333;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }

        nav ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        nav li {
            display: inline;
            margin-right: 20px;
        }

        nav li a {
            text-decoration: none;
            color: #fff;
            padding: 10px;
            transition: background-color 0.3s;
        }

        nav li a:hover {
            background-color: #555;
            border-radius: 5px;
        }

        .back-btn {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            position: absolute;
            bottom: 20px;
            left: 20px;
        }

        .back-btn:hover {
            background-color: #555;
        }

        .btn01 {
            background-color: #333;
            color: #fff;
            size: 15px;
            width: max-content;
            height: max-content;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group2 {
            margin-top: 5px;
        }

        .form-group2 label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group2 select,
        .form-group2 input[type="text"],
        .form-group2 textarea {
            width: calc(15% - 22px);
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .form-group2 textarea {
            height: 100px;
        }

        .form-group2 input[type="submit"] {
            width: auto;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .form-group2 input[type="submit"]:hover {
            background-color: #555;
        }

        .form-group {
            margin-top: 5px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group select,
        .form-group input[type="text"],
        .form-group textarea {
            width: calc(50% - 22px);
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .form-group textarea {
            height: 100px;
        }

        .form-group input[type="submit"] {
            width: auto;
            padding: 10px 20px;
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
        .date-time {
            bottom: 0;
            right: 0;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
<header>
    <h1>Complaint Management System - Admin Dashboard</h1>
</header>

<nav>
    <ul>
        <li><a href="#" onclick="openDialog()">Manage Users</a></li>
        <li class="logout"><a href="index.html">Logout</a></li>
    </ul>
</nav>

<div class="container">
    <strong>Inbox</strong>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="form-group2">
    <label for="dayrange">Filter :</label>
    <select name="dayrange" id="dayrange">
        <option value="10000">All Comments</option>
        <option value="1">1 day</option>
        <option value="7">Last 7 days</option>
        <option value="30">Last 30 days</option>
        <option value="60">Last 60 days</option>
        <option value="180">Last 180 days</option>
        <option value="365">Last 365 days</option>
    </select>
    <select name="complaintstatus01" id="complaintstatus01">
    <option value="Pending" <?php if(isset($_POST["complaintstatus01"]) && $_POST["complaintstatus01"] == "Pending") echo "selected"; ?>>Pending</option>
    <option value="In Process" <?php if(isset($_POST["complaintstatus01"]) && $_POST["complaintstatus10"] == "In Process") echo "selected"; ?>>In Process</option>
    <option value="Resolved" <?php if(isset($_POST["complaintstatus01"]) && $_POST["complaintstatus10"] == "Resolved") echo "selected"; ?>>Resolved</option>
</select>

    <input type="submit" value="Apply Filter">
</form>
 
    <div>
    <?php if (!empty($allComplaints)): ?>
        <ul>
            <?php foreach($allComplaints as $complaint): ?>
                <li>
                    <div class="form-group2">
                        <strong style="margin-top:10px;margin-left:10px;">User name: </strong><?php echo $complaint['username']; ?><br><br>
                        <strong style="margin-top:10px;margin-left:10px;">Department: </strong> <?php echo $complaint['department']; ?><br><br>
                        <strong style="margin-top:10px;margin-left:10px;">Class Year: </strong> <?php echo $complaint['class_year']; ?><br><br>
                        <strong style="margin-top:10px;margin-left:10px;">Category: </strong> <?php echo $complaint['category']; ?><br><br>
                        <strong style="margin-top:10px;margin-left:10px;">Subcategory: </strong> <?php echo $complaint['subcategory']; ?><br><br>
                        <strong style="margin-top:10px;margin-left:10px;">Subject: </strong> <?php echo $complaint['subject']; ?><br><br>
                        <strong style="margin-top:10px;margin-left:10px;">Description: </strong> <?php echo $complaint['description']; ?><br><br>
                        <strong style="margin-top:10px;margin-left:10px;">Status: </strong><br>
                    </div>
                    <div class="form-group2" style="margin-top:10px;margin-left:10px;">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            
                            <input type="hidden" name="userid" value="<?php echo $complaint['userid']; ?>">

                            <input type="hidden" name="complaint_id" value="<?php echo $complaint['id']; ?>">
                            <select name="status">
                                <option value="<?php echo $complaint['status']; ?>"><?php echo $complaint['status']; ?></option>
                                <option value="Pending">Pending</option>
                                <option value="In Process">In Process</option>
                                <option value="Resolved">Resolved</option>
                            </select><br><br>
                            <label for="note">Note:</label><br>
                            <div class="form-group">

                                <textarea name="note" id="note"></textarea><br><br>
                            </div>
                            <input type="submit" class="btn01" value="Update Status">
                        </form>
                    </div>
                    <div class="date-time"  style="margin-top:10px;margin-left:15px;">
                        <?php echo $complaint['date']; ?><br><?php echo $complaint['time']; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No complaints found.</p>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2024 Complaint Management System</p>
</footer>

<div id="dialogOverlay" class="dialog-overlay">
    <div id="dialogBox" class="dialog-box">
        <div id="dialogContent">
            <h2>Manage Users</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Action</th>
                </tr>
                <?php
                // Fetch users from the database directly within the dialog box
                $sql_users = "SELECT id, name, email, mobile FROM users ORDER BY name DESC";
                $result_users = $conn->query($sql_users);
                if ($result_users->num_rows > 0) {
                    while ($row_users = $result_users->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row_users['id'] . "</td>";
                        echo "<td>" . $row_users['name'] . "</td>";
                        echo "<td>" . $row_users['email'] . "</td>";
                        echo "<td>" . $row_users['mobile'] . "</td>";
                        echo "<td><button class='delete-btn' onclick='deleteUser(event, " . $row_users['id'] . ")'>Delete</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No users found.</td></tr>";
                }
                ?>
            </table>
            <button class="back-btn" onclick="closeDialog()">Close</button>
        </div>
    </div>
</div>

<script>
    
    function openDialog() {
        var overlay = document.getElementById('dialogOverlay');
        var dialog = document.getElementById('dialogBox');
        overlay.style.display = 'block';
        dialog.style.display = 'block';
    }

    function closeDialog() {
        var overlay = document.getElementById('dialogOverlay');
        var dialog = document.getElementById('dialogBox');
        overlay.style.display = 'none';
        dialog.style.display = 'none';
    }

    function deleteUser(event, userId) {
        event.preventDefault();
        if (confirm("Are you sure you want to delete this user?")) {
            window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?delete=" + userId;
        }
    }
</script>
</body>
</html>

