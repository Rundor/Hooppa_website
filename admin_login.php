<?php
session_start();

// Database connection
$servername = "localhost";
$db_username = "root"; // your DB username
$db_password = "";     // your DB password
$db_name = "kids_toys_store"; // your DB name

$conn = new mysqli($servername, $db_username, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// When form is submitted
if (isset($_POST['admin_login_btn'])) {
  $user = $_POST['adminName'];
  $pass = $_POST['password'];

  $sql = "SELECT * FROM admin WHERE admin_name = '$user' LIMIT 1";
  $result = $conn->query($sql);

  if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();

    if ($row['password'] == $pass) { // In production, use password_verify()
      $_SESSION['admin_user'] = $row['admin_name']; 
      $_SESSION['email'] = $row['email'];
      $_SESSION['role'] = $row['role']; 
      $_SESSION['created_at'] = $row['created_at'];

    // Save username in session
      header('Location: AdminMain.php');
      exit();
    } else {
      echo "Wrong password!";
    }
  } else {
    echo "User not found!";
  }
}
?>
