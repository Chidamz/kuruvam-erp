
<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, quantity) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$name, $description, $price, $quantity])) {
        header("Location: products.php");
        exit;
    } else {
        echo "Error adding product!";
    }
}
?>

<form method="POST" action="">
    <input type="text" name="name" placeholder="Product Name" required>
    <textarea name="description" placeholder="Description"></textarea>
    <input type="number" name="price" placeholder="Price" step="0.01" required>
    <input type="number" name="quantity" placeholder="Quantity" required>
    <button type="submit">Add Product</button>
</form>
