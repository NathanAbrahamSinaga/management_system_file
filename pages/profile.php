<?php
ob_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/language.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth();

$auth->requireLogin();

// Fetch user data
try {
    $query = "SELECT * FROM User WHERE id_user = :id_user";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_user', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $user = null;
    $_SESSION['error_message'] = 'Failed to fetch user data: ' . $e->getMessage();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=profile');
        exit;
    }

    $nama = sanitizeInput($_POST['nama']);
    $email = sanitizeInput($_POST['email']);
    
    if (!empty($nama) && !empty($email)) {
        try {
            $query = "UPDATE User SET nama = :nama, email = :email WHERE id_user = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $nama;
                $_SESSION['user_email'] = $email;
                $_SESSION['success_message'] = __('update_profile') . ' ' . __('success');
            } else {
                $_SESSION['error_message'] = __('update_profile') . ' ' . __('failed');
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = __('update_failed') . ': Email might already exist.';
        }
    } else {
        $_SESSION['error_message'] = 'Please fill in all fields';
    }
    header('Location: index.php?page=profile');
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=profile');
        exit;
    }

    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 8) {
                try {
                    $query = "SELECT password FROM User WHERE id_user = :user_id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (password_verify($current_password, $user['password'])) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $query = "UPDATE User SET password = :password WHERE id_user = :user_id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':password', $hashed_password);
                        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                        if ($stmt->execute()) {
                            $_SESSION['success_message'] = __('change_password') . ' ' . __('success');
                        } else {
                            $_SESSION['error_message'] = __('change_password') . ' ' . __('failed');
                        }
                    } else {
                        $_SESSION['error_message'] = 'Current password is incorrect';
                    }
                } catch(PDOException $e) {
                    $_SESSION['error_message'] = __('change_password') . ' ' . __('failed') . ': ' . $e->getMessage();
                }
            } else {
                $_SESSION['error_message'] = 'New password must be at least 8 characters long';
            }
        } else {
            $_SESSION['error_message'] = 'New passwords do not match';
        }
    } else {
        $_SESSION['error_message'] = 'Please fill in all fields';
    }
    header('Location: index.php?page=profile');
    exit;
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold text-gray-900"><?php echo __('user_profile'); ?></h2>
    </div>

    <!-- Profile Form -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('update_profile'); ?></h3>
        <form method="POST" id="profileForm">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="space-y-4">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700"><?php echo __('name'); ?></label>
                    <input id="nama" name="nama" type="text" required value="<?php echo htmlspecialchars($user['nama'] ?? ''); ?>" 
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700"><?php echo __('email'); ?></label>
                    <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700"><?php echo __('role'); ?></label>
                    <input id="role" type="text" disabled value="<?php echo htmlspecialchars($user['role'] ?? ''); ?>" 
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-gray-100">
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('save'); ?></button>
            </div>
        </form>
    </div>

    <!-- Change Password Form -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('change_password'); ?></h3>
        <form method="POST" id="passwordForm">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="space-y-4">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700"><?php echo __('current_password'); ?></label>
                    <input id="current_password" name="current_password" type="password" required 
                           class="mt-1 block w-full border border-gray-300 rounded-md px3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700"><?php echo __('new_password'); ?></label>
                    <input id="new_password" name="new_password" type="password" required 
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                           onkeyup="checkPasswordStrength(this.value)">
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
                    <input id="confirm_password" name="confirm_password" type="password" required 
                           class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                           onkeyup="checkPasswordMatch()">
                    <span id="passwordMatchError" class="text-red-500 text-sm hidden">Passwords do not match</span>
                </div>
            </div>
            <div class="mt-6 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('save'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function checkPasswordStrength(password) {
    const strengthBar = document.getElementById('passwordStrength');
    const strengthLabel = document.getElementById('passwordStrengthLabel');
    let strength = 0;
    
    if (password.length >= 8) strength += 20;
    if (password.match(/[A-Z]/)) strength += 20;
    if (password.match(/[0-9]/)) strength += 20;
    if (password.match(/[^A-Za-z0-9]/)) strength += 20;
    if (password.length >= 12) strength += 20;
    
    strengthBar.style.width = strength + '%';
    if (strength < 40) {
        strengthBar.classList.remove('bg-yellow-500', 'bg-green-500');
        strengthBar.classList.add('bg-red-500');
        strengthLabel.textContent = 'Weak';
    } else if (strength < 80) {
        strengthBar.classList.remove('bg-red-500', 'bg-green-500');
        strengthBar.classList.add('bg-yellow-500');
        strengthLabel.textContent = 'Medium';
    } else {
        strengthBar.classList.remove('bg-red-500', 'bg-yellow-500');
        strengthBar.classList.add('bg-green-500');
        strengthLabel.textContent = 'Strong';
    }
}

function checkPasswordMatch() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const errorSpan = document.getElementById('passwordMatchError');
    
    if (newPassword !== confirmPassword) {
        errorSpan.classList.remove('hidden');
    } else {
        errorSpan.classList.add('hidden');
    }
}
</script>
<?php ob_end_flush(); ?>