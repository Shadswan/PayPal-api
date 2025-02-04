<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>form</title>
    <link rel="stylesheet" type="text/css" href="index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>
    <div class="container justify-content-center col-md-6 bg-light p-4 rounded mb-3">
        <form method="POST" action="">
            <div class="mb-3">
                <label for="name" class="form-label">Имя</label>
                <input name="nameForm" type="text" class="form-control" id="name" aria-describedby="emailHelp">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">email</label>
                <input name="emailForm" type="email" class="form-control" id="email">
            </div>
            <select class="form-select" name="tovar" id="tovar">
                <option selected>Выберите товар</option>
                <option value="Скрепки|20">Скрепки - 20 рублей</option>
                <option value="Шариковая ручка|55,5">Шариковая ручка - 55,5 рублей</option>
                <option value="Молоток|100">Молоток - 100 рублей</option>
            </select>
            <div class="mb-3">
                <label for="number" class="form-label">Количество товара</label>
                <input name="numberForm" type="number" class="form-control" id="number">
            </div>
            <button type="submit" class="btn btn-primary">Оплатить</button>
        </form>
    </div>
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
    //для почты
    require 'vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $mail = new PHPMailer(true);
    //для PayPal 
    $sandbox_id_paypal = '';
    $sandbox_secret_paypal = '';
    $url = 'https://api.sandbox.paypal.com/v1/oauth2/token';
    $curl = curl_init();
    $urlApi = 'https://api.sandbox.paypal.com/v2/checkout/orders';
    //Настройка cURL
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERPWD, $sandbox_id_paypal . ":" . $sandbox_secret_paypal);
    curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    //выполняем 3апрос
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        echo 'Ошибка cURL: ' . curl_error($curl);
    } else {
        $data = json_decode($response, true);
        // Получаем токен
        $accessToken = $data['access_token'];
        echo "Токен: " . $accessToken; //токен действует 9 часов(вроде) и для id песочницы он для всех один, потом мб сделаю для всех ра3ные токены
    }
    //переменные с формы и проверка 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = $_POST['nameForm']; // имя клиента
        $emailSend = $_POST['emailForm']; // почта
        $qty = $_POST['numberForm']; //кол-во товара
        $tovarData = $_POST['tovar'];
        //ра3деление имени и цены
        list($tovarName, $tovarPrice) = explode('|', $tovarData);
        $tovarPriceFinnal = floatval($tovarPrice) * $qty; //умножаем цену товара на колл-во
        echo "Имя: $name<br> Почта: $emailSend<br> Количество: $qty<br> Имя товара: $tovarName<br> Итоговая цена: $tovarPriceFinnal<br>";
        try {
            
            //Настройки SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.yandex.ru';
            $mail->SMTPAuth = true;
            $mail->Username = '';
            $mail->Password = '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            //оющие данные для всех писем
            $mail->setFrom('', '');
            $mail->isHTML(true);
            //содержание письма
            $emails = [
                [   //письмо 3ака3чику
                    'address' => $emailSend,
                    'name'    => '',
                    'subject' => "Имя товара: $tovarName<br>Итоговая цена: $tovarPrice<br>Количество: $qty<br>",
                    'body'    => "Имя товара: $tovarName<br>Итоговая цена: $tovarPrice<br>Количество: $qty<br>" //в душе не чаю 3ачем это поле
                ],
                [   //письмо админу
                    'address' => $emailSend,
                    'name'    => '',
                    'subject' => "Имя товара: $tovarName<br>Итоговая цена: $tovarPrice<br>Количество: $qty<br>",
                    'body'    => "Имя товара: $tovarName<br>Итоговая цена: $tovarPrice<br>Количество: $qty<br>" //в душе не чаю 3ачем это поле
                ]
            ];
            foreach ($emails as $email) {
                //очистка предыдущих получателей и вложения
                $mail->clearAddresses();
                $mail->clearAttachments();
                //устанавливаем получателя, тему и тело письма
                $mail->addAddress($email['address'], $email['name']);
                $mail->Subject = $email['subject'];
                $mail->Body = $email['subject'];
                //отправка письма
                $mail->send();
                echo "Письмо отправлено на {$email['address']}<br>";
                //3адержка 
                sleep(1);
            }
            //со3дание 3ака3а
            $dataPay = [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [ //Итого
                            'currency_code' => 'USD',
                            'value' => number_format((float)$tovarPriceFinnal, 2, '.', ''), 
                            'breakdown' => [
                                'item_total' => [
                                    'currency_code' => 'USD',
                                    'value' => number_format((float)$tovarPriceFinnal, 2, '.', '')
                                ]
                            ]
                        ],
                        'items' => [ //по сути кор3ина
                            [
                                'name' => $tovarName, // название товара
                                'unit_amount' => [
                                    'currency_code' => 'USD',
                                    'value' => number_format((float)$tovarPrice, 2, '.', '')
                                ],
                                'quantity' => $qty  // количество товара
                            ]
                        ]
                    ]
                ],
                'application_context' => [
                    'return_url' => 'http://paypal/success.php',
                    'cancel_url' => 'http://paypal/cancel.php'
                ]
            ];
            $jsonData = json_encode($dataPay);
            curl_setopt($curl, CURLOPT_URL, $urlApi);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken
            ]);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
            $responseApi = curl_exec($curl);
            if (curl_errno($curl)) {
                echo 'Ошибка cURL: ' . curl_error($curl);
            } else {
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if ($httpCode === 400) {
                    echo "Ошибка 400: Bad Request. Проверьте данные запроса.";
                }
                $orderData = json_decode($responseApi, true);
                print_r($orderData);
            }
            //перенаправление на страницу оплаты 
            $approverUrl = $orderData['links'][1]['href'];
            $tokenId = $orderData['id'];
            
            //Добавление 3ака3а в бд
            try{
                $sql->beginTransaction();//исполь3уем тран3акцию чтобы все данные были внесены
                $insertOrder = $sql->query("INSERT INTO orders (name, email, qty, sum, currency, status, token, created_at) VALUES ('$tovarName', '$emailSend', $qty, $tovarPriceFinnal, 'USD', 'ожидает оплату', '$tokenId', '" . gmdate('Y-m-d') . "')");//делаем бе3 подготовленых выражений, мне лень 
                //получаем id 
                $lastId = $sql->lastInsertId();
                //добавление в товары
                $insertOrderItems = $sql->query("INSERT INTO order_items (id_order, name_product, price, qty) VALUES ('$lastId', '$tovarName', '$tovarPrice', '$qty')");
                //фиксируем прибыль
                $sql->commit();
            }catch (Exception $e) {
                // В случае ошибки откатить транзакцию
                $sql->rollBack();
                echo "Ошибка: " . $e->getMessage();
            }
            //header("Location: $approverUrl");
            
        } catch (PDOException $e) {
            echo "Ошибка выполнения 3апроса" . $e->getMessage();
        }
    } else {
        echo 'Ошибка отправки формы';
    }
    curl_close($curl); // Закрываем соединение
    ?>
</body>

</html>