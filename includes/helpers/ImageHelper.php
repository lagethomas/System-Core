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
     * @param int $quality Qualidade da conversão (0-100)
     * @return string|bool Nome do arquivo final (.webp) ou false
     */
    public static function uploadAndConvert(array $file, string $targetDir, string $customName = '', int $quality = 80): string|bool {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Garante que o diretório existe
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Limpa e gera o nome do arquivo
        $filename = $customName ?: pathinfo($file['name'], PATHINFO_FILENAME);
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $filename);
        $filename = $filename . '_' . bin2hex(random_bytes(4));
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $tempPath = $targetDir . DIRECTORY_SEPARATOR . $filename . '.' . $extension;

        if (move_uploaded_file($file['tmp_name'], $tempPath)) {
            // Verifica se a extensão GD está ativa para conversão
            if (!extension_loaded('gd')) {
                // Se não houver GD, mantém o original (fallback de segurança)
                return basename($tempPath);
            }

            $webpPath = self::convertToWebp($tempPath, $quality);
            return $webpPath ? basename($webpPath) : basename($tempPath);
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
            default: return false; // Formato não suportado pela GD para conversão
        }

        if (!$image) return false;

        // Manter transparência para PNG/GIF
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        if (imagewebp($image, $newPath, $quality)) {
            imagedestroy($image);
            // Sempre removemos a original após a conversão bem-sucedida para WebP
            if (realpath($sourcePath) !== realpath($newPath)) {
                @unlink($sourcePath);
            }
            return $newPath;
        }

        imagedestroy($image);
        return false;
    }

    /**
     * Remove um arquivo do servidor com segurança, evitando escalada de diretório.
     * 
     * @param string|null $filename Nome do arquivo ou caminho parcial (ex: /uploads/logos/file.webp)
     * @param string $basePath Caminho base seguro (ex: /var/www/public/uploads)
     * @return bool
     */
    public static function safeDelete(?string $filename, string $basePath): bool {
        if (!$filename) return false;
        
        // Se o filename contiver "uploads/", tenta extrair apenas o nome base se estivermos passando o diretório específico em basePath
        // Ou tenta resolver o caminho completo de forma segura
        $filename = basename($filename);
        
        $path = realpath($basePath . DIRECTORY_SEPARATOR . $filename);
        $baseReal = realpath($basePath);

        // Verifica se o arquivo existe e se está dentro do diretório base permitido (segurança)
        if ($path && $baseReal && strpos($path, $baseReal) === 0 && file_exists($path) && is_file($path)) {
            return @unlink($path);
        }

        return false;
    }
}
