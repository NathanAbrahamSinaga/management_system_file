<!-- Sidebar -->
<div id="sidebar" class="sidebar sidebar-transition fixed inset-y-0 left-0 z-30 w-full sm:w-64 bg-gray-800 transform sm:transform-none sm:opacity-100 sm:relative sm:translate-x-0">
    <div class="flex flex-col h-full">
        <!-- Navigation Menu -->
        <nav class="flex-1 px-2 sm:px-4 py-6 space-y-2">
            <!-- Dashboard -->
            <a href="<?php echo url('dashboard'); ?>" 
               class="<?php echo isActivePage('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> 
                      group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-tachometer-alt mr-3"></i>
                <?php echo __('dashboard'); ?>
            </a>
            
            <!-- Documents -->
            <a href="<?php echo url('documents'); ?>" 
               class="<?php echo isActivePage('documents') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> 
                      group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-file-alt mr-3"></i>
                <?php echo __('documents'); ?>
            </a>
            
            <!-- Folders -->
            <a href="<?php echo url('folders'); ?>" 
               class="<?php echo isActivePage('folders') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> 
                      group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-folder mr-3"></i>
                <?php echo __('folders'); ?>
            </a>
            
            <!-- Categories -->
            <a href="<?php echo url('categories'); ?>" 
               class="<?php echo isActivePage('categories') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> 
                      group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-tags mr-3"></i>
                <?php echo __('categories'); ?>
            </a>
            
            <!-- Users (Admin only) -->
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
            <a href="<?php echo url('users'); ?>" 
               class="<?php echo isActivePage('users') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> 
                      group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-users mr-3"></i>
                <?php echo __('users'); ?>
            </a>
            <?php endif; ?>
            
            <!-- Divider -->
            <div class="border-t border-gray-700 my-4"></div>
            
            <!-- Profile -->
            <a href="<?php echo url('profile'); ?>" 
               class="<?php echo isActivePage('profile') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'; ?> 
                      group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-user mr-3"></i>
                <?php echo __('profile'); ?>
            </a>
            
            <!-- Logout -->
            <a href="<?php echo url('logout'); ?>" 
               class="text-gray-300 hover:bg-red-600 hover:text-white group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200">
                <i class="fas fa-sign-out-alt mr-3"></i>
                <?php echo __('logout'); ?>
            </a>
        </nav>
        
        <!-- Sidebar Footer -->
        <div class="flex-shrink-0 p-4 border-t border-gray-700">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-8 w-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white truncate max-w-[150px]">
                        <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest'; ?>
                    </p>
                    <p class="text-xs text-gray-400">
                        <?php echo isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Guest'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overlay for mobile -->
<div id="sidebarOverlay" class="fixed inset-0 z-20 bg-black bg-opacity-50 sm:hidden hidden"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    function toggleSidebar() {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('hidden');
    }
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    if (overlay) {
        overlay.addEventListener('click', toggleSidebar);
    }

    // Close sidebar when a menu item is clicked on mobile
    sidebar.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 640) {
                toggleSidebar();
            }
        });
    });
});
</script>