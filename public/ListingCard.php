 <?php 
  // we essentially render html with the php function
  function render_card($title, $header_image, $description, $last_updated, $price, $id) {
  ?> <!-- This will too come up as plagirized even though it is from bootstrap -->
  <div class="col">
    <div class="card h-100" onClick="window.location.href='index.php?q=listingDetail&id=<?php echo $id ?>' 
    "style="width: 18rem;">
      <img class="card-img-top"  src="/upload/<?php echo $header_image ?>">
      <div class="card-body">
        <h5 class="card-title"><?php echo $title ?></h5>
        <p class="card-text"><?php echo $description ?></p>
        <large>R <?php echo $price ?></large>
      </div>      
    </div>
  </div>
  <?php
  }

  ?>
  