<?php
ob_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

// Initialize authentication
$auth = new Auth();

// Logout user
if ($auth->isLoggedIn()) {
    $auth->logout();
    $_SESSION['success_message'] = 'You have been successfully logged out.';
}

// Redirect to login page
header('Location: index.php?page=login');
exit;
?>
<?php ob_end_flush(); ?>