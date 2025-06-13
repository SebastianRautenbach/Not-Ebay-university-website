<?php
session_start();
require 'config/dbConfig.php';


if (isset($_SESSION["user_id"])) {
    header("Location: ?q=home");
    exit;
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["Email"] = $user["email"];
        $_SESSION["Role"] = $user["role"];
        header("Location: ?q=home");
        exit;
    } else {
        echo "Invalid credentials";
    }
}
?>

<html> 
    <?php include 'navbar.php'; ?>

    
    <div class="mx-auto p-2" style="width: 500px;">
        <div class="card text-center mb-3" style="width: 18rem;">
      <div class="card-body">
        <h3 class="card-title">LOGIN</h3>
        <form method="POST">        
            <input name="email" required placeholder="Email"><br>
            <input name="password" type="password" required placeholder="Password"><br>
            <button class="btn btn-primary" type="submit">Login</button>
        </form>        
      </div>
      <a>don't have an account<a href="index.php?q=register"> Create one!</a></a>
    </div>
</div>
    
    

    <?php include 'footer.php' ?>
</html>



