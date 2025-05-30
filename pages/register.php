<?php
// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = sanitizeInput($_POST['nama'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (!empty($nama) && !empty($email) && !empty($password) && !empty($confirm_password)) {
        if ($password === $confirm_password) {
            if (strlen($password) >= 6) {
                $auth = new Auth();
                if ($auth->register($nama, $email, $password)) {
                    $_SESSION['success_message'] = __('register_success');
                    header('Location: index.php?page=login');
                    exit;
                } else {
                    $error_message = __('register_failed') . ' Email might already exist.';
                }
            } else {
                $error_message = 'Password must be at least 6 characters long';
            }
        } else {
            $error_message = 'Passwords do not match';
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
    <title><?php echo __('register'); ?> - <?php echo __('file_management_system'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-white text-2xl"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900"><?php echo __('register_title'); ?></h2>
                <p class="mt-2 text-sm text-gray-600"><?php echo __('file_management_system'); ?></p>
            </div>

            <!-- Language Selector -->
            <div class="flex justify-center">
                <select onchange="changeLanguage(this.value)" class="appearance-none bg-white border border-gray-300 rounded-md py-2 pl-3 pr-8 text-sm leading-5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="en" <?php echo $language->getCurrentLang() == 'en' ? 'selected' : ''; ?>><?php echo __('english'); ?></option>
                    <option value="id" <?php echo $language->getCurrentLang() == 'id' ? 'selected' : ''; ?>><?php echo __('indonesian'); ?></option>
                </select>
            </div>

            <!-- Registration Form -->
            <form class="mt-8 space-y-6" method="POST" id="registerForm">
                <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700"><?php echo __('name'); ?></label>
                        <div class="mt-1 relative">
                            <input id="nama" name="nama" type="text" required 
                                   class="appearance-none relative block w-full px-3 py-3 pl-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10"
                                   placeholder="<?php echo __('name'); ?>" value="<?php echo isset($nama) ? htmlspecialchars($nama) : ''; ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                    </div>

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
                                   placeholder="<?php echo __('password'); ?>" onkeyup="checkPasswordStrength(this.value)">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Password Strength</span>
                                <span id="passwordStrengthLabel"></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div id="passwordStrength" class="h-2 rounded-full transition-all duration-300 bg-gray-300" style="width: 0%"></div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700"><?php echo __('confirm_password'); ?></label>
                        <div class="mt-1 relative">
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                   class="appearance-none relative block w-full px-3 py-3 pl-10 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10"
                                   placeholder="<?php echo __('confirm_password'); ?>">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-user-plus text-blue-500 group-hover:text-blue-400"></i>
                        </span>
                        <?php echo __('register'); ?>
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        <?php echo __('already_have_account'); ?>
                        <a href="<?php echo url('login'); ?>" class="font-medium text-blue-600 hover:text-blue-500"><?php echo __('login'); ?></a>
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

        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            const indicator = document.getElementById('passwordStrength');
            if (indicator) {
                const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
                const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
                
                indicator.className = `h-2 rounded transition-all duration-300 ${colors[strength - 1] || 'bg-gray-300'}`;
                indicator.style.width = `${(strength / 5) * 100}%`;
                
                const label = document.getElementById('passwordStrengthLabel');
                if (label) {
                    label.textContent = labels[strength - 1] || '';
                }
            }
        }

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>