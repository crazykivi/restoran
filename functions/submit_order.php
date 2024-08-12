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
$name = $_POST['name'];
$phone = $_POST['phone'];
$orderType = $_POST['orderType'];
$deliveryAddress = isset($_POST['deliveryAddress']) ? $_POST['deliveryAddress'] : null;

$conn->autocommit(FALSE);

$sqlOrder = "INSERT INTO orders (ip_address, name, phone, total_price, order_type) VALUES (?, ?, ?, 0, ?)";
$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param("ssss", $ip_address, $name, $phone, $orderType);

if ($stmtOrder->execute()) {
    $order_id = $stmtOrder->insert_id;

    if ($orderType === 'Доставка' && $deliveryAddress) {
        $sqlAddress = "INSERT INTO delivery_addresses (order_id, address) VALUES (?, ?)";
        $stmtAddress = $conn->prepare($sqlAddress);
        $stmtAddress->bind_param("is", $order_id, $deliveryAddress);
        $stmtAddress->execute();
    }

    $sqlCart = "SELECT * FROM cart WHERE ip_address = ?";
    $stmtCart = $conn->prepare($sqlCart);
    $stmtCart->bind_param("s", $ip_address);
    $stmtCart->execute();
    $resultCart = $stmtCart->get_result();

    $total_price = 0;
    while ($row = $resultCart->fetch_assoc()) {
        $sqlOrderItem = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmtOrderItem = $conn->prepare($sqlOrderItem);
        $stmtOrderItem->bind_param("iiid", $order_id, $row['product_id'], $row['quantity'], $row['price']);
        if ($stmtOrderItem->execute()) {
            $total_price += $row['price'] * $row['quantity'];
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to insert order item.']);
            exit();
        }
    }

    $sqlUpdateOrder = "UPDATE orders SET total_price = ? WHERE id = ?";
    $stmtUpdateOrder = $conn->prepare($sqlUpdateOrder);
    $stmtUpdateOrder->bind_param("di", $total_price, $order_id);

    if ($stmtUpdateOrder->execute()) {
        $sqlClearCart = "DELETE FROM cart WHERE ip_address = ?";
        $stmtClearCart = $conn->prepare($sqlClearCart);
        $stmtClearCart->bind_param("s", $ip_address);
        $stmtClearCart->execute();

        $conn->commit();
        echo json_encode(['success' => true]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update order total.']);
    }
} else {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to create order.']);
}

$stmtOrder->close();
$conn->close();
?>
