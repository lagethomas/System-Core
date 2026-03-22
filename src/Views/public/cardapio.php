<?php
/** @var array $produtos */
/** @var string $SITE_URL */
/** @var array $platform_settings */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $platform_settings['system_name'] ?? 'Nosso Cardápio'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: <?php echo $platform_settings['system_color'] ?? '#e6c152'; ?>;
            --primary-rgb: <?php echo $platform_settings['system_color_rgb'] ?? '230, 193, 82'; ?>;
            --bg-dark: #0f1115;
            --bg-card: #16191e;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.08);
            --radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 40px;
            padding: 40px 0;
            background: linear-gradient(180deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(15, 17, 21, 0) 100%);
            border-radius: var(--radius);
        }

        header h1 {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        header p {
            color: var(--text-muted);
            font-size: 16px;
        }

        .category-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--primary);
            margin: 40px 0 20px;
            padding-left: 15px;
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .product-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-8px);
            border-color: var(--primary);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: rgba(255,255,255,0.02);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .product-card:hover .product-image img {
            transform: scale(1.1);
        }

        .product-image i {
            font-size: 40px;
            opacity: 0.1;
        }

        .product-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-info h3 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .product-info .price {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary);
            margin-top: auto;
        }

        .product-details-btn {
            margin-top: 15px;
            background: rgba(var(--primary-rgb), 0.1);
            color: var(--primary);
            border: 1px solid rgba(var(--primary-rgb), 0.2);
            padding: 10px;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .product-card:hover .product-details-btn {
            background: var(--primary);
            color: #000;
        }

        /* Modal Details */
        #product-modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(8px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            position: relative;
            animation: modalIn 0.3s ease;
        }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-header-img {
            width: 100%;
            height: 300px;
            background: var(--bg-dark);
        }
        .modal-header-img img { width: 100%; height: 100%; object-fit: cover; }

        .modal-body {
            padding: 30px;
        }

        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            cursor: pointer;
            z-index: 10;
        }

        .modal-body h2 {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 5px;
        }
        .modal-body .modal-category {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 20px;
            display: block;
        }
        .modal-body .description {
            color: var(--text-main);
            line-height: 1.6;
            margin-bottom: 25px;
            font-size: 16px;
        }
        .modal-body .price-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        .modal-body .price-line .price {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
        }

        @media (max-width: 480px) {
            header h1 { font-size: 28px; }
            .menu-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1><?php echo $platform_settings['system_name'] ?? 'Nosso Cardápio'; ?></h1>
        <p>Experimente nossas delícias preparadas com carinho.</p>
    </header>

    <?php 
    $last_category = '';
    foreach ($produtos as $p): 
        if ($p['categoria'] !== $last_category):
            echo "<h2 class='category-title'><i class='fas fa-utensils'></i> " . htmlspecialchars($p['categoria']) . "</h2>";
            echo "<div class='menu-grid'>";
            $last_category = $p['categoria'];
        endif;
    ?>
        <div class="product-card" onclick='openDetails(<?php echo json_encode($p); ?>)'>
            <div class="product-image">
                <?php if ($p['imagem']): ?>
                    <img src="<?php echo $SITE_URL . $p['imagem']; ?>" alt="">
                <?php else: ?>
                    <i class="fas fa-image"></i>
                <?php endif; ?>
            </div>
            <div class="product-info">
                <h3><?php echo htmlspecialchars($p['nome']); ?></h3>
                <div class="price">R$ <?php echo number_format((float)$p['preco'], 2, ',', '.'); ?></div>
                <button class="product-details-btn">
                    <i class="fas fa-eye"></i> Detalhes
                </button>
            </div>
        </div>
    <?php 
        // End grid if next is different or it's last
        $next = next($produtos);
        if (!$next || $next['categoria'] !== $last_category) echo "</div>";
        prev($produtos); next($produtos); // Restore pointer for loop
    endforeach; ?>
</div>

<div id="product-modal" onclick="closeDetails(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="close-modal" onclick="closeDetails()">
            <i class="fas fa-times"></i>
        </div>
        <div class="modal-header-img" id="modal-img-container">
            <img id="modal-img" src="" alt="">
        </div>
        <div class="modal-body">
            <span class="modal-category" id="modal-category">Categoria</span>
            <h2 id="modal-name">Nome do Produto</h2>
            <p class="description" id="modal-description">Descrição do produto vai aqui.</p>
            <div class="price-line">
                <span>Valor:</span>
                <span class="price" id="modal-price">R$ 0,00</span>
            </div>
        </div>
    </div>
</div>

<script>
function openDetails(p) {
    document.getElementById('modal-name').innerText = p.nome;
    document.getElementById('modal-category').innerText = p.categoria;
    document.getElementById('modal-description').innerText = p.descricao || 'Sem descrição disponível.';
    document.getElementById('modal-price').innerText = 'R$ ' + parseFloat(p.preco).toLocaleString('pt-BR', {minimumFractionDigits: 2});
    
    const imgCont = document.getElementById('modal-img-container');
    const img = document.getElementById('modal-img');
    if (p.imagem) {
        img.src = '<?php echo $SITE_URL; ?>' + p.imagem;
        imgCont.style.display = 'block';
    } else {
        imgCont.style.display = 'none';
    }

    document.getElementById('product-modal').style.display = 'flex';
}

function closeDetails(e) {
    document.getElementById('product-modal').style.display = 'none';
}
</script>

</body>
</html>
