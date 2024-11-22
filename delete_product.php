<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    die("Access denied.");
}

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
if ($stmt->execute([$id])) {
    header("Location: products.php");
    exit;
} else {
    echo "Error deleting product!";
}
?>
