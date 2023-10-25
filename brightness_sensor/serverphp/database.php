<?php
    $server = "127.0.0.1";
    $username = "root";
    $password = "";
    $database = "mqtt_esp32";

    $conn = new mysqli($server, $username, $password, $database);

    if ($conn->connect_error) {
        die("Lỗi kết nối đến cơ sở dữ liệu: " . $conn->connect_error);
    }
?>
