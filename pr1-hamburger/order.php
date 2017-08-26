<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once '../vendor/phpmailer/phpmailer/PHPMailerAutoload.php';

if (isset($_POST['submit'])) {

    $secret = "6LeIHC4UAAAAAIzFCUZb4EUpwO11lxRl_Opb26tY";
    $responseKey = $_POST['g-recaptcha-response'];
    $remoteip = $_SERVER['REMOTE_ADDR'];
    $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$responseKey&remoteip=$remoteip";
    $captcha = file_get_contents($url);
    $captcha = json_decode($captcha);

    if ($captcha->success) proceedOrder();
    else echo "Ошибка при отправке формы!";

}



function proceedOrder()
{
    try {
        $pdo = new PDO("mysql:host=localhost:8889;dbname=hamburgers-vp1", "root", "root");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        $street = filter_input(INPUT_POST, 'street', FILTER_SANITIZE_STRING);
        $home = filter_input(INPUT_POST, 'home', FILTER_SANITIZE_STRING);
        $part = filter_input(INPUT_POST, 'part', FILTER_SANITIZE_STRING);
        $appt = filter_input(INPUT_POST, 'appt', FILTER_SANITIZE_STRING);
        $floor = filter_input(INPUT_POST, 'floor', FILTER_SANITIZE_STRING);

        $address = $street . " " . $home . " Корпус: {$part} " . "Квартира: {$appt} " . "Этаж: {$floor}";

        $comment = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_STRING);
        $payment = filter_input(INPUT_POST, 'payment', FILTER_SANITIZE_STRING);
        $payment = $payment === "nalik" ? "Наличные" : "Картой";
        $callback = filter_input(INPUT_POST, 'callback', FILTER_SANITIZE_STRING);
        $callback = $callback != "notcall" ? "Перезвонить" : "Не звонить";

        // Получаем из базы заказы по полю email
        $query = "select * from users where email = '{$email}'";
        $select_from_users = $pdo->prepare($query);
        $select_from_users->execute();
        $result = $select_from_users->fetchAll();
        $first_order = false;

        // Проверяем есть ли такой email уже заказывал, то прибавляем 1 к его заказам и меняем дату
        // если нет, тогда создаем его запись
        if ($result) {
            $user =$result[0];
            $user_id = $user['user_id'];
            $order = $user['orders'] + 1;
            $query = "UPDATE users SET orders = '$order' WHERE user_id = '$user_id'";
            $add_order = $pdo->prepare($query);
            $add_order->execute();
            $query = "UPDATE users SET last_order = NOW() WHERE user_id = '$user_id'";
            $add_order = $pdo->prepare($query);
            $add_order->execute();
        } else {
            $orders = 1;
            $query = "insert into users (name, email, orders, last_order) values ('$name', '$email', '$orders', NOW())";
            $insert_user = $pdo->prepare($query);
            $insert_user->execute();
            global $first_order;
            $first_order = true;
        }

        $insert_order =
            $pdo->prepare("insert into orders (name, phone, address, callback, payment, comment, date) values ('$name', '$phone', '$address', '$callback', '$payment', '$comment', NOW())");

        if ($insert_order->execute()) {
            $order_id = $pdo->prepare("SELECT MAX(id) 
                                                 FROM orders WHERE name = '$name' 
                                                 AND phone='$phone' AND address='$address'");
            $order_id->execute();
            $order_id = $order_id->fetchColumn();
            $order_number = $pdo->prepare("SELECT orders FROM users WHERE email = '$email'");
            $order_number->execute();
            $order_number = $order_number->fetchColumn();

            // Отпроавляем письмо
            $config = require 'mailer-config.php';
            sendMail($config, $email, $name, $order_id, $address, $first_order, $order_number);
            echo "Ваш заказ успешно размещен";
        }
    } catch (Exception $e) {
        echo 'Message: ' .$e->getMessage();
    }
}

function sendMail($config, $to, $name, $order_id, $address, $first_order, $order_number)
{
    $mail = new PHPMailer;
    //$mail->SMTPDebug = 3;
    $mail->isSMTP();
    $mail->Host = $config->host;
    $mail->SMTPAuth = true;
    $mail->Username = $config->username;
    $mail->Password = $config->password;
    $mail->SMTPSecure = 'SSL';
    $mail->PORT = $config->port;
    $mail->setFrom($config->username, 'Гамбургеры');
    $mail->addReplyTo($config->username, 'Гамбургеры');
    $mail->addAddress($to);
    $mail->Subject = "Ваш заказ успешно размещен.";
    $mail->Body = "Уважаемый";
    $mail->Subject = 'Ваш заказ успешно размещен.';
    $mail->Body = "Уважаемый " . $name . ", Ваш заказ " . $order_id . " успешно размещен.\n"
                . "Заказ будет доставлен по адресу: {$address}.\n";

    if ($first_order) {
        $mail->Body .= "Спасибо за ваш первый заказ!";
    } else {
        $mail->Body .= "Спасибо, это ваш {$order_number} заказ!\n";
    }

    if (!$mail->send()) {
        echo 'Message could not be sent.';
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message has been sent';
    }
}
