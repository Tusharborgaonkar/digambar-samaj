<?php
require_once 'includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'gallery';

if ($id > 0) {
    if ($type === 'gallery') {
        $stmt = $pdo->prepare("SELECT image_path, title FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $media = $stmt->fetch();
        if ($media && strpos($media['image_path'], 'data:') === 0) {
            $data = $media['image_path'];
            // format: data:[<mediatype>][;base64],<data>
            $parts = explode(',', $data, 2);
            if (count($parts) === 2) {
                $typeInfo = $parts[0];
                $encodedData = $parts[1];
                
                $mime = 'application/octet-stream';
                if (preg_match('/^data:([^;]+)/', $typeInfo, $matches)) {
                    $mime = $matches[1];
                }
                
                $decodedData = base64_decode($encodedData);
                
                header("Content-Type: $mime");
                $filename = !empty($media['title']) ? $media['title'] : 'media';
                
                $ext = '';
                if ($mime === 'application/pdf') $ext = '.pdf';
                elseif ($mime === 'image/jpeg') $ext = '.jpg';
                elseif ($mime === 'image/png') $ext = '.png';
                elseif ($mime === 'video/mp4') $ext = '.mp4';
                
                $safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename) . $ext;
                header("Content-Disposition: inline; filename=\"" . $safe_filename . "\"");
                
                // Allow browser caching for media
                header('Cache-Control: public, max-age=86400');
                echo $decodedData;
                exit;
            }
        } elseif ($media && !empty($media['image_path'])) {
            header("Location: " . $media['image_path']);
            exit;
        }
    }
}
header("HTTP/1.1 404 Not Found");
echo "Media not found.";
exit;
