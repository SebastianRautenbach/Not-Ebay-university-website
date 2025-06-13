<?php 
session_start();
require '../config/dbConfig.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: ?q=login");
    exit;
}

$listing_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$listing_id) {   
    http_response_code(400);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM listings WHERE id = ?");
$stmt->execute([$listing_id]);
$listing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$listing) {
    http_response_code(404);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?" );
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// naughty naughty,
if ($listing['sellerID'] === $_SESSION['user_id'] ||
// we check if the user has the correct role because we also want the admin be able to delete other peoples listings
$user['role'] === 'ADMIN' ) {
    $conn->prepare("DELETE FROM listings WHERE id = ?")->execute([$listing_id]);
    $conn->prepare("DELETE FROM listing_images WHERE listing_id = ?")->execute([$listing_id]);
} else {
    http_response_code(403);
}


header("Location: /index.php?q=selling");

?>
