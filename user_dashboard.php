<?php
session_start();

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: login.php");
    exit;
}

$servername = "localhost";
$username = "college"; 
$password = "vinu123"; 
$dbname = "complaint";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST["logout"])) {
    session_destroy();
    header("Location: index.html");
    exit;
}
date_default_timezone_set('Asia/Kolkata');
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_complaint'])) {
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    $status = "Pending";
    $note = "No Message From Admin";
    $date = date("Y-m-d");
    $time = date("h:i:s"); 
    $user_id = $_SESSION["id"];
    $complaint_table_name = "complaints_" . $user_id;
    $sql_insert = "INSERT INTO $complaint_table_name (category, subcategory, subject, description, status, note, date, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("ssssssss", $category, $subcategory, $subject, $description, $status, $note, $date, $time);
    
    if ($stmt->execute()) {
        echo "Complaint sent successfully!";
    } else {
        echo "Failed to send complaint. Please try again later.";
    }
    
    $stmt->close();
    
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

$user_id = $_SESSION["id"];
$sql = "SELECT * FROM complaints_$user_id ORDER BY date DESC, time DESC";
$result = $conn->query($sql);

$complaints = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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

        .menu {
            background-color: #333;
            color: #fff;
            text-align: right;
            padding-right: 90px;
            padding: 10px 0;
        }

        .menu a {
            color: #fff;
            text-decoration: none;
            margin: 0 20px;

        }

        .container {
            max-width: 75%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .content {
            padding: 20px;
        }

        .complaints-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .complaints-list li {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .complaints-list li h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .complaints-list li p {
            margin-top: 5px;
            color: #666;
        }

        .complaint-form {
            display: block;
        }

        .complaint-form.inactive {
            display: none;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold; 
        }

        .form-group select,
        .form-group input[type="text"],
        .form-group textarea {
            width: calc(100% - 22px);
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box; =
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
    </style>
</head>
<body>
    <header>
        <h1>Complaint Management System</h1>
    </header>

    <div class="menu">
        <a href="#" onclick="document.getElementById('logout-form').submit();">Logout</a>
        <form id="logout-form" method="post" action="">
            <input type="hidden" name="logout" value="1">
        </form>
    </div>

    <div class="container">
        <div class="content">
            <form id="complaint-form" class="complaint-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <h2>Send Complaint</h2>
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category" required>
                        <option value="">Select Category</option>
                        <option value="Teaching Methods">Teaching Methods </option>
                        <option value="Communication">Communication</option>
                        <option value="Assessment and Grading">Assessment and Grading</option>
                        <option value="Classroom Environment">Classroom Environment</option>
                        <option value="Professional Conduct">Professional Conduct </option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subcategory">Subcategory:</label>
                    <select name="subcategory" id="subcategory" required>
                        <option value="">Select Subcategory</option>
                        <option value="Lecture Style">Lecture Style</option>
                        <option value="Use of Visual Aids">Use of Visual Aids</option>
                        <option value="Interaction with Students">Interaction with Students</option>
                        <option value="Engagement with Students">Engagement with Students</option>
                        <option value="Clarity of Explanation">Clarity of Explanation</option>
                        <option value="Fairness in Evaluation">Fairness in Evaluation</option>
                        <option value="Timeliness of Feedback">Timeliness of Feedback</option>
                        <option value="Consistency in Grading">Consistency in Grading</option>
                        <option value="Alignment with Curriculum">Alignment with Curriculum</option>
                        <option value="Clarity in Communication">Clarity in Communication</option>
                        <option value="Responsiveness to Queries">Responsiveness to Queries</option>
                        <option value="Professionalism in Communication">Professionalism in Communication</option>
                        <option value="Classroom Discipline">Classroom Discipline</option>
                        <option value="Respectful Treatment of Students">Respectful Treatment of Students</option>
                        <option value="Management of Student Behavior">Management of Student Behavior</option>
                        <option value="Creating Inclusive Environment">Creating Inclusive Environment</option>
                        <option value="Attendance and Punctuality">Attendance and Punctuality</option>
                        <option value="Professional Ethics">Professional Ethics</option>
                        <option value="Handling of Student Concerns">Handling of Student Concerns</option>
                        <option value="Collaboration with Peers">Collaboration with Peers</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="subject">Subject:</label>
                    <input type="text" name="subject" id="subject" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea name="description" id="description" required></textarea>
                </div>
                <div class="form-group">
                    <input type="submit" name="send_complaint" value="Send Complaint">
                </div>
            </form>

            <div id="complaints-list" class="complaints-list inactive">
                <h2>Inbox</h2>
                <?php if (empty($complaints)) : ?>
                    <p>No complaints found.</p>
                <?php else : ?>
                    <ul>
                        <?php foreach ($complaints as $complaint) : ?>
                            <li>
                                <h3><?php echo $complaint['subject']; ?></h3>
                                <p><strong>Category: </strong><?php echo $complaint['category']; ?></p>
                                <p><strong>Subcategory: </strong><?php echo $complaint['subcategory']; ?></p>
                                <p><strong>Description: </strong><?php echo $complaint['description']; ?></p>
                                <p><strong>Status: </strong><?php echo $complaint['status']; ?></p>
                                <p><strong>Note Form Admin: </strong><?php echo $complaint['note']; ?></p>
                                <p><?php echo $complaint['date']; ?><br>
                                <?php echo $complaint['time']; ?></p>
                                

                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Complaint Management System</p>
    </footer>

    <script>
        function showForm(formId) {
            document.getElementById('complaints-list').classList.add('inactive');
            document.getElementById('complaint-form').classList.remove('inactive');
        }

        function showInbox() {
            document.getElementById('complaint-form').classList.add('inactive');
            document.getElementById('complaints-list').classList.remove('inactive');
        }
    </script>
</body>
</html>
