<?php
header('Content-Type: application/json');
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$customer_name = $data['customer_name'];
$phone = $data['phone'];
$items = $data['items'];
$order_type = $data['order_type'];
$table_number = $data['table_number'];
$address = $data['address'];

$stmt = $conn->prepare("INSERT INTO customers (name, phone) VALUES (?, ?)");
$stmt->bind_param("ss", $customer_name, $phone);
$stmt->execute();
$customer_id = $stmt->insert_id;
$stmt->close();


$total = 0;
foreach($items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$order_time = date('Y-m-d H:i:s');
$stmt = $conn->prepare("INSERT INTO orders (customer_id, order_time, total, order_type, table_number) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("isdsi", $customer_id, $order_time, $total, $order_type, $table_number);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();


foreach($items as $item) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
    $stmt->execute();
    $stmt->close();
}


if ($order_type === 'Home Delivery') {
    $stmt = $conn->prepare("INSERT INTO delivery_details (order_id, address) VALUES (?, ?)");
    $stmt->bind_param("is", $order_id, $address);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['message' => 'Order placed successfully!', 'order_id' => $order_id]);
$conn->close();
?>
