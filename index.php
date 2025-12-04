<?php
/**
 * Página Principal da TV Corporativa
 * Layout fullscreen responsivo (1920x1080)
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

// Obtém configurações e conteúdos
$config = getTVConfig();
$dateExtensive = getDateExtensive();

// Cores do tema
$themePrimary = $config['theme_primary'] ?? '#1E3A8A';
$themeSecondary = $config['theme_secondary'] ?? '#0F172A';
$themeAccent = $config['theme_accent'] ?? '#3B82F6';
$rotationInterval = ($config['rotation_interval_seconds'] ?? 20) * 1000;
$companyName = $config['company_name'] ?? 'ASTI';
$logoUrl = $config['logo_url'] ?? '';
$logoBase64 = $config['logo_base64'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=1920, height=1080, initial-scale=1.0">
    <meta http-equiv="refresh" content="300">
    <title>TV Corporativa - <?php echo sanitize($companyName); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: <?php echo sanitize($themePrimary); ?>;
            --secondary: <?php echo sanitize($themeSecondary); ?>;
            --accent: <?php echo sanitize($themeAccent); ?>;
            --text-primary: #FFFFFF;
            --text-secondary: #94A3B8;
            --card-bg: rgba(30, 41, 59, 0.8);
            --card-border: rgba(148, 163, 184, 0.2);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 50%, var(--secondary) 100%);
            background-size: 400% 400%;
            animation: gradientShift 30s ease infinite;
            min-height: 100vh;
            width: 1920px;
            height: 1080px;
            overflow: hidden;
            cursor: none;
            color: var(--text-primary);
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--card-border);
            height: 100px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            height: 60px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
        }
        
        .logo-placeholder {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent), #60A5FA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .company-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .datetime-section {
            text-align: right;
        }
        
        .clock {
            font-size: 3rem;
            font-weight: 700;
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
            letter-spacing: 2px;
        }
        
        .date {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-top: 5px;
        }
        
        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 30px 40px;
            height: calc(100vh - 100px - 80px);
        }
        
        .section {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid var(--card-border);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid var(--card-border);
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-icon {
            font-size: 1.6rem;
        }
        
        .page-indicators {
            display: flex;
            gap: 8px;
        }
        
        .page-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .page-dot.active {
            background: var(--accent);
            transform: scale(1.2);
        }
        
        .section-content {
            flex: 1;
            padding: 25px;
            overflow: hidden;
            position: relative;
        }
        
        /* News Cards */
        .news-page {
            position: absolute;
            top: 25px;
            left: 25px;
            right: 25px;
            bottom: 25px;
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.5s ease;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .news-page.active {
            opacity: 1;
            transform: translateX(0);
        }
        
        .news-card {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid var(--card-border);
            transition: all 0.3s ease;
        }
        
        .news-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent);
        }
        
        .news-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-primary);
        }
        
        .news-description {
            font-size: 0.9rem;
            color: var(--accent);
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .news-message {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        .news-meta {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--card-border);
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        /* Media Cards */
        .media-page {
            position: absolute;
            top: 25px;
            left: 25px;
            right: 25px;
            bottom: 25px;
            opacity: 0;
            transform: scale(0.95);
            transition: all 0.5s ease;
        }
        
        .media-page.active {
            opacity: 1;
            transform: scale(1);
        }
        
        .media-card {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid var(--card-border);
        }
        
        .media-image-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .media-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .media-info {
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
        }
        
        .media-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .media-description {
            font-size: 1rem;
            color: var(--text-secondary);
        }
        
        /* Ticker */
        .ticker-container {
            height: 80px;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            overflow: hidden;
            position: relative;
        }
        
        .ticker-label {
            background: var(--accent);
            color: white;
            padding: 10px 25px;
            font-weight: 600;
            font-size: 1rem;
            height: 100%;
            display: flex;
            align-items: center;
            z-index: 1;
            position: relative;
        }
        
        .ticker-label::after {
            content: '';
            position: absolute;
            right: -20px;
            top: 0;
            bottom: 0;
            border-left: 20px solid var(--accent);
            border-top: 40px solid transparent;
            border-bottom: 40px solid transparent;
        }
        
        .ticker-content {
            flex: 1;
            overflow: hidden;
            padding-left: 30px;
        }
        
        .ticker-text {
            display: inline-block;
            white-space: nowrap;
            animation: ticker 60s linear infinite;
            font-size: 1.2rem;
            color: var(--text-primary);
        }
        
        @keyframes ticker {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        .ticker-item {
            display: inline;
            margin-right: 80px;
        }
        
        .ticker-separator {
            display: inline;
            margin: 0 40px;
            color: var(--accent);
        }
        
        /* No Content */
        .no-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            color: var(--text-secondary);
        }
        
        .no-content-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .no-content-text {
            font-size: 1.2rem;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo-section">
            <?php if (!empty($logoBase64)): ?>
                <img src="<?php echo sanitize($logoBase64); ?>" alt="Logo" class="logo">
            <?php elseif (!empty($logoUrl)): ?>
                <img src="<?php echo sanitize($logoUrl); ?>" alt="Logo" class="logo">
            <?php else: ?>
                <span class="logo-placeholder">📺</span>
            <?php endif; ?>
            <span class="company-name"><?php echo sanitize($companyName); ?></span>
        </div>
        <div class="datetime-section">
            <div class="clock" id="clock">00:00:00</div>
            <div class="date" id="date"><?php echo sanitize($dateExtensive); ?></div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- News Section -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="section-icon">📰</span>
                    Notícias
                </h2>
                <div class="page-indicators" id="news-indicators"></div>
            </div>
            <div class="section-content" id="news-container">
                <div class="no-content">
                    <div class="no-content-icon">📰</div>
                    <div class="no-content-text">Carregando notícias...</div>
                </div>
            </div>
        </section>
        
        <!-- Media Section -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="section-icon">🖼️</span>
                    Mídias
                </h2>
                <div class="page-indicators" id="media-indicators"></div>
            </div>
            <div class="section-content" id="media-container">
                <div class="no-content">
                    <div class="no-content-icon">🖼️</div>
                    <div class="no-content-text">Carregando mídias...</div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Ticker -->
    <footer class="ticker-container">
        <div class="ticker-label">📢 AVISOS</div>
        <div class="ticker-content">
            <div class="ticker-text" id="ticker-text">
                Carregando avisos...
            </div>
        </div>
    </footer>
    
    <script>
        // Configurações
        const ROTATION_INTERVAL = <?php echo (int) $rotationInterval; ?>;
        const ITEMS_PER_PAGE = 2;
        
        // Estado
        let newsData = [];
        let mediaData = [];
        let currentNewsPage = 0;
        let currentMediaPage = 0;
        let newsPages = [];
        let mediaPages = [];
        
        // Atualiza o relógio
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
        }
        
        // Atualiza a data em português
        function updateDate() {
            const now = new Date();
            const diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
            const meses = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
            
            const diaSemana = diasSemana[now.getDay()];
            const dia = String(now.getDate()).padStart(2, '0');
            const mes = meses[now.getMonth()];
            const ano = now.getFullYear();
            
            document.getElementById('date').textContent = `${diaSemana}, ${dia} de ${mes} de ${ano}`;
        }
        
        // Busca dados da API
        async function fetchData() {
            try {
                const response = await fetch('api/tv.php');
                const result = await response.json();
                
                if (result.success) {
                    newsData = result.data.news || [];
                    mediaData = result.data.media || [];
                    renderNews();
                    renderMedia();
                    updateTicker();
                }
            } catch (error) {
                console.error('Erro ao buscar dados:', error);
            }
        }
        
        // Divide array em páginas
        function paginateArray(array, itemsPerPage) {
            const pages = [];
            for (let i = 0; i < array.length; i += itemsPerPage) {
                pages.push(array.slice(i, i + itemsPerPage));
            }
            return pages;
        }
        
        // Renderiza indicadores de página
        function renderIndicators(containerId, totalPages, currentPage) {
            const container = document.getElementById(containerId);
            container.innerHTML = '';
            
            for (let i = 0; i < totalPages; i++) {
                const dot = document.createElement('div');
                dot.className = 'page-dot' + (i === currentPage ? ' active' : '');
                container.appendChild(dot);
            }
        }
        
        // Renderiza notícias
        function renderNews() {
            const container = document.getElementById('news-container');
            
            if (newsData.length === 0) {
                container.innerHTML = `
                    <div class="no-content">
                        <div class="no-content-icon">📰</div>
                        <div class="no-content-text">Nenhuma notícia disponível</div>
                    </div>
                `;
                return;
            }
            
            newsPages = paginateArray(newsData, ITEMS_PER_PAGE);
            container.innerHTML = '';
            
            newsPages.forEach((page, pageIndex) => {
                const pageDiv = document.createElement('div');
                pageDiv.className = 'news-page' + (pageIndex === 0 ? ' active' : '');
                pageDiv.id = `news-page-${pageIndex}`;
                
                page.forEach(news => {
                    const card = document.createElement('div');
                    card.className = 'news-card';
                    card.innerHTML = `
                        <h3 class="news-title">${escapeHtml(news.titulo)}</h3>
                        <p class="news-description">${escapeHtml(news.descricao)}</p>
                        <p class="news-message">${escapeHtml(news.mensagem || '')}</p>
                        <div class="news-meta">
                            <span>👤 ${escapeHtml(news.nome_autor)}</span>
                            <span>📅 ${formatDate(news.dt_publicacao)}</span>
                        </div>
                    `;
                    pageDiv.appendChild(card);
                });
                
                container.appendChild(pageDiv);
            });
            
            renderIndicators('news-indicators', newsPages.length, currentNewsPage);
        }
        
        // Renderiza mídias
        function renderMedia() {
            const container = document.getElementById('media-container');
            
            if (mediaData.length === 0) {
                container.innerHTML = `
                    <div class="no-content">
                        <div class="no-content-icon">🖼️</div>
                        <div class="no-content-text">Nenhuma mídia disponível</div>
                    </div>
                `;
                return;
            }
            
            mediaPages = mediaData.map(item => [item]); // Uma mídia por página
            container.innerHTML = '';
            
            mediaPages.forEach((page, pageIndex) => {
                const media = page[0];
                const pageDiv = document.createElement('div');
                pageDiv.className = 'media-page' + (pageIndex === 0 ? ' active' : '');
                pageDiv.id = `media-page-${pageIndex}`;
                
                const imageSrc = media.image || '';
                
                pageDiv.innerHTML = `
                    <div class="media-card">
                        <div class="media-image-container">
                            ${imageSrc ? `<img src="${escapeHtml(imageSrc)}" alt="${escapeHtml(media.titulo)}" class="media-image">` : '<div class="no-content-icon">🖼️</div>'}
                        </div>
                        <div class="media-info">
                            <h3 class="media-title">${escapeHtml(media.titulo)}</h3>
                            <p class="media-description">${escapeHtml(media.descricao)}</p>
                        </div>
                    </div>
                `;
                
                container.appendChild(pageDiv);
            });
            
            renderIndicators('media-indicators', mediaPages.length, currentMediaPage);
        }
        
        // Atualiza ticker
        function updateTicker() {
            const ticker = document.getElementById('ticker-text');
            
            if (newsData.length === 0) {
                ticker.textContent = 'Nenhum aviso no momento';
                return;
            }
            
            const tickerItems = newsData.map(news => 
                `<span class="ticker-item">📌 ${escapeHtml(news.titulo)}: ${escapeHtml(news.mensagem || news.descricao)}</span>`
            ).join('<span class="ticker-separator">•</span>');
            
            ticker.innerHTML = tickerItems;
        }
        
        // Rotação de páginas de notícias
        function rotateNewsPage() {
            if (newsPages.length <= 1) return;
            
            const currentPage = document.getElementById(`news-page-${currentNewsPage}`);
            currentNewsPage = (currentNewsPage + 1) % newsPages.length;
            const nextPage = document.getElementById(`news-page-${currentNewsPage}`);
            
            if (currentPage) currentPage.classList.remove('active');
            if (nextPage) nextPage.classList.add('active');
            
            renderIndicators('news-indicators', newsPages.length, currentNewsPage);
        }
        
        // Rotação de páginas de mídia
        function rotateMediaPage() {
            if (mediaPages.length <= 1) return;
            
            const currentPage = document.getElementById(`media-page-${currentMediaPage}`);
            currentMediaPage = (currentMediaPage + 1) % mediaPages.length;
            const nextPage = document.getElementById(`media-page-${currentMediaPage}`);
            
            if (currentPage) currentPage.classList.remove('active');
            if (nextPage) nextPage.classList.add('active');
            
            renderIndicators('media-indicators', mediaPages.length, currentMediaPage);
        }
        
        // Escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Formata data
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR');
        }
        
        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            // Atualiza relógio a cada segundo
            updateClock();
            setInterval(updateClock, 1000);
            
            // Atualiza data a cada minuto
            updateDate();
            setInterval(updateDate, 60000);
            
            // Carrega dados
            fetchData();
            
            // Recarrega dados a cada 5 minutos
            setInterval(fetchData, 300000);
            
            // Rotação de páginas
            setInterval(rotateNewsPage, ROTATION_INTERVAL);
            setInterval(rotateMediaPage, ROTATION_INTERVAL);
        });
    </script>
</body>
</html>
