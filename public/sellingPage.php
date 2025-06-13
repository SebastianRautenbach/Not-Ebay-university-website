<?php
    session_start();
    require 'config/dbConfig.php';
    include 'ListingEditCard.php';

    if (!isset($_SESSION["user_id"])) {
        header("Location: ?q=login");
        exit;
    }


    $stmt = $conn->prepare("SELECT * FROM listings WHERE sellerID = ?");      
    $stmt->execute([$_SESSION['user_id']]);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $product_listings = $stmt->fetchAll();

?>



<html>

<head><title>Not Ebay</title></head>

<body>
    <?php include 'navbar.php'; ?>
    
    <div class="d-flex justify-content-center"><h1>Your listed products</h1></div>
    <hr/>

    <div style="flex: 1;">             
        <div class="row row-cols-1 row-cols-md-3 g-4"> 
            <?php 
                foreach($product_listings as $product) {                      
                    $stmt = $conn->prepare("SELECT * FROM listing_images WHERE listing_id = ? AND image_type = ?" );
                    $stmt->execute([$product['id'], "main"]);
                    $header_image = $stmt->fetch();
                
                    // I love reusability by creating a modular card page
                    render_card($product['listing_title'],
                    $header_image['image_path'] ,$product['listing_description'], '', $product['price'], $product['id']);
                }   
            ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>


</html>