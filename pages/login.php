<?php
// Handle login form submission | updated v1.0.1
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $auth = new Auth();
        if ($auth->login($email, $password)) {
            $_SESSION['success_message'] = __('login_success');
            header('Location: index.php?page=dashboard');
            exit;
        } else {
            $error_message = __('login_failed');
        }
    } else {
        $error_message = 'Please fill in all fields';
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $language->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('login'); ?> - <?php echo __('file_management_system'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .login-bg {
            background-color: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .login-bg::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(13, 122, 255, 0.3) 0%, rgba(96, 165, 250, 0) 70%);
            top: -100px;
            left: -100px;
            z-index: 0;
            animation: pulse 8s infinite alternate;
        }

        .login-bg::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(169, 85, 247, 0.25) 0%, rgba(168, 85, 247, 0) 70%);
            bottom: -50px;
            right: -50px;
            z-index: 0;
            animation: pulse 8s infinite alternate-reverse;
        }

        @keyframes pulse {
            0% {
                opacity: 0.5;
                transform: scale(1);
            }

            100% {
                opacity: 0.8;
                transform: scale(1.1);
            }
        }

        .grid-pattern {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(226, 232, 240, 0.3) 1px, transparent 1px),
                linear-gradient(90deg, rgba(226, 232, 240, 0.3) 1px, transparent 1px);
            background-size: 20px 20px;
            z-index: 1;
            opacity: 0.4;
        }

        .glass-effect {
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(226, 232, 240, 0.7);
            transform: rotate(-3deg);
            transition: transform 0.5s ease;
        }

        .glass-effect:hover,
        .glass-effect:focus-within {
            transform: rotate(0deg);
        }

        .input-transition {
            transition: all 0.3s ease;
        }

        .input-transition:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.25);
        }

        .floating-shape {
            position: absolute;
            border-radius: 8px;
            background-color: rgba(226, 232, 240, 0.5);
            z-index: 0;
            transition: transform 0.5s ease;
        }

        .floating-shape:nth-child(odd) {
            animation: float 6s infinite alternate ease-in-out;
        }

        .floating-shape:nth-child(even) {
            animation: float 8s infinite alternate-reverse ease-in-out;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-10px) rotate(5deg);
            }

            100% {
                transform: translateY(10px) rotate(-5deg);
            }
        }
    </style>
</head>

<body class="login-bg min-h-screen">
    <div class="grid-pattern"></div>
    <!-- Floating shapes -->
    <div class="floating-shape w-16 h-16 top-32 left-1/4 rotate-12"></div>
    <div class="floating-shape w-24 h-24 bottom-20 left-1/3 rotate-45"></div>
    <div class="floating-shape w-12 h-12 top-1/3 right-1/4 -rotate-12"></div>
    <div class="floating-shape w-20 h-20 top-2/3 right-1/3 rotate-6"></div>

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-md w-full">
            <!-- Language Selector -->
            <div class="absolute top-4 right-4 z-10">
                <div class="relative inline-block">
                    <button id="languageToggle"
                        class="flex items-center space-x-2 bg-white/80 backdrop-blur-sm px-4 py-2 rounded-full shadow-lg border border-gray-100 hover:bg-white transition-all duration-300 focus:outline-none">
                        <div class="w-5 h-5 rounded-full overflow-hidden flex-shrink-0">
                            <?php if ($language->getCurrentLang() == 'en'): ?>
                                <div
                                    class="w-full h-full bg-gradient-to-r from-blue-500 via-white to-red-500 flex items-center justify-center">
                                    <span class="text-[8px] font-bold">EN</span>
                                </div>
                            <?php else: ?>
                                <div
                                    class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                                    <span class="text-[8px] font-bold">ID</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <span class="text-sm font-medium text-gray-700">
                            <?php echo $language->getCurrentLang() == 'en' ? 'English' : 'Indonesia'; ?>
                        </span>
                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                    </button>

                    <div id="languageDropdown"
                        class="absolute right-0 mt-2 w-40 bg-white rounded-xl shadow-xl border border-gray-100 py-2 hidden transform transition-all duration-300 scale-95 opacity-0">
                        <a href="javascript:void(0)" onclick="changeLanguage('en')"
                            class="flex items-center space-x-3 px-4 py-2 text-sm hover:bg-blue-50 transition-colors <?php echo $language->getCurrentLang() == 'en' ? 'text-blue-600 bg-blue-50/50' : 'text-gray-700'; ?>">
                            <div class="w-5 h-5 rounded-full overflow-hidden flex-shrink-0">
                                <div
                                    class="w-full h-full bg-gradient-to-r from-blue-500 via-white to-red-500 flex items-center justify-center">
                                    <span class="text-[8px] font-bold">EN</span>
                                </div>
                            </div>
                            <span>English</span>
                            <?php if ($language->getCurrentLang() == 'en'): ?>
                                <i class="fas fa-check ml-auto text-blue-600 text-xs"></i>
                            <?php endif; ?>
                        </a>
                        <a href="javascript:void(0)" onclick="changeLanguage('id')"
                            class="flex items-center space-x-3 px-4 py-2 text-sm hover:bg-blue-50 transition-colors <?php echo $language->getCurrentLang() == 'id' ? 'text-blue-600 bg-blue-50/50' : 'text-gray-700'; ?>">
                            <div class="w-5 h-5 rounded-full overflow-hidden flex-shrink-0">
                                <div
                                    class="w-full h-full bg-gradient-to-r from-red-500 via-white to-red-500 flex items-center justify-center">
                                    <span class="text-[8px] font-bold">ID</span>
                                </div>
                            </div>
                            <span>Indonesia</span>
                            <?php if ($language->getCurrentLang() == 'id'): ?>
                                <i class="fas fa-check ml-auto text-blue-600 text-xs"></i>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Login Card -->
            <div class="glass-effect rounded-3xl shadow-2xl overflow-hidden">
                <!-- Header -->
                <div class="px-8 pt-8 pb-4 text-center relative">
                    <div
                        class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500">
                    </div>
                    <div
                        class="mx-auto h-20 w-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center transform -rotate-6 shadow-xl">
                        <i class="fas fa-file-alt text-white text-3xl"></i>
                    </div>
                    <h2 class="mt-6 text-3xl font-bold text-gray-900"><?php echo __('login_title'); ?></h2>
                    <p class="mt-2 text-sm text-gray-600"><?php echo __('file_management_system'); ?></p>
                </div>

                <!-- Login Form -->
                <div class="p-8 pt-4">
                    <form class="space-y-6" method="POST">
                        <?php if (isset($error_message)): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm"><?php echo $error_message; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="space-y-5">
                            <div>
                                <div class="relative">
                                    <input id="email" name="email" type="email" required
                                        class="input-transition appearance-none block w-full px-5 py-4 pl-12 border border-gray-200 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                        placeholder="<?php echo __('email'); ?>"
                                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-blue-500"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="relative">
                                    <input id="password" name="password" type="password" required
                                        class="input-transition appearance-none block w-full px-5 py-4 pl-12 border border-gray-200 rounded-xl placeholder-gray-400 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                                        placeholder="<?php echo __('password'); ?>">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-blue-500"></i>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer"
                                        onclick="togglePassword()">
                                        <i id="passwordToggleIcon"
                                            class="fas fa-eye text-gray-400 hover:text-blue-500 transition-colors"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input id="remember_me" name="remember_me" type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="remember_me"
                                    class="ml-2 block text-sm text-gray-700"><?php echo __('remember_me'); ?></label>
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                <?php echo __('login'); ?>
                                <span class="absolute right-4 inset-y-0 flex items-center">
                                    <i
                                        class="fas fa-arrow-right text-white group-hover:translate-x-1 transition-transform"></i>
                                </span>
                            </button>
                        </div>

                        <div class="text-center mt-6">
                            <p class="text-sm text-gray-600">
                                <?php echo __('dont_have_account'); ?>
                                <a href="<?php echo url('register'); ?>"
                                    class="font-medium text-blue-600 hover:text-blue-500 transition-colors"><?php echo __('register'); ?></a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500">
                    &copy; Copyright SMD <?php echo date('Y'); ?> All Rights Reserved
                </p>
            </div>
        </div>
    </div>

    <script>
        function changeLanguage(lang) {
            const url = new URL(window.location);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordToggleIcon = document.getElementById('passwordToggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggleIcon.classList.remove('fa-eye');
                passwordToggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggleIcon.classList.remove('fa-eye-slash');
                passwordToggleIcon.classList.add('fa-eye');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const languageToggle = document.getElementById('languageToggle');
            const languageDropdown = document.getElementById('languageDropdown');

            languageToggle.addEventListener('click', function () {
                if (languageDropdown.classList.contains('hidden')) {
                    // Show dropdown
                    languageDropdown.classList.remove('hidden');
                    setTimeout(() => {
                        languageDropdown.classList.remove('scale-95', 'opacity-0');
                        languageDropdown.classList.add('scale-100', 'opacity-100');
                    }, 10);
                } else {
                    // Hide dropdown
                    languageDropdown.classList.add('scale-95', 'opacity-0');
                    languageDropdown.classList.remove('scale-100', 'opacity-100');
                    setTimeout(() => {
                        languageDropdown.classList.add('hidden');
                    }, 300);
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function (event) {
                if (!languageToggle.contains(event.target) && !languageDropdown.contains(event.target)) {
                    languageDropdown.classList.add('scale-95', 'opacity-0');
                    languageDropdown.classList.remove('scale-100', 'opacity-100');
                    setTimeout(() => {
                        languageDropdown.classList.add('hidden');
                    }, 300);
                }
            });
        });
    </script>
</body>

</html>