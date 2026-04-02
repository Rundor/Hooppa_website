<?php
$servername = "localhost"; // your server (localhost if you're using a local environment)
$username = "root"; // your MySQL username (usually 'root' in local dev environments)
$password = ""; // your MySQL password (empty if you haven't set one)
$dbname = "kids_toys_store"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>
