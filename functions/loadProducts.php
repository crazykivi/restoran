<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT p.id, p.category, p.name, p.price, p.image_url, GROUP_CONCAT(i.ingredient_name SEPARATOR ', ') AS ingredients 
        FROM products p 
        LEFT JOIN ingredients i ON p.id = i.product_id 
        GROUP BY p.id, p.category, p.name, p.price, p.image_url 
        ORDER BY p.id, p.category DESC";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
$conn->close();
?>
