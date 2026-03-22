<?php
/** @var array $categorias */
/** @var array $nonces */
?>

<div class="users-header">
    <div style="display: flex; justify-content: space-between; align-items: center; width:100%;">
        <div>
            <h2>Categorias de Produtos</h2>
            <p>Organize seus produtos e controle o que aparece no cardápio.</p>
        </div>
        <button class="btn-primary" onclick="openCategoriaModal()">
            <i class="fas fa-plus"></i> Nova Categoria
        </button>
    </div>
</div>

<div class="user-list-card">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th width="80">Ícone</th>
                    <th>Nome</th>
                    <th>Slug</th>
                    <th>Ordem</th>
                    <th>Qtd. Produtos</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categorias)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-tags" style="font-size: 40px; opacity: 0.1; display: block; margin-bottom: 15px;"></i>
                            <span class="text-muted">Nenhuma categoria cadastrada.</span>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categorias as $c): ?>
                        <tr>
                            <td>
                                <div style="width: 40px; height: 40px; background: rgba(var(--primary-rgb), 0.1); color: var(--primary); display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                    <i class="fas <?php echo htmlspecialchars($c['icone']); ?>"></i>
                                </div>
                            </td>
                            <td><strong><?php echo htmlspecialchars($c['nome']); ?></strong></td>
                            <td><code style="background: rgba(255,255,255,0.05); padding: 2px 6px; border-radius: 4px; font-size: 11px;"><?php echo htmlspecialchars($c['slug']); ?></code></td>
                            <td><?php echo (int)$c['ordem']; ?></td>
                            <td>
                                <div class="badge" style="background: rgba(255,255,255,0.05);">
                                    <?php echo (int)$c['total_produtos']; ?> itens
                                </div>
                            </td>
                            <td class="text-right">
                                <button onclick="openCategoriaModal(<?php echo htmlspecialchars(json_encode($c)); ?>)" class="btn-user-action" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteCategoria(<?php echo $c['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const CAT_NONCES = <?php echo json_encode($nonces); ?>;

function openCategoriaModal(data = null) {
    const html = `
        <form action="<?php echo SITE_URL; ?>/api/admin/categorias/save" class="ajax-form">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            <input type="hidden" name="nonce" value="${CAT_NONCES.save}">
            
            <div class="form-group mb-4">
                <label class="form-label">Nome da Categoria</label>
                <input type="text" name="nome" class="form-control" value="${data ? data.nome : ''}" required placeholder="Ex: Bebidas, Sobremesas...">
            </div>

            <div class="form-grid-2 mb-4">
                <div class="form-group">
                    <label class="form-label">Ícone (FontAwesome)</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" name="icone" class="form-control" value="${data ? data.icone : 'fa-tags'}" required>
                        <div style="width: 45px; height: 45px; background: rgba(var(--primary-rgb), 0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                             <i id="preview-icon" class="fas ${data ? data.icone : 'fa-tags'}"></i>
                        </div>
                    </div>
                    <small class="text-muted">Ex: fa-coffee, fa-beer, fa-pizza-slice</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Ordem de Exibição</label>
                    <input type="number" name="ordem" class="form-control" value="${data ? data.ordem : '0'}">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">
                    ${data ? 'Salvar Alterações' : 'Criar Categoria'}
                </button>
            </div>
        </form>
    `;
    UI.showModal(data ? 'Editar Categoria' : 'Nova Categoria', html);
    
    // Icon preview update
    const iconInput = document.querySelector('input[name="icone"]');
    if (iconInput) {
        iconInput.oninput = (e) => {
            const preview = document.getElementById('preview-icon');
            if (preview) {
                preview.className = 'fas ' + (e.target.value || 'fa-question');
            }
        };
    }
}

async function deleteCategoria(id) {
    if (await UI.confirm('Deseja realmente remover esta categoria? Produtos associados não serão removidos, mas ficarão sem categoria.')) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/admin/categorias/delete', { id, nonce: CAT_NONCES.delete });
        if (res && res.success) {
            UI.showToast('Categoria removida');
            window.location.reload();
        }
    }
}
</script>
