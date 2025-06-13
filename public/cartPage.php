<?php

    session_start();
    require 'config/dbConfig.php';


    if (!isset($_SESSION["user_id"])) {
        header("Location: ?q=login");
        exit;
    }


    // we fake essentially two post methods in one class by specifying the action if we want to add or remove it
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['item_id'])) {
    $item_id = (int) $_POST['item_id'];
    $action = $_POST['action'];

    foreach ($_SESSION['cart'] as $index => $cart_item) {

        $index_cart_item = $_SESSION['cart'][$index];

        if ($cart_item['id'] == $item_id) {
            if ($action === 'remove') {
                if ($index_cart_item['quantity'] > 1) {
                    $_SESSION['cart'][$index]['quantity'] -= 1;
                } else {
                    echo "Why you no remove:" . $index_cart_item['id'];
                    unset($_SESSION['cart'][$index]);
                }
            } elseif ($action === 'add') {

                $stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
                $stmt->execute([$cart_item['id']]);
                $product_listing = $stmt->fetch();

                    // lets add an item to the cart onky if there is enough of it in the database 
                if($product_listing['quantity'] >= ($index_cart_item['quantity'] + 1)) // we dont want the user able to buy more than what there is stock of y'know
                {$_SESSION['cart'][$index]['quantity'] += 1;}
            }
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }
}


?>

<html>
<head>
    <title>Not EBAY</title>
     
</head>
        
        <body>
        <?php include 'navbar.php'; ?>

       <div class="container mt-5">
            <h2>Your Shopping Cart</h2>
            <div class="container-fluid">
                <?php if (!empty($_SESSION['cart'])):?>
                    <div class="list-group">
                        <?php foreach ($_SESSION['cart'] as $cart_item):?> <!-- We display each cart item -->
                            <div class="list-group-item d-flex align-items-center justify-content-between ">
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($cart_item['listing_title']);?></h5>
                                    <small>Quantity: <?php echo $cart_item['quantity'];?></small><br>
                                    <small>Price: R <?php echo number_format($cart_item['price'], 2);?></small>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <a type="button" href="index.php?q=listingDetail&id=<?php echo $cart_item['id'] ?>" class="btn btn-outline-primary btn-sm">vist</a>
                                     <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="add"> <!-- We specify the action to be the adding -->
                                        <input type="hidden" name="item_id" value="<?php echo $cart_item['id'];?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm">+</button>
                                    </form>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="remove"> <!-- We specify the action to be the removing -->
                                        <input type="hidden" name="item_id" value="<?php echo $cart_item['id'];?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                    </form>                                   
                                </div>    
                                                                    
                            
                            
                            </div>
                        <?php endforeach;?>
                    </div>
            </div>
            
            <div class="container-fluid mx-auto p-5" style="width: 50;">            
                <a class="btn btn-primary" href="index.php?q=checkout" role="button">Checkout</a>
            </div>            
            <?php else: ?>
                <div class="alert alert-info mt-4" role="alert">
                    Your cart is empty.
                </div>
            <?php endif; ?>
        </div></div>

        <?php include 'footer.php'; ?>
        
</body>

</html>