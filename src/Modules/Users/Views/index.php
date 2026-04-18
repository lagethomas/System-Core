<?php declare(strict_types=1);
/** @var array $all_users */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $totalUsers */
/** @var string $searchTerm */
?>

<div class="users-header mb-5">
    <div class="flex items-center gap-4">
        <div class="header-icon-box">
            <i data-lucide="users"></i>
        </div>
        <div>
            <h2 class="m-0">Gerenciamento de Usuários</h2>
            <p class="text-muted m-0">Controle quem tem acesso ao sistema e seus níveis de permissão.</p>
        </div>
    </div>
</div>

<div class="card user-list-card">
    <div class="card-header d-flex justify-content-between align-items-center mb-4">
        <h3 class="m-0">Lista de Usuários</h3>
        <button class="btn-primary" onclick="openUserModal()">
            <i data-lucide="user-plus" class="icon-sm"></i> Novo Usuário
        </button>
    </div>

    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Papel</th>
                    <th>Último Acesso</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($all_users as $u): ?>
                    <tr>
                        <td class="user-name-cell fw-700"><?php echo htmlspecialchars($u['name']); ?></td>
                        <td>
                            <div class="user-username-small text-primary small">@<?php echo htmlspecialchars($u['username']); ?></div>
                            <div class="user-email-small text-muted small"><?php echo htmlspecialchars($u['email']); ?></div>
                        </td>
                        <td>
                            <span class="status-badge status-primary">
                                <?php echo htmlspecialchars(ucfirst($u['role'])); ?>
                            </span>
                        </td>
                        <td class="user-last-login small">
                            <?php
                            if (!empty($u['last_login'])) {
                                $ts   = strtotime($u['last_login']);
                                $diff = time() - $ts;
                                if ($diff < 60)          $rel = 'Agora mesmo';
                                elseif ($diff < 3600)    $rel = floor($diff / 60) . 'min atrás';
                                elseif ($diff < 86400)   $rel = floor($diff / 3600) . 'h atrás';
                                elseif ($diff < 604800)  $rel = floor($diff / 86400) . 'd atrás';
                                else                     $rel = date('d/m/Y', $ts);
                                echo '<span title="' . date('d/m/Y H:i:s', $ts) . '">' . $rel . '</span>';
                            } else {
                                echo '<span>Nunca</span>';
                            }
                            ?>
                        </td>
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick="sendAccess(<?php echo $u['id']; ?>)" class="btn-primary-glass btn-sm" title="Enviar Dados de Acesso">
                                    <i data-lucide="send" class="icon-sm"></i>
                                </button>
                                <button onclick='openUserModal(<?php echo htmlspecialchars(json_encode($u), JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-dark btn-sm" title="Editar">
                                    <i data-lucide="edit" class="icon-sm"></i>
                                </button>
                                <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                                    <button onclick="deleteUser(<?php echo $u['id']; ?>)" class="btn-danger btn-sm" title="Remover">
                                        <i data-lucide="trash-2" class="icon-sm"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        <?php echo \App\Core\Pagination::render($currentPage, $totalPages, SITE_URL . '/users', $totalUsers, 10); ?>
    </div>
</div>

<script src="<?php echo SITE_URL; ?>/assets/js/modules/users.js"></script>
