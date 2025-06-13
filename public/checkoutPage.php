<?php 
    session_start();
    $products = $_SESSION['cart'];
    $total = 0;
    foreach($products as $product) {
        $total +=  $product['price'] *  $product['quantity'];
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        echo "jwfiwpenfi-wevniwpkvm;";


        if (!isset($_SESSION["user_id"])) {
            header("Location: index.php?p=login");
            exit;
        }
        header("Location: index.php?q=paygate");
    }

?>

<html>
    <head></head>
    <body>
        <?php include 'navbar.php'; ?>
        <div class="container mt-5">
            <h1>Checkout</h1>     
            <div class="d-grid gap-2 container my-2 p-2 border">
                <form method="post">
                    <div class="row g-3 align-items-center">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control"  placeholder="First Name" aria-label="Recipient’s username" aria-describedby="basic-addon2" required>                
                            <input type="text" class="form-control"  placeholder="Last Name" aria-label="Recipient’s username" aria-describedby="basic-addon2" required>                
                        </div>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control"  placeholder="Street" aria-label="Recipient’s username" aria-describedby="basic-addon2" required>                
                            <input type="text" class="form-control"  placeholder="City" aria-label="Recipient’s username" aria-describedby="basic-addon2" required>                
                            <input type="text" class="form-control"  placeholder="Suburb" aria-label="Recipient’s username" aria-describedby="basic-addon2" required>                
                        </div>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control"  placeholder="Phone number" aria-label="Recipient’s " aria-describedby="basic-addon2" required>
                        </div>
                    </div>
                    <div class="col-auto">
                       <h3> <?php echo "Your total is: R" . $total; ?> </h3>                                        
                        <button type="submit" class="btn btn-primary">Pay using Not-PayPal</button>
                    </div>
                </form>
            </div>
        </div>
        <?php include 'footer.php'; ?>
    </body>
</html>