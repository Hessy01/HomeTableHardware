
<?php
require_once "db.php";

$message = "";

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
        $stmt = $conn->prepare(
            "INSERT INTO Product
            (ProductCode, ProductName, Category, Unit, StandardPrice)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("ssssd", $code, $name, $category, $unit, $price);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: index.php?added=1");
            exit();
        } else {
            $message = "Unable to add product. Product code may already exist.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product - Home & Table Hardware</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Home & Table Hardware</h1>
    <h2>Add New Product</h2>

    <form method="POST">

        <p class="error-message">
            <?php echo htmlspecialchars($message); ?>
        </p>

        <label>Product Code</label>
        <input type="text" name="ProductCode" required>

        <label>Product Name</label>
        <input type="text" name="ProductName" required>

        <label>Category</label>
        <input type="text" name="Category" required>

        <label>Unit</label>
        <input type="text" name="Unit" required>

        <label>Standard Price</label>
        <input type="number" name="StandardPrice" min="0" step="0.01" required>

        <br><br>

        <button type="submit">Add Product</button>
        <a href="index.php">Cancel</a>

    </form>

</body>
</html>
