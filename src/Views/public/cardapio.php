<?php
/** @var array $produtos */
/** @var string $SITE_URL */
/** @var array $platform_settings */

$systemName = $platform_settings['system_name'] ?? 'Nosso Cardápio';
$primaryColor = $platform_settings['system_color'] ?? '#e6c152';
$primaryRGB = $platform_settings['system_color_rgb'] ?? '230, 193, 82';
$systemLogo = !empty($platform_settings['system_logo']) ? $SITE_URL . $platform_settings['system_logo'] : null;
$bgImage = !empty($platform_settings['cardapio_bg']) ? $SITE_URL . $platform_settings['cardapio_bg'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($systemName); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: <?php echo $primaryColor; ?>;
            --primary-rgb: <?php echo $primaryRGB; ?>;
            --bg-dark: #0a0c10;
            --bg-card: rgba(22, 25, 30, 0.8);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.1);
            --radius: 20px;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; outline: none; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            <?php if ($bgImage): ?>
            background: linear-gradient(rgba(10, 12, 16, 0.85), rgba(10, 12, 16, 0.95)), url('<?php echo $bgImage; ?>') center/cover fixed no-repeat;
            <?php endif; ?>
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            text-align: center;
            padding: 60px 20px;
            margin-bottom: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-container {
            margin-bottom: 25px;
            animation: fadeInDown 0.8s ease;
        }

        .logo-container img {
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 0 15px rgba(var(--primary-rgb), 0.3));
        }

        .logo-container .icon-placeholder {
            font-size: 50px;
            color: var(--primary);
            width: 80px;
            height: 80px;
            background: rgba(var(--primary-rgb), 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(var(--primary-rgb), 0.2);
        }

        header h1 {
            font-size: clamp(32px, 5vw, 42px);
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        header p {
            color: var(--text-muted);
            font-size: 18px;
            max-width: 600px;
            font-weight: 300;
        }

        /* Categorias */
        .category-nav {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding: 0 0 30px;
            margin-bottom: 40px;
            scrollbar-width: none;
            justify-content: center;
        }
        .category-nav::-webkit-scrollbar { display: none; }

        .category-link {
            padding: 10px 24px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border);
            border-radius: 50px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            white-space: nowrap;
            transition: var(--transition);
        }

        .category-link:hover, .category-link.active {
            background: var(--primary);
            color: #000;
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.3);
            transform: translateY(-2px);
        }

        .category-section {
            margin-bottom: 60px;
        }

        .category-title {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 20px;
            letter-spacing: -0.5px;
        }
        .category-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, rgba(var(--primary-rgb), 0.3), transparent);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }
        
        /* Staggered entry animation for products */
        .product-card {
            background: var(--bg-card);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1), 
                        border-color 0.4s ease, 
                        box-shadow 0.4s ease;
            display: flex;
            flex-direction: column;
            cursor: pointer;
            position: relative;
            transform-origin: center;
        }

        .product-card:hover {
            transform: translateY(-8px) scale(1.01);
            border-color: rgba(var(--primary-rgb), 0.5);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        .product-image-wrapper {
            position: relative;
            width: 100%;
            height: 220px;
            overflow: hidden;
        }

        .product-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image-wrapper img {
            transform: scale(1.1);
        }



        .product-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.02);
            color: rgba(255, 255, 255, 0.05);
            font-size: 50px;
        }

        .price-badge {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: var(--primary);
            color: #000;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            z-index: 2;
        }

        .product-info {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-info h3 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--text-main);
        }

        .product-info p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .view-btn {
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Modal Moderno */
        #modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.92);
            backdrop-filter: blur(20px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        #modal-overlay.active {
            opacity: 1;
        }

        .modern-modal {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 30px;
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0,0,0,0.8);
            transform: translateY(40px) scale(0.95);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #modal-overlay.active .modern-modal {
            transform: translateY(0) scale(1);
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-hero {
            position: relative;
            width: 100%;
            height: 350px;
        }
        .modal-hero img { width: 100%; height: 100%; object-fit: cover; }
        .close-modal {
            position: absolute;
            top: 20px; right: 20px;
            width: 45px; height: 45px;
            background: rgba(0,0,0,0.5);
            border: 1px solid rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            z-index: 10;
            transition: var(--transition);
        }
        .close-modal:hover { background: #ef4444; transform: rotate(90deg); }

        .modal-body {
            padding: 40px;
        }
        .modal-body .cat-badge {
            display: inline-block;
            padding: 6px 14px;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .modal-body h2 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 15px;
            color: #fff;
        }
        .modal-body .desc {
            color: var(--text-muted);
            line-height: 1.8;
            font-size: 17px;
            margin-bottom: 40px;
        }
        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 30px;
            border-top: 1px solid var(--border);
        }
        .modal-footer .price {
            font-size: 32px;
            font-weight: 800;
            color: var(--primary);
        }

        @media (max-width: 600px) {
            .container { padding: 15px; }
            header { padding: 40px 10px; }
            .menu-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 12px !important;
            }
            .product-image-wrapper { height: 140px !important; }
            .product-info { padding: 12px !important; }
            .product-info h3 { font-size: 14px !important; margin-bottom: 5px !important; }
            .product-info p { display: none !important; }
            .price-badge { font-size: 13px !important; padding: 4px 10px !important; bottom: 10px !important; right: 10px !important; }
            .view-btn { font-size: 11px !important; }
            
            .modal-hero { height: 200px !important; }
            .modal-body { padding: 25px !important; }
            .modal-body h2 { font-size: 24px !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div class="logo-container">
            <?php if ($systemLogo): ?>
                <img src="<?php echo $systemLogo; ?>" alt="Logo">
            <?php else: ?>
                <div class="icon-placeholder"><i class="fas fa-utensils"></i></div>
            <?php endif; ?>
        </div>
        <h1><?php echo htmlspecialchars($systemName); ?></h1>
        <p>Explore nosso menu exclusivo preparado com os melhores ingredientes.</p>
    </header>

    <?php if (!empty($categorias)): ?>
    <nav class="category-nav">
        <a href="javascript:void(0)" class="category-link active" onclick="filterCategory('all', this)">
            <i class="fas fa-th-large" style="margin-right: 8px; opacity: 0.5;"></i> Todos
        </a>
        <?php foreach ($categorias as $cat): ?>
            <a href="javascript:void(0)" class="category-link" data-cat="<?php echo $cat['slug']; ?>" onclick="filterCategory('<?php echo $cat['slug']; ?>', this)">
                <i class="fas <?php echo $cat['icone'] ?: 'fa-tag'; ?>" style="margin-right: 8px; opacity: 0.5;"></i> <?php echo htmlspecialchars($cat['nome']); ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>

    <div id="products-container">
        <?php 
        $current_category = '';
        foreach ($produtos as $p): 
            if ($p['categoria_slug'] !== $current_category):
                if ($current_category !== '') echo "</div></div>";
                $current_category = $p['categoria_slug'];
        ?>
            <div class="category-section" data-category="<?php echo $p['categoria_slug']; ?>">
                <h2 class="category-title"><?php echo htmlspecialchars($p['categoria_nome']); ?></h2>
                <div class="menu-grid">
        <?php endif; ?>
                <div class="product-card" onclick='openDetails(<?php echo json_encode($p); ?>)'>
                    <div class="product-image-wrapper">
                        <?php if ($p['imagem']): ?>
                            <img src="<?php echo $SITE_URL . $p['imagem']; ?>" alt="<?php echo htmlspecialchars($p['nome']); ?>" loading="lazy">
                        <?php else: ?>
                            <div class="product-image-placeholder"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                        <div class="price-badge">R$ <?php echo number_format((float)$p['preco'], 2, ',', '.'); ?></div>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                        <p><?php echo htmlspecialchars($p['descricao'] ?? 'Sabor e qualidade incomparáveis.'); ?></p>
                        <div class="view-btn">
                            Ver detalhes <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
        <?php endforeach; ?>
        <?php if (!empty($produtos)) echo "</div></div>"; ?>
    </div>
</div>

<!-- Modal Detalhes -->
<div id="modal-overlay" onclick="closeDetails()">
    <div class="modern-modal" onclick="event.stopPropagation()">
        <div class="close-modal" onclick="closeDetails()">
            <i class="fas fa-times"></i>
        </div>
        <div class="modal-hero" id="modal-hero">
            <img id="modal-img" src="" alt="">
        </div>
        <div class="modal-body">
            <span class="cat-badge" id="modal-cat">Categoria</span>
            <h2 id="modal-name">Nome</h2>
            <p class="desc" id="modal-desc">Descrição...</p>
            <div class="modal-footer">
                <span class="label">Valor:</span>
                <span class="price" id="modal-price">R$ 0,00</span>
            </div>
        </div>
    </div>
</div>

<script>
function openDetails(p) {
    const hero = document.getElementById('modal-hero');
    const img = document.getElementById('modal-img');
    const overlay = document.getElementById('modal-overlay');

    // Pre-set data
    document.getElementById('modal-name').innerText = p.nome;
    document.getElementById('modal-cat').innerText = p.categoria;
    document.getElementById('modal-desc').innerText = p.descricao || 'Nenhuma descrição detalhada disponível para este item.';
    document.getElementById('modal-price').innerText = 'R$ ' + parseFloat(p.preco).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    
    if (p.imagem) {
        img.src = '<?php echo $SITE_URL; ?>' + p.imagem;
        hero.style.display = 'block';
    } else {
        hero.style.display = 'none';
        img.src = '';
    }

    // Show with transition
    overlay.style.display = 'flex';
    setTimeout(() => {
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }, 10);
}

function closeDetails() {
    const overlay = document.getElementById('modal-overlay');
    overlay.classList.remove('active');
    setTimeout(() => {
        overlay.style.display = 'none';
        document.body.style.overflow = 'auto';
    }, 400);
}

function filterCategory(cat, btn) {
    document.querySelectorAll('.category-link').forEach(l => l.classList.remove('active'));
    btn.classList.add('active');

    const sections = document.querySelectorAll('.category-section');
    
    // Quick fade out
    document.getElementById('products-container').style.opacity = '0';
    document.getElementById('products-container').style.transform = 'translateY(10px)';
    
    setTimeout(() => {
        sections.forEach(s => {
            if (cat === 'all' || s.getAttribute('data-category') === cat) {
                s.style.display = 'block';
            } else {
                s.style.display = 'none';
            }
        });
        
        // Refined Fade In
        document.getElementById('products-container').style.transition = 'all 0.5s ease';
        document.getElementById('products-container').style.opacity = '1';
        document.getElementById('products-container').style.transform = 'translateY(0)';
        
        if (cat !== 'all') {
            const firstVisible = document.querySelector('.category-section[style*="display: block"]');
            if (firstVisible) {
                window.scrollTo({ top: firstVisible.offsetTop - 100, behavior: 'smooth' });
            }
        }
    }, 300);
}

document.getElementById('products-container').style.transition = 'all 0.5s ease';

// Fechar com ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDetails();
});
</script>

</body>
</html>
