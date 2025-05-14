<?php
header('Content-Type: application/json');
include 'db.php';

$order_id = intval($_GET['order_id']);

// Get order info + customer info
$sql = "SELECT o.id, c.name as customer_name, c.phone, o.order_type, o.table_number
        FROM orders o
        JOIN customers c ON o.customer_id = c.id
        WHERE o.id = $order_id";
$result = $conn->query($sql);
$order = $result->fetch_assoc();

if (!$order) {
    echo json_encode(['error' => 'Order not found']);
    exit;
}

// Get delivery address if home delivery
$address = null;
if ($order['order_type'] === 'Home Delivery') {
    $res = $conn->query("SELECT address FROM delivery_details WHERE order_id = $order_id");
    $addrRow = $res->fetch_assoc();
    $address = $addrRow ? $addrRow['address'] : null;
}
$order['address'] = $address;

// Get order items
$res = $conn->query("SELECT oi.menu_item_id, mi.name, oi.quantity, oi.price 
                     FROM order_items oi
                     JOIN menu_items mi ON oi.menu_item_id = mi.id
                     WHERE oi.order_id = $order_id");
$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row;
}
$order['items'] = $items;

echo json_encode($order);
$conn->close();
?>
