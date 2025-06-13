<?php 
session_start();


if (!isset($_SESSION["user_id"])) {
    header("Location: ?q=login");
    exit;
}

?>


<html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Page</title>
</head>
<body>
    <?php
    include 'navbar.php';
    ?>

    <div class="container mt-5">
        <h1>Summary Page</h1>        
        <div class="d-grid gap-2 container my-2 p-2 border">
        <a class="btn btn-outline-primary" href="/index.php?q=createListing" role="button">Create Lisitng</a>
        <a class="btn btn-outline-danger" href="../public/logout.php" role="button">Logout</a>
        </div>
    </div>
</body>
</html>