<!-- Sidebar -->
<div id="sidebar"
    class="sidebar sidebar-transition fixed inset-y-0 left-0 z-30 w-full sm:w-64 bg-black border-r border-gray-800 shadow-lg transform sm:transform-none sm:opacity-100 sm:relative sm:translate-x-0">
    <div class="flex flex-col h-full">
        <!-- Logo/Brand -->
        <div class="flex items-center justify-center h-16 px-4 border-b border-gray-800">
            <div class="flex items-center space-x-2">
                <div
                    class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-archive text-white text-sm"></i>
                </div>
                <span class="text-lg font-semibold text-white">SMD Integrasi</span>
                <span class="bg-slate-700 text-white text-xs font-semibold px-2 py-0.5 rounded-md">v1.1</span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <!-- Main Navigation -->
            <div class="space-y-1">
                <!-- Dashboard -->
                <a href="<?php echo url('dashboard'); ?>"
                    class="<?php echo isActivePage('dashboard') ? 'bg-blue-900/50 border-r-2 border-blue-500 text-blue-400' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> 
                          group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                    <div class="<?php echo isActivePage('dashboard') ? 'bg-blue-900 text-blue-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700'; ?> 
                                w-6 h-6 rounded-md flex items-center justify-center mr-3 transition-colors">
                        <i class="fas fa-home text-xs"></i>
                    </div>
                    <span class="truncate"><?php echo __('dashboard'); ?></span>
                </a>

                <!-- Documents -->
                <a href="<?php echo url('documents'); ?>"
                    class="<?php echo isActivePage('documents') ? 'bg-blue-900/50 border-r-2 border-blue-500 text-blue-400' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> 
                          group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                    <div class="<?php echo isActivePage('documents') ? 'bg-blue-900 text-blue-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700'; ?> 
                                w-6 h-6 rounded-md flex items-center justify-center mr-3 transition-colors">
                        <i class="fas fa-file-alt text-xs"></i>
                    </div>
                    <span class="truncate"><?php echo __('documents'); ?></span>
                </a>

                <!-- Folders -->
                <a href="<?php echo url('folders'); ?>"
                    class="<?php echo isActivePage('folders') ? 'bg-blue-900/50 border-r-2 border-blue-500 text-blue-400' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> 
                          group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                    <div class="<?php echo isActivePage('folders') ? 'bg-blue-900 text-blue-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700'; ?> 
                                w-6 h-6 rounded-md flex items-center justify-center mr-3 transition-colors">
                        <i class="fas fa-folder text-xs"></i>
                    </div>
                    <span class="truncate"><?php echo __('folders'); ?></span>
                </a>

                <!-- Categories -->
                <a href="<?php echo url('categories'); ?>"
                    class="<?php echo isActivePage('categories') ? 'bg-blue-900/50 border-r-2 border-blue-500 text-blue-400' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> 
                          group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                    <div class="<?php echo isActivePage('categories') ? 'bg-blue-900 text-blue-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700'; ?> 
                                w-6 h-6 rounded-md flex items-center justify-center mr-3 transition-colors">
                        <i class="fas fa-tags text-xs"></i>
                    </div>
                    <span class="truncate"><?php echo __('categories'); ?></span>
                </a>

                <!-- Users (Admin only) -->
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
                    <a href="<?php echo url('users'); ?>"
                        class="<?php echo isActivePage('users') ? 'bg-blue-900/50 border-r-2 border-blue-500 text-blue-400' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> 
                          group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                        <div class="<?php echo isActivePage('users') ? 'bg-blue-900 text-blue-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700'; ?> 
                                w-6 h-6 rounded-md flex items-center justify-center mr-3 transition-colors">
                            <i class="fas fa-users text-xs"></i>
                        </div>
                        <span class="truncate"><?php echo __('users'); ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-800 my-4"></div>

            <!-- Secondary Navigation -->
            <div class="space-y-1">
                <!-- Profile -->
                <a href="<?php echo url('profile'); ?>"
                    class="<?php echo isActivePage('profile') ? 'bg-blue-900/50 border-r-2 border-blue-500 text-blue-400' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?> 
                          group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                    <div class="<?php echo isActivePage('profile') ? 'bg-blue-900 text-blue-400' : 'bg-gray-800 text-gray-400 group-hover:bg-gray-700'; ?> 
                                w-6 h-6 rounded-md flex items-center justify-center mr-3 transition-colors">
                        <i class="fas fa-user text-xs"></i>
                    </div>
                    <span class="truncate"><?php echo __('profile'); ?></span>
                </a>

                <!-- Logout -->
                <a href="<?php echo url('logout'); ?>"
                    class="text-gray-300 hover:bg-red-900/30 hover:text-red-400 group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 ease-in-out">
                    <div
                        class="bg-gray-800 text-gray-400 group-hover:bg-red-900/50 group-hover:text-red-400 w-6 h-6 rounded-md flex items-center justify-center mr-3 transition-colors">
                        <i class="fas fa-sign-out-alt text-xs"></i>
                    </div>
                    <span class="truncate"><?php echo __('logout'); ?></span>
                </a>
            </div>
        </nav>

        <!-- Sidebar Footer (Hidden on Mobile) -->
        <div class="flex-shrink-0 p-4 border-t border-gray-800 bg-black hidden sm:block">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div
                        class="h-10 w-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-sm">
                        <span class="text-white text-sm font-medium">
                            <?php echo isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'G'; ?>
                        </span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">
                        <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Guest'; ?>
                    </p>
                    <p class="text-xs text-gray-400 truncate">
                        <?php echo isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'Guest'; ?>
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <button class="text-gray-400 hover:text-gray-300 transition-colors">
                        <i class="fas fa-ellipsis-v text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Button for Closed Sidebar -->
<button id="sidebarOpenBtn"
    class="fixed top-4 left-4 z-20 p-2 rounded-md bg-blue-600 text-white shadow-lg sm:hidden transform transition-transform duration-300 ease-in-out"
    data-toggle="tooltip" data-placement="right" title="Open sidebar">
    <span class="sr-only">Open sidebar</span>
    <i class="fas fa-list fa-fw"></i>
</button>

<!-- Overlay for mobile -->
<div id="sidebarOverlay"
    class="fixed inset-0 z-20 bg-black bg-opacity-50 sm:hidden hidden transition-opacity duration-300"></div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const sidebarToggle = document.getElementById('sidebarToggle');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');

            const openBtn = document.getElementById('sidebarOpenBtn');

            // Add smooth animation
            if (sidebar.classList.contains('open')) {
                sidebar.style.transform = 'translateX(0)';
                if (openBtn) openBtn.classList.add('opacity-0');
            } else {
                sidebar.style.transform = 'translateX(-100%)';
                if (openBtn) openBtn.classList.remove('opacity-0');
            }
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        const sidebarOpenBtn = document.getElementById('sidebarOpenBtn');
        if (sidebarOpenBtn) {
            sidebarOpenBtn.addEventListener('click', toggleSidebar);
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

        // Handle window resize
        window.addEventListener('resize', () => {
            const openBtn = document.getElementById('sidebarOpenBtn');

            if (window.innerWidth >= 640) {
                sidebar.classList.remove('open');
                overlay.classList.add('hidden');
                sidebar.style.transform = '';
                if (openBtn) openBtn.classList.add('hidden');
            } else {
                if (openBtn && !sidebar.classList.contains('open')) {
                    openBtn.classList.remove('hidden');
                    openBtn.classList.remove('opacity-0');
                }
            }
        });

        // Initialize button visibility
        const openBtn = document.getElementById('sidebarOpenBtn');
        if (openBtn) {
            if (window.innerWidth >= 640 || sidebar.classList.contains('open')) {
                openBtn.classList.add('opacity-0');
            }
        }
    });
</script>