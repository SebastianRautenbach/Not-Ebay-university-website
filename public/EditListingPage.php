<?php

session_start();
require 'config/dbConfig.php';
require 'config/imageFileTypes.php';
require 'config/listingCategories.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ?q=login");
    exit;
}

$listing_id = filter_input(INPUT_GET, 'id');
if ($listing_id === false || $listing_id === null) {
    http_response_code(400);
    echo "Invalid listing category.";
    exit;
}

$user_id = $_SESSION["user_id"];








$upload_dir = 'upload/';



$stmt = $conn->prepare("SELECT * FROM listings WHERE id =?");
$stmt->execute([$listing_id]);
$listingInfo = $stmt->fetch();

$stmt = $conn->prepare("SELECT * FROM users WHERE id =?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Naughty naughty
if ($listingInfo['sellerID'] !== $user_id && $user['role'] !== 'ADMIN') {
    http_response_code(403);
    echo "user isnt verified";
    exit;
}



$stmt = $conn->prepare("SELECT * FROM listing_images WHERE listing_id = ?");
$stmt->execute([$listing_id]);
$listing_images = $stmt->fetchAll();


$fLocationParts = explode(',', $listingInfo['location']);
$fStreetAddress = trim($fLocationParts[0]);
$fSuburb = trim($fLocationParts[1]);
$fCity = trim($fLocationParts[2]);
$fPostalCode = trim($fLocationParts[3]);

$fmain_image = '';
$fextra_images = [];

foreach ($listing_images as $image) {
    if ($image['image_type'] === 'main') {
        $fmain_image = $image['image_path'];
    } else {
        $fextra_images[] = $image['image_path'];
    }
}

$can_upload = true;
$err_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $productTitle = trim($_POST["productTitle"]);
    $productDescription = trim($_POST["productDescription"]);
    $productQuantity = trim($_POST["productQuantity"]);
    $productPrice = trim($_POST["productPrice"]);
    $productCategory = trim($_POST["productCategory"]);
    $productStatus = trim($_POST["productStatus"]);
    $productCondition = trim($_POST["productCondition"]);

    // images

    $goingToChangeMainImage = false;
    $goingToChangeExtraImages = false;

    $mainImage = $_FILES["mainImage"];
    $extraImages = rearange_files($_FILES["extraImages"]);

    if ($mainImage['error'] === UPLOAD_ERR_OK && $mainImage['size'] > 0) {
        $goingToChangeMainImage = true;
    }

    foreach ($extraImages as $image) { // as son as one image is invalid we just break away
        if ($image['error'] === UPLOAD_ERR_OK && $image['size'] > 0) {
            $goingToChangeExtraImages = true;
            break;
        }
    }

    if ($goingToChangeMainImage) {
        if (isset($mainImage["size"]) && $mainImage["size"] > 500000) {
            // file too big
            $can_upload = false;
            $err_message = "main file too big";
        }

        if (isset($mainImage["mime"]) && !in_array($mainImage["mime"], $image_types)) {
            // file not correct type
            $can_upload = false;
            $err_message = "main file not correct type";
        }
    }

    if ($goingToChangeExtraImages) {
        foreach ($extraImages as $extraImage) {
            if (isset($extraImage["size"]) && $extraImage["size"] > 500000) {
                // file too big
                $can_upload = false;
                $err_message = "extra file too big";
            }

            if (isset($extraImage["mime"]) && !in_array($extraImage["mime"], $image_types)) {
                // file not correct type
                $can_upload = false;
                $err_message = "extra file not correct type";
            }
        }
    }

    $City = str_replace(',', '', filter_var(
        trim($_POST["City"]),
        FILTER_SANITIZE_SPECIAL_CHARS
    ));
    $StreetAddress = str_replace(',', '', filter_var(
        trim($_POST["streetAddress"]),
        FILTER_SANITIZE_SPECIAL_CHARS
    ));
    $PostalCode = str_replace(',', '', filter_var(
        trim($_POST["postalCode"]),
        FILTER_SANITIZE_SPECIAL_CHARS
    ));
    $Suburb = str_replace(',', '', filter_var(
        trim($_POST["Suburb"]),
        FILTER_SANITIZE_SPECIAL_CHARS
    ));

    // we concat it into one string sperated by commas because this will make for much speedier searches when the database is large in size.
    $location = "$StreetAddress, $Suburb, $City, $PostalCode";

    if ($can_upload) {
        $stmt = $conn->prepare("UPDATE listings SET 
            listing_title = ?, 
            listing_description = ?, 
            quantity = ?, 
            price = ?, 
            location = ?, 
            category = ?, 
            product_condition = ?, 
            product_status = ?
            WHERE id = ? AND sellerID = ?");

        try {

            $stmt->execute([
                $productTitle,
                $productDescription,
                $productQuantity,
                $productPrice,
                $location,
                $productCategory,
                $productCondition,
                $productStatus,
                $listing_id,
                $listingInfo['sellerID']
            ]);
        } catch (PDOException $e) {
            $err_message = "Server error, couldnt update your product :(";
            $can_upload = false;
        }
    }


    if ($can_upload) {
        if ($goingToChangeMainImage && move_uploaded_file($mainImage["tmp_name"], $upload_dir . basename($mainImage["name"]))) {

            $stmt = $conn->prepare("DELETE FROM listing_images WHERE listing_id = ? AND image_type = ?");
            $stmt->execute([$listing_id, 'main']);
            echo 'deleted main image';

            // uploaded the file B)
            $stmt = $conn->prepare("INSERT INTO listing_images (listing_id, image_path, image_type) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$listing_id, (basename($mainImage["name"])), 'main']);
            } catch (PDOException $e) {
                $err_message = "Server error, could not uplload your main image :(";
                $can_upload = false;
            }
        }

        if ($goingToChangeExtraImages) {

            $stmt = $conn->prepare("DELETE FROM listing_images WHERE listing_id = ? AND image_type = ?");
            $stmt->execute([$listing_id, 'additional']);
            echo 'deleted add images';


            foreach ($extraImages as $extraImage) {

                if (move_uploaded_file($extraImage["tmp_name"], $upload_dir . basename($extraImage["name"]))) {
                    // uploaded the file B)

                    $stmt = $conn->prepare("INSERT INTO listing_images (listing_id, image_path, image_type) VALUES (?, ?, ?)");
                    try {
                        $stmt->execute([$listing_id, (basename($extraImage["name"])), 'additional']);
                    } catch (PDOException $e) {
                        $err_message = "Server error, could not uplload your extra image :(";
                        $can_upload = false;
                    }
                } else {
                    $err_message = "Server error, could not uplload your extra image :(";
                    $can_upload = false;
                }
            }
        }
    }


    if (!$can_upload) {
        echo "<div class='alert alert-danger' role='alert'>$err_message</div>";
    } else {
        echo "<div class='alert alert-success' role='alert'>Updated successful!</div>";
    }
}
?>



<html>

<head>
    <title>Not EBAY</title>
</head>

<body>

    <?php include 'navbar.php'; ?>

    <div class="card align-items-center">
        <div class="card-body" style="max-width:80%">
            <h1 class="card-title">Edit Product</h1>
            <hr />
            <form class="row g-3 " method="post" action="?q=editListing&id=<?php echo $listing_id ?>" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="productTitle" class="form-label">Product Title</label>
                    <input class="form-control" type="text" value="<?php echo htmlspecialchars($listingInfo['listing_title']); ?>" id="productTitle" name="productTitle">
                </div>

                <div class="mb-3">
                    <label for="productDescription" class="form-label">Product Description</label>
                    <textarea class="form-control" id="productDescription" name="productDescription" rows="3"><?php echo htmlspecialchars($listingInfo['listing_description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="productQuantity" class="form-label">Quantity</label>
                    <input class="form-control" type="number" id="productQuantity" value="<?php echo (int) htmlspecialchars($listingInfo['quantity']); ?>" name="productQuantity" min="0">
                </div>

                <div class="mb-3">
                    <label for="productPrice" class="form-label">Price</label>
                    <input class="form-control" type="number" id="productPrice" value="<?php echo htmlspecialchars($listingInfo['price']); ?>" name="productPrice" min="0" step="0.1">
                </div>

                <div class="mb-3">
                    <label for="productCategory" class="form-label">Category</label>
                    <select class="form-select form-select mb-3" id="productCategory" name="productCategory" aria-label=".form-select-lg example">
                        <option value="" disabled>Select a category</option>
                        <?php // we do this type of implementation because we want to be able to see what the previous choice was                        
                        foreach ($gListingCategories as $category) {
                            $selected = "";
                            if ($listingInfo['category'] == $category) {
                                $selected = 'selected';
                            } else {
                                $selected = '';
                            };
                            echo "<option value=\"$category\" $selected>" . // we loop through our entries from the database and when the category is found we mark it as selected
                                ucfirst($category) .
                                "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="productStatus" class="form-label">Status</label>
                    <select class="form-select form-select mb-3" id="productStatus" name="productStatus" aria-label=".form-select-lg example">
                        <option value="" disabled>Select product Status</option>
                        <?php // we do this type of implementation because we want to be able to see what the previous choice was                        
                        foreach (['Available', 'Soldout'] as $status) {
                            $selected = "";
                            if ($listingInfo['status'] == $status) {
                                $selected = 'selected';
                            } else {
                                $selected = '';
                            };
                            echo "<option value=\"$status\" $selected>" . // we loop through our entries from the database and when the category is found we mark it as selected
                                ucfirst($status) .
                                "</option>";
                        }
                        ?>
                    </select>
                </div>


                <div class="mb-3">
                    <label for="productCondition" class="form-label">Product Condition</label>
                    <input class="form-control" type="text" value="<?php echo htmlspecialchars($listingInfo['product_condition']); ?>" name="productCondition" id="productCondition">
                </div>

                <hr />
                <h3 class="card-title">Images</h3> <!-- --------------------------------------------------------- IMAGE UPLOADING -->
                <hr />

                <div class="mb-3">
                    <label class="form-label">Current Main Image</label><br>
                    <?php if (!empty($fmain_image)): ?>
                        <img
                            src="/upload/<?php echo htmlspecialchars($fmain_image); ?>"
                            class="img-thumbnail me-2 mb-2"
                            style="max-height: 150px;">
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="mainImage" class="form-label">Add Main Image</label>
                    <input class="form-control" type="file" name="mainImage" id="mainImage" accept="image/*">
                </div>


                <div class="mb-3">
                    <label class="form-label">Current Additional Images</label><br>
                    <?php if (!empty($fextra_images)): ?>
                        <?php foreach ($fextra_images as $img): ?>
                            <img
                                src="/upload/<?php echo htmlspecialchars($img); ?>"
                                class="img-thumbnail me-2 mb-2"
                                style="max-height: 150px;">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label for="extraImages" class="form-label">Add Extra Images</label>
                    <input class="form-control" type="file" name="extraImages[]" id="extraImages" accept="image/*" multiple>
                </div>

                <hr />
                <h3 class="card-title">Location</h3> <!-- --------------------------------------------------------- LOCATION UPLOADING -->
                <hr />

                <div class=" mb-3">
                    <label for="streetAddress" class="form-label" for="location">Street Address</label>
                    <input class="form-control" type="text" id="streetAddress" value="<?php echo htmlspecialchars($fStreetAddress); ?>" name="streetAddress">
                </div>

                <div class="mb-3">
                    <label for="Suburb" class="form-label">Suburb</label>
                    <input class="form-control" type="text" id="Suburb" value="<?php echo htmlspecialchars($fSuburb); ?>" name="Suburb">
                </div>

                <div class="mb-3">
                    <label for="City" class="form-label">City</label>
                    <input class="form-control" type="text" id="City" value="<?php echo htmlspecialchars($fCity); ?>" name="City">
                </div>

                <div class="mb-3">
                    <label for="postalCode" class="form-label">Postal Code</label>
                    <input class="form-control" type="text" id="postalCode" value="<?php echo htmlspecialchars($fPostalCode); ?>" name="postalCode">
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary mb-3">Update Product</button>
                </div>

            </form>
        </div>
    </div>


    <?php include 'footer.php'; ?>


</body>

</html>