<?php
ob_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$database = new Database();
$db = $database->getConnection();
$auth = new Auth();

$auth->requireLogin();

function validateFileSize($file, $maxSize = MAX_FILE_SIZE) {
    return $file['size'] <= $maxSize;
}

function validateCategory($db, $category_id) {
    if ($category_id == 0) {
        return true;
    }
    try {
        $query = "SELECT id_kategori FROM Kategori WHERE id_kategori = :category_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        error_log("Category validation failed: " . $e->getMessage());
        return false;
    }
}

function validateFolder($db, $folder_id) {
    if ($folder_id == 0) {
        return true;
    }
    try {
        $query = "SELECT id_folder FROM Folder WHERE id_folder = :folder_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':folder_id', $folder_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        error_log("Folder validation failed: " . $e->getMessage());
        return false;
    }
}

function checkDuplicateFileName($db, $file_name, $id_user) {
    try {
        $query = "SELECT COUNT(*) as total FROM Dokumen WHERE nama_file = :nama_file AND id_user = :id_user";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nama_file', $file_name);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] > 0;
    } catch(PDOException $e) {
        error_log("Duplicate file name check failed: " . $e->getMessage());
        return false;
    }
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=documents');
        exit;
    }

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $nama_file = sanitizeInput($file['name']);
        $deskripsi = sanitizeInput($_POST['deskripsi'] ?? '');
        $id_kategori = (int)($_POST['id_kategori'] ?? 0);
        $id_folder = (int)($_POST['id_folder'] ?? 0);

        if (checkDuplicateFileName($db, $nama_file, $_SESSION['user_id'])) {
            $_SESSION['error_message'] = 'A file with the same name already exists';
            header('Location: index.php?page=documents');
            exit;
        }

        if (!validateCategory($db, $id_kategori)) {
            $_SESSION['error_message'] = 'Invalid category selected';
            header('Location: index.php?page=documents');
            exit;
        }
        if (!validateFolder($db, $id_folder)) {
            $_SESSION['error_message'] = 'Invalid folder selected';
            header('Location: index.php?page=documents');
            exit;
        }

        if (!validateFileType($file)) {
            // CHANGED: Specific error message for invalid file type
            $_SESSION['error_message'] = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS);
            header('Location: index.php?page=documents');
            exit;
        }
        if (!validateFileSize($file)) {
            $_SESSION['error_message'] = 'File is too large. Maximum size: ' . formatFileSize(MAX_FILE_SIZE);
            header('Location: index.php?page=documents');
            exit;
        }

        $unique_filename = generateUniqueFilename($nama_file);
        $destination = UPLOAD_DIR . $unique_filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            try {
                $query = "INSERT INTO Dokumen (nama_file, deskripsi, tanggal_upload, tipe_file, ukuran_file, path_file, id_user, id_kategori) 
                          VALUES (:nama_file, :deskripsi, NOW(), :tipe_file, :ukuran_file, :path_file, :id_user, :id_kategori)";
                $stmt = $db->prepare($query);
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $stmt->bindParam(':nama_file', $unique_filename);
                $stmt->bindParam(':deskripsi', $deskripsi);
                $stmt->bindParam(':tipe_file', $extension);
                $stmt->bindParam(':ukuran_file', $file['size'], PDO::PARAM_INT);
                $stmt->bindParam(':path_file', $destination);
                $stmt->bindParam(':id_user', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->bindValue(':id_kategori', $id_kategori > 0 ? $id_kategori : null, PDO::PARAM_INT);
                if ($stmt->execute()) {
                    $id_dokumen = $db->lastInsertId();
                    
                    $query = "INSERT INTO Metadata (id_dokumen, judul, penulis, tanggal_dibuat, versi) 
                              VALUES (:id_dokumen, :judul, :penulis, NOW(), :versi)";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
                    $stmt->bindParam(':judul', $nama_file);
                    $stmt->bindParam(':penulis', $_SESSION['user_name']);
                    $stmt->bindValue(':versi', '1.0');
                    $stmt->execute();

                    if ($id_folder > 0) {
                        $query = "INSERT INTO Dokumen_Folder (id_dokumen, id_folder) VALUES (:id_dokumen, :id_folder)";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
                        $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                    logActivity($_SESSION['user_id'], $id_dokumen, 'upload');
                    $_SESSION['success_message'] = __('upload_success');
                } else {
                    unlink($destination);
                    $_SESSION['error_message'] = __('upload_failed');
                }
            } catch(PDOException $e) {
                unlink($destination);
                $_SESSION['error_message'] = __('upload_failed') . ': ' . $e->getMessage();
            }
        } else {
            $_SESSION['error_message'] = __('upload_failed') . ': Unable to move file';
        }
    } else {
        $_SESSION['error_message'] = __('select_file');
    }
    header('Location: index.php?page=documents');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error_message'] = 'Invalid CSRF token';
        header('Location: index.php?page=documents');
        exit;
    }

    $id_dokumen = (int)$_POST['id_dokumen'];
    if (hasFilePermission($_SESSION['user_id'], $id_dokumen, 'write')) {
        $deskripsi = sanitizeInput($_POST['deskripsi'] ?? '');
        $id_kategori = (int)($_POST['id_kategori'] ?? 0);
        $id_folder = (int)($_POST['id_folder'] ?? 0);

        if (!validateCategory($db, $id_kategori)) {
            $_SESSION['error_message'] = 'Invalid category selected';
            header('Location: index.php?page=documents');
            exit;
        }
        if (!validateFolder($db, $id_folder)) {
            $_SESSION['error_message'] = 'Invalid folder selected';
            header('Location: index.php?page=documents');
            exit;
        }

        try {
            $query = "UPDATE Dokumen SET deskripsi = :deskripsi, id_kategori = :id_kategori WHERE id_dokumen = :id_dokumen";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':deskripsi', $deskripsi);
            $stmt->bindValue(':id_kategori', $id_kategori > 0 ? $id_kategori : null, PDO::PARAM_INT);
            $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
            $stmt->execute();

            $query = "DELETE FROM Dokumen_Folder WHERE id_dokumen = :id_dokumen";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
            $stmt->execute();

            if ($id_folder > 0) {
                $query = "INSERT INTO Dokumen_Folder (id_dokumen, id_folder) VALUES (:id_dokumen, :id_folder)";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
                $stmt->bindParam(':id_folder', $id_folder, PDO::PARAM_INT);
                $stmt->execute();
            }

            if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['file'];
                $nama_file = sanitizeInput($file['name']);

                if (checkDuplicateFileName($db, $nama_file, $_SESSION['user_id'])) {
                    $_SESSION['error_message'] = 'A file with the same name already exists';
                    header('Location: index.php?page=documents');
                    exit;
                }

                if (!validateFileType($file)) {
                    // CHANGED: Specific error message for invalid file type
                    $_SESSION['error_message'] = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_EXTENSIONS);
                    header('Location: index.php?page=documents');
                    exit;
                }
                if (!validateFileSize($file)) {
                    $_SESSION['error_message'] = 'File is too large. Maximum size: ' . formatFileSize(MAX_FILE_SIZE);
                    header('Location: index.php?page=documents');
                    exit;
                }

                $unique_filename = generateUniqueFilename($nama_file);
                $destination = UPLOAD_DIR . $unique_filename;

                $query = "SELECT path_file FROM Dokumen WHERE id_dokumen = :id_dokumen";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
                $stmt->execute();
                $old_file = $stmt->fetch(PDO::FETCH_ASSOC)['path_file'];

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $query = "UPDATE Metadata SET judul = :judul, penulis = :penulis, tanggal_dibuat = NOW(), versi = :versi WHERE id_dokumen = :id_dokumen";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':judul', $nama_file);
                    $stmt->bindParam(':penulis', $_SESSION['user_name']);
                    $stmt->bindValue(':versi', '2.0');
                    $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
                    $stmt->execute();

                    $query = "UPDATE Dokumen SET nama_file = :nama_file, tipe_file = :tipe_file, ukuran_file = :ukuran_file, path_file = :path_file WHERE id_dokumen = :id_dokumen";
                    $stmt = $db->prepare($query);
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $stmt->bindParam(':nama_file', $unique_filename);
                    $stmt->bindParam(':tipe_file', $extension);
                    $stmt->bindParam(':ukuran_file', $file['size'], PDO::PARAM_INT);
                    $stmt->bindParam(':path_file', $destination);
                    $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
                    if ($stmt->execute()) {
                        if ($old_file && file_exists($old_file)) {
                            unlink($old_file);
                        }
                    } else {
                        unlink($destination);
                        $_SESSION['error_message'] = __('update_failed');
                        header('Location: index.php?page=documents');
                        exit;
                    }
                } else {
                    $_SESSION['error_message'] = __('update_failed') . ': Unable to move file';
                    header('Location: index.php?page=documents');
                    exit;
                }
            }

            logActivity($_SESSION['user_id'], $id_dokumen, 'edit');
            $_SESSION['success_message'] = __('update_success');
        } catch(PDOException $e) {
            $_SESSION['error_message'] = __('update_failed') . ': ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = __('permission_denied');
    }
    header('Location: index.php?page=documents');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_dokumen = (int)$_GET['id'];
    if (hasFilePermission($_SESSION['user_id'], $id_dokumen, 'delete')) {
        try {
            $query = "SELECT path_file FROM Dokumen WHERE id_dokumen = :id_dokumen";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
            $stmt->execute();
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($doc && file_exists($doc['path_file'])) {
                unlink($doc['path_file']);
            }

            $query = "DELETE FROM Dokumen WHERE id_dokumen = :id_dokumen";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
            if ($stmt->execute()) {
                logActivity($_SESSION['user_id'], $id_dokumen, 'hapus');
                $_SESSION['success_message'] = __('delete_success');
            } else {
                $_SESSION['error_message'] = __('delete_failed');
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = __('delete_failed') . ': ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = __('permission_denied');
    }
    header('Location: index.php?page=documents');
    exit;
}

try {
    $query = "SELECT d.*, u.nama as user_name, k.nama_kategori, f.nama_folder 
              FROM Dokumen d 
              LEFT JOIN User u ON d.id_user = u.id_user 
              LEFT JOIN Kategori k ON d.id_kategori = k.id_kategori 
              LEFT JOIN Dokumen_Folder df ON d.id_dokumen = df.id_dokumen 
              LEFT JOIN Folder f ON df.id_folder = f.id_folder 
              WHERE d.id_user = :id_user OR EXISTS (
                  SELECT 1 FROM Hak_Akses ha WHERE ha.id_dokumen = d.id_dokumen AND ha.id_user = :id_user AND ha.hak_baca = 1
              )";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_user', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $documents = [];
    $_SESSION['error_message'] = 'Failed to fetch documents: ' . $e->getMessage();
}

try {
    $query = "SELECT * FROM Kategori ORDER BY nama_kategori";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT * FROM Folder ORDER BY nama_folder";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $folders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $categories = $folders = [];
    $_SESSION['error_message'] = 'Failed to fetch categories or folders: ' . $e->getMessage();
}

$edit_document = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id_dokumen = (int)$_GET['id'];
    if (hasFilePermission($_SESSION['user_id'], $id_dokumen, 'write')) {
        try {
            $query = "SELECT d.*, df.id_folder 
                      FROM Dokumen d 
                      LEFT JOIN Dokumen_Folder df ON d.id_dokumen = df.id_dokumen 
                      WHERE d.id_dokumen = :id_dokumen";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
            $stmt->execute();
            $edit_document = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            $_SESSION['error_message'] = 'Failed to fetch document: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = __('permission_denied');
        header('Location: index.php?page=documents');
        exit;
    }
}
?>

<div class="space-y-6">
    <!-- NEW: Display error or success messages -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error_message']); ?></span>
            <?php unset($_SESSION['error_message']); ?>
        </div>
    <?php elseif (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['success_message']); ?></span>
            <?php unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo __('document_list'); ?></h2>
            <a href="#uploadDocumentModal" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                <i class="fas fa-upload mr-2"></i><span class="hidden sm:inline"><?php echo __('upload_document'); ?></span>
            </a>
        </div>
    </div>

    <div id="uploadDocumentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('upload_document'); ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="space-y-4">
                    <div>
                        <label for="file" class="block text-sm font-medium text-gray-700"><?php echo __('select_file'); ?></label>
                        <input id="file" name="file" type="file" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500" onchange="previewFile(this)">
                        <div id="filePreview" class="mt-2"></div>
                    </div>
                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700"><?php echo __('description'); ?></label>
                        <textarea id="deskripsi" name="deskripsi" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label for="id_kategori" class="block text-sm font-medium text-gray-700"><?php echo __('category'); ?></label>
                        <select id="id_kategori" name="id_kategori" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="0"><?php echo __('select_category'); ?></option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id_kategori']; ?>"><?php echo htmlspecialchars($category['nama_kategori']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="id_folder" class="block text-sm font-medium text-gray-700"><?php echo __('folder'); ?></label>
                        <select id="id_folder" name="id_folder" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="0"><?php echo __('select_folder'); ?></option>
                            <?php foreach ($folders as $folder): ?>
                            <option value="<?php echo $folder['id_folder']; ?>"><?php echo htmlspecialchars($folder['nama_folder']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('uploadDocumentModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('upload'); ?></button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($edit_document): ?>
    <div id="editDocumentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4"><?php echo __('edit') . ' ' . __('document'); ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_dokumen" value="<?php echo $edit_document['id_dokumen']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <div class="space-y-4">
                    <div>
                        <label for="edit_file" class="block text-sm font-medium text-gray-700"><?php echo __('select_file'); ?> (<?php echo __('optional'); ?>)</label>
                        <input id="edit_file" name="file" type="file" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500" onchange="previewFile(this)">
                        <div id="filePreview" class="mt-2"></div>
                        <p class="text-sm text-gray-500 mt-1"><?php echo __('current_file'); ?>: <?php echo htmlspecialchars($edit_document['nama_file']); ?></p>
                    </div>
                    <div>
                        <label for="edit_deskripsi" class="block text-sm font-medium text-gray-700"><?php echo __('description'); ?></label>
                        <textarea id="edit_deskripsi" name="deskripsi" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo htmlspecialchars($edit_document['deskripsi'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label for="edit_id_kategori" class="block text-sm font-medium text-gray-700"><?php echo __('category'); ?></label>
                        <select id="edit_id_kategori" name="id_kategori" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="0"><?php echo __('select_category'); ?></option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id_kategori']; ?>" <?php echo $category['id_kategori'] == ($edit_document['id_kategori'] ?? 0) ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['nama_kategori']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="edit_id_folder" class="block text-sm font-medium text-gray-700"><?php echo __('folder'); ?></label>
                        <select id="edit_id_folder" name="id_folder" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="0"><?php echo __('select_folder'); ?></option>
                            <?php foreach ($folders as $folder): ?>
                            <option value="<?php echo $folder['id_folder']; ?>" <?php echo $folder['id_folder'] == ($edit_document['id_folder'] ?? 0) ? 'selected' : ''; ?>><?php echo htmlspecialchars($folder['nama_folder']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editDocumentModal')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><?php echo __('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="mt-4">
                <input type="text" id="searchInput" onkeyup="searchDocuments()" placeholder="<?php echo __('search'); ?>..." class="w-full md:w-64 border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <!-- Table View (Desktop) -->
            <div class="mt-4 overflow-x-auto hidden sm:block">
                <table id="documentsTable" class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('file_name'); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('category'); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('folder'); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('upload_date'); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('file_size'); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('version'); ?></th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500"><?php echo __('no_data'); ?></td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($documents as $doc): ?>
                        <?php
                            $query = "SELECT versi FROM Metadata WHERE id_dokumen = :id_dokumen";
                            $stmt = $db->prepare($query);
                            $stmt->bindParam(':id_dokumen', $doc['id_dokumen'], PDO::PARAM_INT);
                            $stmt->execute();
                            $metadata = $stmt->fetch(PDO::FETCH_ASSOC);
                            $version = $metadata['versi'] ?? '1.0';
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <i class="<?php echo htmlspecialchars(getFileIcon(pathinfo($doc['nama_file'], PATHINFO_EXTENSION))); ?> mr-2"></i>
                                <?php echo htmlspecialchars($doc['nama_file']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($doc['nama_kategori'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($doc['nama_folder'] ?? ''); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($doc['tanggal_upload'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars(formatFileSize($doc['ukuran_file'])); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($version); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <?php if (hasFilePermission($_SESSION['user_id'], $doc['id_dokumen'], 'read')): ?>
                                <a href="<?php echo url('download', ['id' => $doc['id_dokumen']]); ?>" class="text-blue-600 hover:text-blue-800 mr-3"><i class="fas fa-download"></i> <?php echo __('download'); ?></a>
                                <?php endif; ?>
                                <?php if (hasFilePermission($_SESSION['user_id'], $doc['id_dokumen'], 'write')): ?>
                                <a href="<?php echo url('documents', ['action' => 'edit', 'id' => $doc['id_dokumen']]); ?>" class="text-blue-600 hover:text-blue-800 mr-3"><i class="fas fa-edit"></i> <?php echo __('edit'); ?></a>
                                <?php endif; ?>
                                <?php if (hasFilePermission($_SESSION['user_id'], $doc['id_dokumen'], 'delete')): ?>
                                <a href="<?php echo url('documents', ['action' => 'delete', 'id' => $doc['id_dokumen']]); ?>" onclick="return confirmDelete('<?php echo __('delete_confirmation'); ?>')" class="text-red-600 hover:text-red-800"><i class="fas fa-trash"></i> <?php echo __('delete'); ?></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Card View (Mobile) -->
            <div class="mt-4 grid grid-cols-2 gap-4 sm:hidden">
                <?php if (empty($documents)): ?>
                <div class="col-span-full text-center text-gray-500 py-4"><?php echo __('no_data'); ?></div>
                <?php else: ?>
                <?php foreach ($documents as $doc): ?>
                <?php
                    $query = "SELECT versi FROM Metadata WHERE id_dokumen = :id_dokumen";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id_dokumen', $doc['id_dokumen'], PDO::PARAM_INT);
                    $stmt->execute();
                    $metadata = $stmt->fetch(PDO::FETCH_ASSOC);
                    $version = $metadata['versi'] ?? '1.0';
                    $fileExt = pathinfo($doc['nama_file'], PATHINFO_EXTENSION);
                    $fileIcon = getFileIcon($fileExt);
                    $bgColor = 'bg-blue-100';
                    $textColor = 'text-blue-700';
                    
                    if (strpos($fileIcon, 'pdf') !== false) {
                        $bgColor = 'bg-red-100';
                        $textColor = 'text-red-700';
                    } elseif (strpos($fileIcon, 'word') !== false) {
                        $bgColor = 'bg-blue-100';
                        $textColor = 'text-blue-700';
                    } elseif (strpos($fileIcon, 'excel') !== false) {
                        $bgColor = 'bg-green-100';
                        $textColor = 'text-green-700';
                    } elseif (strpos($fileIcon, 'image') !== false) {
                        $bgColor = 'bg-purple-100';
                        $textColor = 'text-purple-700';
                    }
                ?>
                <div class="document-card bg-white rounded-lg shadow overflow-hidden border border-gray-200">
                    <div class="p-4">
                        <div class="flex items-center mb-3">
                            <div class="<?php echo $bgColor; ?> <?php echo $textColor; ?> p-2 rounded-lg mr-3">
                                <i class="<?php echo htmlspecialchars($fileIcon); ?> text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($doc['nama_file']); ?></h3>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars(formatFileSize($doc['ukuran_file'])); ?> • v<?php echo htmlspecialchars($version); ?></p>
                            </div>
                        </div>
                        
                        <div class="text-xs text-gray-500 mb-3">
                            <?php if (!empty($doc['nama_kategori'])): ?>
                            <div class="flex items-center mb-1">
                                <i class="fas fa-tag mr-1"></i>
                                <span><?php echo htmlspecialchars($doc['nama_kategori']); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($doc['nama_folder'])): ?>
                            <div class="flex items-center mb-1">
                                <i class="fas fa-folder mr-1"></i>
                                <span><?php echo htmlspecialchars($doc['nama_folder']); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="flex items-center">
                                <i class="fas fa-calendar mr-1"></i>
                                <span><?php echo date('M d, Y', strtotime($doc['tanggal_upload'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between border-t border-gray-200 pt-3">
                            <?php if (hasFilePermission($_SESSION['user_id'], $doc['id_dokumen'], 'read')): ?>
                            <a href="<?php echo url('download', ['id' => $doc['id_dokumen']]); ?>" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasFilePermission($_SESSION['user_id'], $doc['id_dokumen'], 'write')): ?>
                            <a href="<?php echo url('documents', ['action' => 'edit', 'id' => $doc['id_dokumen']]); ?>" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (hasFilePermission($_SESSION['user_id'], $doc['id_dokumen'], 'delete')): ?>
                            <a href="<?php echo url('documents', ['action' => 'delete', 'id' => $doc['id_dokumen']]); ?>" onclick="return confirmDelete('<?php echo __('delete_confirmation'); ?>')" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}

document.querySelector('a[href="#uploadDocumentModal"]').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('uploadDocumentModal').classList.remove('hidden');
});

function previewFile(input) {
    const preview = input.nextElementSibling;
    preview.innerHTML = '';
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.className = 'mt-2 max-w-full h-auto rounded';
            img.style.maxHeight = '100px';
            preview.appendChild(img);
        } else {
            preview.textContent = `Selected file: ${file.name}`;
        }
    }
}

function confirmDelete(message) {
    return confirm(message);
}

function searchDocuments() {
    const filter = document.getElementById('searchInput').value.toLowerCase();
    
    // Search in table view
    const table = document.getElementById('documentsTable');
    const rows = table.getElementsByTagName('tr');
    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().includes(filter)) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
    
    // Search in card view
    const cards = document.querySelectorAll('.document-card');
    cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
<?php ob_end_flush(); ?>