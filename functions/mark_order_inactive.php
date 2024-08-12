<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$orderId = $_POST['orderId'];

$sql = "UPDATE orders SET status = 'Неактивный' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderId);

$response = [];
if ($stmt->execute()) {
    $response['message'] = 'Заказ успешно удален';
} else {
    $response['message'] = 'Ошибка при удалении заказа';
}

$stmt->close();
$conn->close();

echo json_encode($response);
