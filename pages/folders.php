<?php
ob_start();
$database = new Database();
$db = $database->getConnection();
$auth = new Auth();

$auth->requireLogin();

// Function to get folder path
function getFolderPath($db, $id_folder) {
    try {
        $query = "SELECT path FROM Folder WHERE id_folder = :id_folder";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['path'] : '';
    } catch(PDOException $e) {
        error_log("Failed to get folder path: " . $e->getMessage());
        return '';
    }
}

// Function to check if folder has circular reference
function hasCircularReference($db, $id_folder, $parent_id) {
    if ($parent_id == 0) {
        return false;
    }
    $checked = [$id_folder];
    $current_id = $parent_id;
    
    while ($current_id != 0) {
        if (in_array($current_id, $checked)) {
            return true;
        }
        $checked[] = $current_id;
        try {
            $query = "SELECT parent_id FROM Folder WHERE id_folder = :id_folder";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_folder', $current_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $current_id = $result ? (int)$result['parent_id'] : 0;
        } catch(PDOException $e) {
            error_log("Circular reference check failed: " . $e->getMessage());
            return false;
        }
    }
    return false;
}

// Function to delete folder and its subfolders recursively
function deleteFolderRecursively($db, $id_folder) {
    try {
        // Delete associated documents
        $query = "SELECT id_dokumen FROM Dokumen_Folder WHERE id_folder = :id_folder";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
        $stmt->execute();
        $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($documents as $doc) {
            $query = "SELECT path_file FROM Dokumen WHERE id_dokumen = :id_dokumen";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_dokumen', $doc['id_dokumen'], PDO::PARAM_INT);
            $stmt->execute();
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($file && file_exists($file['path_file'])) {
                unlink($file['path_file']);
            }
            $query = "DELETE FROM Dokumen WHERE id_dokumen = :id_dokumen";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_dokumen', $doc['id_dokumen'], PDO::PARAM_INT);
            $stmt->execute();
        }

        // Delete document-folder associations
        $query = "DELETE FROM Dokumen_Folder WHERE id_folder = :id_folder";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
        $stmt->execute();

        // Get subfolders
        $query = "SELECT id_folder FROM Folder WHERE parent_id = :id_folder";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
        $stmt->execute();
        $subfolders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recursively delete subfolders
        foreach ($subfolders as $subfolder) {
            deleteFolderRecursively($db, $subfolder['id_folder']);
        }

        // Delete the folder itself
        $query = "DELETE FROM Folder WHERE id_folder = :id_folder";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
        $stmt->execute();

        return true;
    } catch(PDOException $e) {
        error_log("Recursive folder deletion failed: " . $e->getMessage());
        return false;
    }
}

// Handle folder creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=folders');
        exit;
    }

    $nama_folder = sanitizeInput($_POST['nama_folder']);
    $parent_id = (int)($_POST['parent_id'] ?? 0);
    
    if (!empty($nama_folder)) {
        try {
            // Validate parent_id
            if ($parent_id > 0) {
                $query = "SELECT id_folder FROM Folder WHERE id_folder = :parent_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() == 0) {
                    $_SESSION['error_message'] = 'Invalid parent folder';
                    header('Location: index.php?page=folders');
                    exit;
                }
            }

            // Check if folder name already exists in the same parent
            $query = "SELECT COUNT(*) as total FROM Folder WHERE nama_folder = :nama_folder AND (parent_id = :parent_id OR (parent_id IS NULL AND :parent_id = 0))";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama_folder', $nama_folder);
            $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                $_SESSION['error_message'] = 'Folder name already exists in this parent folder';
                header('Location: index.php?page=folders');
                exit;
            }

            $path = $parent_id > 0 ? getFolderPath($db, $parent_id) . '/' . $nama_folder : $nama_folder;
            $query = "INSERT INTO Folder (nama_folder, path, parent_id) VALUES (:nama_folder, :path, :parent_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama_folder', $nama_folder);
            $stmt->bindParam(':path', $path);
            $stmt->bindValue(':parent_id', $parent_id > 0 ? $parent_id : null, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = __('create_folder') . ' ' . __('success');
            } else {
                $_SESSION['error_message'] = __('create_folder') . ' ' . __('failed');
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = __('create_folder') . ' ' . __('failed') . ': ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = 'Please fill in the folder name';
    }
    header('Location: index.php?page=folders');
    exit;
}

// Handle folder edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=folders');
        exit;
    }

    $id_folder = (int)$_POST['id_folder'];
    $nama_folder = sanitizeInput($_POST['nama_folder']);
    $parent_id = (int)($_POST['parent_id'] ?? 0);
    
    if (!empty($nama_folder)) {
        try {
            // Validate parent_id and prevent self-reference
            if ($parent_id > 0) {
                if ($parent_id === $id_folder) {
                    $_SESSION['error_message'] = 'Folder cannot be its own parent';
                    header('Location: index.php?page=folders');
                    exit;
                }
                $query = "SELECT id_folder FROM Folder WHERE id_folder = :parent_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() == 0) {
                    $_SESSION['error_message'] = 'Invalid parent folder';
                    header('Location: index.php?page=folders');
                    exit;
                }
                if (hasCircularReference($db, $id_folder, $parent_id)) {
                    $_SESSION['error_message'] = 'Invalid parent folder: Circular reference detected';
                    header('Location: index.php?page=folders');
                    exit;
                }
            }

            // Check for duplicate folder name in the same parent
            $query = "SELECT COUNT(*) as total FROM Folder WHERE nama_folder = :nama_folder AND (parent_id = :parent_id OR (parent_id IS NULL AND :parent_id = 0)) AND id_folder != :id_folder";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama_folder', $nama_folder);
            $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
            $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0) {
                $_SESSION['error_message'] = 'Folder name already exists in this parent folder';
                header('Location: index.php?page=folders');
                exit;
            }

            // Update folder path
            $path = $parent_id > 0 ? getFolderPath($db, $parent_id) . '/' . $nama_folder : $nama_folder;
            $query = "UPDATE Folder SET nama_folder = :nama_folder, path = :path, parent_id = :parent_id WHERE id_folder = :id_folder";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nama_folder', $nama_folder);
            $stmt->bindParam(':path', $path);
            $stmt->bindValue(':parent_id', $parent_id > 0 ? $parent_id : null, PDO::PARAM_INT);
            $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
            if ($stmt->execute()) {
                // Update paths of subfolders
                $query = "SELECT id_folder, nama_folder FROM Folder WHERE parent_id = :id_folder";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
                $stmt->execute();
                $subfolders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($subfolders as $subfolder) {
                    $new_path = $path . '/' . $subfolder['nama_folder'];
                    $query = "UPDATE Folder SET path = :path WHERE id_folder = :id_folder";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':path', $new_path);
                    $stmt->bindParam(':id_folder', $subfolder['id_folder'], PDO::PARAM_INT);
                    $stmt->execute();
                }
                $_SESSION['success_message'] = __('update_success');
            } else {
                $_SESSION['error_message'] = __('update_failed');
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = __('update_failed') . ': ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = 'Please fill in the folder name';
    }
    header('Location: index.php?page=folders');
    exit;
}

// Handle folder deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_folder = (int)$_GET['id'];
    try {
        // Check if folder has documents or subfolders
        $query = "SELECT COUNT(*) as total FROM Dokumen_Folder WHERE id_folder = :id_folder";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
        $stmt->execute();
        $has_documents = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

        $query = "SELECT COUNT(*) as total FROM Folder WHERE parent_id = :id_folder";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
        $stmt->execute();
        $has_subfolders = $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;

        if ($has_documents || $has_subfolders) {
            // Allow recursive deletion if confirmed
            if (isset($_GET['recursive']) && $_GET['recursive'] === 'true') {
                if (deleteFolderRecursively($db, $id_folder)) {
                    $_SESSION['success_message'] = __('delete_success');
                } else {
                    $_SESSION['error_message'] = __('delete_failed') . ': Unable to delete folder and its contents';
                }
            } else {
                $message = 'This folder contains ';
                if ($has_documents) {
                    $message .= 'documents';
                    if ($has_subfolders) $message .= ' and ';
                }
                if ($has_subfolders) $message .= 'subfolders';
                $message .= '. Do you want to delete it and all its contents?';
                $_SESSION['error_message'] = $message . ' <a href="' . url('folders', ['action' => 'delete', 'id' => $id_folder, 'recursive' => 'true']) . '" class="text-blue-600 hover:text-blue-500">Delete All</a>';
            }
        } else {
            $query = "DELETE FROM Folder WHERE id_folder = :id_folder";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = __('delete_success');
            } else {
                $_SESSION['error_message'] = __('delete_failed');
            }
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = __('delete_failed') . ': ' . $e->getMessage();
    }
    header('Location: index.php?page=folders');
    exit;
}

// Fetch all folders
try {
    $query = "SELECT * FROM Folder ORDER BY path";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $folders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $folders = [];
    $_SESSION['error_message'] = 'Failed to fetch folders: ' . $e->getMessage();
}

// Fetch folder data for edit
$edit_folder = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_folder = (int)$_GET['id'];
    try {
        $query = "SELECT * FROM Folder WHERE id_folder = :id_folder";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
        $stmt->execute();
        $edit_folder = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Failed to fetch folder: ' . $e->getMessage();
    }
}
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo __('folder_list'); ?></h2>
            <a href="#createFolderModal" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                <i class="fas fa-folder-plus mr-2"></i><?php echo __('create_folder'); ?>
            </a>
        </div>
    </div>

    <!-- Create Folder Modal -->
    <div id="createFolderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('create_folder'); ?></h3>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="space-y-4">
                    <div>
                        <label for="nama_folder" class="block text-sm font-medium text-gray-700"><?php echo __('folder_name'); ?></label>
                        <input id="nama_folder" name="nama_folder" type="text" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700"><?php echo __('parent_folder'); ?></label>
                        <select id="parent_id" name="parent_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="0"><?php echo __('none'); ?></option>
                            <?php foreach ($folders as $folder): ?>
                            <option value="<?php echo $folder['id_folder']; ?>"><?php echo htmlspecialchars($folder['path']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('createFolderModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('submit'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Folder Modal -->
    <?php if ($edit_folder): ?>
        <div id="editFolderModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('edit') . ' ' . __('folder'); ?></h3>
                <form method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_folder" value="<?php echo $edit_folder['id_folder']; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="space-y-4">
                        <div>
                            <label for="edit_nama_folder" class="block text-sm font-medium text-gray-700"><?php echo __('folder_name'); ?></label>
                            <input id="edit_nama_folder" name="nama_folder" type="text" required value="<?php echo htmlspecialchars($edit_folder['nama_folder']); ?>" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="edit_parent_id" class="block text-sm font-medium text-gray-700"><?php echo __('parent_folder'); ?></label>
                            <select id="edit_parent_id" name="parent_id" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="0"><?php echo __('none'); ?></option>
                                <?php foreach ($folders as $folder): ?>
                                    <?php if ($folder['id_folder'] !== $edit_folder['id_folder']): ?>
                                        <option value="<?php echo $folder['id_folder']; ?>" <?php echo $folder['id_folder'] == ($edit_folder['parent_id'] ?? 0) ? 'selected' : ''; ?>><?php echo htmlspecialchars($folder['path']); ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('editFolderModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"><?php echo __('cancel'); ?></button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('save'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- Folders Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="mt-4">
                <input type="text" onkeyup="searchTable(this, 'foldersTable')" placeholder="<?php echo __('search'); ?>..." class="w-full md:w-64 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="mt-4 overflow-x-auto">
                <table id="foldersTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('folder_name'); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('folder_path'); ?></th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($folders)): ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500"><?php echo __('no_data'); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($folders as $folder): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <i class="fas fa-folder text-yellow-500 mr-2"></i>
                                        <?php echo htmlspecialchars($folder['nama_folder']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($folder['path']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="<?php echo url('folders', ['action' => 'edit', 'id' => $folder['id_folder']]); ?>" class="text-blue-600 hover:text-blue-500 mr-3"><i class="fas fa-edit"></i> <?php echo __('edit'); ?></a>
                                        <a href="<?php echo url('folders', ['action' => 'delete', 'id' => $folder['id_folder']]); ?>" onclick="return confirmDelete('<?php echo __('delete_confirmation'); ?>')" class="text-red-600 hover:text-red-500"><i class="fas fa-trash"></i> <?php echo __('delete'); ?></a>
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

document.querySelector('a[href="#createFolderModal"]').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('createFolderModal').classList.remove('hidden');
});

function confirmDelete(message) {
    return confirm(message);
}

function searchTable(input, tableId) {
    const filter = input.value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
}
</script>
<?php ob_end_flush(); ?>