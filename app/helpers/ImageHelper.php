<?php

namespace App\Helpers;

class ImageHelper
{
    private const UPLOAD_BASE_PATH = APPROOT . '/uploads/establishments/';
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /**
     * Upload and save establishment image
     * 
     * @param array $file $_FILES array element
     * @param string $type 'logo' or 'header'
     * @param int $establishmentId
     * @return array Result with success status and file path or error message
     */
    public static function uploadEstablishmentImage($file, $type, $establishmentId)
    {
        try {
            // Validate file upload
            $validation = self::validateUploadedFile($file);
            if (!$validation['success']) {
                return $validation;
            }

            // Validate type
            if (!in_array($type, ['logo', 'header'])) {
                return ['success' => false, 'error' => 'Invalid image type. Must be logo or header.'];
            }

            // Create directory path
            $subDirectory = $type === 'logo' ? 'logos' : 'headers';
            $uploadDir = self::UPLOAD_BASE_PATH . $subDirectory . '/';
            
            // Ensure directory exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = $establishmentId . '_' . $type . '_' . time() . '_' . uniqid() . '.' . $extension;
            $fullPath = $uploadDir . $filename;

            // Resize image if needed
            $resizedImage = self::resizeImage($file['tmp_name'], $file['type'], $type);
            if (!$resizedImage['success']) {
                return $resizedImage;
            }

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $fullPath)) {
                // Apply additional image processing if resized
                if ($resizedImage['processed']) {
                    self::applyImageProcessing($fullPath, $type);
                }

                return [
                    'success' => true,
                    'filename' => $filename,
                    'path' => $subDirectory . '/' . $filename,
                    'full_path' => $fullPath
                ];
            } else {
                return ['success' => false, 'error' => 'Failed to upload file.'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Upload error: ' . $e->getMessage()];
        }
    }

    /**
     * Validate uploaded file
     */
    private static function validateUploadedFile($file)
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.'
            ];
            
            $error = $errorMessages[$file['error']] ?? 'Unknown upload error.';
            return ['success' => false, 'error' => $error];
        }

        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            return ['success' => false, 'error' => 'File size exceeds maximum allowed size (5MB).'];
        }

        // Check MIME type
        if (!in_array($file['type'], self::ALLOWED_TYPES)) {
            return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, and WebP images are allowed.'];
        }

        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            return ['success' => false, 'error' => 'Invalid file extension.'];
        }

        // Additional security: Check if file is actually an image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['success' => false, 'error' => 'File is not a valid image.'];
        }

        return ['success' => true];
    }

    /**
     * Resize image based on type
     */
    private static function resizeImage($tmpPath, $mimeType, $type)
    {
        $maxDimensions = [
            'logo' => ['width' => 400, 'height' => 400],
            'header' => ['width' => 1200, 'height' => 400]
        ];

        $maxWidth = $maxDimensions[$type]['width'];
        $maxHeight = $maxDimensions[$type]['height'];

        $imageInfo = getimagesize($tmpPath);
        if (!$imageInfo) {
            return ['success' => false, 'error' => 'Cannot get image dimensions.'];
        }

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];

        // If image is already within limits, no need to resize
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return ['success' => true, 'processed' => false];
        }

        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = (int)($originalWidth * $ratio);
        $newHeight = (int)($originalHeight * $ratio);

        // Create image resource based on type
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $source = imagecreatefromjpeg($tmpPath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($tmpPath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($tmpPath);
                break;
            default:
                return ['success' => false, 'error' => 'Unsupported image type for resizing.'];
        }

        if (!$source) {
            return ['success' => false, 'error' => 'Failed to create image resource.'];
        }

        // Create new image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefill($resized, 0, 0, $transparent);
        }

        // Resize image
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Save resized image back to temp file
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                imagejpeg($resized, $tmpPath, 90);
                break;
            case 'image/png':
                imagepng($resized, $tmpPath, 8);
                break;
            case 'image/webp':
                imagewebp($resized, $tmpPath, 90);
                break;
        }

        // Clean up
        imagedestroy($source);
        imagedestroy($resized);

        return ['success' => true, 'processed' => true];
    }

    /**
     * Apply additional image processing (optimization)
     */
    private static function applyImageProcessing($filePath, $type)
    {
        // Additional optimization can be added here
        // For now, just ensure proper file permissions
        chmod($filePath, 0644);
    }

    /**
     * Delete establishment image file
     */
    public static function deleteEstablishmentImage($imagePath)
    {
        if (empty($imagePath)) {
            return true; // Nothing to delete
        }

        $fullPath = self::UPLOAD_BASE_PATH . $imagePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return true; // File doesn't exist, consider it deleted
    }

    /**
     * Get image URL for viewing (through the protected endpoint)
     */
    public static function getImageUrl($imagePath)
    {
        if (empty($imagePath)) {
            return null;
        }

        return URLROOT . '/establishment/image/' . htmlspecialchars($imagePath);
    }

    /**
     * Check if image file exists
     */
    public static function imageExists($imagePath)
    {
        if (empty($imagePath)) {
            return false;
        }

        $fullPath = self::UPLOAD_BASE_PATH . $imagePath;
        return file_exists($fullPath);
    }

    /**
     * Get image file path for serving
     */
    public static function getImageFilePath($imagePath)
    {
        if (empty($imagePath)) {
            return false;
        }

        $fullPath = self::UPLOAD_BASE_PATH . $imagePath;
        
        // Security check: ensure path is within allowed directory
        $allowedPath = realpath(self::UPLOAD_BASE_PATH);
        
        // Check if file exists first
        if (!file_exists($fullPath)) {
            return false;
        }
        
        $realPath = realpath($fullPath);
        
        if ($realPath && $allowedPath && strpos($realPath, $allowedPath) === 0) {
            return $realPath;
        }
        
        return false;
    }
}
