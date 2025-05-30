<?php
// Routes handler
function getPageContent($page) {
    $allowed_pages = [
        'dashboard', 'documents', 'folders', 'categories', 'users', 
        'profile', 'login', 'register', 'logout'
    ];
    
    if (in_array($page, $allowed_pages)) {
        $file = __DIR__ . '/../pages/' . $page . '.php';
        if (file_exists($file)) {
            include $file;
        } else {
            include __DIR__ . '/../pages/dashboard.php';
        }
    } else {
        include __DIR__ . '/../pages/dashboard.php';
    }
}

// Get current page
function getCurrentPage() {
    return isset($_GET['page']) ? $_GET['page'] : 'dashboard';
}

// Check if page requires authentication
function requiresAuth($page) {
    $public_pages = ['login', 'register'];
    return !in_array($page, $public_pages);
}

// Generate URL with parameters
function url($page = '', $params = []) {
    $url = 'index.php';
    $query_params = [];
    
    if (!empty($page)) {
        $query_params['page'] = $page;
    }
    
    if (!empty($params)) {
        $query_params = array_merge($query_params, $params);
    }
    
    if (!empty($query_params)) {
        $url .= '?' . http_build_query($query_params);
    }
    
    return $url;
}

// Check if current page is active
function isActivePage($page) {
    return getCurrentPage() === $page;
}
?>