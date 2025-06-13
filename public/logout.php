<?php // very basic we just delete the session and return to home ;)
session_start();
session_unset();
session_destroy();
header("Location: /index.php?q=home");
exit;
?>