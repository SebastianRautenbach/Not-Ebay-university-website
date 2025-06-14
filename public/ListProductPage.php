<?php
session_start();
require 'config/dbConfig.php';
require 'config/imageFileTypes.php';
require 'config/listingCategories.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ?q=login");
    exit;
}

$user_id = $_SESSION["user_id"];

$upload_dir = 'upload/';


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

    $mainImage = $_FILES["mainImage"];
    $extraImages = rearange_files($_FILES["extraImages"]);


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



    // location
    $streetAddress  = trim($_POST["streetAddress"]);
    $Suburb         = trim($_POST["Suburb"]);
    $City           = trim($_POST["City"]);
    $PostalCode     = trim($_POST["PostalCode"]);

    $location =
        $streetAddress . ',' .
        $Suburb        . ',' .
        $City          . ',' .
        $PostalCode;





    if ($can_upload) {
        $stmt = $conn->prepare("INSERT INTO listings 
        (listing_title, listing_description, quantity, price, location, category, sellerID, product_condition, product_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        try {
            $stmt->execute([
                $productTitle,
                $productDescription,
                $productQuantity,
                $productPrice,
                $location,
                $productCategory,
                $user_id,
                $productCondition,
                $productStatus
            ]);
        } catch (PDOException $e) {
            $err_message = "Server error, could not uplload your product :(";
            $can_upload = false;
        }
    }

    $lastInsertedListingID = $conn->lastInsertId();

    if ($can_upload) {
        if (move_uploaded_file($mainImage["tmp_name"], $upload_dir . basename($mainImage["name"]))) {
            // uploaded the file B)
            $stmt = $conn->prepare("INSERT INTO listing_images (listing_id, image_path, image_type) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$lastInsertedListingID, (basename($mainImage["name"])), 'main']);
            } catch (PDOException $e) {
                $err_message = "Server error, could not uplload your main image :(";
                $can_upload = false;
            }
        }


        foreach ($extraImages as $extraImage) {

            if (move_uploaded_file($extraImage["tmp_name"], $upload_dir . basename($extraImage["name"]))) {
                // uploaded the file B)

                $stmt = $conn->prepare("INSERT INTO listing_images (listing_id, image_path, image_type) VALUES (?, ?, ?)");
                try {
                    $stmt->execute([$lastInsertedListingID, (basename($extraImage["name"])), 'additional']);
                } catch (PDOException $e) {
                    $err_message = "Server error, could not uplload your extra image :(";
                    $can_upload = false;
                }
            }
        }
    }


    if (!$can_upload) {
        echo "<div class='alert alert-danger' role='alert'>$err_message</div>";
    }
}




?>

<html>

<body>
    <?php include 'navbar.php'; ?>

    <div class="card align-items-center">
        <div class="card-body" style="max-width:80%">
            <h1 class="card-title">List Your Product</h1>
            <hr />
            <form class="row g-3 " method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="productTitle" class="form-label">Product Title</label>
                    <input class="form-control" type="text" id="productTitle" name="productTitle">
                </div>

                <div class="mb-3">
                    <label for="productDescription" class="form-label">Product Description</label>
                    <textarea class="form-control" id="productDescription" name="productDescription" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label for="productQuantity" class="form-label">Quantity</label>
                    <input class="form-control" type="number" id="productQuantity" name="productQuantity" min="0">
                </div>

                <div class="mb-3">
                    <label for="productPrice" class="form-label">Price</label>
                    <input class="form-control" type="number" id="productPrice" name="productPrice" min="0" step="0.1">
                </div>

                <div class="mb-3">
                    <label for="productCategory" class="form-label">Category</label>
                    <select class="form-select form-select mb-3" id="productCategory" name="productCategory" aria-label=".form-select-lg example">
                        <option value="" disabled>Select a category</option>
                        <?php // we do this type of implementation because we want to be able to see what the previous choice was                        
                        foreach ($gListingCategories as $category) {
                            $selected = "";                           
                            echo "<option value=\"$category\" $selected>" . // we loop through our entries from the database and when the category is found we mark it as selected
                                ucfirst($category) .
                                "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="productStatus" class="form-label">Status</label>
                    <select class="form-select form-select mb-3" name="productStatus" id="productStatus" aria-label=".form-select-lg example">
                        <option value="" selected disabled>choose product status</option>
                        <option value="Available">Available</option>
                        <option value="Soldout">Soldout</option>
                    </select>
                </div>



                <div class="mb-3">
                    <label for="productCondition" class="form-label">Product Condition</label>
                    <input class="form-control" type="text" name="productCondition" id="productCondition">
                </div>

                <hr />
                <h3 class="card-title">Images</h3>
                <hr />

                <div class="mb-3">
                    <label for="mainImage" class="form-label">Add Main Image</label>
                    <input class="form-control" type="file" name="mainImage" id="mainImage" accept="image/*">
                </div>

                <div class="mb-3">
                    <label for="extraImages" class="form-label">Add Extra Images</label>
                    <input class="form-control" type="file" name="extraImages[]" id="extraImages" accept="image/*" multiple>
                </div>

                <hr />
                <h3 class="card-title">Location</h3>
                <hr />

                <div class=" mb-3">
                    <label for="streetAddress" class="form-label" for="location">Street Address</label>
                    <input class="form-control" type="text" id="streetAddress" name="streetAddress">
                </div>

                <div class="mb-3">
                    <label for="Suburb" class="form-label">Suburb</label>
                    <input class="form-control" type="text" id="Suburb" name="Suburb">
                </div>

                <div class="mb-3">
                    <label for="City" class="form-label">City</label>
                    <input class="form-control" type="text" id="City" name="City">
                </div>

                <div class="mb-3">
                    <label for="PostalCode" class="form-label">Postal Code</label>
                    <input class="form-control" type="text" id="PostalCode" name="PostalCode">
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary mb-3">Create Product</button>
                </div>

            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>