<?php
//переменные для подключения к бд
$dsn = 'mysql:host=localhost;dbname=paypal;charset=utf8';
$dbuser = 'root';
$password = '';
$sql = new PDO($dsn, $dbuser, $password); //конект к бд
//проверка подключения
if ($sql == true) {
    echo "Успешное подключение<br>";
} else {
    echo "Ошибка: ";
}
try {
    $allOrders = $sql->query("SELECT * FROM orders");
    echo '<div class="container mt-5">';
    echo '<h2>Список всех заказов</h2>';
    echo '<table class="table table-striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>ID</th>';
    echo '<th>Имя</th>';
    echo '<th>Email</th>';
    echo '<th>Количество</th>';
    echo '<th>Сумма</th>';
    echo '<th>Валюта</th>';
    echo '<th>Статус</th>';
    echo '<th>Токен</th>';
    echo '<th>Дата создания</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($allOrders as $order) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($order['id']) . '</td>';
        echo '<td>' . htmlspecialchars($order['name']) . '</td>';
        echo '<td>' . htmlspecialchars($order['email']) . '</td>';
        echo '<td>' . htmlspecialchars($order['qty']) . '</td>';
        echo '<td>' . htmlspecialchars($order['sum']) . '</td>';
        echo '<td>' . htmlspecialchars($order['currency']) . '</td>';
        echo '<td>' . htmlspecialchars($order['status']) . '</td>';
        echo '<td>' . htmlspecialchars($order['token']) . '</td>';
        echo '<td>' . htmlspecialchars($order['created_at']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>';
} catch (PDOException $e) {
    echo "Ошибка выполнения 3апроса" . $e->getMessage();
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">