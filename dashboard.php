<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

echo "Welcome, " . $_SESSION['user']['username'] . "! You are logged in as " . $_SESSION['user']['role'] . ".";

if ($_SESSION['user']['role'] === 'admin') {
    echo "<br><a href='products.php'>Manage Products</a>";
} else {
    echo "<br><a href='products.php'>View Products</a>";
}
?>
