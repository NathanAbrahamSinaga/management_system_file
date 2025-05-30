<?php
// Handle login form submission
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
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-alt text-white text-2xl"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900"><?php echo __('login_title'); ?></h2>
                <p class="mt-2 text-sm text-gray-600"><?php echo __('file_management_system'); ?></p>
            </div>

            <!-- Language Selector -->
            <div class="flex justify-center">
                <select onchange="changeLanguage(this.value)" class="appearance-none bg-white border border-gray-300 rounded-md py-2 pl-3 pr-8 text-sm leading-5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="en" <?php echo $language->getCurrentLang() == 'en' ? 'selected' : ''; ?>><?php echo __('english'); ?></option>
                    <option value="id" <?php echo $language->getCurrentLang() == 'id' ? 'selected' : ''; ?>><?php echo __('indonesian'); ?></option>
                </select>
            </div>

            <!-- Login Form -->
            <form class="mt-8 space-y-6" method="POST">
                <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700"><?php echo __('email'); ?></label>
                        <div class="mt-1 relative">
                            <input id="email" name="email" type="email" required 
                                   class="appearance-none relative block w-full px-3 py-3 pl-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10"
                                   placeholder="<?php echo __('email'); ?>" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700"><?php echo __('password'); ?></label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password" required 
                                   class="appearance-none relative block w-full px-3 py-3 pl-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10"
                                   placeholder="<?php echo __('password'); ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" 
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900"><?php echo __('remember_me'); ?></label>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-blue-500 group-hover:text-blue-400"></i>
                        </span>
                        <?php echo __('login'); ?>
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        <?php echo __('dont_have_account'); ?>
                        <a href="<?php echo url('register'); ?>" class="font-medium text-blue-600 hover:text-blue-500"><?php echo __('register'); ?></a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script>
        function changeLanguage(lang) {
            const url = new URL(window.location);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>