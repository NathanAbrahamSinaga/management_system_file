<?php
require_once __DIR__ . '/../config/config.php';

// Format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Get file icon based on extension
function getFileIcon($extension) {
    switch (strtolower($extension)) {
        case 'pdf':
            return 'fas fa-file-pdf text-red-500';
        case 'doc':
        case 'docx':
            return 'fas fa-file-word text-blue-500';
        case 'txt':
            return 'fas fa-file-alt text-gray-500';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return 'fas fa-file-image text-green-500';
        case 'zip':
        case 'rar':
            return 'fas fa-file-archive text-orange-500';
        default:
            return 'fas fa-file text-gray-400';
    }
}

// Log user activity
function logActivity($user_id, $document_id, $action) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "INSERT INTO Log_Akses (id_user, id_dokumen, aksi, timestamp) VALUES (:user_id, :document_id, :action, NOW())";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->bindParam(':action', $action);
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}

// Clean filename
function cleanFilename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}

// Generate unique filename
function generateUniqueFilename($original_filename) {
    $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    $name = pathinfo($original_filename, PATHINFO_FILENAME);
    $clean_name = cleanFilename($name);
    return $clean_name . '_' . time() . '.' . $extension;
}

// Check file permissions
function hasFilePermission($user_id, $document_id, $permission) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Check if user is owner
        $query = "SELECT id_user FROM Dokumen WHERE id_dokumen = :document_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($doc['id_user'] == $user_id) {
                return true; // Owner has all permissions
            }
        }
        
        // Check specific permissions
        $query = "SELECT * FROM Hak_Akses WHERE id_user = :user_id AND id_dokumen = :document_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':document_id', $document_id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $access = $stmt->fetch(PDO::FETCH_ASSOC);
            switch ($permission) {
                case 'read':
                    return (bool)$access['hak_baca'];
                case 'write':
                    return (bool)$access['hak_tulis'];
                case 'delete':
                    return (bool)$access['hak_hapus'];
            }
        }
        
        return false;
    } catch(PDOException $e) {
        error_log("Permission check failed: " . $e->getMessage());
        return false;
    }
}

// Sanitize input
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    // Regenerate CSRF token after verification
    unset($_SESSION['csrf_token']);
    return true;
}

// Validate file type
function validateFileType($file) {
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedTypes = ALLOWED_EXTENSIONS;
    return in_array($extension, $allowedTypes);
}
?>