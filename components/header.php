<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('file_management_system'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }
        .content-transition {
            transition: margin-left 0.3s ease-in-out;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-md fixed w-full top-0 z-40">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="flex-shrink-0 flex items-center ml-4 md:ml-0">
                        <h1 class="text-xl font-semibold text-gray-900"><?php echo __('file_management_system'); ?></h1>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Language Selector -->
                    <div class="relative">
                        <select onchange="changeLanguage(this.value)" class="appearance-none bg-white border border-gray-300 rounded-md py-2 pl-3 pr-8 text-sm leading-5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="en" <?php echo $language->getCurrentLang() == 'en' ? 'selected' : ''; ?>><?php echo __('english'); ?></option>
                            <option value="id" <?php echo $language->getCurrentLang() == 'id' ? 'selected' : ''; ?>><?php echo __('indonesian'); ?></option>
                        </select>
                    </div>
                    
                    <!-- User Menu -->
                    <?php if (isset($_SESSION['user_name'])): ?>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <div class="relative">
                            <button onclick="toggleUserMenu()" class="flex items-center p-2 rounded-full bg-gray-200 text-gray-600 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-user"></i>
                            </button>
                            <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                <a href="<?php echo url('profile'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user mr-2"></i><?php echo __('profile'); ?>
                                </a>
                                <a href="<?php echo url('logout'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i><?php echo __('logout'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function changeLanguage(lang) {
            const url = new URL(window.location);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }

        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('mainContent');
            sidebar.classList.toggle('open');
        }

        document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);

        // Close user menu when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const userButton = event.target.closest('button');
            
            if (!userButton || !userButton.onclick || userButton.onclick.toString().indexOf('toggleUserMenu') === -1) {
                userMenu.classList.add('hidden');
            }
        });
    </script>