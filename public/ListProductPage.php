<?php 
session_start();
require 'config/dbConfig.php';
require 'config/imageFileTypes.php';
if (!isset($_SESSION["user_id"])) {
    header("Location: ?q=login");
    exit;
}

$upload_dir = 'upload/'; 

/*

    we do this because in production we it is bad to echo the errors
    so we store the error message that we type ourselves
    and display it as a alaert.


*/
$can_proceed = true;
$err_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pt = trim($_POST["product_title"]);
    $pd = trim($_POST["product_description"]);
    $cate = trim($_POST["Category"]);
    $pc = trim($_POST["product_condition"]);
    $ps = trim($_POST["product_status"]);
    $main_image = $_FILES["product_image"];
    $extra_images = $_FILES["product_images"];

    if($main_image['error'] === UPLOAD_ERR_OK) {
        $image_info_error  = getimagesize($main_image['tmp_name']);
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
        $err_message="Main Image could not upload!";
    }

    for ($i = 0; $i < count($extra_images['name']); $i++) {
      if ($extra_images['error'][$i] === UPLOAD_ERR_OK) {
          $tmp_name = $extra_images['tmp_name'][$i];

          $image_info_error  = getimagesize($tmp_name);
          if ($image_info_error  !== false) {
              if (!in_array($image_info_error ['mime'], $image_types)) {
                  $can_proceed = false;
                  $err_message="Not a valid image type!";
                  break;
              }
          } else {
              $can_proceed = false;
              $err_message="Not a valid image file!";
              break;
          }
      } else {
          $can_proceed = false;
          $err_message="Main Image could not upload!";
          break;
      }
  }

    
    $city_extracted_field = str_replace(',', '', filter_var(trim($_POST["City"]), 
    FILTER_SANITIZE_SPECIAL_CHARS));
    $street_address_extracted_field = str_replace(',', '', filter_var(trim($_POST["Street_Address"]), 
    FILTER_SANITIZE_SPECIAL_CHARS));
    $postal_code_extracted_field = str_replace(',', '', filter_var(trim($_POST["Postal_Code"]),
     FILTER_SANITIZE_SPECIAL_CHARS));
    $suburb_extracted_field = str_replace(',', '', filter_var(trim($_POST["Suburb"]), 
    FILTER_SANITIZE_SPECIAL_CHARS));


    $price = filter_var($_POST["price"], FILTER_VALIDATE_FLOAT);

    if($price < 0) {
        $price = $price * -1; // we swizzle if it was negative
    }


    $quantity = filter_var($_POST["quantity"], FILTER_VALIDATE_INT);


    $location = "$street_address_extracted_field, $suburb_extracted_field, $city_extracted_field, $postal_code_extracted_field";

    $user_id = $_SESSION["user_id"];

    if($can_proceed) {
      $stmt = $conn->prepare("INSERT INTO listings 
        (listing_title, listing_description, quantity, price, location, category, sellerID, product_condition, product_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    try {
        $stmt->execute([
            $pt, $pd, $quantity , $price,
            $location, $cate , $user_id, $pc, $ps
        ]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }


    $stmt = $conn->prepare("INSERT INTO listing_images (listing_id, image_path, image_type) 
        VALUES (?, ?, ?)");
        try {
            
            $lastInsertedID = $conn->lastInsertId();
            
            $stmt->execute([
                $lastInsertedID, $main_image['name'], 'main'
            ]);
            

            
            if (!empty($_FILES['product_image']) && 
            $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
                 move_uploaded_file($main_image['tmp_name'], $upload_dir . $main_image['name']);
                 echo "bingo main image uploaded";
            }
            else {
                echo "cannot upload main";
            }

            if (!empty($_FILES['product_images']) && 
            is_array($_FILES['product_images']['name'])) {
                if (isset($extra_images['name']) && is_array($extra_images['name'])) {
                   foreach ($extra_images['name'] as $index => $image_name) {
                       if ($image_name) {
                           $stmt->execute([
                               $lastInsertedID, $image_name, 'additional'
                           ]);
                           move_uploaded_file($extra_images['tmp_name'][$index], $upload_dir . $image_name);
                           echo "Extra image $image_name uploaded successfully";
                       }
                   }
                }
                else {
                    echo "No extra images uploaded."; // will need to do an alert
                }
            } else {
                echo "No extra images uploaded.";
            }
        }
        catch (PDOException $e) {
         
        }
    }else {
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

    
        
        
    <form method="POST" action="?q=createListing" enctype="multipart/form-data" class="container my-5 p-4 border rounded shadow-sm bg-light">
        <h1 class="mb-4">Create Product Listing</h1>

        <div class="mb-3">
          <label for="product_title" class="form-label">Product Name</label>
          <input type="text" class="form-control" id="product_title" 
          name="product_title" required placeholder="Product Name">
        </div>

        <div class="mb-3">
          <label for="product_description" class="form-label">Description</label>
          <textarea maxlength="255" class="form-control" id="product_description" 
          name="product_description" rows="3" required placeholder="Description"></textarea>
        </div>

        <div class="mb-3">
          <label for="quantity" class="form-label">Quantity</label>
          <input type="number" class="form-control" id="quantity" 
          name="quantity" min="0" step="1" required placeholder="Quantity">
        </div>
        <div class="mb-3">
          <label for="price" class="form-label">Price</label>
          <input type="number" class="form-control" id="price" 
          name="price" min="0.0" step="0.01" required placeholder="Price">
        </div>

        <h3 class="mt-4 mb-3">Location</h3>

        <div class="mb-3">
          <label for="City" class="form-label">City</label>
          <input type="text" class="form-control" id="City" 
          name="City" required placeholder="City">
        </div>

        <div class="mb-3">
          <label for="Street_Address" class="form-label">Street Address</label>
          <input type="text" class="form-control" id="Street_Address" 
          name="Street Address" required placeholder="Street Address">
        </div>
        <div class="mb-3">
          <label for="Postal_Code" class="form-label">Postal Code</label>
          <input type="text" class="form-control" id="Postal_Code" 
          name="Postal Code" required placeholder="7441">
        </div>

        <div class="mb-3">
          <label for="Suburb" class="form-label">Suburb</label>
          <input type="text" class="form-control" id="Suburb" 
          name="Suburb" required placeholder="Suburb">
        </div>
        <div class="mb-3">
          <label for="product_image" class="form-label">Main Product Image</label>
          <input class="form-control" type="file" id="product_image" 
          name="product_image" required accept="image/*">
        </div>

        <div class="mb-3">
          <label for="product_images" class="form-label">Additional Product Images (optional)</label>
          <input class="form-control" type="file" id="product_images" 
          name="product_images[]" accept="image/*" required multiple>
        </div>

        <div class="mb-3"> <!-- we dont have to do somehting similar to the edit listing page because there is no predifined variable -->
            <label for="Category" class="form-label">Category</label>
            <select class="form-select" id="Category" name="Category" required>
              <option value="" disabled selected>Select a category </option>
              <option value="technology">Technology </option>
              <option value="software">Software </option>
              <option value="clothes">Clothes </option>
              <option value="supplements">Supplements </option>
              <option value="furniture">Furniture</option>
              <option value="tool">Tool </option>
              <option value="auto">Auto </option>
              <option value="accessories">Accessories </option>
              <option value="health">Health </option>
              <option value="weapon">Weapon </option>
              <option value="other">Other </option>
            </select>
        </div>

        <div class="mb-3">
          <label for="product_condition" class="form-label">Product Condition</label>
          <input type="text" class="form-control" id="product_condition" 
          name="product_condition" placeholder="broken, new , factory sealed, etc."  required>
        </div>

        <div class="mb-4">
          <label for="product_status" class="form-label">Product State</label>
          <select class="form-select" id="product_status" name="product_status" required>
              <option value="" disabled selected>Select product state</option>
              <option value="Available">Available</option>              
              <option value="Soldout">Soldout</option>              
              <option value="Hidden">Hidden</option>              
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Create Listing</button>
    </form>

    </body>
</html>