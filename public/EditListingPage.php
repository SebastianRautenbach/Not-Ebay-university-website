<?php 
session_start();
require 'config/dbConfig.php';
require 'config/imageFileTypes.php';

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
$listing_information = $stmt->fetch();

$stmt = $conn->prepare("SELECT * FROM users WHERE id =?" );
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Naughty naughty
if($listing_information['sellerID']!== $user_id && $user['role']!== 'ADMIN') {
 http_response_code(403);
 echo "user isnt verified";
 exit;
}



$stmt = $conn->prepare("SELECT * FROM listing_images WHERE listing_id = ?");
$stmt->execute([$listing_id]);
$listing_images = $stmt->fetchAll();


$location_parts = explode(',', $listing_information['location']);
$street_address_extracted_field = trim($location_parts[0]);
$suburb_extracted_field = trim($location_parts[1]);
$city_extracted_field = trim($location_parts[2]);
$postal_code_extracted_field = trim($location_parts[3]);

$fmain_image = '';
$fextra_images = [];

foreach ($listing_images as $image) {
 if ($image['image_type'] === 'main') {
 $fmain_image = $image['image_path'];
 } else {
 $fextra_images[] = $image['image_path'];
 }
}

$can_proceed = true;
$err_message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $listing_title = trim($_POST["product_title"]);
    $listing_description  = trim($_POST["product_description"]);
    $category = trim($_POST["Category"]);
    $product_condition = trim($_POST["product_condition"]);
    $product_status = trim($_POST["product_status"]);
    $main_image = $_FILES["product_image"];
    $extra_images = $_FILES["product_images"];

    $city_extracted_field = str_replace(',', '', filter_var(trim($_POST["City"]), 
    FILTER_SANITIZE_SPECIAL_CHARS));
    $street_address_extracted_field = str_replace(',', '', filter_var(trim($_POST["Street_Address"]), 
    FILTER_SANITIZE_SPECIAL_CHARS));
    $postal_code_extracted_field = str_replace(',', '', filter_var(trim($_POST["Postal_Code"]),
     FILTER_SANITIZE_SPECIAL_CHARS));
    $suburb_extracted_field = str_replace(',', '', filter_var(trim($_POST["Suburb"]), 
    FILTER_SANITIZE_SPECIAL_CHARS));

    // we concat it into one string sperated by commas because this will make for much speedier searches when the database is large in size.
    $location = "$street_address_extracted_field, $suburb_extracted_field, $city_extracted_field, $postal_code_extracted_field";


    $price = filter_var($_POST["price"], FILTER_VALIDATE_FLOAT);
    $quantity = filter_var($_POST["quantity"], FILTER_VALIDATE_INT);


    if($main_image['error'] === UPLOAD_ERR_OK) {
        $image_info_error  = getimagesize($main_image['name']);
        if ($image_info_error  !== false) {
            if (in_array($image_info_error ['mime'], $image_types)) {
                // continue as normal
            }
            else {
                $can_proceed = false;
                $err_message="This isnt a supported image fromat!";
            }
        }
        else {
            $can_proceed = false;
            $err_message="Not a valid image file!";
        }
    }
    else {
        $can_proceed = false;
        $err_message="Image could not upload!";
    }

    if(!empty($extra_images)) {
        foreach($extra_images as $image) {
        if($image['error'] === UPLOAD_ERR_OK) {
            $image_info_error  = getimagesize($image['name']);
            if ($image_info_error  !== false) {
                if (in_array($image_info_error ['mime'], $image_types)) {
                    // continue as normal
                }
                else {
                    $can_proceed = false;
                    $err_message="Not a valid image type!";
                }
            }
            else {
                $can_proceed = false;
                $err_message="Not a valid image file!";
            }
        }
        else {
            $can_proceed = false;
            $err_message="Image could not upload!";
        }
    }
    }

    



    $should_change_main_image = false;
    $should_change_add_images = false;

    if (!empty($main_image['name']) && $can_proceed) {
        $stmt = $conn->prepare("DELETE FROM listing_images WHERE listing_id = ? AND image_type = ?");
        $stmt->execute([$listing_id, 'main']);
        $should_change_main_image = true;
        echo 'deleted main image';
    }

    $should_change_add_images = false;

    if (isset($extra_images['name']) && is_array($extra_images['name']) && $can_proceed) {
        foreach ($extra_images['name'] as $index => $filename) {
            if (!empty($filename) && $extra_images['error'][$index] === UPLOAD_ERR_OK) {
                $should_change_add_images = true;
                break;
            }
        }
    }

    if ($should_change_add_images && $can_proceed) {
        $stmt = $conn->prepare("DELETE FROM listing_images WHERE listing_id = ? AND image_type = ?");
        $stmt->execute([$listing_id, 'additional']);
        $should_change_add_images = true;
        echo 'deleted add images';
    }


    if($can_proceed)
    { 
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
                $listing_title, $listing_description , $quantity , $price,
                $location, $category, $product_condition, $product_status,
                $listing_id, $user_id
            ]);

        } catch (PDOException $err) {
            
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO listing_images (listing_id, image_path, image_type) 
            VALUES (?, ?, ?)");
            try {            
               if($should_change_main_image){ $stmt->execute([
                    $listing_id, $main_image['name'], 'main'
                ]);

                if (!empty($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
                     move_uploaded_file($main_image['tmp_name'], $upload_dir . $main_image['name']);
                     echo "Main image uploaded successfully.";
                }
                else {
                    echo "Error uploading main image.";
                }}

               if($should_change_add_images){if (!empty($_FILES['product_images']) && is_array($_FILES['product_images']['name'])) {
                    if (isset($extra_images['name']) && is_array($extra_images['name'])) {
                       foreach ($extra_images['name'] as $index => $image_name) {
                           if ($image_name) {
                               $stmt->execute([
                                    $listing_id, $image_name, 'additional'
                                    ]);
                                    move_uploaded_file($extra_images['tmp_name'][$index], $upload_dir . $image_name);
                                    echo "Additional image $image_name uploaded successfully.";
                                }
                            }
                        }
                    } 
                }
            }
            catch (PDOException $err) {
                
            }
    }
    else {
        echo "<div class=\"alert alert-danger\" role=\"alert\">$err_message!</div>";
    }
}
?>



<html>
<head>
    <title>Not EBAY</title>
</head>
    <body>

    <?php include 'navbar.php'; ?>

    
        
        
    <form method="POST" action="?q=editListing&id=<?php echo $listing_id ?>" enctype="multipart/form-data" class="container my-5 p-4 border rounded shadow-sm bg-light">
        <h1 class="mb-4">Edit Product Listing</h1>

        <div class="mb-3">
            <label for="product_title" class="form-label">Product Name</label>
            <input type="text" class="form-control" id="product_title" name="product_title"
             value="<?php echo htmlspecialchars($listing_information['listing_title']); ?>"required>
        </div>

        <div class="mb-3">
            <label for="product_description" class="form-label">Description</label>
            <textarea maxlength="255" class="form-control" id="product_description" name="product_description"
             rows="3"required><?php echo htmlspecialchars($listing_information['listing_description']); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" id="quantity" name="quantity" step="1"  
            value="<?php echo htmlspecialchars($listing_information['quantity']); ?>"required>
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Price</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" 
            value="<?php echo htmlspecialchars($listing_information['price']);  ?>"required>
        </div>

        <h3 class="mt-4 mb-3">Location</h3>

        <div class="mb-3">
            <label for="City" class="form-label">City</label>
            <input type="text" class="form-control" id="City" name="City" required 
            value="<?php echo htmlspecialchars($city_extracted_field); ?>">
        </div>

        <div class="mb-3">
            <label for="Street_Address" class="form-label">Street Address</label>
            <input type="text" class="form-control" id="Street_Address" name="Street_Address"  
            value="<?php echo htmlspecialchars($street_address_extracted_field); ?>"required>
        </div>

        <div class="mb-3">
            <label for="Postal_Code" class="form-label">Postal Code</label>
            <input type="text" class="form-control" id="Postal_Code" name="Postal_Code"  
            value="<?php echo htmlspecialchars($postal_code_extracted_field); ?>"required>
        </div>

        <div class="mb-3">
            <label for="Suburb" class="form-label">Suburb</label>
            <input type="text" class="form-control" id="Suburb" name="Suburb" 
            value="<?php echo htmlspecialchars($suburb_extracted_field); ?>"required>
        </div>

        <div class="mb-3"> Just note that all image changes will delete the previous and use the new uploaded ones </div>

        <div class="mb-3">
            <label class="form-label">Current Main Image</label><br>
            <?php if ($fmain_image): ?>
                <img 
                src="/upload/<?php echo htmlspecialchars($fmain_image); ?>" 
                alt="Main aawesome Image" class="img-thumbnail" style="max-height: 200px;">
            <?php else: ?>
                <p>Please upload an image </p>
            <?php endif; ?>
        </div>
            
        <div class="mb-3">
            <label for="product_image" class="form-label">Change Main Product Image</label>
            <input class="form-control" type="file" id="product_image" name="product_image" accept="image/*"> <!-- we only accpet image formats -->
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
            <?php else: ?>
                <p>No extra images uploaded.</p>
            <?php endif; ?>
        </div>
            
        <div class="mb-3">
            <label for="product_images" class="form-label">
            Upload Extra Product Images
            </label>
            <input class="form-control" type="file" id="product_images" name="product_images[]" accept="image/*" multiple>
        </div>
            
        <div class="mb-3">
            <label for="Category" class="form-label">Category</label>
            <select class="form-select" id="Category" name="Category" required>
                <option value="" disabled>Select a category</option>
                <?php // we do this type of implementation because we want to be able to see what the previous choice was
                $categories = ["technology", "software", "clothes", "supplements", "furniture", "tool", "auto", "accessories", "health", "weapon", "other", "etc"];
                foreach ($categories as $category) {
                    $selected ="";
                    if($listing_information['category'] == $category) {$selected= 'selected';} 
                    else {$selected='';};
                    echo "<option value=\"$category\" $selected>" . ucfirst($category) . 
                    "</option>";
                }
                ?>
            </select>
        </div>
              
        <div class="mb-3">
            <label for="product_condition" class="form-label">Product Condition</label>
            <input type="text" class="form-control" id="product_condition" name="product_condition" 
            required 
            value="<?php echo htmlspecialchars($listing_information['product_condition']); ?>">
        </div>
              
        <div class="mb-4">
          <label for="product_status" class="form-label">Product State</label>
          <select class="form-select" id="product_status" name="product_status" required>
              <option value="" disabled selected>Select product state</option>
               <?php
                $categories = ["Available", "Sold-out", "Hidden"];
                foreach ($categories as $status) {
                    $selected = "";
                    if($listing_information['product_status'] == $status) {$selected='selected';} 
                    else {$selected='';};
                    echo "<option value=\"$status\" $selected>". ucfirst($status) . 
                    "</option>";
                }
               ?>        
            </select>
        </div>
              
        <button type="submit" class="btn btn-primary w-100">Update Listing</button>
    </form>

    </body>
</html>