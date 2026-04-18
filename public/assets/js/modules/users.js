/**
 * Users Module Logic
 */

window.openUserModal = function(data = null) {
    const html = `
        <form action="${window.SITE_URL}/api/admin/users/save" method="POST" class="ajax-form">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            <input type="hidden" name="csrf_token" value="${window.CSRF_TOKEN}">
            
            <div class="floating-group mb-4">
                <input type="text" name="name" class="form-control" value="${data ? data.name : ''}" required onkeyup="${!data ? 'suggestUsername(this.value)' : ''}" placeholder=" ">
                <label class="floating-label">Nome Completo</label>
            </div>

            <div class="form-grid-2 gap-4 mb-4">
                <div class="floating-group">
                    <input type="text" name="username" id="user-username" class="form-control" value="${data ? data.username : ''}" ${data ? 'readonly' : 'required'} placeholder=" ">
                    <label class="floating-label">Username</label>
                </div>
                <div class="floating-group">
                    <input type="email" name="email" class="form-control" value="${data ? data.email : ''}" required placeholder=" ">
                    <label class="floating-label">E-mail</label>
                </div>
            </div>

            <div class="form-grid-2 gap-4 mb-4">
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

            <div class="floating-group mb-4">
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

            <div class="form-grid-2 gap-4 mb-4">
                <div class="floating-group">
                    <input type="text" name="zip_code" class="form-control mask-zip" value="${data ? data.zip_code || '' : ''}" onblur="UI.lookupZip(this.value, 'user-city', 'user-state', 'user-street', 'user-neighborhood')" placeholder=" ">
                    <label class="floating-label">CEP</label>
                </div>
                <div class="floating-group">
                    <input type="text" name="street" id="user-street" class="form-control" value="${data ? data.street || '' : ''}" placeholder=" ">
                    <label class="floating-label">Rua / Logradouro</label>
                </div>
            </div>

            <div class="form-grid-2 gap-4 mb-4">
                <div class="floating-group">
                    <input type="text" name="neighborhood" id="user-neighborhood" class="form-control" value="${data ? data.neighborhood || '' : ''}" placeholder=" ">
                    <label class="floating-label">Bairro</label>
                </div>
                <div class="floating-group">
                    <input type="text" name="city" id="user-city" class="form-control" value="${data ? data.city || '' : ''}" placeholder=" ">
                    <label class="floating-label">Cidade</label>
                </div>
            </div>

            <div class="form-grid-2 gap-4 mb-4">
                <div class="floating-group">
                    <input type="text" name="state" id="user-state" class="form-control" value="${data ? data.state || '' : ''}" maxlength="2" placeholder=" ">
                    <label class="floating-label">UF</label>
                </div>
                <div class="floating-group">
                    <input type="text" name="address_number" class="form-control" value="${data ? data.address_number || '' : ''}" placeholder=" ">
                    <label class="floating-label">Número</label>
                </div>
            </div>

            <div class="modal-footer pt-4 border-top d-flex justify-content-end gap-2">
                <button type="button" class="btn-dark" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    <i data-lucide="save" class="icon-sm mr-2"></i> ${data ? 'Salvar Alterações' : 'Criar Usuário'}
                </button>
            </div>
        </form>
    `;
    UI.showModal(data ? 'Editar Usuário' : 'Novo Usuário', html, 'lg');
}

window.suggestUsername = function(name) {
    const input = document.getElementById('user-username');
    if (!input || input.readOnly) return;
    input.value = name.toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]/g, '.')
        .replace(/\.+/g, '.')
        .replace(/^\.|\.$/g, '');
}

window.sendAccess = async function(id) {
    const confirm = await UI.confirm('Deseja realmente gerar e enviar novos dados de acesso para este usuário por e-mail? A senha atual dele será alterada.');
    if (confirm) {
        const formData = new FormData();
        formData.append('id', id);

        const res = await UI.request(`${window.SITE_URL}/api/admin/users/send-access`, formData);
        if (res && res.success) {
            UI.showToast(res.message, 'success');
        }
    }
}

window.deleteUser = async function(id) {
    const confirm = await UI.confirm('Deseja realmente remover este usuário?');
    if (confirm) {
        const formData = new FormData();
        formData.append('id', id);
        formData.append('csrf_token', window.CSRF_TOKEN);
        
        const res = await UI.request(`${window.SITE_URL}/api/admin/users/delete`, formData);
        if (res && res.success) {
            const row = document.querySelector(`button[onclick*="deleteUser(${id})"]`)?.closest('tr');
            if (row) {
                row.style.transition = 'all 0.4s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(20px)';
                setTimeout(() => row.remove(), 400);
            }
            UI.showToast(res.message, 'success');
        }
    }
}
