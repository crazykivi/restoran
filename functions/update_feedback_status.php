<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$status = $_POST['status'];

if ($status === 'Удалить') {
    $sql = "DELETE FROM feedbacks WHERE id = ?";
} else {
    $sql = "UPDATE feedbacks SET status = ? WHERE id = ?";
}

$stmt = $conn->prepare($sql);
if ($status === 'Удалить') {
    $stmt->bind_param("i", $id);
} else {
    $stmt->bind_param("si", $status, $id);
}

if ($stmt->execute()) {
    echo json_encode(['message' => 'Статус обновлен успешно']);
} else {
    echo json_encode(['message' => 'Ошибка при обновлении статуса']);
}

$stmt->close();
$conn->close();
