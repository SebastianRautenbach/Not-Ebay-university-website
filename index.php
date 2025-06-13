<?php
if (!empty($_GET['q'])) {
    $query = htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8'); // stopping funny buisiness with url modifications >:(

    switch ($query) {

        case 'info':
            phpinfo();
            exit;
        
        case 'home':
            require '../www/public/homepage.php';
            exit;

        case 'admin':
            require 'public/adminPanelPage.php';
            exit;

        case 'login':
            require '../www/public/login.php';
            exit;

        case 'cart':
            require '../www/public/cartPage.php';
            exit;

        case 'register':
            require '../www/public/register.php';
            exit;

        case 'createListing':
            require '../www/public/ListProductPage.php';
            exit;

        case 'listings':
            require '../www/public/listingspage.php';
            exit;

        case 'listingDetail':
            require '../www/public/listingPage.php';
            exit;

        case 'logout':
            require 'public/logout.php';
            exit;

        case 'summary':
            require 'public/summaryPage.php';
            exit;

        case 'editListing':
            require 'public/EditListingPage.php';
            exit;


        case 'checkout':
            require 'public/checkoutPage.php';
            exit;

        case 'paygate':
            require 'public/paygateExample.php';
            exit;

        case 'selling':
            require 'public/sellingPage.php';
            exit;

        default:
            header("HTTP/1.0 404 Not Found");
            require 'public/404.php';
            exit;
    }
} else {  
    header("Location: ?q=home");
    exit;
}
