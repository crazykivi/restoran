<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, comment, rating, time_feedback FROM feedbacks WHERE status='Одобрено' ORDER BY id DESC LIMIT 3";
$result = $conn->query($sql);

$feedbacks = [];

while ($row = $result->fetch_assoc()) {
    $date = new DateTime($row['time_feedback']);
    $formatted_date = $date->format('F Y');
    $feedbacks[] = [
        'name' => $row['name'],
        'comment' => $row['comment'],
        'rating' => $row['rating'],
        'formatted_date' => $formatted_date 
    ];
}

echo json_encode($feedbacks);
$conn->close();
