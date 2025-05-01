<?php
header('Content-Type: application/json');
include 'db.php';

$sql = "SELECT o.id, c.name as customer_name, c.phone, o.order_time, o.total, o.order_type, o.table_number
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        ORDER BY o.order_time DESC";
$result = $conn->query($sql);

$orders = array();
while($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
echo json_encode($orders);
$conn->close();
?>
