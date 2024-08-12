<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

// Создаем соединение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$phone = $_POST['phone'];
$date = $_POST['date'];
$guests = $_POST['guests'];

$sql = "INSERT INTO reservations (name, phone, date, guests) VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $name, $phone, $date, $guests);
$stmt->execute();

echo "Бронь успешна";

$stmt->close();
$conn->close();
