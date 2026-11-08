
<?php
require_once "db.php";

// Search and filter values
$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

// Sorting options
$sortOptions = [
    "ProductID" => "ProductID",
    "ProductCode" => "ProductCode",
    "ProductName" => "ProductName",
    "Category" => "Category",
    "StandardPrice" => "StandardPrice"
];

$sort = $_GET["sort"] ?? "ProductID";
if (!array_key_exists($sort, $sortOptions)) {
    $sort = "ProductID";
}

$order = strtoupper($_GET["order"] ?? "ASC");
if (!in_array($order, ["ASC", "DESC"])) {
    $order = "ASC";
}

// Get categories for the filter
$categoryResult = $conn->query(
    "SELECT DISTINCT Category FROM Product ORDER BY Category ASC"
);

// Get products using a prepared statement
$sql = "SELECT * FROM Product
        WHERE (ProductCode LIKE ?
        OR ProductName LIKE ?
        OR Category LIKE ?
        OR Unit LIKE ?)
        AND (? = '' OR Category = ?)
        ORDER BY $sort $order";

$stmt = $conn->prepare($sql);

$searchValue = "%" . $search . "%";

$stmt->bind_param(
    "ssssss",
    $searchValue,
    $searchValue,
    $searchValue,
    $searchValue,
    $category,
    $category
);

$stmt->execute();
$result = $stmt->get_result();

$showReadMessage = (
    isset($_GET["search"]) ||
    isset($_GET["category"]) ||
    isset($_GET["sort"]) ||
    isset($_GET["order"])
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home & Table Hardware</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Home & Table Hardware</h1>
    <h2>Product Management System</h2>

    <?php if (isset($_GET["added"])) { ?>
        <p class="success-message">Product added successfully!</p>
    <?php } ?>

    <?php if (isset($_GET["updated"])) { ?>
        <p class="success-message">Product updated successfully!</p>
    <?php } ?>

    <?php if (isset($_GET["deleted"])) { ?>
        <p class="success-message">Product deleted successfully!</p>
    <?php } ?>

    <?php if ($showReadMessage) { ?>
        <p class="success-message">
            Product records displayed successfully!
        </p>
    <?php } ?>

    <form method="GET" class="search-form">

        <label>Search Product</label>
        <input
            type="text"
            name="search"
            placeholder="Enter product code, name, category, or unit"
            value="<?php echo htmlspecialchars($search); ?>"
        >

        <label>Filter by Category</label>
        <select name="category">
            <option value="">All Categories</option>

            <?php while ($cat = $categoryResult->fetch_assoc()) { ?>
                <option
                    value="<?php echo htmlspecialchars($cat["Category"]); ?>"
                    <?php echo ($category == $cat["Category"]) ? "selected" : ""; ?>
                >
                    <?php echo htmlspecialchars($cat["Category"]); ?>
                </option>
            <?php } ?>
        </select>

        <label>Sort By</label>
        <select name="sort">
            <?php foreach ($sortOptions as $value => $label) { ?>
                <option
                    value="<?php echo $value; ?>"
                    <?php echo ($sort == $value) ? "selected" : ""; ?>
                >
                    <?php echo $label; ?>
                </option>
            <?php } ?>
        </select>

        <label>Order</label>
        <select name="order">
            <option value="ASC" <?php echo ($order == "ASC") ? "selected" : ""; ?>>
                Ascending
            </option>
            <option value="DESC" <?php echo ($order == "DESC") ? "selected" : ""; ?>>
                Descending
            </option>
        </select>

        <button type="submit">Apply</button>
        <a href="index.php">Reset</a>

    </form>

    <p>Total products displayed: <?php echo $result->num_rows; ?></p>

    <table>
        <tr>
            <th>ID</th>
            <th>Product Code</th>
            <th>Product Name</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Standard Price</th>
            <th>Actions</th>
        </tr>

        <?php if ($result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row["ProductID"]; ?></td>
                    <td><?php echo htmlspecialchars($row["ProductCode"]); ?></td>
                    <td><?php echo htmlspecialchars($row["ProductName"]); ?></td>
                    <td><?php echo htmlspecialchars($row["Category"]); ?></td>
                    <td><?php echo htmlspecialchars($row["Unit"]); ?></td>
                    <td><?php echo number_format($row["StandardPrice"], 2); ?></td>
                    <td class="actions">
                        <a href="edit_product.php?id=<?php echo $row["ProductID"]; ?>">Edit</a>
                        |
                        <a href="delete_product.php?id=<?php echo $row["ProductID"]; ?>">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="7">No products found.</td>
            </tr>
        <?php } ?>

    </table>

    <div class="button-container">
        <a href="add_product.php" class="add-button">+ Add New Product</a>
    </div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
