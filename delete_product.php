
<?php
require_once "db.php";

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$id = (int) $_GET["id"];

// Get product information
$stmt = $conn->prepare("SELECT * FROM Product WHERE ProductID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found.");
}

// Delete only after confirmation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delete = $conn->prepare("DELETE FROM Product WHERE ProductID = ?");
    $delete->bind_param("i", $id);

    if ($delete->execute()) {
        $delete->close();
        header("Location: index.php?deleted=1");
        exit();
    } else {
        echo "Unable to delete product.";
    }

    $delete->close();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Home & Table Hardware</h1>
    <h2>Delete Product</h2>

    <form method="POST">

        <p>Are you sure you want to delete this product?</p>

        <p>
            <strong>Product Code:</strong>
            <?php echo htmlspecialchars($product["ProductCode"]); ?>
        </p>

        <p>
            <strong>Product Name:</strong>
            <?php echo htmlspecialchars($product["ProductName"]); ?>
        </p>

        <p>
            <strong>Standard Price:</strong>
            <?php echo number_format($product["StandardPrice"], 2); ?>
        </p>

        <button type="submit">Yes, Delete Product</button>
        <a href="index.php">Cancel</a>

    </form>

</body>
</html>

<?php $conn->close(); ?>
