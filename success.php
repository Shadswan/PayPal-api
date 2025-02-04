<?php
//проверка токена в ссылке
if (isset($_GET['token'])) {
    $orderId = $_GET['token']; //id 3ака3а
    //3ахват платежа
    $captureUrl = "https://api.sandbox.paypal.com/v2/checkout/orders/$orderId/capture";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $captureUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $captureData = json_decode($response, true);
    if ($captureData['status'] === 'COMPLETED') {
        echo "Платеж успешно 3авершен!";
        $cancelUpdate = $sql->query("UPDATE orders SET status = 'оплачено' WHERE token = '$orderId'");
    } else {
        echo "Ошибка при 3ахвате платежа.";
    }
} else {
    echo "Ошибка: параметр token не найден.";
}
