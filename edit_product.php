<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Access denied.");
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];

    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, quantity = ? WHERE id = ?");
    if ($stmt->execute([$name, $description, $price, $quantity, $id])) {
        header("Location: products.php");
        exit;
    } else {
        echo "Error updating product!";
    }
}
?>

<form method="POST" action="">
    <input type="text" name="name" value="<?php echo $product['name']; ?>" required>
    <textarea name="description"><?php echo $product['description']; ?></textarea>
    <input type="number" name="price" value="<?php echo $product['price']; ?>" step="0.01" required>
    <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" required>
    <button type="submit">Update Product</button>
</form>
