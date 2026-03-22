<?php
/** @var string $user_name */
/** @var int $total_users */
/** @var int $total_logs */
/** @var PDO $pdo */
?>


<div class="welcome-header" style="margin-bottom: 30px; background: linear-gradient(135deg, var(--primary) 0%, #fff 300%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
    <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 5px;">Olá, <?php echo htmlspecialchars($user_name); ?>! 👋</h1>
    <p style="color: var(--text-muted); font-size: 16px; -webkit-text-fill-color: var(--text-muted);">Bem-vindo ao centro de controle do seu novo sistema.</p>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div class="stat-card" style="background: var(--bg-card); padding: 25px; border-radius: 12px; border: 1px solid var(--border); display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; background: rgba(var(--primary-rgb), 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 24px;">
            <i class="fas fa-users"></i>
        </div>
        <div>
            <h3 style="margin: 0; font-size: 14px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Usuários</h3>
            <div style="font-size: 24px; font-weight: 800; color: var(--text-main);"><?php echo $total_users; ?></div>
        </div>
    </div>

    <div class="stat-card" style="background: var(--bg-card); padding: 25px; border-radius: 12px; border: 1px solid var(--border); display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; background: rgba(52, 211, 153, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #34d399; font-size: 24px;">
            <i class="fas fa-terminal"></i>
        </div>
        <div>
            <h3 style="margin: 0; font-size: 14px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Atividades</h3>
            <div style="font-size: 24px; font-weight: 800; color: var(--text-main);"><?php echo $total_logs; ?></div>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <div class="card" style="background: var(--bg-card); border-radius: 12px; padding: 25px; border: 1px solid var(--border);">
        <h3 style="margin-top: 0;">Resumo do Sistema</h3>
        <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">
            Este é o seu sistema base **SaaSFlow Core**. Ele foi limpo e otimizado para servir como ponto de partida para novos projetos.
            <br><br>
            **O que está incluído:**
            <ul style="color: var(--text-muted); font-size: 14px; padding-left: 20px;">
                <li>Estrutura de pastas profissional e escalável.</li>
                <li>Autenticação segura e controle de acesso Admin.</li>
                <li>Gerenciamento de usuários simplificado.</li>
                <li>Logs globais de atividades.</li>
                <li>Sistema de temas dinâmicos.</li>
            </ul>
        </p>
        <a href="<?php echo SITE_URL; ?>/admin/settings" class="btn-primary" style="display: inline-block; padding: 12px 25px; border-radius: 8px; text-decoration: none; margin-top: 15px;">
            Configurar Sistema
        </a>
    </div>

    <div class="card" style="background: var(--bg-card); border-radius: 12px; padding: 25px; border: 1px solid var(--border);">
        <h3 style="margin-top: 0; font-size: 18px; margin-bottom: 20px;">Link do Cardápio Público</h3>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <div style="background: rgba(255,255,255,0.03); border: 1px dashed var(--border); padding: 15px; border-radius: 10px; text-align: center;">
                <i class="fas fa-utensils mb-2" style="color: var(--primary); font-size: 24px; display: block;"></i>
                <small style="color: var(--text-muted); display: block; margin-bottom: 10px;">Compartilhe seu cardápio com os clientes</small>
                <div style="display: flex; gap: 10px;">
                    <button class="btn-primary" style="flex: 1; padding: 10px; font-size: 13px; border-radius: 8px;" onclick="UI.copyToClipboard('<?php echo SITE_URL; ?>/cardapio')">
                        <i class="fas fa-copy"></i> Copiar Link
                    </button>
                    <button class="btn-secondary" style="flex: 1; padding: 10px; font-size: 13px; border-radius: 8px;" onclick="UI.showQRModal('<?php echo SITE_URL; ?>/cardapio', 'Seu Cardápio Digital')">
                        <i class="fas fa-qrcode"></i> QRCode
                    </button>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border); margin: 10px 0;">

            <h3 style="margin-top: 0; font-size: 16px; margin-bottom: 15px;">Links Rápidos</h3>
            <a href="<?php echo SITE_URL; ?>/admin/users" class="btn-secondary" style="display: flex; align-items: center; gap: 10px; padding: 12px; border-radius: 8px; text-decoration: none; color: var(--text-main); background: rgba(255,255,255,0.03); border: 1px solid var(--border);">
                <i class="fas fa-user-plus"></i> Gerenciar Usuários
            </a>
            <a href="<?php echo SITE_URL; ?>/profile" class="btn-secondary" style="display: flex; align-items: center; gap: 10px; padding: 12px; border-radius: 8px; text-decoration: none; color: var(--text-main); background: rgba(255,255,255,0.03); border: 1px solid var(--border);">
                <i class="fas fa-user-circle"></i> Meu Perfil
            </a>
            <a href="<?php echo SITE_URL; ?>/admin/logs" class="btn-secondary" style="display: flex; align-items: center; gap: 10px; padding: 12px; border-radius: 8px; text-decoration: none; color: var(--text-main); background: rgba(255,255,255,0.03); border: 1px solid var(--border);">
                <i class="fas fa-list"></i> Ver Logs Globais
            </a>
        </div>
    </div>
</div>


