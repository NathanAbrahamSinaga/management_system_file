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

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=categories');
        exit;
    }

    $nama_kategori = sanitizeInput($_POST['nama_kategori']);
    $deskripsi = sanitizeInput($_POST['deskripsi']);
    
    if (!empty($nama_kategori)) {
        try {
            // Check if category name already exists
            $query = "SELECT COUNT(*) as total FROM Kategori WHERE nama_kategori = :nama_kategori";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama_kategori', $nama_kategori);
            $stmt->execute();
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                $_SESSION['error_message'] = 'Category name already exists';
                header('Location: index.php?page=categories');
                exit;
            }

            $query = "INSERT INTO Kategori (nama_kategori, deskripsi) VALUES (:nama_kategori, :deskripsi)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama_kategori', $nama_kategori);
            $stmt->bindParam(':deskripsi', $deskripsi);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = __('create_category') . ' ' . __('success');
            } else {
                $_SESSION['error_message'] = __('create_category') . ' ' . __('failed');
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = __('create_category') . ' ' . __('failed') . ': ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = 'Please fill in the category name';
    }
    header('Location: index.php?page=categories');
    exit;
}

// Handle category edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=categories');
        exit;
    }

    $id_kategori = (int)$_POST['id_kategori'];
    $nama_kategori = sanitizeInput($_POST['nama_kategori']);
    $deskripsi = sanitizeInput($_POST['deskripsi']);
    
    if (!empty($nama_kategori)) {
        try {
            // Check if category name already exists (excluding current category)
            $query = "SELECT COUNT(*) as total FROM Kategori WHERE nama_kategori = :nama_kategori AND id_kategori != :id_kategori";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama_kategori', $nama_kategori);
            $stmt->bindParam(':id_kategori', $id_kategori, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                $_SESSION['error_message'] = 'Category name already exists';
                header('Location: index.php?page=categories');
                exit;
            }

            $query = "UPDATE Kategori SET nama_kategori = :nama_kategori, deskripsi = :deskripsi WHERE id_kategori = :id_kategori";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama_kategori', $nama_kategori);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindParam(':id_kategori', $id_kategori, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = __('update_success');
            } else {
                $_SESSION['error_message'] = __('update_failed');
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = __('update_failed') . ': ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = 'Please fill in the category name';
    }
    header('Location: index.php?page=categories');
    exit;
}

// Handle category deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_kategori = (int)$_GET['id'];
    try {
        $query = "DELETE FROM Kategori WHERE id_kategori = :id_kategori";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_kategori', $id_kategori, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = __('delete_success');
        } else {
            $_SESSION['error_message'] = __('delete_failed');
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = __('delete_failed') . ': ' . $e->getMessage();
    }
    header('Location: index.php?page=categories');
    exit;
}

// Fetch all categories
try {
    $query = "SELECT * FROM Kategori ORDER BY nama_kategori";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categories = [];
    $_SESSION['error_message'] = 'Failed to fetch categories: ' . $e->getMessage();
}

// Fetch category data for edit
$edit_category = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_kategori = (int)$_GET['id'];
    try {
        $query = "SELECT * FROM Kategori WHERE id_kategori = :id_kategori";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_kategori', $id_kategori, PDO::PARAM_INT);
        $stmt->execute();
        $edit_category = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Failed to fetch category: ' . $e->getMessage();
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo __('category_list'); ?></h2>
            <a href="#createCategoryModal" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                <i class="fas fa-plus mr-2"></i><?php echo __('create_category'); ?>
            </a>
        </div>
    </div>

    <!-- Create Category Modal -->
    <div id="createCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('create_category'); ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="space-y-4">
                    <div>
                        <label for="nama_kategori" class="block text-sm font-medium text-gray-700"><?php echo __('category_name'); ?></label>
                        <input id="nama_kategori" name="nama_kategori" type="text" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700"><?php echo __('category_description'); ?></label>
                        <textarea id="deskripsi" name="deskripsi" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('createCategoryModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('submit'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <?php if ($edit_category): ?>
    <div id="editCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('edit') . ' ' . __('category'); ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_kategori" value="<?php echo $edit_category['id_kategori']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="space-y-4">
                    <div>
                        <label for="edit_nama_kategori" class="block text-sm font-medium text-gray-700"><?php echo __('category_name'); ?></label>
                        <input id="edit_nama_kategori" name="nama_kategori" type="text" required value="<?php echo htmlspecialchars($edit_category['nama_kategori']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="edit_deskripsi" class="block text-sm font-medium text-gray-700"><?php echo __('category_description'); ?></label>
                        <textarea id="edit_deskripsi" name="deskripsi" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($edit_category['deskripsi'] ?? ''); ?></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editCategoryModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Categories Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="mt-4">
                <input type="text" onkeyup="searchTable(this, 'categoriesTable')" placeholder="<?php echo __('search'); ?>..." class="w-full md:w-64 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mt-4 overflow-x-auto">
                <table id="categoriesTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('category_name'); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('description'); ?></th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500"><?php echo __('no_data'); ?></td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($category['nama_kategori']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($category['deskripsi'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?php echo url('categories', ['action' => 'edit', 'id' => $category['id_kategori']]); ?>" class="text-blue-600 hover:text-blue-500 mr-3"><i class="fas fa-edit"></i> <?php echo __('edit'); ?></a>
                                <a href="<?php echo url('categories', ['action' => 'delete', 'id' => $category['id_kategori']]); ?>" onclick="return confirm('<?php echo __('delete_confirmation'); ?>')" class="text-red-600 hover:text-red-500"><i class="fas fa-trash"></i> <?php echo __('delete'); ?></a>
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
document.querySelector('a[href="#createCategoryModal"]').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('createCategoryModal').classList.remove('hidden');
});
</script>
<?php ob_end_flush(); ?>