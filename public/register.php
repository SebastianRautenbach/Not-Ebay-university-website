<?php
session_start();

require 'config/dbConfig.php';

if (isset($_SESSION["user_id"])) {
    header("Location: ?q=home");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);

    $password = $_POST["password"];

    // check password if it follows policy:
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/'; // got this from geeksforgeeks
    if(!preg_match($pattern, $password)) {
        echo "<div class=\"alert alert-danger\" role=\"alert\">Password does not match policy!</div>";
    }
    else {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $email, $password]);
            header("Location: ?q=login"); // we need to create a session by ensuring a successful account login
        } catch (PDOException $e) {
            // error handling
        }

    }


    
}
?>


<html> 
    <?php include 'navbar.php'; ?>

    
    <div class="mx-auto p-2" style="width: 500px;">
        <div class="card text-center mb-3" style="width: 18rem;">
      <div class="card-body">
        <h3 class="card-title">REGISTER</h3>
        <form method="POST">                    
            <input name="username"  placeholder="Username" required><br>
            <input name="email" type="email"  placeholder="Email" required><br>
            <input name="password" type="password"  placeholder="Password" required><br>
            <button class="btn btn-primary" type="submit">Register</button>
        </form>        
      </div>
    </div>
</div>
    
    

    <?php include 'footer.php' ?>
</html>