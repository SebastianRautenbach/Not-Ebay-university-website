<?php

    session_start();
    require 'config/dbConfig.php';
    include 'ListingEditCard.php';

    if (!isset($_SESSION["user_id"])) {
        header("Location: ?q=login");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?" );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if(empty($user)) {
        header("Location: ?q=login");
        exit;
    }

    // naughty naughty :) trying todo something ur not supposed too
    if($user['role'] !== "ADMIN") {
        header("Location: ?q=home");
        exit;
    }

    // for now we can only manage listings maybe users in the future
    $stmt = $conn->prepare("SELECT * FROM listings" );
    $stmt->execute();
    $listings = $stmt->fetchAll(PDO::FETCH_ASSOC);





?>


<html>
    <head>
    <title>Not EBAY</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
    <body>
        <?php include 'navbar.php'; ?>
        <div class="container mt-5">
            <h1>Admin Page</h1>        
            <div class="d-grid gap-2 container my-2 p-2 border">
                <h3>Edit Products:</h3>
                    <div style="display: flex;">
                        <div style="flex: 1;">             
                            <div class="row row-cols-1 row-cols-md-3 g-4">                
                                <?php // we render each listing instance in a card would maybe have been easier just sending the id through and loading it on the actual card.
                                    foreach($listings as $product) {                      
                                        $stmt = $conn->prepare("SELECT * FROM listing_images WHERE listing_id = ? AND image_type = ?" );
                                        $stmt->execute([$product['id'], "main"]);
                                        $header_image = $stmt->fetch();
                      
                      
                                        render_card($product['listing_title'],
                                        $header_image['image_path'] ,$product['listing_description'], '', $product['price'], $product['id']);
                                    }   
                                ?>                
                            </div>
                        </div>
                    </div>
                <hr/>
            </div>
    </div>






    



</body>
</html>