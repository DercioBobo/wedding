<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Use absolute paths based on this file's location
$baseDir = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
$uploadDir = $baseDir . '/public/uploads/photos/';

if (!file_exists($uploadDir)) {
    if (!@mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'Cannot create upload directory', 'path' => $uploadDir]);
        exit;
    }
}

if (!is_writable($uploadDir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload directory is not writable', 'path' => $uploadDir]);
    exit;
}

/**
 * Compress and Resize Image
 */
function processImage($sourcePath, $destPath, $maxDim = 1920, $quality = 80) {
    if (!function_exists('imagecreatefromjpeg')) {
        return false; // GD not available
    }

    $info = getimagesize($sourcePath);
    if ($info === false) {
        return false; // Unreadable image
    }

    list($width, $height, $type) = $info;

    if ($width === 0 || $height === 0) {
        return false;
    }

    // Load Image
    $image = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $image = @imagecreatefromjpeg($sourcePath); break;
        case IMAGETYPE_PNG:  $image = @imagecreatefrompng($sourcePath); break;
        case IMAGETYPE_WEBP: $image = @imagecreatefromwebp($sourcePath); break;
        default: return false;
    }

    if (!$image) {
        return false;
    }

    // Calculate new dimensions
    $ratio = $width / $height;
    if ($width > $maxDim || $height > $maxDim) {
        if ($width > $height) {
            $newWidth = $maxDim;
            $newHeight = (int)($maxDim / $ratio);
        } else {
            $newHeight = $maxDim;
            $newWidth = (int)($maxDim * $ratio);
        }
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    $white = imagecolorallocate($newImage, 255, 255, 255);
    imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $white);

    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $result = imagejpeg($newImage, $destPath, $quality);

    imagedestroy($image);
    imagedestroy($newImage);

    return $result;
}

if ($method === 'GET') {
    // List all photos
    $stmt = $pdo->query("SELECT * FROM photos ORDER BY uploaded_at DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;

} elseif ($method === 'POST') {
    // Handle Upload
    if (isset($_FILES['photo'])) {
        $file = $_FILES['photo'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'Upload failed']);
            exit;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'heic'];
        
        if (!in_array($ext, $allowed)) {
             http_response_code(400);
             echo json_encode(['error' => 'Invalid file type']);
             exit;
        }

        // For HEIC or when GD is missing, keep original extension
        $useOriginalExt = ($ext === 'heic' || !function_exists('imagecreatefromjpeg'));
        $saveExt = $useOriginalExt ? $ext : 'jpg';
        $filename = uniqid('photo_') . '.' . $saveExt;
        $dest = $uploadDir . $filename;

        $processed = false;
        if (!$useOriginalExt) {
            $processed = processImage($file['tmp_name'], $dest);
        }

        if (!$processed) {
            // Fallback: save original file as-is
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to save file']);
                exit;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO photos (filename) VALUES (?)");
        $stmt->execute(['uploads/photos/' . $filename]);
        echo json_encode(['success' => true]);
        exit;
    }
    
} elseif ($method === 'DELETE') {
    // Delete Photo
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id'])) {
        http_response_code(400);
        exit;
    }

    // Get filename to unlink
    $stmt = $pdo->prepare("SELECT filename FROM photos WHERE id = ?");
    $stmt->execute([$data['id']]);
    $photo = $stmt->fetch();

    if ($photo) {
        $filepath = $baseDir . '/public/' . $photo['filename'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        $del = $pdo->prepare("DELETE FROM photos WHERE id = ?");
        $del->execute([$data['id']]);
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
    exit;
}
?>
