<?php
ob_start();
$database = new Database();
$db = $database->getConnection();
$auth = new Auth();

if (!$auth->hasPermission('Admin')) {
    $_SESSION['error_message'] = __('permission_denied');
    header('Location: index.php?page=dashboard');
    exit;
}

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=users');
        exit;
    }

    $nama = sanitizeInput($_POST['nama']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    if (!empty($nama) && !empty($email) && !empty($password) && !empty($confirm_password) && in_array($role, ['Admin', 'User', 'Viewer'])) {
        if ($password === $confirm_password) {
            if (strlen($password) >= 6) {
                if ($auth->register($nama, $email, $password, $role)) {
                    $_SESSION['success_message'] = __('add_user') . ' ' . __('success');
                } else {
                    $_SESSION['error_message'] = __('add_user') . ' ' . __('failed') . ' Email might already exist.';
                }
            } else {
                $_SESSION['error_message'] = 'Password must be at least 6 characters long';
            }
        } else {
            $_SESSION['error_message'] = 'Passwords do not match';
        }
    } else {
        $_SESSION['error_message'] = 'Please fill in all fields';
    }
    header('Location: index.php?page=users');
    exit;
}

// Handle user edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=users');
        exit;
    }

    $id_user = (int)$_POST['id_user'];
    $nama = sanitizeInput($_POST['nama']);
    $email = sanitizeInput($_POST['email']);
    $role = $_POST['role'];
    
    if ($id_user !== $_SESSION['user_id'] && !empty($nama) && !empty($email) && in_array($role, ['Admin', 'User', 'Viewer'])) {
        try {
            $query = "SELECT email FROM User WHERE email = :email AND id_user != :id_user";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $_SESSION['error_message'] = 'Email already exists';
                header('Location: index.php?page=users');
                exit;
            }

            $query = "UPDATE User SET nama = :nama, email = :email, role = :role WHERE id_user = :id_user";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama', $nama);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = __('update_success');
            } else {
                $_SESSION['error_message'] = __('update_failed');
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = __('update_failed') . ': ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = 'Cannot edit your own account or invalid data';
    }
    header('Location: index.php?page=users');
    exit;
}

// Handle user deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_user = (int)$_GET['id'];
    if ($id_user !== $_SESSION['user_id']) { // Prevent self-deletion
        try {
            $query = "DELETE FROM User WHERE id_user = :id_user";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = __('delete_success');
            } else {
                $_SESSION['error_message'] = __('delete_failed');
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = __('delete_failed') . ': ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = 'Cannot delete your own account';
    }
    header('Location: index.php?page=users');
    exit;
}

// Fetch all users
try {
    $query = "SELECT * FROM User ORDER BY nama";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $users = [];
    $_SESSION['error_message'] = 'Failed to fetch users: ' . $e->getMessage();
}

// Fetch user data for edit
$edit_user = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_user = (int)$_GET['id'];
    try {
        $query = "SELECT * FROM User WHERE id_user = :id_user";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Failed to fetch user: ' . $e->getMessage();
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-center">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900"><?php echo __('user_list'); ?></h2>
            <a href="#addUserModal" class="mt-2 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                <i class="fas fa-user-plus mr-2"></i><?php echo __('add_user'); ?>
            </a>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center overflow-y-auto">
        <div class="bg-white rounded-lg p-4 sm:p-6 max-w-md w-full m-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('add_user'); ?></h3>
            <form method="POST" id="addUserForm">
                <input type="hidden" name="action" value="add_user">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="space-y-4">
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700"><?php echo __('user_name'); ?></label>
                        <input id="nama" name="nama" type="text" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700"><?php echo __('user_email'); ?></label>
                        <input id="email" name="email" type="email" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700"><?php echo __('password'); ?></label>
                        <input id="password" name="password" type="password" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
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
                        <input id="confirm_password" name="confirm_password" type="password" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700"><?php echo __('user_role'); ?></label>
                        <select id="role" name="role" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="Admin"><?php echo __('admin'); ?></option>
                            <option value="User"><?php echo __('user'); ?></option>
                            <option value="Viewer"><?php echo __('viewer'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('addUserModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('submit'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <?php if ($edit_user): ?>
    <div id="editUserModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center overflow-y-auto">
        <div class="bg-white rounded-lg p-4 sm:p-6 max-w-md w-full m-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('edit') . ' ' . __('user'); ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="id_user" value="<?php echo $edit_user['id_user']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="space-y-4">
                    <div>
                        <label for="edit_nama" class="block text-sm font-medium text-gray-700"><?php echo __('user_name'); ?></label>
                        <input id="edit_nama" name="nama" type="text" required value="<?php echo htmlspecialchars($edit_user['nama']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="edit_email" class="block text-sm font-medium text-gray-700"><?php echo __('user_email'); ?></label>
                        <input id="edit_email" name="email" type="email" required value="<?php echo htmlspecialchars($edit_user['email']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="edit_role" class="block text-sm font-medium text-gray-700"><?php echo __('user_role'); ?></label>
                        <select id="edit_role" name="role" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="Admin" <?php echo $edit_user['role'] === 'Admin' ? 'selected' : ''; ?>><?php echo __('admin'); ?></option>
                            <option value="User" <?php echo $edit_user['role'] === 'User' ? 'selected' : ''; ?>><?php echo __('user'); ?></option>
                            <option value="Viewer" <?php echo $edit_user['role'] === 'Viewer' ? 'selected' : ''; ?>><?php echo __('viewer'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editUserModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="mt-4">
                <input type="text" onkeyup="searchTable(this, 'usersTable')" placeholder="<?php echo __('search'); ?>..." class="w-full md:w-64 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mt-4 overflow-x-auto">
                <table id="usersTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 hidden sm:table-header-group">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('user_name'); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('user_email'); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('user_role'); ?></th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500"><?php echo __('no_data'); ?></td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr class="block sm:table-row border-b sm:border-0">
                            <td class="block sm:table-cell px-6 py-2 sm:py-4 text-sm font-medium text-gray-900 before:content-['Name:'] before:font-bold before:mr-2 sm:before:content-none"><?php echo htmlspecialchars($user['nama']); ?></td>
                            <td class="block sm:table-cell px-6 py-2 sm:py-4 text-sm text-gray-500 before:content-['Email:'] before:font-bold before:mr-2 sm:before:content-none"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td class="block sm:table-cell px-6 py-2 sm:py-4 text-sm text-gray-500 before:content-['Role:'] before:font-bold before:mr-2 sm:before:content-none"><?php echo htmlspecialchars($user['role']); ?></td>
                            <td class="block sm:table-cell px-6 py-2 sm:py-4 text-sm font-medium text-right">
                                <a href="<?php echo url('users', ['action' => 'edit', 'id' => $user['id_user']]); ?>" class="text-blue-600 hover:text-blue-500 mr-3"><i class="fas fa-edit"></i> <?php echo __('edit'); ?></a>
                                <a href="<?php echo url('users', ['action' => 'delete', 'id' => $user['id_user']]); ?>" onclick="return confirmDelete('<?php echo __('delete_confirmation'); ?>')" class="text-red-600 hover:text-red-500"><i class="fas fa-trash"></i> <?php echo __('delete'); ?></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

document.querySelector('a[href="#addUserModal"]').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('addUserModal').classList.remove('hidden');
});

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;

    const indicator = document.getElementById('passwordStrength');
    const label = document.getElementById('passwordStrengthLabel');
    if (indicator && label) {
        const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];
        const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        
        indicator.className = `h-2 rounded-full transition-all duration-300 ${colors[strength - 1] || 'bg-gray-300'}`;
        indicator.style.width = `${(strength / 5) * 100}%`;
        label.textContent = labels[strength - 1] || '';
    }
}

function searchTable(input, tableId) {
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 0; j < cells.length; j++) {
            if (cells[j] && cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
}

// Form validation
document.getElementById('addUserForm').addEventListener('submit', function(e) {
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

<style>
/* Mobile responsive table styles */
@media (max-width: 640px) {
    #usersTable tbody tr {
        display: block;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #f9fafb;
        border-radius: 0.5rem;
    }
    #usersTable tbody td {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
    }
    #usersTable tbody td:before {
        flex: 1;
        color: #4b5563;
    }
    #usersTable tbody td:last-child {
        justify-content: flex-end;
    }
}
</style>
<?php ob_end_flush(); ?>