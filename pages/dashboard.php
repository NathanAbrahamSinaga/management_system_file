<?php
// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Get statistics
try {
    // Total documents
    $query = "SELECT COUNT(*) as total FROM Dokumen";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_documents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total folders
    $query = "SELECT COUNT(*) as total FROM Folder";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_folders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total categories
    $query = "SELECT COUNT(*) as total FROM Kategori";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_categories = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total users (Admin only)
    $total_users = 0;
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin') {
        $query = "SELECT COUNT(*) as total FROM User";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    // Recent uploads
    $query = "SELECT d.*, u.nama as user_name, k.nama_kategori 
              FROM Dokumen d 
              LEFT JOIN User u ON d.id_user = u.id_user 
              LEFT JOIN Kategori k ON d.id_kategori = k.id_kategori 
              ORDER BY d.tanggal_upload DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $recent_uploads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent activities
    $query = "SELECT l.*, u.nama as user_name, d.nama_file 
              FROM Log_Akses l 
              LEFT JOIN User u ON l.id_user = u.id_user 
              LEFT JOIN Dokumen d ON l.id_dokumen = d.id_dokumen 
              ORDER BY l.timestamp DESC 
              LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $total_documents = $total_folders = $total_categories = $total_users = 0;
    $recent_uploads = $recent_activities = [];
}
?>

<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                <?php echo __('welcome'); ?>, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
            </h1>
            <p class="text-gray-600"><?php echo __('file_management_system'); ?></p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Documents -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate"><?php echo __('total_documents'); ?></dt>
                            <dd class="text-lg font-medium text-gray-900"><?php echo number_format($total_documents); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Folders -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-folder text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate"><?php echo __('total_folders'); ?></dt>
                            <dd class="text-lg font-medium text-gray-900"><?php echo number_format($total_folders); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Categories -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-tags text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate"><?php echo __('total_categories'); ?></dt>
                            <dd class="text-lg font-medium text-gray-900"><?php echo number_format($total_categories); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Users (Admin only) -->
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate"><?php echo __('total_users'); ?></dt>
                            <dd class="text-lg font-medium text-gray-900"><?php echo number_format($total_users); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Uploads -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    <i class="fas fa-upload mr-2 text-blue-500"></i><?php echo __('recent_uploads'); ?>
                </h3>
                <?php if (empty($recent_uploads)): ?>
                    <p class="text-gray-500 text-sm"><?php echo __('no_recent_uploads'); ?></p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($recent_uploads as $upload): ?>
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <i class="<?php echo getFileIcon(pathinfo($upload['nama_file'], PATHINFO_EXTENSION)); ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($upload['nama_file']); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo __('uploaded_by'); ?>: <?php echo htmlspecialchars($upload['user_name']); ?> • 
                                    <?php echo date('M d, Y', strtotime($upload['tanggal_upload'])); ?>
                                </p>
                            </div>
                            <div class="text-xs text-gray-400">
                                <?php echo formatFileSize($upload['ukuran_file']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo url('documents'); ?>" class="text-sm text-blue-600 hover:text-blue-500">
                            <?php echo __('view_all_documents'); ?> →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    <i class="fas fa-history mr-2 text-green-500"></i><?php echo __('recent_activities'); ?>
                </h3>
                <?php if (empty($recent_activities)): ?>
                    <p class="text-gray-500 text-sm"><?php echo __('no_recent_activities'); ?></p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($recent_activities as $activity): ?>
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <?php
                                $icon_class = '';
                                switch($activity['aksi']) {
                                    case 'lihat':
                                        $icon_class = 'fas fa-eye text-blue-500';
                                        break;
                                    case 'edit':
                                        $icon_class = 'fas fa-edit text-yellow-500';
                                        break;
                                    case 'hapus':
                                        $icon_class = 'fas fa-trash text-red-500';
                                        break;
                                    default:
                                        $icon_class = 'fas fa-info-circle text-gray-500';
                                }
                                ?>
                                <i class="<?php echo $icon_class; ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($activity['user_name']); ?> 
                                    <span class="font-normal"><?php echo ucfirst($activity['aksi']); ?></span>
                                    <?php echo htmlspecialchars($activity['nama_file']); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo date('M d, Y H:i', strtotime($activity['timestamp'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                <i class="fas fa-bolt mr-2 text-orange-500"></i><?php echo __('quick_actions'); ?>
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="<?php echo url('documents', ['action' => 'upload']); ?>" 
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-upload text-2xl text-blue-500 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900"><?php echo __('upload_document'); ?></span>
                </a>
                
                <a href="<?php echo url('folders', ['action' => 'create']); ?>" 
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-folder-plus text-2xl text-yellow-500 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900"><?php echo __('create_folder'); ?></span>
                </a>
                
                <a href="<?php echo url('categories', ['action' => 'create']); ?>" 
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-plus text-2xl text-green-500 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900"><?php echo __('create_category'); ?></span>
                </a>
                
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin'): ?>
                <a href="<?php echo url('users', ['action' => 'add']); ?>" 
                   class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-user-plus text-2xl text-purple-500 mb-2"></i>
                    <span class="text-sm font-medium text-gray-900"><?php echo __('add_user'); ?></span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
