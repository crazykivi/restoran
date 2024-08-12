<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

$pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$data = json_decode(file_get_contents('php://input'), true);

$ipAddress = $_SERVER['REMOTE_ADDR'];
$productId = $data['productId'];
$productName = $data['productName'];
$price = $data['price'];

$stmt = $pdo->prepare("INSERT INTO cart (ip_address, product_id, product_name, price) VALUES (?, ?, ?, ?)");
$stmt->execute([$ipAddress, $productId, $productName, $price]);

echo json_encode(['message' => 'Продукт добавлен в корзину']);
?>