<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Sanitize inputs
function sanitize($data) {
    return htmlspecialchars(trim($data));
}

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Sorting setup
$allowed_sort_columns = ['name', 'price', 'quantity'];
$sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $allowed_sort_columns) ? $_GET['sort_by'] : 'name';
$order = isset($_GET['order']) && $_GET['order'] === 'desc' ? 'desc' : 'asc';
$order_toggle = $order === 'asc' ? 'desc' : 'asc';

// Search and filtering parameters
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : PHP_INT_MAX;
$min_quantity = isset($_GET['min_quantity']) && is_numeric($_GET['min_quantity']) ? (int)$_GET['min_quantity'] : 0;
$max_quantity = isset($_GET['max_quantity']) && is_numeric($_GET['max_quantity']) ? (int)$_GET['max_quantity'] : PHP_INT_MAX;

// Query for total products (used for pagination)
$total_products_stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name LIKE ? AND price BETWEEN ? AND ? AND quantity BETWEEN ? AND ?");
$total_products_stmt->execute(["%$search%", $min_price, $max_price, $min_quantity, $max_quantity]);
$total_products = $total_products_stmt->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Fetch products with filtering, sorting, and pagination
$query = "
    SELECT * FROM products 
    WHERE name LIKE ? AND price BETWEEN ? AND ? AND quantity BETWEEN ? AND ? 
    ORDER BY $sort_by $order 
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", $min_price, $max_price, $min_quantity, $max_quantity]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>Product List</h1>

<!-- Filter/Search Form -->
<form method="GET" action="">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search by product name">
    <br><br>
    
    <!-- Advanced Filtering: Price Range -->
    <label>Price Range:</label>
    <input type="number" name="min_price" value="<?php echo $min_price; ?>" placeholder="Min Price" step="0.01">
    <input type="number" name="max_price" value="<?php echo $max_price == PHP_INT_MAX ? '' : $max_price; ?>" placeholder="Max Price" step="0.01">
    <br><br>
    
    <!-- Advanced Filtering: Quantity Range -->
    <label>Quantity Range:</label>
    <input type="number" name="min_quantity" value="<?php echo $min_quantity; ?>" placeholder="Min Quantity">
    <input type="number" name="max_quantity" value="<?php echo $max_quantity == PHP_INT_MAX ? '' : $max_quantity; ?>" placeholder="Max Quantity">
    <br><br>

    <button type="submit">Search</button>
</form>

<!-- Export Button -->
<form method="POST" action="export_csv.php">
    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
    <input type="hidden" name="min_price" value="<?php echo $min_price; ?>">
    <input type="hidden" name="max_price" value="<?php echo $max_price == PHP_INT_MAX ? '' : $max_price; ?>">
    <input type="hidden" name="min_quantity" value="<?php echo $min_quantity; ?>">
    <input type="hidden" name="max_quantity" value="<?php echo $max_quantity == PHP_INT_MAX ? '' : $max_quantity; ?>">
    <input type="hidden" name="sort_by" value="<?php echo $sort_by; ?>">
    <input type="hidden" name="order" value="<?php echo $order; ?>">
    <button type="submit">Export CSV</button>
</form>

<!-- Product Table with Sorting -->
<?php if (count($products) > 0): ?>
<table border="1">
    <tr>
        <th><a href="?search=<?php echo $search; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price == PHP_INT_MAX ? '' : $max_price; ?>&min_quantity=<?php echo $min_quantity; ?>&max_quantity=<?php echo $max_quantity == PHP_INT_MAX ? '' : $max_quantity; ?>&sort_by=name&order=<?php echo $order_toggle; ?>">Name</a></th>
        <th>Description</th>
        <th><a href="?search=<?php echo $search; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price == PHP_INT_MAX ? '' : $max_price; ?>&min_quantity=<?php echo $min_quantity; ?>&max_quantity=<?php echo $max_quantity == PHP_INT_MAX ? '' : $max_quantity; ?>&sort_by=price&order=<?php echo $order_toggle; ?>">Price</a></th>
        <th><a href="?search=<?php echo $search; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price == PHP_INT_MAX ? '' : $max_price; ?>&min_quantity=<?php echo $min_quantity; ?>&max_quantity=<?php echo $max_quantity == PHP_INT_MAX ? '' : $max_quantity; ?>&sort_by=quantity&order=<?php echo $order_toggle; ?>">Quantity</a></th>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
            <th>Actions</th>
        <?php endif; ?>
    </tr>
    <?php foreach ($products as $product): ?>
        <tr>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo htmlspecialchars($product['description']); ?></td>
            <td><?php echo htmlspecialchars($product['price']); ?></td>
            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <td>
                    <a href="edit_product.php?id=<?php echo $product['id']; ?>">Edit</a>
                    <a href="delete_product.php?id=<?php echo $product['id']; ?>">Delete</a>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
    <p>No products found matching your criteria.</p>
<?php endif; ?>

<!-- Pagination Links -->
<div>
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price == PHP_INT_MAX ? '' : $max_price; ?>&min_quantity=<?php echo $min_quantity; ?>&max_quantity=<?php echo $max_quantity == PHP_INT_MAX ? '' : $max_quantity; ?>&sort_by=<?php echo $sort_by; ?>&order=<?php echo $order; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>

<?php if ($_SESSION['user']['role'] === 'admin'): ?>
    <a href="add_product.php">Add New Product</a>
<?php endif; ?>
