
<?php
require_once "db.php";

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    header("Location: index.php");
    exit();
}

$id = (int) $_GET["id"];
$message = "";

// Get the existing product
$stmt = $conn->prepare("SELECT * FROM Product WHERE ProductID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found.");
}

// Update product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST["ProductCode"]);
    $name = trim($_POST["ProductName"]);
    $category = trim($_POST["Category"]);
    $unit = trim($_POST["Unit"]);
    $price = $_POST["StandardPrice"];

    if (
        $code == "" ||
        $name == "" ||
        $category == "" ||
        $unit == "" ||
        !is_numeric($price) ||
        $price < 0
    ) {
        $message = "Please enter valid information in all fields.";
    } else {
        $update = $conn->prepare(
            "UPDATE Product
             SET ProductCode = ?, ProductName = ?,
                 Category = ?, Unit = ?, StandardPrice = ?
             WHERE ProductID = ?"
        );

        $update->bind_param(
            "ssssdi",
            $code,
            $name,
            $category,
            $unit,
            $price,
            $id
        );

        if ($update->execute()) {
            $update->close();
            header("Location: index.php?updated=1");
            exit();
        } else {
            $message = "Unable to update product. Product code may already exist.";
        }

        $update->close();
    }

    // Keep entered values if validation fails
    $product["ProductCode"] = $code;
    $product["ProductName"] = $name;
    $product["Category"] = $category;
    $product["Unit"] = $unit;
    $product["StandardPrice"] = $price;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Home & Table Hardware</h1>
    <h2>Edit Product</h2>

    <form method="POST">

        <p class="error-message">
            <?php echo htmlspecialchars($message); ?>
        </p>

        <label>Product ID</label>
        <input
            type="text"
            value="<?php echo $product["ProductID"]; ?>"
            readonly
        >

        <label>Product Code</label>
        <input
            type="text"
            name="ProductCode"
            value="<?php echo htmlspecialchars($product["ProductCode"]); ?>"
            required
        >

        <label>Product Name</label>
        <input
            type="text"
            name="ProductName"
            value="<?php echo htmlspecialchars($product["ProductName"]); ?>"
            required
        >

        <label>Category</label>
        <input
            type="text"
            name="Category"
            value="<?php echo htmlspecialchars($product["Category"]); ?>"
            required
        >

        <label>Unit</label>
        <input
            type="text"
            name="Unit"
            value="<?php echo htmlspecialchars($product["Unit"]); ?>"
            required
        >

        <label>Standard Price</label>
        <input
            type="number"
            name="StandardPrice"
            min="0"
            step="0.01"
            value="<?php echo htmlspecialchars($product["StandardPrice"]); ?>"
            required
        >

        <br><br>

        <button type="submit">Update Product</button>
        <a href="index.php">Cancel</a>

    </form>

</body>
</html>

<?php $conn->close(); ?>
