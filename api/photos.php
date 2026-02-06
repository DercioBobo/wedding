<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Ensure upload dir exists
$uploadDir = '../public/uploads/photos/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/**
 * Compress and Resize Image
 */
function processImage($sourcePath, $destPath, $maxDim = 1920, $quality = 80) {
    list($width, $height, $type) = getimagesize($sourcePath);
    
    // Load Image
    switch ($type) {
        case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($sourcePath); break;
        case IMAGETYPE_PNG:  $image = imagecreatefrompng($sourcePath); break;
        case IMAGETYPE_WEBP: $image = imagecreatefromwebp($sourcePath); break;
        default: return false;
    }

    // Calculate new dimensions
    $ratio = $width / $height;
    if ($width > $maxDim || $height > $maxDim) {
        if ($width > $height) {
            $newWidth = $maxDim;
            $newHeight = $maxDim / $ratio;
        } else {
            $newHeight = $maxDim;
            $newWidth = $maxDim * $ratio;
        }
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Handle Transparency (for PNG/WEBP being saved as JPEG, we fill white, or keep transparency if saving as same)
    // For this gallery, we'll standardize on keeping mostly original look but compressing. 
    // Since we output as JPEG for max compression usually, let's handle alpha.
    // Actually, usually easier to save as JPEG to ensure small size for photos.
    
    // Fill white background for transparency (good for photos)
    $white = imagecolorallocate($newImage, 255, 255, 255);
    imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $white);

    // Resize
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save as JPEG with compression
    // We intentionally change extension to .jpg in the main logic to match this
    $result = imagejpeg($newImage, $destPath, $quality);

    // Cleanup
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

        // Generate new filename (always jpg for consistency & ease of compression)
        $filename = uniqid('photo_') . '.jpg';
        $dest = $uploadDir . $filename;

        // Process Image
        if (processImage($file['tmp_name'], $dest)) {
            $stmt = $pdo->prepare("INSERT INTO photos (filename) VALUES (?)");
            $stmt->execute(['uploads/photos/' . $filename]);
            echo json_encode(['success' => true]);
        } else {
            // Fallback: move original if processing fails (e.g. GD missing)
             if (move_uploaded_file($file['tmp_name'], $dest)) {
                $stmt = $pdo->prepare("INSERT INTO photos (filename) VALUES (?)");
                $stmt->execute(['uploads/photos/' . $filename]);
                echo json_encode(['success' => true, 'warning' => 'Compression failed, saved original']);
             } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to save file']);
             }
        }
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
        $filepath = '../public/' . $photo['filename'];
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
