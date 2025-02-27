<?php require APP . 'view/_templates/header.php'; ?>

<?php

// Dummy-Produkte (Normalerweise aus einer Datenbank)
$products = [
    1 => ["name" => "Gaming-Maus", "price" => 49.99, "image" => "images/mouse.jpg"],
    2 => ["name" => "Mechanische Tastatur", "price" => 89.99, "image" => "images/keyboard.jpg"],
    3 => ["name" => "Gaming-Headset", "price" => 69.99, "image" => "images/headset.jpg"]
];

// Produkt in den Warenkorb legen
if (isset($_POST['add_to_cart'])) {
    $id = $_POST['product_id'];
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
}

// Anzahl im Warenkorb
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Respawn Gaming</title>
    <link rel="stylesheet" href="/public/scss/cart/style.css">
</head>
<body>

<header>
    <h1>Respawn Gaming Shop</h1>
</header>

<main>
    <h2>Produkte</h2>
    <div class="products">
        <?php foreach ($products as $id => $product): ?>
            <div class="product">
                <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                <h3><?= $product['name'] ?></h3>
                <p>Preis: €<?= number_format($product['price'], 2) ?></p>
                <form method="post">
                    <input type="hidden" name="product_id" value="<?= $id ?>">
                    <button type="submit" name="add_to_cart">In den Warenkorb</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require APP . 'view/_templates/footer.php'; ?>
</body>
</html>
