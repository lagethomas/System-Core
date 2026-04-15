<?php
/** @var array $all_users */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $totalUsers */
/** @var string $searchTerm */
?>

<div class="users-header">
    <h2>Gerenciamento de Usuários</h2>
    <p>Controle quem tem acesso ao sistema e seus níveis de permissão.</p>
</div>

<div class="card user-list-card">
    <div class="user-list-header">
        <h3>Lista de Usuários</h3>
        <button class="btn-primary" onclick="openUserModal()">
            <i data-lucide="user-plus" class="icon-lucide"></i> Novo Usuário
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
                        <td class="user-name-cell"><?php echo htmlspecialchars($u['name']); ?></td>
                        <td>
                            <div class="user-username-small">@<?php echo htmlspecialchars($u['username']); ?></div>
                            <div class="user-email-small"><?php echo htmlspecialchars($u['email']); ?></div>
                        </td>
                        <td>
                            <span class="user-role-badge">
                                <?php echo htmlspecialchars($u['role']); ?>
                            </span>
                        </td>
                        <td class="user-last-login">
                            <?php
                            if (!empty($u['last_login'])) {
                                $ts   = strtotime($u['last_login']);
                                $diff = time() - $ts;
                                if ($diff < 60)          $rel = 'Agora mesmo';
                                elseif ($diff < 3600)    $rel = floor($diff / 60) . 'min atrás';
                                elseif ($diff < 86400)   $rel = floor($diff / 3600) . 'h atrás';
                                elseif ($diff < 604800)  $rel = floor($diff / 86400) . 'd atrás';
                                else                     $rel = date('d/m/Y', $ts);
                                echo '<span title="' . date('d/m/Y H:i:s', $ts) . '" style="cursor:default">' . $rel . '</span>';
                            } else {
                                echo '<span>Nunca</span>';
                            }
                            ?>
                        </td>
                        <td class="text-right">
                            <button onclick="sendAccess(<?php echo $u['id']; ?>)" class="btn-user-action icon-blue" title="Enviar Dados de Acesso">
                                <i data-lucide="send" class="icon-lucide"></i>
                            </button>
                            <button onclick='openUserModal(<?php echo htmlspecialchars(json_encode($u), JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' class="btn-user-action" title="Editar">
                                <i data-lucide="edit" class="icon-lucide"></i>
                            </button>
                            <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                                <button onclick="deleteUser(<?php echo $u['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover">
                                    <i data-lucide="trash-2" class="icon-lucide"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php echo \App\Core\Pagination::render($currentPage, $totalPages, SITE_URL . '/admin/users', $totalUsers, 10); ?>
</div>

<script>
function openUserModal(data = null) {
    const html = `
        <form action="<?php echo SITE_URL; ?>/api/admin/users/save" method="POST" class="ajax-form">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            
            <div class="floating-group">
                <input type="text" name="name" class="form-control" value="${data ? data.name : ''}" required onkeyup="${!data ? 'suggestUsername(this.value)' : ''}" placeholder=" ">
                <label class="floating-label">Nome Completo</label>
            </div>

            <div class="form-grid-2">
                <div class="floating-group">
                    <input type="text" name="username" id="user-username" class="form-control" value="${data ? data.username : ''}" ${data ? 'readonly' : 'required'} placeholder=" ">
                    <label class="floating-label">Username</label>
                </div>
                <div class="floating-group">
                    <input type="email" name="email" class="form-control" value="${data ? data.email : ''}" required placeholder=" ">
                    <label class="floating-label">E-mail</label>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="floating-group">
                    <input type="text" name="phone" class="form-control mask-phone" value="${data ? data.phone || '' : ''}" placeholder=" ">
                    <label class="floating-label">Telefone</label>
                </div>
                <div class="floating-group">
                    <select name="role" class="form-control" placeholder=" ">
                        <option value="usuario" ${data && data.role === 'usuario' ? 'selected' : ''}>Usuário Comum</option>
                        <option value="administrador" ${data && data.role === 'administrador' ? 'selected' : ''}>Administrador</option>
                        <option value="proprietario" ${data && data.role === 'proprietario' ? 'selected' : ''}>Proprietário</option>
                    </select>
                    <label class="floating-label">Permissão</label>
                </div>
            </div>

            <div class="floating-group">
                <div class="password-toggle-wrapper">
                    <input type="password" name="password" id="modal-password" class="form-control" ${data ? '' : 'required'} placeholder=" ">
                    <label class="floating-label">Senha ${data ? '(deixe em branco para manter)' : ''}</label>
                    <div class="floating-actions">
                        <button type="button" class="btn-password-toggle" onclick="UI.togglePassword(this, 'modal-password')">
                            <i data-lucide="eye"></i>
                        </button>
                        <button type="button" onclick="UI.generatePassword('modal-password')" class="btn-generate-password" title="Gerar Senha">
                            <i data-lucide="shuffle"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="floating-group">
                    <input type="text" name="zip_code" class="form-control mask-zip" value="${data ? data.zip_code || '' : ''}" onblur="UI.lookupZip(this.value, 'user-city', 'user-state', 'user-street', 'user-neighborhood')" placeholder=" ">
                    <label class="floating-label">CEP</label>
                </div>
                <div class="floating-group">
                    <input type="text" name="street" id="user-street" class="form-control" value="${data ? data.street || '' : ''}" placeholder=" ">
                    <label class="floating-label">Rua / Logradouro</label>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="floating-group">
                    <input type="text" name="neighborhood" id="user-neighborhood" class="form-control" value="${data ? data.neighborhood || '' : ''}" placeholder=" ">
                    <label class="floating-label">Bairro</label>
                </div>
                <div class="floating-group">
                    <input type="text" name="city" id="user-city" class="form-control" value="${data ? data.city || '' : ''}" placeholder=" ">
                    <label class="floating-label">Cidade</label>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="floating-group">
                    <input type="text" name="state" id="user-state" class="form-control" value="${data ? data.state || '' : ''}" maxlength="2" placeholder=" ">
                    <label class="floating-label">UF</label>
                </div>
                <div class="floating-group">
                    <input type="text" name="address_number" class="form-control" value="${data ? data.address_number || '' : ''}" placeholder=" ">
                    <label class="floating-label">Número</label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-dark" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="icon-lucide icon-sm mr-2"></i> ${data ? 'Salvar Alterações' : 'Criar Usuário'}
                </button>
            </div>
        </form>
    `;
    UI.showModal(data ? 'Editar Usuário' : 'Novo Usuário', html, 'lg');
    UI.initMasks();
    if(window.lucide) lucide.createIcons();
}

function suggestUsername(name) {
    const input = document.getElementById('user-username');
    if (!input || input.readOnly) return;
    input.value = name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/g, '.').replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
}

document.addEventListener('ajaxSuccess', (e) => {
    if (e.target.action.includes('/api/admin/users/save')) {
        // window.location.reload(); // Deixando comentado pois o usuário pediu sem refresh nas mutações
    }
});

async function sendAccess(id) {
    if (await UI.confirm('Deseja realmente gerar e enviar novos dados de acesso para este usuário por e-mail? A senha atual dele será alterada.', {
        title: 'Confirmar Envio',
        confirmText: 'Sim, Enviar',
        type: 'success',
        icon: 'send'
    })) {
        const formData = new FormData();
        formData.append('id', id);

        const res = await UI.request('<?php echo SITE_URL; ?>/api/admin/users/send-access', formData);
    }
}

async function deleteUser(id) {
    if (await UI.confirm('Deseja realmente remover este usuário?')) {
        const formData = new FormData();
        formData.append('id', id);
        
        const res = await UI.request('<?php echo SITE_URL; ?>/api/admin/users/delete', formData);
        if (res && res.success) {
            const row = document.querySelector(`button[onclick*="deleteUser(${id})"]`)?.closest('tr');
            if (row) {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => row.remove(), 400);
            }
        }
    }
}
</script>
