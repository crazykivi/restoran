<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$comment = $_POST['comment'];
$rating = $_POST['rating'];

$sql = "INSERT INTO feedbacks (name, comment, rating) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $comment, $rating);
$stmt->execute();

echo "Отзыв добавлен!";
$conn->close();
