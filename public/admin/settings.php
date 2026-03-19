<?php
<<<<<<<< HEAD:src/Views/admin/settings.php
/** @var array $settings */
/** @var string $active_tab */

include_once __DIR__ . '/../../../includes/header.php';
========
declare(strict_types=1);
require_once '../../includes/DB.php';
require_once dirname(__DIR__, 2) . "/includes/helpers/Auth.php";

Auth::requireAdmin();

$active_tab = $_GET['tab'] ?? 'general';

// Fetch Current Settings
$stmt = $pdo->prepare("SELECT setting_key, setting_value FROM cp_settings");
$stmt->execute();
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_general'])) {
        $keys = ['system_name', 'enable_system_logs'];
        foreach ($keys as $key) {
            $val = trim($_POST[$key] ?? '');
            if ($key === 'enable_system_logs') $val = isset($_POST[$key]) ? '1' : '0';
            
            $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$key, $val, $val]);
        }
        Cache::delete('platform_settings');
        header("Location: settings.php?tab=general&msg=saved");
        exit;
    }

    if (isset($_POST['save_theme'])) {
        $theme = $_POST['system_theme'] ?? 'gold-black';
        $stmt = $pdo->prepare("INSERT INTO cp_settings (setting_key, setting_value) VALUES ('system_theme', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$theme, $theme]);
        Cache::delete('platform_settings');
        
        header("Location: settings.php?tab=themes&msg=updated");
        exit;
    }
}

include_once '../../includes/header.php';
>>>>>>>> ab660bf99d6d155d59d9302691d0bc8f9c62eeb9:public/admin/settings.php
?>

<div class="settings-tab-nav">
    <a href="?tab=general" class="nav-link-tab <?php echo $active_tab === 'general' ? 'active' : ''; ?>">
        <i class="fas fa-cog"></i> Geral
    </a>
    <a href="?tab=themes" class="nav-link-tab <?php echo $active_tab === 'themes' ? 'active' : ''; ?>">
        <i class="fas fa-palette"></i> Temas
    </a>
</div>

<div class="card settings-main-card">
    <?php if ($active_tab === 'general'): ?>
        <form method="POST">
            <div class="settings-header-box">
                <h5><i class="fas fa-cog text-primary"></i> Configurações Gerais</h5>
            </div>
            
            <div class="form-group mb-4">
                <label class="form-label">Nome do Sistema</label>
                <input type="text" name="system_name" value="<?php echo htmlspecialchars($settings['system_name'] ?? ''); ?>" class="form-control w-100">
            </div>

            <div class="form-group mb-4">
                <label class="switch-label" style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <h6 class="mb-0">Ativar Logs do Sistema</h6>
                        <small class="text-muted">Registrar erros e atividades no diretório /logs</small>
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="enable_system_logs" value="1" <?php echo ($settings['enable_system_logs'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </label>
            </div>

            <button type="submit" name="save_general" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Alterações
            </button>
        </form>

    <?php elseif ($active_tab === 'themes'): ?>
        <form method="POST">
            <div class="settings-header-box">
                <h5><i class="fas fa-palette text-primary"></i> Personalização de Tema</h5>
                <p>Selecione a identidade visual que será aplicada a todos os usuários do sistema.</p>
            </div>

            <div class="theme-grid">
                <?php 
                $themes = ThemeHelper::getAvailableThemes();
                $current_theme = $settings['system_theme'] ?? 'gold-black';
                
                foreach ($themes as $slug => $theme): 
                    $isSelected = ($slug === $current_theme);
                ?>
                    <label class="theme-card-label">
                        <input type="radio" name="system_theme" value="<?php echo $slug; ?>" <?php echo $isSelected ? 'checked' : ''; ?> style="display: none;">
                        <div class="theme-card-ui">
                            <div class="theme-card-preview" style="background: <?php echo $theme['bg']; ?>;">
                                <div class="theme-card-accent" style="background: <?php echo $theme['color']; ?>; box-shadow: 0 0 15px <?php echo $theme['color']; ?>88;"></div>
                                <div class="theme-card-subaccent" style="background: <?php echo ($theme['bg'] == '#ffffff' || $theme['bg'] == 'white') ? '#eee' : 'rgba(255,255,255,0.1)'; ?>;"></div>
                            </div>
                            <div class="text-center">
                                <span class="theme-card-name"><?php echo $theme['name']; ?></span>
                            </div>
                            <div class="theme-check-icon" style="display: <?php echo $isSelected ? 'flex' : 'none'; ?>;">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit" name="save_theme" class="btn-primary">
                <i class="fas fa-save"></i> Aplicar Tema Selecionado
            </button>
        </form>
    <?php endif; ?>
</div>

<<<<<<<< HEAD:src/Views/admin/settings.php
<?php include_once __DIR__ . '/../../../includes/footer.php'; ?>
========
<?php include_once '../../includes/footer.php'; ?>
>>>>>>>> ab660bf99d6d155d59d9302691d0bc8f9c62eeb9:public/admin/settings.php
