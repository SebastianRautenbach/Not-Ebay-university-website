<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Not-Ebay</title> <!-- This is how we will utilize the bootstrap in the project -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>


<nav class="navbar navbar-expand-sm navbar-dark bg-dark">
  <div class="container-fluid"> <!-- Dynamic sizing of the nav bar when we scale the window -->
    <a class="navbar-brand" href="/index.php">Not Ebay</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_nav_content_nice" aria-controls="collapse_nav_content_nice" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="collapse_nav_content_nice">
      <a>Add</a>
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="/index.php">Home</a><!-- This is the heading -->
        </li>
        <?php if (isset($_SESSION["Role"]) && $_SESSION["Role"] === "ADMIN"): ?> <!-- User role check for admin panel -->
          <li class="nav-item">
            <a class="nav-link" href="/index.php?q=admin">Admin Panel</a>
          </li>
        <?php endif; ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="MyverycoolandawesomenavbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">My Summary</a>
          <ul class="dropdown-menu" aria-labelledby="MyverycoolandawesomenavbarDropdown">
            <li><a class="dropdown-item" href="/index.php?q=summary">My Summary</a></li>
            <li><a class="dropdown-item" href="/index.php?q=selling">Selling</a></li>
            <?php if (isset($_SESSION["user_id"])): ?>
              <li><a class="dropdown-item" href="../public/logout.php">Logout</a></li>
            <?php else: ?>
              <li><a class="dropdown-item" href="/index.php?q=login">Login</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <li>
          <a class="btn btn-outline-primary position-relative" href="/index.php?q=cart">
            <i class="fas fa-shopping-cart"></i> Cart
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"> <!-- we can set the banner on the "button" to display the ammount of items we have -->
              <?php echo isset($_SESSION["cart"]) ? sizeof($_SESSION["cart"]) : 0; ?>              
            </span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>




</body>
</html>