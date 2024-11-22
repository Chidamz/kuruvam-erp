<?php
include 'config.php';

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(trim($data));
}

$search = isset($_POST['search']) ? sanitize($_POST['search']) : '';
$min_price = isset($_POST['min_price']) && is_numeric($_POST['min_price']) ? (float)$_POST['min_price'] : 0;
$max_price = isset($_POST['max_price']) && is_numeric($_POST['max_price']) ? (float)$_POST['max_price'] : PHP_INT_MAX;
$min_quantity = isset($_POST['min_quantity']) && is_numeric($_POST['min_quantity']) ? (int)$_POST['min_quantity'] : 0;
$max_quantity = isset($_POST['max_quantity']) && is_numeric($_POST['max_quantity']) ? (int)$_POST['max_quantity'] : PHP_INT_MAX;
$sort_by = isset($_POST['sort_by']) ? sanitize($_POST['sort_by']) : 'name';
$order = isset($_POST['order']) && $_POST['order'] === 'desc' ? 'desc' : 'asc';

// Fetch products based on the filtered criteria
$stmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE name LIKE ? AND price BETWEEN ? AND ? AND quantity BETWEEN ? AND ? 
    ORDER BY $sort_by $order
");
$stmt->execute(["%$search%", $min_price, $max_price, $min_quantity, $max_quantity]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="products.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Name', 'Description', 'Price', 'Quantity']); // CSV header

foreach ($products as $product) {
    fputcsv($output, [$product['name'], $product['description'], $product['price'], $product['quantity']]);
}

fclose($output);
exit;
?>
