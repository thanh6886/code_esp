<?php
    include 'database.php';
    $sql = "SELECT datetime, brightness FROM sensor ORDER BY datetime DESC";
    $result = $conn->query($sql);

    $data = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = array(
                strtotime($row["datetime"]) * 1000,
                floatval($row["brightness"])
            );
        }    
    }

    header('Content-Type: application/json');
    echo json_encode($data);
?>