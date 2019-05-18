<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('file_management_system'); ?> - <?php echo ucfirst(getCurrentPage()); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar-transition {
            transition: transform 0.3s ease-in-out;
        }

        .content-transition {
            transition: margin-left 0.3s ease-in-out;
        }

        .language-selector {
            min-width: 100px;
        }

        /* Hamburger Menu Animation */
        .hamburger-menu.active .hamburger-line:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .hamburger-menu.active .hamburger-line:nth-child(2) {
            opacity: 0;
            transform: translateX(-10px);
        }

        .hamburger-menu.active .hamburger-line:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }

        @media (max-width: 640px) {
            .sidebar {
                transform: translateX(-100%);
                width: 100%;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .user-menu {
                width: 200px;
            }

            body {
                padding-bottom: 80px;
                /* Space for floating navigation */
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- Desktop Navigation Bar (Top) -->
    <nav
        class="bg-white/95 backdrop-blur-sm border-b border-gray-200 fixed w-full top-0 z-40 shadow-sm hidden sm:block">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Left Section -->
                <div class="flex items-center space-x-4">
                    <button id="sidebarToggle"
                        class="sm:hidden inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                        <div class="hamburger-menu w-6 h-6 flex flex-col justify-center items-center space-y-1">
                            <span
                                class="hamburger-line block w-5 h-0.5 bg-current transition-all duration-300 ease-in-out"></span>
                            <span
                                class="hamburger-line block w-5 h-0.5 bg-current transition-all duration-300 ease-in-out"></span>
                            <span
                                class="hamburger-line block w-5 h-0.5 bg-current transition-all duration-300 ease-in-out"></span>
                        </div>
                    </button>
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center sm:hidden">
                            <i class="fas fa-folder-open text-white text-sm"></i>
                        </div>
                        <h1 class="text-lg sm:text-xl font-semibold text-gray-900 hidden sm:block">
                            <?php echo __('file_management_system'); ?>
                        </h1>
                        <span class="text-lg font-semibold text-gray-900 sm:hidden">FileManager</span>
                    </div>
                </div>

                <!-- Right Section -->
                <div class="flex items-center space-x-3">
                    <!-- Language Selector -->
                    <div class="relative">
                        <button id="languageToggle"
                            class="inline-flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <div class="w-4 h-4 rounded-full overflow-hidden flex-shrink-0">
                                <?php if ($language->getCurrentLang() == 'en'): ?>
                                    <div
                                        class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                                        <span class="text-xs font-bold text-blue-600">EN</span>
                                    </div>
                                <?php else: ?>
                                    <div
                                        class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                                        <span class="text-xs font-bold text-red-600">ID</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="hidden sm:inline">
                                <?php echo $language->getCurrentLang() == 'en' ? 'English' : 'Indonesia'; ?>
                            </span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>

                        <!-- Language Dropdown -->
                        <div id="languageMenu"
                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                            <button onclick="changeLanguage('en')"
                                class="<?php echo $language->getCurrentLang() == 'en' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> w-full text-left px-4 py-2.5 text-sm flex items-center space-x-3 transition-colors">
                                <div class="w-5 h-5 rounded-full overflow-hidden flex-shrink-0">
                                    <div
                                        class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                                        <span class="text-xs font-bold text-blue-600">EN</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">English</div>
                                    <div class="text-xs text-gray-500">United States</div>
                                </div>
                                <?php if ($language->getCurrentLang() == 'en'): ?>
                                    <i class="fas fa-check text-blue-600 text-sm"></i>
                                <?php endif; ?>
                            </button>
                            <button onclick="changeLanguage('id')"
                                class="<?php echo $language->getCurrentLang() == 'id' ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-50'; ?> w-full text-left px-4 py-2.5 text-sm flex items-center space-x-3 transition-colors">
                                <div class="w-5 h-5 rounded-full overflow-hidden flex-shrink-0">
                                    <div
                                        class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                                        <span class="text-xs font-bold text-red-600">ID</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">Indonesia</div>
                                    <div class="text-xs text-gray-500">Bahasa Indonesia</div>
                                </div>
                                <?php if ($language->getCurrentLang() == 'id'): ?>
                                    <i class="fas fa-check text-blue-600 text-sm"></i>
                                <?php endif; ?>
                            </button>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <?php if (isset($_SESSION['user_name'])): ?>
                        <div class="flex items-center space-x-3">
                            <div class="hidden md:flex flex-col items-end">
                                <span
                                    class="text-sm font-medium text-gray-900 truncate max-w-[120px]"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                <span class="text-xs text-gray-500"><?php echo $_SESSION['user_role']; ?></span>
                            </div>
                            <div class="relative">
                                <button onclick="toggleUserMenu()"
                                    class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full text-white hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                                    <span class="text-sm font-medium">
                                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                    </span>
                                </button>

                                <!-- User Dropdown -->
                                <div id="userMenu"
                                    class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500"><?php echo $_SESSION['user_role']; ?></p>
                                    </div>
                                    <a href="<?php echo url('profile'); ?>"
                                        class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <div class="w-5 h-5 bg-gray-100 rounded-md flex items-center justify-center mr-3">
                                            <i class="fas fa-user text-xs text-gray-600"></i>
                                        </div>
                                        <span><?php echo __('profile'); ?></span>
                                    </a>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <a href="<?php echo url('logout'); ?>"
                                        class="flex items-center px-4 py-2.5 text-sm text-red-700 hover:bg-red-50 transition-colors">
                                        <div class="w-5 h-5 bg-red-100 rounded-md flex items-center justify-center mr-3">
                                            <i class="fas fa-sign-out-alt text-xs text-red-600"></i>
                                        </div>
                                        <span><?php echo __('logout'); ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Bar (Floating) -->
    <nav class="fixed bottom-3 left-1/2 transform -translate-x-1/2 z-40 sm:hidden">
        <div
            class="flex justify-around items-center h-14 px-4 py-2 bg-white/90 backdrop-blur-md rounded-full shadow-xl border border-gray-200/50 space-x-1">
            <!-- Home Button -->
            <a href="<?php echo url('dashboard'); ?>"
                class="flex items-center justify-center w-12 h-12 rounded-full text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all">
                <i class="fas fa-home text-lg"></i>
            </a>

            <!-- Documents Button -->
            <a href="<?php echo url('documents'); ?>"
                class="flex items-center justify-center w-12 h-12 rounded-full text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all">
                <i class="fas fa-file-alt text-lg"></i>
            </a>

            <!-- Sidebar Toggle Button -->
            <button id="mobileSidebarToggle"
                class="flex items-center justify-center w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full text-white shadow-lg hover:shadow-blue-500/50 transition-all transform hover:scale-110">
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Language Toggle -->
            <button id="mobileLanguageToggle"
                class="flex items-center justify-center w-12 h-12 rounded-full text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all">
                <div class="w-6 h-6 rounded-full overflow-hidden">
                    <?php if ($language->getCurrentLang() == 'en'): ?>
                        <div
                            class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                            <span class="text-xs font-bold text-blue-600">EN</span>
                        </div>
                    <?php else: ?>
                        <div
                            class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                            <span class="text-xs font-bold text-red-600">ID</span>
                        </div>
                    <?php endif; ?>
                </div>
            </button>

            <!-- User Profile -->
            <?php if (isset($_SESSION['user_name'])): ?>
                <button onclick="toggleMobileUserMenu()"
                    class="flex items-center justify-center w-12 h-12 rounded-full text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-all">
                    <div
                        class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-xs font-medium text-white">
                            <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                        </span>
                    </div>
                </button>
            <?php endif; ?>
        </div>

        <!-- Mobile Language Menu -->
        <div id="mobileLangMenu"
            class="hidden fixed bottom-24 left-4 right-4 bg-white rounded-2xl border border-gray-200 shadow-xl z-50">
            <button onclick="changeLanguage('en')"
                class="w-full flex items-center px-4 py-3 <?php echo $language->getCurrentLang() == 'en' ? 'bg-blue-50 text-blue-700' : 'text-gray-700'; ?>">
                <div class="w-5 h-5 rounded-full overflow-hidden mr-3">
                    <div
                        class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                        <span class="text-xs font-bold text-blue-600">EN</span>
                    </div>
                </div>
                <span>English</span>
                <?php if ($language->getCurrentLang() == 'en'): ?>
                    <i class="fas fa-check ml-auto text-blue-600"></i>
                <?php endif; ?>
            </button>
            <button onclick="changeLanguage('id')"
                class="w-full flex items-center px-4 py-3 <?php echo $language->getCurrentLang() == 'id' ? 'bg-blue-50 text-blue-700' : 'text-gray-700'; ?>">
                <div class="w-5 h-5 rounded-full overflow-hidden mr-3">
                    <div
                        class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                        <span class="text-xs font-bold text-red-600">ID</span>
                    </div>
                </div>
                <span>Indonesia</span>
                <?php if ($language->getCurrentLang() == 'id'): ?>
                    <i class="fas fa-check ml-auto text-blue-600"></i>
                <?php endif; ?>
            </button>
        </div>

        <!-- Mobile User Menu -->
        <div id="mobileUserMenu"
            class="hidden fixed bottom-24 left-4 right-4 bg-white rounded-2xl border border-gray-200 shadow-xl z-50">
            <div class="px-4 py-3 border-b border-gray-200">
                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
                <p class="text-xs text-gray-500"><?php echo $_SESSION['user_role']; ?></p>
            </div>
            <a href="<?php echo url('profile'); ?>" class="flex items-center px-4 py-3 text-gray-700">
                <i class="fas fa-user text-gray-500 mr-3"></i>
                <span><?php echo __('profile'); ?></span>
            </a>
            <a href="<?php echo url('logout'); ?>" class="flex items-center px-4 py-3 text-red-700">
                <i class="fas fa-sign-out-alt text-red-500 mr-3"></i>
                <span><?php echo __('logout'); ?></span>
            </a>
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
            const languageMenu = document.getElementById('languageMenu');
            menu.classList.toggle('hidden');
            languageMenu.classList.add('hidden'); // Close language menu
        }

        function toggleLanguageMenu() {
            const menu = document.getElementById('languageMenu');
            const userMenu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
            userMenu.classList.add('hidden'); // Close user menu
        }

        function toggleMobileUserMenu() {
            const menu = document.getElementById('mobileUserMenu');
            const langMenu = document.getElementById('mobileLangMenu');
            menu.classList.toggle('hidden');
            langMenu.classList.add('hidden');
        }

        function toggleMobileLangMenu() {
            const menu = document.getElementById('mobileLangMenu');
            const userMenu = document.getElementById('mobileUserMenu');
            menu.classList.toggle('hidden');
            userMenu.classList.add('hidden');
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const hamburgerMenu = document.querySelector('.hamburger-menu');

            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
            hamburgerMenu.classList.toggle('active');

            // Add smooth animation
            if (sidebar.classList.contains('open')) {
                sidebar.style.transform = 'translateX(0)';
                document.body.style.overflow = 'hidden'; // Prevent scroll when sidebar is open
            } else {
                sidebar.style.transform = 'translateX(-100%)';
                document.body.style.overflow = 'auto';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const languageToggle = document.getElementById('languageToggle');
            const mobileLanguageToggle = document.getElementById('mobileLanguageToggle');
            const overlay = document.getElementById('sidebarOverlay');

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleSidebar();
                });
            }

            if (languageToggle) {
                languageToggle.addEventListener('click', toggleLanguageMenu);
            }

            if (mobileSidebarToggle) {
                mobileSidebarToggle.addEventListener('click', toggleSidebar);
            }

            if (mobileLanguageToggle) {
                mobileLanguageToggle.addEventListener('click', toggleMobileLangMenu);
            }

            // Close sidebar when clicking overlay
            if (overlay) {
                overlay.addEventListener('click', function () {
                    toggleSidebar();
                });
            }

            // Handle window resize
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 640) {
                    const sidebar = document.getElementById('sidebar');
                    const hamburgerMenu = document.querySelector('.hamburger-menu');

                    sidebar.classList.remove('open');
                    overlay.classList.add('hidden');
                    hamburgerMenu.classList.remove('active');
                    sidebar.style.transform = '';
                    document.body.style.overflow = 'auto';
                }
            });
        });

        // Close menus when clicking outside
        document.addEventListener('click', function (event) {
            const userMenu = document.getElementById('userMenu');
            const languageMenu = document.getElementById('languageMenu');
            const mobileUserMenu = document.getElementById('mobileUserMenu');
            const mobileLangMenu = document.getElementById('mobileLangMenu');

            const userButton = event.target.closest('#userMenu, [onclick*="toggleUserMenu"]');
            const languageButton = event.target.closest('#languageMenu, #languageToggle');
            const mobileUserButton = event.target.closest('#mobileUserMenu, [onclick*="toggleMobileUserMenu"]');
            const mobileLangButton = event.target.closest('#mobileLangMenu, #mobileLanguageToggle');

            if (!userButton && userMenu) {
                userMenu.classList.add('hidden');
            }

            if (!languageButton && languageMenu) {
                languageMenu.classList.add('hidden');
            }

            if (!mobileUserButton && mobileUserMenu) {
                mobileUserMenu.classList.add('hidden');
            }

            if (!mobileLangButton && mobileLangMenu) {
                mobileLangMenu.classList.add('hidden');
            }
        });
    </script>