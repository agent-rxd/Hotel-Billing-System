<?php
header('Content-Type: application/json');
include 'db.php';

$order_id = intval($_GET['order_id']);

$sql = "SELECT mi.name, oi.quantity, oi.price
        FROM order_items oi
        JOIN menu_items mi ON oi.menu_item_id = mi.id
        WHERE oi.order_id = $order_id";
$result = $conn->query($sql);

$items = array();
while($row = $result->fetch_assoc()) {
    $items[] = $row;
}
echo json_encode($items);
$conn->close();
?>
