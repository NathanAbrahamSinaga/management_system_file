<?php
ob_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Initialize database and authentication
$database = new Database();
$db = $database->getConnection();
$auth = new Auth();

$auth->requireLogin();

// Validate document ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    header('Content-Type: text/plain');
    echo 'Invalid document ID';
    exit;
}

$id_dokumen = (int)$_GET['id'];

// Check user permissions
if (!hasFilePermission($_SESSION['user_id'], $id_dokumen, 'read')) {
    http_response_code(403);
    header('Content-Type: text/plain');
    echo 'Permission denied';
    exit;
}

try {
    // Fetch document details
    $query = "SELECT nama_file, path_file, tipe_file, ukuran_file FROM Dokumen WHERE id_dokumen = :id_dokumen";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_dokumen', $id_dokumen, PDO::PARAM_INT);
    $stmt->execute();
    $doc = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        http_response_code(404);
        header('Content-Type: text/plain');
        echo 'Document not found';
        exit;
    }

    $file_path = $doc['path_file']; // Expected: /path/to/root/uploads/documents/filename.ext
    $file_name = $doc['nama_file'];
    $file_size = $doc['ukuran_file'];
    $file_type = $doc['tipe_file'];

    // Verify file exists and is readable
    if (!file_exists($file_path) || !is_readable($file_path)) {
        error_log("Download failed: File not found or inaccessible at $file_path");
        http_response_code(404);
        header('Content-Type: text/plain');
        echo 'File not found or inaccessible';
        exit;
    }

    // Determine MIME type
    $mime_types = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'txt' => 'text/plain',
        // Add more as needed
    ];
    $mime_type = $mime_types[strtolower($file_type)] ?? 'application/octet-stream';

    // Set download headers
    header('Content-Description: File Transfer');
    header("Content-Type: $mime_type");
    header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
    header('Content-Length: ' . $file_size);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('X-Content-Type-Options: nosniff');
    header('Content-Transfer-Encoding: binary');

    // Clear output buffer
    ob_clean();
    flush();

    // Stream the file
    $file = fopen($file_path, 'rb');
    while (!feof($file)) {
        echo fread($file, 8192);
        flush();
    }
    fclose($file);

    // Log the download
    logActivity($_SESSION['user_id'], $id_dokumen, 'download');

    exit;
} catch (PDOException $e) {
    error_log("Download failed: Database error - " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain');
    echo 'Database error';
    exit;
}
?>