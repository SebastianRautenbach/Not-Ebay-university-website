<?php
    require 'config/dbConfig.php';
    include 'ListingCard.php';

    $search_query = filter_input(INPUT_GET, 'category');
    if ($search_query === false || $search_query === null) {   
        http_response_code(400);
        exit;
    }

    
    // ----------------------------------------------------------------------- PAGE WORKOUT
    $current_page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
    if ($current_page === false || $current_page <= 0) {
      $current_page = 1;
    }

    $limit = 10;

    $start_from = ($current_page - 1) * $limit;

    // ----------------------------------------------------------------------- PAGE WORKOUT
    $search_term = '%' . $search_query . '%';

    $sql = "
      SELECT * FROM listings 
      WHERE product_status = 'available'
        AND (category LIKE :search OR listing_title LIKE :search)
      LIMIT $start_from, $limit
    ";
      
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':search', $search_term, PDO::PARAM_STR);
    $stmt->execute();
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $product_listings = $stmt->fetchAll();


    $sql = "SELECT COUNT(*) FROM listings WHERE product_status = 'available' 
    AND (category LIKE :search OR
     listing_title LIKE :search)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':search', $search_term, PDO::PARAM_STR);
    $stmt->execute();
    $total_items = $stmt->fetchColumn();    
    $total_pages = ceil($total_items / $limit);
?>


<html>
<head>
    <title>Not EBAY</title>      
    
</head>

      
        
        <body>
        <?php include 'navbar.php'; ?>
        <?php include 'searchbar.php' ?>
        
        <h3 style="text-align: center;">Search Results for: <?php echo $search_query ?></h3>
        <hr/>

        <div style="display: flex;">
          <div style="width: 250px; margin-right: 20px;">
            <?php include 'sideCategory.php'; ?>
          </div>

        <?php if (sizeof($product_listings) === 0): ?>
          <div style="flex: 1;">
            <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
              <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16"> <!-- No chatgpt was not used for this, it will be flagged due to the bootstrap tips -->
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
              </symbol>
            </svg>

            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
                <div>
                  No item found with that search
                </div>
            </div>
          </div>
        <?php endif;?>
                     
          <div style="flex: 1;">             
              <div class="row row-cols-1 row-cols-md-3 g-4">                
                  <?php 
                      foreach($product_listings as $product) {                      
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
        <div class="container my-2 p-2">
          <nav aria-label="navigation">  
            <ul class="pagination justify-content-center">          
              <li class="page-item"><a class="page-link" href="?q=listings&category=<?php echo $search_query; ?>&page=<?php if($current_page >= 0){ echo 1;}else{ echo $current_page - 1;}; ?>">Previous</a></li>
              <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item"><a class="page-link" href="?q=listings&category=<?php echo $search_query; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
              <?php endfor; ?>
              <li class="page-item"><a class="page-link" href="?q=listings&category=<?php echo $search_query; ?>&page=<?php if($current_page >= $total_pages) {echo $current_page;} else {echo $current_page + 1;} ; ?>">Next</a></li>
            </ul>
          </nav>
        </div>
    
        <?php include 'footer.php'; ?>

<script>

// The script waits for document completion before execution to prevent potential race conditions
document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);
  const current_page = parseInt(urlParams.get("page")) || 1;

  // The script loops through each pagination link to verify its active status against the current page number.
  document.querySelectorAll(".pagination .page-link").forEach(linkItem => {
  const linkURL = new URL(linkItem.href, window.location.origin);

  // Get the page number from this link.
  const pageNum = parseInt(linkURL.searchParams.get("page"));

  if (pageNum === current_page) {
    linkItem.parentElement.classList.add("active");
  }
  });
});

</script>
<script>

// Again, only run when everything is loaded to be safe.
document.addEventListener("DOMContentLoaded", () => {
  const curPage = <?php echo (int)$current_page; ?>;
  const totalPages = <?php echo (int)$total_pages; ?>;
  // The "Previos" button becomes disabled when users reach the final page of the pagination.
  if (curPage <= 1) {
    
    let firstItem = document.querySelector('.pagination li:first-child');
    if (firstItem) { // Double-checking to avoid null errors
      firstItem.classList.add('disabled');
    }
  }

  // If we’re on the last page, disable the "Next" button.
  if (curPage >= totalPages) {

  // The "Next " button becomes disabled when users reach the final page of the pagination.

  let lastItem = document.querySelector('.pagination li:last-child');

  if (lastItem) { // Just being extra cautious with a null check
    lastItem.classList.add('disabled');
  }
}
});

</script>



</body>

</html>