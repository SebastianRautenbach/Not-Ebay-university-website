<?php
session_start();
require 'config/dbConfig.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
// getting the product from the url
$id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);    
    exit;
}

$stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "Product aint existing";
    exit;
}
// checking if the cart isnot null
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;
// we check if the product exist and if it does we add 1 only if there is enough of that product
foreach ($_SESSION['cart'] as &$item) {

    $Iquantity = $item['quantity'];
    $Pquantity = $product['quantity'];

    if ($item['id'] === $product['id']) {
        if ($Iquantity < $Pquantity) {
            $Iquantity['quantity'] += 1;
        }
        $found = true;
        break;
    }
}
unset($item);


// we create a new cart
if (!$found) {
    $product['quantity'] = 1; 
    $_SESSION['cart'][] = $product;
}


exit;
