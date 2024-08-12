<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$ip_address = $_SERVER['REMOTE_ADDR'];

$sql = "
    SELECT o.id, o.name, o.phone, o.total_price, o.status, o.order_type, o.ready_by, da.address as delivery_address 
    FROM orders o 
    LEFT JOIN delivery_addresses da ON o.id = da.order_id 
    WHERE o.ip_address = ? AND o.status NOT IN ('Заказ отдан', 'Неактивный')
    ORDER BY o.created_at ASC
    LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $ip_address);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
    echo json_encode($order);
} else {
    echo json_encode(null);
}

$stmt->close();
$conn->close();
?>
