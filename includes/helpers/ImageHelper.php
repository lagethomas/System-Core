<?php
declare(strict_types=1);

/**
 * ImageHelper - Gerenciamento e Otimização de Imagens
 * Converte uploads para WebP automaticamente para economizar espaço e melhorar performance.
 */
class ImageHelper {
    
    /**
     * Processa um upload de imagem, converte para WebP e remove a original.
     * 
     * @param array $file O array do $_FILES['campo']
     * @param string $targetDir Diretório de destino
     * @param string $customName Nome opcional para o arquivo
     * @return string|bool Nome do arquivo final ou false
     */
    public static function uploadAndConvert(array $file, string $targetDir, string $customName = ''): string|bool {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Limpa o nome do arquivo
        $filename = $customName ?: pathinfo($file['name'], PATHINFO_FILENAME);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        $filename = $filename . '_' . uniqid();
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $tempPath = $targetDir . DIRECTORY_SEPARATOR . $filename . '.' . $extension;

        if (move_uploaded_file($file['tmp_name'], $tempPath)) {
            $webpPath = self::convertToWebp($tempPath);
            return $webpPath ? basename($webpPath) : false;
        }

        return false;
    }

    /**
     * Converte um arquivo local para WebP
     */
    public static function convertToWebp(string $sourcePath, int $quality = 80): string|bool {
        if (!file_exists($sourcePath)) return false;

        $info = getimagesize($sourcePath);
        if (!$info) return false;

        $directory = pathinfo($sourcePath, PATHINFO_DIRNAME);
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $newPath = $directory . DIRECTORY_SEPARATOR . $filename . '.webp';

        // Se já for WebP, apenas retorna
        if ($info['mime'] === 'image/webp') return $sourcePath;

        $image = null;
        switch ($info['mime']) {
            case 'image/jpeg': $image = imagecreatefromjpeg($sourcePath); break;
            case 'image/png':  $image = imagecreatefrompng($sourcePath);  break;
            case 'image/gif':  $image = imagecreatefromgif($sourcePath);  break;
            default: return false;
        }

        if (!$image) return false;

        // Manter transparência
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        if (imagewebp($image, $newPath, $quality)) {
            imagedestroy($image);
            if ($sourcePath !== $newPath) {
                unlink($sourcePath); // Deleta original
            }
            return $newPath;
        }

        imagedestroy($image);
        return false;
    }

    /**
     * Remove um arquivo do servidor com segurança
     */
    public static function safeDelete(?string $filename, string $targetDir): bool {
        if (!$filename) return false;
        
        $path = realpath($targetDir . DIRECTORY_SEPARATOR . $filename);
        if ($path && file_exists($path) && is_file($path)) {
            return unlink($path);
        }
        return false;
    }
}
