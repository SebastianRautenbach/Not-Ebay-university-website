<?php
session_start();
require 'config/dbConfig.php';


// stopping people from inputing text in the url that is not a valid int
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    http_response_code(400);
    exit;
}

//probably a good idea to check if the listing is even listed as available

$stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
$stmt->execute([$id]);
$product_listing = $stmt->fetch();

if (!$product_listing) {
    echo "Product not found.";
    exit;
}


$stmt = $conn->prepare("SELECT * FROM listing_images WHERE listing_id = ? AND image_type = ?" );
$stmt->execute([$product_listing['id'], 'main']);
$header_image = $stmt->fetch();


$stmt = $conn->prepare("SELECT * FROM listing_images WHERE listing_id = ? AND image_type = ?" );
$stmt->execute([$product_listing['id'], 'additional']);
$extra_images = $stmt->fetchAll();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION["user_id"])) {
        header("Location: index.php?p=login");
        exit;
    }
    // double checking if the user is adding a valid id'd item to their cart
    $id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if (!$id) {
        http_response_code(400);
        exit;
    }

    

    $stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    
    
    $found = false;
    if(!empty($_SESSION['cart']))
    { // we are checking if there already is the same item if so we add an extra if their is enough in the actual listing quantity
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $product['id']) {
                if ($item['quantity'] < $product['quantity']) {
                    $item['quantity'] += 1;
                }
                $found = true;
                break;
            }
        }
    }


    unset($item);


    if (!$found) {
        $product['quantity'] = 1;
        $_SESSION['cart'][] = $product;
    }
}





?>

<!DOCTYPE html>
<html>
<head>
    <title>Not EBAY</title>
</head>
<body>

<?php include 'navbar.php'; ?>


    <div class="w-100 bg-light py-5 px-3">
    <div class="container">
        <div class="card bg-dark mx-auto shadow-sm" style="max-width: 100%;">
            <div class="card-body">                             
                <div id="carouselExample" class="carousel slide">    <!-- This will come up as plagirism because it is from the tooltips from bootstrap -->
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                           <img src="/upload/<?php echo htmlspecialchars($header_image['image_path']); ?>" class="d-block w-100" alt="Header main cool image" style="max-height: 600px; object-fit: cover;">
                        </div>
                        <?php if (!empty($extra_images)): ?>
                            <?php foreach ($extra_images as $image): ?>
                                <div class="carousel-item">
                                    <img src="/upload/<?php echo htmlspecialchars($image['image_path']); ?>" class="d-block w-100" alt="extrawindo imags" style="max-height: 600px; object-fit: cover;">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                      <span class="visually-hidden"><</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                      <span class="carousel-control-next-icon" aria-hidden="true"></span>
                      <span class="visually-hidden">></span>
                    </button>
                </div>
                <hr class="text-light"/>                   
                <div class="rounded px-3 py-2 mt-3">
                    <h2 class="card-title text-light mb-4">
                        <?php echo htmlspecialchars($product_listing['listing_title']); ?>
                    </h2>
                    <p class="card-text text-light "><?php echo htmlspecialchars($product_listing['listing_description']); ?></p>
                    <p class="card-text text-light"><strong> R <?php echo number_format($product_listing['price'], 2); ?></strong></p>
                    <p class="card-text text-light"><strong>Quantity Available:</strong> <?php echo (int)$product_listing['quantity']; ?></p>
                    <?php if (!isset($_SESSION["user_id"])): ?>
                        <a href="/index.php?q=login" class="btn btn-warning">Log in to add to cart</a>
                    <?php else: ?>
                        <form method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product_listing['id']; ?>"> <!-- this is how we send the id to the post request -->
                            <button type="submit" class="btn btn-primary">Add to Cart</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>


<?php include 'footer.php'; ?>

</body>
</html>