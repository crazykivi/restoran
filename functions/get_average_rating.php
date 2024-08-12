<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT AVG(rating) as averageRating FROM feedbacks WHERE status = 'Одобрено'";
$result = $conn->query($sql);

if ($row = $result->fetch_assoc()) {
    $averageRating = round($row['averageRating'], 1);
    echo json_encode(['averageRating' => $averageRating]);
} else {
    echo json_encode(['averageRating' => 'Нет данных']);
}

$conn->close();
