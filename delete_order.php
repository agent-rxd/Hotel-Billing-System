<?php
header('Content-Type: application/json');
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$order_id = intval($data['order_id']);

$conn->query("DELETE FROM delivery_details WHERE order_id = $order_id");
$conn->query("DELETE FROM order_items WHERE order_id = $order_id");
$conn->query("DELETE FROM orders WHERE id = $order_id");

echo json_encode(['message' => 'Order deleted successfully!']);
$conn->close();
?>
