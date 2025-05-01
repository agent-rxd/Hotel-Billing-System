<?php
header('Content-Type: application/json');
include 'db.php';

$sql = "SELECT * FROM menu_items";
$result = $conn->query($sql);

$menu = array();
while($row = $result->fetch_assoc()) {
    $menu[] = $row;
}
echo json_encode($menu);
$conn->close();
?>
