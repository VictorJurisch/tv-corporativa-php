<?php
/**
 * Página Principal da TV Corporativa
 * Layout fullscreen estilo PowerPoint (1920x1080)
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
            position: relative;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Header */
        .header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 40px;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            height: 80px;
            z-index: 100;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            height: 50px;
            width: auto;
            max-width: 180px;
            object-fit: contain;
        }
        
        .logo-placeholder {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--accent), #60A5FA);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .company-name {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .datetime-section {
            text-align: right;
        }
        
        .clock {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            font-variant-numeric: tabular-nums;
            letter-spacing: 2px;
        }
        
        .date {
            font-size: 1rem;
            color: var(--text-secondary);
            margin-top: 3px;
        }
        
        /* Main Content - Slideshow */
        .main-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        /* Slide */
        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .slide.active {
            opacity: 1;
            z-index: 1;
        }
        
        /* Slide de Imagem (Mídia) */
        .slide-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: transparent;
        }
        
        /* Slide de Notícia */
        .slide-news {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 100px;
            text-align: center;
            background: rgba(0, 0, 0, 0.2);
        }
        
        .news-title-slide {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 40px;
            color: var(--text-primary);
            line-height: 1.3;
            max-width: 1400px;
        }
        
        .news-description-slide {
            font-size: 1.8rem;
            color: var(--accent);
            margin-bottom: 30px;
            font-weight: 500;
        }
        
        .news-message-slide {
            font-size: 2rem;
            color: var(--text-secondary);
            line-height: 1.6;
            max-width: 1200px;
        }
        
        .news-meta-slide {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 40px;
            font-size: 1.2rem;
            color: var(--text-secondary);
            opacity: 0.7;
        }
        
        /* Page Indicators */
        .page-indicators {
            position: absolute;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 10;
        }
        
        .page-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .page-dot.active {
            background: var(--accent);
            transform: scale(1.3);
            box-shadow: 0 0 20px var(--accent);
        }
        
        /* Ticker */
        .ticker-container {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 70px;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            overflow: hidden;
            z-index: 100;
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
            border-top: 35px solid transparent;
            border-bottom: 35px solid transparent;
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
            font-size: 6rem;
            margin-bottom: 30px;
            opacity: 0.5;
        }
        
        .no-content-text {
            font-size: 2rem;
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
    
    <!-- Main Content - Slideshow -->
    <main class="main-content" id="slideshow-container">
        <div class="no-content">
            <div class="no-content-icon">📺</div>
            <div class="no-content-text">Carregando conteúdos...</div>
        </div>
    </main>
    
    <!-- Page Indicators -->
    <div class="page-indicators" id="page-indicators"></div>
    
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
        
        // Estado
        let allSlides = [];
        let currentSlideIndex = 0;
        let slideInterval = null;
        
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
                    const newsData = result.data.news || [];
                    const mediaData = result.data.media || [];
                    
                    // Combina todos os conteúdos em uma única lista de slides
                    allSlides = [];
                    
                    // Adiciona mídias como slides (apenas imagem, sem título/descrição)
                    mediaData.forEach(media => {
                        if (media.image) {
                            allSlides.push({
                                type: 'media',
                                image: media.image,
                                titulo: media.titulo
                            });
                        }
                    });
                    
                    // Adiciona notícias como slides
                    newsData.forEach(news => {
                        allSlides.push({
                            type: 'news',
                            titulo: news.titulo,
                            descricao: news.descricao,
                            mensagem: news.mensagem,
                            nome_autor: news.nome_autor,
                            dt_publicacao: news.dt_publicacao
                        });
                    });
                    
                    renderSlideshow();
                    updateTicker(newsData);
                }
            } catch (error) {
                console.error('Erro ao buscar dados:', error);
            }
        }
        
        // Renderiza o slideshow
        function renderSlideshow() {
            const container = document.getElementById('slideshow-container');
            const indicators = document.getElementById('page-indicators');
            
            if (allSlides.length === 0) {
                container.innerHTML = `
                    <div class="no-content">
                        <div class="no-content-icon">📺</div>
                        <div class="no-content-text">Nenhum conteúdo disponível</div>
                    </div>
                `;
                indicators.innerHTML = '';
                return;
            }
            
            container.innerHTML = '';
            indicators.innerHTML = '';
            
            allSlides.forEach((slide, index) => {
                const slideDiv = document.createElement('div');
                slideDiv.className = 'slide' + (index === 0 ? ' active' : '');
                slideDiv.id = `slide-${index}`;
                
                if (slide.type === 'media') {
                    // Slide de imagem - fullscreen sem título/descrição
                    slideDiv.innerHTML = `
                        <img src="${escapeHtml(slide.image)}" alt="${escapeHtml(slide.titulo)}" class="slide-image">
                    `;
                } else {
                    // Slide de notícia
                    slideDiv.innerHTML = `
                        <div class="slide-news">
                            <h2 class="news-title-slide">${escapeHtml(slide.titulo)}</h2>
                            <p class="news-description-slide">${escapeHtml(slide.descricao)}</p>
                            <p class="news-message-slide">${escapeHtml(slide.mensagem || '')}</p>
                            <div class="news-meta-slide">
                                <span>👤 ${escapeHtml(slide.nome_autor)}</span>
                                <span>📅 ${formatDate(slide.dt_publicacao)}</span>
                            </div>
                        </div>
                    `;
                }
                
                container.appendChild(slideDiv);
                
                // Cria indicador
                const dot = document.createElement('div');
                dot.className = 'page-dot' + (index === 0 ? ' active' : '');
                dot.onclick = () => goToSlide(index);
                indicators.appendChild(dot);
            });
            
            // Inicia rotação automática
            startSlideRotation();
        }
        
        // Vai para um slide específico
        function goToSlide(index) {
            if (index === currentSlideIndex) return;
            
            const currentSlide = document.getElementById(`slide-${currentSlideIndex}`);
            const nextSlide = document.getElementById(`slide-${index}`);
            
            if (currentSlide) currentSlide.classList.remove('active');
            if (nextSlide) nextSlide.classList.add('active');
            
            // Atualiza indicadores
            const dots = document.querySelectorAll('.page-dot');
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === index);
            });
            
            currentSlideIndex = index;
        }
        
        // Próximo slide
        function nextSlide() {
            if (allSlides.length <= 1) return;
            const nextIndex = (currentSlideIndex + 1) % allSlides.length;
            goToSlide(nextIndex);
        }
        
        // Inicia rotação automática
        function startSlideRotation() {
            if (slideInterval) clearInterval(slideInterval);
            if (allSlides.length > 1) {
                slideInterval = setInterval(nextSlide, ROTATION_INTERVAL);
            }
        }
        
        // Atualiza ticker
        function updateTicker(newsData) {
            const ticker = document.getElementById('ticker-text');
            
            if (!newsData || newsData.length === 0) {
                ticker.textContent = 'Nenhum aviso no momento';
                return;
            }
            
            const tickerItems = newsData.map(news => 
                `<span class="ticker-item">📌 ${escapeHtml(news.titulo)}: ${escapeHtml(news.mensagem || news.descricao)}</span>`
            ).join('<span class="ticker-separator">•</span>');
            
            ticker.innerHTML = tickerItems;
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
        });
    </script>
</body>
</html>
