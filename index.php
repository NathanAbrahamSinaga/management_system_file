<?php
// Start session and include required files
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'includes/language.php';
require_once 'includes/routes.php';

// Initialize authentication
$auth = new Auth();

// Get current page
$current_page = getCurrentPage();

// Check authentication for protected pages
if (requiresAuth($current_page) && !$auth->isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

// Redirect logged-in users away from login/register pages
if (in_array($current_page, ['login', 'register']) && $auth->isLoggedIn()) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Handle public pages (login and register)
if (in_array($current_page, ['login', 'register'])) {
    // Directly include the page content for public pages
    $file = __DIR__ . '/pages/' . $current_page . '.php';
    if (file_exists($file)) {
        include $file;
    } else {
        // Fallback to login if page doesn't exist
        include __DIR__ . '/pages/login.php';
    }
} else {
    // Include header for authenticated pages
    include 'components/header.php';
    ?>
    
    <div class="flex h-screen bg-gray-100 pt-16">
        <?php include 'components/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div id="mainContent" class="content-transition flex-1 overflow-x-hidden overflow-y-auto md:ml-0">
            <main class="p-6">
                <?php
                // Display flash messages
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert-auto-hide bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">';
                    echo '<i class="fas fa-check-circle mr-2"></i>' . htmlspecialchars($_SESSION['success_message']);
                    echo '</div>';
                    unset($_SESSION['success_message']);
                }
                
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert-auto-hide bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">';
                    echo '<i class="fas fa-exclamation-circle mr-2"></i>' . htmlspecialchars($_SESSION['error_message']);
                    echo '</div>';
                    unset($_SESSION['error_message']);
                }
                
                // Include page content
                getPageContent($current_page);
                ?>
            </main>
            
            <?php include 'components/footer.php'; ?>
        </div>
    </div>
    <?php
}
?>