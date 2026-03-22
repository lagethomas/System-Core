<?php
declare(strict_types=1);

/**
 * Nonce Helper (Stateless Action Verification)
 * Following Rule 6 of development guidelines.
 */
class Nonce {
    /**
     * Gera um nonce baseado no usuário, ação e um salt do sistema.
     * @param string $action Identificador único da ação (ex: 'caixa_fechar', 'financeiro_add')
     * @param int|null $user_id ID do usuário (0 para visitantes)
     * @return string
     */
    public static function create(string $action, ?int $user_id = null): string {
        if ($user_id === null) {
            $user_id = (int)($_SESSION['user_id'] ?? 0);
        }
        
        $salt = $_ENV['APP_KEY'] ?? 'saasflow_default_salt';
        // Baseamos no ID do usuário e em um segredo do servidor (.env / APP_KEY)
        return hash_hmac('sha256', $action . $user_id, $salt);
    }

    /**
     * Valida se o nonce fornecido é legítimo para a ação/usuário.
     * @param string $nonce O nonce enviado via form/ajax
     * @param string $action A ação pretendida
     * @param int|null $user_id
     * @return bool
     */
    public static function verify(string $nonce, string $action, ?int $user_id = null): bool {
        if (empty($nonce)) return false;
        
        $expected = self::create($action, $user_id);
        return hash_equals($expected, $nonce);
    }
}
