<?php 
    require 'config/dbConfig.php';
    session_start();

    if (!isset($_SESSION["user_id"])) {
        echo "not logged in";
        header("Location: ?q=login");
        exit;
    }

// we simulate the "buying of a item by just deleting it. In the future I will add a order db and notify the seller about a purchased Item
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as &$product) { 
        $productId = $product['id'];
        
        $stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
        $stmt->execute([$productId]);    
        $product_listing = $stmt->fetch();
        
        // remove the ammount from the database and if the quantity is 0 we set it to soldout
        $productStatus = (($product_listing['quantity'] - $product['quantity']) <= 1) ? 'soldout' : 'available';
        
        $stmt = $conn->prepare("UPDATE listings SET quantity = ?, product_status = ? WHERE id = ?");
        $stmt->execute([$product_listing['quantity'] - $product['quantity'], $productStatus, $productId]);
    }
    unset($product);

    $_SESSION['cart'] = [];
}

?>

<html>
    <head></head>
    <body>
        <?php include 'navbar.php'; ?>
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Payment successful!</h4>
            <p>You have purchased your products</p>
            <hr>
            <p class="mb-0">The products will be shipped to you soon!</p>
        </div>
        <img src="../public/src/images/notpaypal.png" class="img-fluid" alt="this is defnitely not paypal and it wont steal ur info">
        <?php include 'footer.php'; ?>
    </body>
</html>