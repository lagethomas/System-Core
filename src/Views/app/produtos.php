<?php
/** @var array $produtos */
?>

<div class="page-header">
    <div>
        <h2 style="color: var(--primary); margin-bottom: 5px;">Gestão de Produtos</h2>
        <p style="color: var(--text-muted);">Administre seu catálogo de itens, preços e imagens.</p>
    </div>
    <div class="page-header-actions">
        <button class="btn-primary" onclick="openProdutoModal()">
            <i class="fas fa-plus"></i> Novo Produto
        </button>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Código</th>
                    <th>Nome</th>
                    <th>Categoria</th>
                    <th>No Cardápio</th>
                    <th>Preço</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr><td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted);">Nenhum produto cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($produtos as $p): ?>
                    <tr>
                        <td>
                            <div class="product-info-cell">
                                <div class="product-image-mini">
                                    <?php if ($p['imagem']): ?>
                                        <img src="<?php echo SITE_URL . $p['imagem']; ?>" alt="">
                                    <?php else: ?>
                                        <i class="fas fa-image"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="product-name"><?php echo htmlspecialchars($p['nome']); ?></div>
                                    <div class="product-code">#<?php echo htmlspecialchars($p['codigo']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(var(--primary-rgb), 0.1); color: var(--primary);">
                                <?php echo htmlspecialchars($p['categoria_nome'] ?: ($p['categoria'] ?: 'Sem Categoria')); ?>
                            </span>
                        </td>
                        <td style="color: var(--primary); font-weight: 700;">R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></td>
                        <td>
                            <?php if ($p['disponivel_cardapio'] == 1): ?>
                                <span class="badge status-active" style="padding: 2px 8px; font-size: 10px;"><i class="fas fa-check"></i> Sim</span>
                            <?php else: ?>
                                <span class="badge status-danger" style="padding: 2px 8px; font-size: 10px;"><i class="fas fa-times"></i> Não</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right">
                            <button onclick="openProdutoModal(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="btn-user-action" title="Editar"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteProduto(<?php echo $p['id']; ?>)" class="btn-user-action btn-user-delete" title="Remover"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const PRODUCT_NONCES = <?php echo json_encode($nonces); ?>;
    const CATEGORIAS = <?php echo json_encode($categorias); ?>;

    function openProdutoModal(data = null) {
    const html = `
        <form id="form-produto" enctype="multipart/form-data">
            <input type="hidden" name="id" value="${data ? data.id : ''}">
            <input type="hidden" name="nonce" value="${PRODUCT_NONCES.save}">
            
            <div class="form-grid-2 mb-3">
                <div class="form-group">
                    <label class="form-label">Código (Opcional)</label>
                    <input type="text" name="codigo" class="form-control" value="${data ? data.codigo : ''}" placeholder="Gerado automaticamente se vazio">
                </div>
                <div class="form-group">
                    <label class="form-label">Categoria</label>
                    <select name="categoria_id" class="form-control">
                        <option value="">Selecione uma Categoria...</option>
                        ${CATEGORIAS.map(cat => `
                            <option value="${cat.id}" ${data && data.categoria_id == cat.id ? 'selected' : ''}>${cat.nome}</option>
                        `).join('')}
                    </select>
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Nome do Produto</label>
                <input type="text" name="nome" class="form-control" value="${data ? data.nome : ''}" required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Descrição (Informações detalhadas)</label>
                <textarea name="descricao" class="form-control" rows="3" placeholder="Ex: 250g de carne, alface, tomate...">${data ? data.descricao || '' : ''}</textarea>
            </div>

            <div class="form-grid-2 mb-3">
                 <div class="form-group">
                    <label class="form-label">Preço (R$)</label>
                    <input type="text" name="preco" class="form-control mask-money" value="${data ? data.preco : ''}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Foto do Produto</label>
                    <div class="modern-upload">
                        <input type="file" name="imagem" id="prod-img-input" accept="image/*" onchange="document.getElementById('img-name-preview').innerText = this.files[0].name">
                        <label for="prod-img-input">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span id="img-name-preview">Selecionar Foto</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <h6 class="mb-0" style="font-size: 14px; font-weight: 700; color:var(--text-main)">Disponível no Cardápio</h6>
                        <small class="text-muted" style="font-size: 11px;">Aparecerá na página pública via QR Code</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="disponivel_cardapio" value="1" ${!data || data.disponivel_cardapio == 1 ? 'checked' : ''}>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="UI.closeModal()">Cancelar</button>
                <button type="submit" class="btn-primary">${data ? 'Salvar Alterações' : 'Cadastrar'}</button>
            </div>
        </form>
    `;
    UI.showModal(data ? 'Editar Produto' : 'Novo Produto', html);
    
    document.getElementById('form-produto').onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        
        try {
            const res = await fetch('<?php echo SITE_URL; ?>/api/produtos/save', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                UI.showToast('Produto salvo!');
                window.location.reload();
            } else {
                UI.showToast(result.message || 'Erro', 'error');
            }
        } catch (err) {
            UI.showToast('Erro na conexão', 'error');
        }
    };
}

async function deleteProduto(id) {
    if (await UI.confirm('Deseja realmente remover este produto?', { icon: 'fa-trash' })) {
        const res = await UI.request('<?php echo SITE_URL; ?>/api/produtos/delete', { id, nonce: PROD_NONCES.delete });
        if (res && res.success) window.location.reload();
    }
}
</script>
