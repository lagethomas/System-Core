<?php
/** @var array $all_users */
?>


<div class="users-header">
    <h2>Gerenciamento de Usuários</h2>
    <p>Controle quem tem acesso ao sistema e seus níveis de permissão.</p>
</div>

<div class="user-list-card">
    <div class="user-list-header">
        <h3>Lista de Usuários</h3>
        <button class="btn-primary" onclick="openUserModal()">
            <i data-lucide="user-plus"></i> Novo Usuário
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
                            <div class="user-username-info">@<?php echo htmlspecialchars($u['username']); ?></div>
                            <div class="user-email-info"><?php echo htmlspecialchars($u['email']); ?></div>
                        </td>
                        <td>
                            <span class="badge badge-primary">
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
                                echo '<span style="color:var(--text-muted);font-size:0.8em">Nunca</span>';
                            }
                            ?>
                        </td>
                        <td class="text-right">
                            <button onclick="sendAccess(<?php echo $u['id']; ?>)" class="btn-user-action" title="Enviar Dados de Acesso" style="color: var(--primary);">
                                <i data-lucide="send"></i>
                            </button>
                            <button onclick="openUserModal(<?php echo htmlspecialchars(json_encode($u)); ?>)" class="btn-user-action" title="Editar">
                                <i data-lucide="edit"></i>
                            </button>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                <button onclick="deleteUser(<?php echo $u['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover">
                                    <i data-lucide="trash-2"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php echo \App\Core\Pagination::render($currentPage, $totalPages, SITE_URL . '/admin/users', $totalUsers, 25); ?>
</div>



<script>
function openUserModal(data = null) {
    const html = `
        <form action="<?php echo SITE_URL; ?>/api/admin/users/save" class="ajax-form">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            
            <div class="form-group mb-3">
                <label class="form-label">Nome Completo</label>
                <input type="text" name="name" class="form-control w-100" value="${data ? data.name : ''}" required onkeyup="${!data ? 'suggestUsername(this.value)' : ''}">
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" id="user-username" class="form-control w-100" value="${data ? data.username : ''}" ${data ? 'readonly' : 'required'}>
                </div>
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="form-control w-100" value="${data ? data.email : ''}" required>
                </div>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="phone" class="form-control mask-phone w-100" value="${data ? data.phone || '' : ''}" placeholder="(00) 00000-0000">
                </div>
                <div class="form-group">
                    <label class="form-label">Papel</label>
                    <select name="role" class="form-control w-100">
                        <option value="usuario" ${data && data.role === 'usuario' ? 'selected' : ''}>Usuário Comum</option>
                        <option value="administrador" ${data && data.role === 'administrador' ? 'selected' : ''}>Administrador</option>
                        <option value="proprietario" ${data && data.role === 'proprietario' ? 'selected' : ''}>Proprietário</option>
                    </select>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Senha ${data ? '(opcional)' : ''}</label>
                <div class="password-toggle-wrapper">
                    <i data-lucide="lock" class="input-icon"></i>
                    <input type="password" name="password" id="modal-password" class="form-control pl-10 pr-20" ${data ? '' : 'required'} placeholder="••••••••" data-toggle-init="true">
                    <button type="button" class="btn-password-toggle" onclick="UI.togglePassword(this, 'modal-password')" style="right: 40px !important;">
                        <i data-lucide="lock"></i>
                    </button>
                    <button type="button" onclick="UI.generatePassword('modal-password')" class="btn-generate-password" title="Gerar Senha">
                        <i data-lucide="shuffle"></i>
                    </button>
                </div>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">CEP</label>
                    <input type="text" name="zip_code" class="form-control mask-zip w-100" value="${data ? data.zip_code || '' : ''}" onblur="UI.lookupZip(this.value, 'user-city', 'user-state', 'user-street', 'user-neighborhood')">
                </div>
                <div class="form-group">
                    <label class="form-label">Rua / Logradouro</label>
                    <input type="text" name="street" id="user-street" class="form-control w-100" value="${data ? data.street || '' : ''}">
                </div>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">Bairro</label>
                    <input type="text" name="neighborhood" id="user-neighborhood" class="form-control w-100" value="${data ? data.neighborhood || '' : ''}">
                </div>
                <div class="form-group">
                    <label class="form-label">Cidade</label>
                    <input type="text" name="city" id="user-city" class="form-control w-100" value="${data ? data.city || '' : ''}">
                </div>
            </div>

            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">UF</label>
                    <input type="text" name="state" id="user-state" class="form-control w-100" value="${data ? data.state || '' : ''}" maxlength="2">
                </div>
                <div class="form-group">
                    <label class="form-label">Número</label>
                    <input type="text" name="address_number" class="form-control w-100" value="${data ? data.address_number || '' : ''}">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    ${data ? 'Salvar Alterações' : 'Criar Usuário'}
                </button>
            </div>
        </form>
    `;
    UI.showModal(data ? 'Editar Usuário' : 'Novo Usuário', html);
    
    // Initialize masks after injection
    UI.initMasks();
}

function suggestUsername(name) {
    const input = document.getElementById('user-username');
    if (!input || input.readOnly) return;
    input.value = name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]/g, '.').replace(/\.+/g, '.').replace(/^\.|\.$/g, '');
}

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
        if (res && res.success) {
            UI.showToast('Dados de acesso enviados!');
        }
    }
}

async function deleteUser(id) {
    if (await UI.confirm('Deseja realmente remover este usuário?')) {
        const formData = new FormData();
        formData.append('id', id);
        
        const res = await UI.request('<?php echo SITE_URL; ?>/api/admin/users/delete', formData);
        if (res && res.success) {
            UI.showToast('Usuário removido');
            window.location.reload();
        }
    }
}
</script>


