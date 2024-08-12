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
$estimatedTime = $_POST['estimated_time'];

$now = new DateTime('now', new DateTimeZone('Asia/Krasnoyarsk'));

list($hours, $minutes) = explode(':', $estimatedTime);

$now->add(new DateInterval("PT{$hours}H{$minutes}M"));

// Получаем конечное время в формате Y-m-d H:i:s
$readyBy = $now->format('Y-m-d H:i:s');

$sql = "UPDATE orders SET estimated_time = ?, ready_by = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $estimatedTime, $readyBy, $orderId);

$response = [];
if ($stmt->execute()) {
    $response['message'] = 'Время примерной готовки успешно обновлено';
} else {
    $response['message'] = 'Ошибка при обновлении времени примерной готовки';
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
