<?php
header('Content-Type: application/json');
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$order_id = intval($data['order_id']);
$customer_name = $data['customer_name'];
$phone = $data['phone'];
$items = $data['items'];
$order_type = $data['order_type'];
$table_number = $data['table_number'];
$address = $data['address'];

// Get customer_id from order
$res = $conn->query("SELECT customer_id FROM orders WHERE id = $order_id");
if ($res->num_rows === 0) {
    echo json_encode(['message' => 'Order not found']);
    exit;
}
$row = $res->fetch_assoc();
$customer_id = $row['customer_id'];

// Update customer info
$stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ? WHERE id = ?");
$stmt->bind_param("ssi", $customer_name, $phone, $customer_id);
$stmt->execute();
$stmt->close();

// Calculate total
$total = 0;
foreach($items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Update order info
$stmt = $conn->prepare("UPDATE orders SET total = ?, order_type = ?, table_number = ? WHERE id = ?");
$stmt->bind_param("dsii", $total, $order_type, $table_number, $order_id);
$stmt->execute();
$stmt->close();

// Delete old order items
$conn->query("DELETE FROM order_items WHERE order_id = $order_id");

// Insert new order items
foreach($items as $item) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
    $stmt->execute();
    $stmt->close();
}

// Update or insert delivery details
if ($order_type === 'Home Delivery') {
    $res = $conn->query("SELECT id FROM delivery_details WHERE order_id = $order_id");
    if ($res->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE delivery_details SET address = ? WHERE order_id = ?");
        $stmt->bind_param("si", $address, $order_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("INSERT INTO delivery_details (order_id, address) VALUES (?, ?)");
        $stmt->bind_param("is", $order_id, $address);
        $stmt->execute();
        $stmt->close();
    }
} else {
    // If changed to Dine-In, delete delivery details if exist
    $conn->query("DELETE FROM delivery_details WHERE order_id = $order_id");
}

echo json_encode(['message' => 'Order updated successfully!']);
$conn->close();
?>
