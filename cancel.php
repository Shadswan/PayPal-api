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
// Проверяем, есть ли параметр token в URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    echo "Платеж отменен. Заказ ID: " . $_GET['token'];
    $cancelUpdate = $sql->query("UPDATE orders SET status = 'отменён' WHERE token = '$token'");
} else {
    echo "Ошибка: параметр token не найден.";
}