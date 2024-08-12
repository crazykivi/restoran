<?php
session_start();
if (!isset($_SESSION['logged_in'])) {
    header('Location: staff_login.html');
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "restoran";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_reservation'])) {
    $id = $_POST['id'];
    $result = $_POST['result'];
    $updateSql = "UPDATE reservations SET result=? WHERE id=?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("si", $result, $id);
    $stmt->execute();
    $stmt->close();
}

$sql = "SELECT id, name, phone, date, guests, result FROM reservations WHERE `position` = 'Активна' AND `date` >= CURDATE() ORDER BY date ASC";
$result = $conn->query($sql);

$sqlOrders = "
    SELECT o.id, o.name, o.phone, o.created_at, o.estimated_time, o.ready_by, oi.product_id, p.name as product_name, oi.quantity, oi.price, o.total_price, o.status 
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id 
    JOIN products p ON oi.product_id = p.id 
    WHERE o.status NOT IN ('Неактивный')
    ORDER BY o.created_at ASC";
$resultOrders = $conn->query($sqlOrders);

$orders = [];
$completedOrders = [];
while ($row = $resultOrders->fetch_assoc()) {
    if ($row['status'] === 'Заказ отдан') {
        $completedOrders[$row['id']]['name'] = $row['name'];
        $completedOrders[$row['id']]['phone'] = $row['phone'];
        $completedOrders[$row['id']]['created_at'] = $row['created_at'];
        $completedOrders[$row['id']]['estimated_time'] = $row['estimated_time'];
        $completedOrders[$row['id']]['ready_by'] = $row['ready_by'];
        $completedOrders[$row['id']]['total_price'] = $row['total_price'];
        $completedOrders[$row['id']]['status'] = $row['status'];
        $completedOrders[$row['id']]['items'][] = $row;
    } else {
        $orders[$row['id']]['name'] = $row['name'];
        $orders[$row['id']]['phone'] = $row['phone'];
        $orders[$row['id']]['created_at'] = $row['created_at'];
        $orders[$row['id']]['estimated_time'] = $row['estimated_time'];
        $orders[$row['id']]['ready_by'] = $row['ready_by'];
        $orders[$row['id']]['total_price'] = $row['total_price'];
        $orders[$row['id']]['status'] = $row['status'];
        $orders[$row['id']]['items'][] = $row;
    }
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Записи</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .rowspan-cell {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <a href="functions/logout.php">Выйти</a>
    <h1>Записи</h1>
    <table>
        <tr>
            <th>Имя</th>
            <th>Телефон</th>
            <th>Дата и время</th>
            <th>Кол-во гостей</th>
            <th>Результат</th>
            <th>Кнопка обновление</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <form action="" method="post">
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo $row['guests']; ?></td>
                    <td>
                        <select name="result">
                            <option <?php echo $row['result'] === 'Бронь подтверждена' ? 'selected' : ''; ?>>Бронь подтверждена</option>
                            <option <?php echo $row['result'] === 'Отмена' ? 'selected' : ''; ?>>Отмена</option>
                            <option <?php echo $row['result'] === 'Не ответил' ? 'selected' : ''; ?>>Не ответил</option>
                        </select>
                    </td>
                    <td>
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="update_reservation">Обновить</button>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
    </table>

    <h1>Онлайн заказы</h1>
    <button id="toggleCompletedOrders">Показать выполненные заказы</button>
    <table>
        <tr>
            <th>Имя</th>
            <th>Телефон</th>
            <th>Товар</th>
            <th>Количество</th>
            <th>Цена за единицу</th>
            <th>Итого</th>
            <th>Дата создания</th>
            <th>Статус</th>
            <th>Примерное время готовки</th>
            <th>Удалить заказ</th>
        </tr>
        <?php foreach ($orders as $orderId => $order) : ?>
            <?php $rowspan = count($order['items']); ?>
            <?php foreach ($order['items'] as $index => $item) : ?>
                <tr>
                    <?php if ($index == 0) : ?>
                        <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['name']); ?></td>
                        <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['phone']); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo $item['price']; ?></td>
                    <?php if ($index == 0) : ?>
                        <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>"><?php echo $order['total_price']; ?></td>
                        <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['created_at']); ?></td>
                        <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>">
                            <form onsubmit="updateOrderStatus(event, <?php echo $orderId; ?>)">
                                <select name="status">
                                    <?php if ($order['status'] === 'Подтвердил заказ') : ?>
                                        <option value="Подтвердил заказ" <?php echo $order['status'] === 'Подтвердил заказ' ? 'selected' : ''; ?>>Подтвердил заказ</option>
                                        <option value="Заказ отдан" <?php echo $order['status'] === 'Заказ отдан' ? 'selected' : ''; ?>>Заказ отдан</option>
                                    <?php else : ?>
                                        <option value="Не звонили" <?php echo $order['status'] === 'Не звонили' ? 'selected' : ''; ?>>Не звонили</option>
                                        <option value="Не ответил" <?php echo $order['status'] === 'Не ответил' ? 'selected' : ''; ?>>Не ответил</option>
                                        <option value="Подтвердил заказ" <?php echo $order['status'] === 'Подтвердил заказ' ? 'selected' : ''; ?>>Подтвердил заказ</option>
                                    <?php endif; ?>
                                </select>
                                <input type="hidden" name="orderId" value="<?php echo $orderId; ?>">
                                <button type="submit">Обновить</button>
                            </form>
                        </td>
                        <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>">
                            <?php if (empty($order['ready_by'])) : ?>
                                <form onsubmit="updateEstimatedTime(event, <?php echo $orderId; ?>)">
                                    <select name="estimated_time">
                                        <option value="" disabled selected>Выберите время</option>
                                        <option value="00:10">10 минут</option>
                                        <option value="00:20">20 минут</option>
                                        <option value="00:30">30 минут</option>
                                        <option value="00:40">40 минут</option>
                                        <option value="00:50">50 минут</option>
                                        <option value="01:00">1 час</option>
                                        <option value="01:10">1 час 10 минут</option>
                                        <option value="01:20">1 час 20 минут</option>
                                        <option value="01:30">1 час 30 минут</option>
                                        <option value="01:40">1 час 40 минут</option>
                                        <option value="01:50">1 час 50 минут</option>
                                        <option value="02:00">2 часа</option>
                                        <option value="02:10">2 часа 10 минут</option>
                                        <option value="02:20">2 часа 20 минут</option>
                                        <option value="02:30">2 часа 30 минут</option>
                                        <option value="02:40">2 часа 40 минут</option>
                                        <option value="02:50">2 часа 50 минут</option>
                                        <option value="03:00">3 часа</option>
                                    </select>
                                    <button type="submit">Сохранить</button>
                                </form>
                            <?php else : ?>
                                <div class="timer" data-ready-by="<?php echo $order['ready_by']; ?>">
                                    Оставшееся время: <span class="time-remaining"></span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>">
                            <?php if ($order['status'] !== 'Подтвердил заказ') : ?>
                                <button onclick="markOrderInactive(<?php echo $orderId; ?>)">Удалить заказ</button>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>

    <div id="completedOrders" style="display: none;">
        <h2>Выполненные заказы</h2>
        <table>
            <tr>
                <th>Имя</th>
                <th>Телефон</th>
                <th>Товар</th>
                <th>Количество</th>
                <th>Цена за единицу</th>
                <th>Итого</th>
                <th>Дата создания</th>
                <th>Статус</th>
            </tr>
            <?php foreach ($completedOrders as $orderId => $order) : ?>
                <?php $rowspan = count($order['items']); ?>
                <?php foreach ($order['items'] as $index => $item) : ?>
                    <tr>
                        <?php if ($index == 0) : ?>
                            <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['name']); ?></td>
                            <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['phone']); ?></td>
                        <?php endif; ?>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo $item['price']; ?></td>
                        <?php if ($index == 0) : ?>
                            <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>"><?php echo $order['total_price']; ?></td>
                            <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['created_at']); ?></td>
                            <td class="rowspan-cell" rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['status']); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Функция для обновления таймера
            function updateTimers() {
                document.querySelectorAll('.timer').forEach(timer => {
                    const readyBy = new Date(timer.dataset.readyBy).getTime();
                    const now = new Date().getTime();
                    const distance = readyBy - now;

                    if (distance < 0) {
                        timer.querySelector('.time-remaining').innerText = 'Время истекло';
                        return;
                    }

                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    timer.querySelector('.time-remaining').innerText = `${hours}ч ${minutes}м ${seconds}с`;
                });
            }

            setInterval(updateTimers, 1000);
            updateTimers();

            window.updateEstimatedTime = function(event, orderId) {
                event.preventDefault();
                const form = event.target;
                const estimatedTime = form.querySelector('select[name="estimated_time"]').value;

                fetch('functions/update_estimated_time.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `orderId=${orderId}&estimated_time=${estimatedTime}`
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.message) {
                            alert(result.message);
                            location.reload(); // Перезагружаем страницу для обновления данных
                        } else {
                            alert('Ошибка обновления времени примерной готовки');
                        }
                    });
            };

            document.getElementById('toggleCompletedOrders').addEventListener('click', function() {
                const completedOrdersSection = document.getElementById('completedOrders');
                if (completedOrdersSection.style.display === 'none') {
                    completedOrdersSection.style.display = 'block';
                    this.textContent = 'Скрыть выполненные заказы';
                } else {
                    completedOrdersSection.style.display = 'none';
                    this.textContent = 'Показать выполненные заказы';
                }
            });

            window.markOrderInactive = function(orderId) {
                fetch('functions/mark_order_inactive.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `orderId=${orderId}`
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.message) {
                            alert(result.message);
                            location.reload(); // Перезагружаем страницу для обновления данных
                        } else {
                            alert('Ошибка при удалении заказа');
                        }
                    });
            };
        });
    </script>
    <div id="feedbackContainer">
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchFeedbacks();

            function fetchFeedbacks() {
                fetch('functions/get_feedbacks_panel.php')
                    .then(response => response.json())
                    .then(data => {
                        const container = document.getElementById('feedbackContainer');
                        container.innerHTML = '';
                        data.forEach(feedback => {
                            const div = document.createElement('div');
                            div.className = 'feedback';
                            div.innerHTML = `
                                <p>Автор: ${feedback.name}</p>
                                <p>Рейтинг: ${feedback.rating}</p>
                                <p>Комментарий: ${feedback.comment}</p>
                                <p>Статус: ${feedback.status}</p>
                                <button onclick="updateStatus(${feedback.id}, 'Одобрено')">Одобрить</button>
                                <button onclick="updateStatus(${feedback.id}, 'Удалить')">Удалить</button>
                            `;
                            container.appendChild(div);
                        });
                    });
            }

            window.updateStatus = function(id, status) {
                fetch('functions/update_feedback_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `id=${id}&status=${status}`
                    })
                    .then(response => response.json())
                    .then(result => {
                        alert(result.message);
                        fetchFeedbacks();
                    });
            };

            window.updateOrderStatus = function(event, orderId) {
                event.preventDefault();
                const form = event.target;
                const status = form.querySelector('select[name="status"]').value;

                fetch('functions/update_order_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `orderId=${orderId}&status=${status}`
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            alert('Статус заказа обновлен');
                        } else {
                            alert('Ошибка обновления статуса заказа');
                        }
                    });
            };
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>