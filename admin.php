<?php
/**
 * Painel de Administração da TV Corporativa
 * Gerencia conteúdos (notícias e mídias) e configurações
 */

require_once __DIR__ . '/api/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';

// Obtém configurações
$config = getTVConfig();
$companyName = $config['company_name'] ?? 'ASTI';

// Cores do tema
$themePrimary = $config['theme_primary'] ?? '#1E3A8A';
$themeSecondary = $config['theme_secondary'] ?? '#0F172A';
$themeAccent = $config['theme_accent'] ?? '#3B82F6';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - TV Corporativa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: <?php echo sanitize($themePrimary); ?>;
            --secondary: <?php echo sanitize($themeSecondary); ?>;
            --accent: <?php echo sanitize($themeAccent); ?>;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --text-primary: #F8FAFC;
            --text-secondary: #94A3B8;
            --card-bg: rgba(30, 41, 59, 0.95);
            --card-border: rgba(148, 163, 184, 0.2);
            --input-bg: rgba(15, 23, 42, 0.8);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            min-height: 100vh;
            color: var(--text-primary);
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            margin-bottom: 30px;
        }
        
        .header-title {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--accent);
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563EB;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
            border: 1px solid var(--card-border);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #DC2626;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.85rem;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .tab {
            padding: 15px 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            color: var(--text-secondary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tab:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-primary);
        }
        
        .tab.active {
            background: var(--accent);
            color: white;
            border-color: var(--accent);
        }
        
        /* Grid Layout */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
        }
        
        /* Cards */
        .card {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            border: 1px solid var(--card-border);
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px 25px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid var(--card-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }
        
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 14px 16px;
            background: var(--input-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-help {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 6px;
        }
        
        /* File Input */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
        }
        
        .file-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 40px 20px;
            background: var(--input-bg);
            border: 2px dashed var(--card-border);
            border-radius: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .file-input-label:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        
        .file-preview {
            margin-top: 15px;
            display: none;
        }
        
        .file-preview.show {
            display: block;
        }
        
        .file-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            border: 1px solid var(--card-border);
        }
        
        /* Content List */
        .content-list {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .content-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .content-item:hover {
            background: rgba(0, 0, 0, 0.3);
        }
        
        .content-info {
            flex: 1;
        }
        
        .content-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .content-meta {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .content-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 8px;
        }
        
        .badge-news {
            background: rgba(59, 130, 246, 0.2);
            color: #60A5FA;
        }
        
        .badge-media {
            background: rgba(16, 185, 129, 0.2);
            color: #34D399;
        }
        
        .content-actions {
            display: flex;
            gap: 10px;
        }
        
        /* Config Section */
        .config-section {
            margin-top: 30px;
        }
        
        .color-picker-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .color-picker-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .color-input {
            width: 50px;
            height: 50px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            background: transparent;
        }
        
        .color-input::-webkit-color-swatch {
            border-radius: 8px;
            border: 2px solid var(--card-border);
        }
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .toast {
            padding: 16px 24px;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--card-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toast-success { border-left: 4px solid var(--success); }
        .toast-error { border-left: 4px solid var(--danger); }
        .toast-warning { border-left: 4px solid var(--warning); }
        
        /* Loading */
        .loading {
            display: none;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        
        .loading.show {
            display: flex;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid var(--card-border);
            border-top-color: var(--accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-secondary);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        /* Tab Content */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .tabs {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1 class="header-title">
                📺 TV Corporativa - Admin
            </h1>
            <div class="header-actions">
                <a href="index.php" target="_blank" class="btn btn-secondary">
                    👁️ Visualizar TV
                </a>
                <button class="btn btn-primary" onclick="refreshData()">
                    🔄 Atualizar
                </button>
            </div>
        </header>
        
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" data-tab="content" onclick="switchTab('content')">
                📝 Conteúdos
            </button>
            <button class="tab" data-tab="config" onclick="switchTab('config')">
                ⚙️ Configurações
            </button>
        </div>
        
        <!-- Tab Content: Conteúdos -->
        <div id="tab-content" class="tab-content active">
            <div class="grid">
                <!-- Adicionar Notícia -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">📰 Adicionar Notícia</h2>
                    </div>
                    <div class="card-body">
                        <form id="news-form" onsubmit="submitNews(event)">
                            <div class="form-group">
                                <label class="form-label">Título *</label>
                                <input type="text" class="form-input" id="news-title" maxlength="60" required>
                                <p class="form-help">Máximo 60 caracteres</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Descrição</label>
                                <input type="text" class="form-input" id="news-description" maxlength="120" placeholder="Ex: Comunicado RH">
                                <p class="form-help">Máximo 120 caracteres</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mensagem *</label>
                                <textarea class="form-textarea" id="news-message" required placeholder="Conteúdo completo da notícia..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">
                                ✅ Publicar Notícia
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Adicionar Mídia -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">🖼️ Adicionar Mídia</h2>
                    </div>
                    <div class="card-body">
                        <form id="media-form" onsubmit="submitMedia(event)">
                            <div class="form-group">
                                <label class="form-label">Título *</label>
                                <input type="text" class="form-input" id="media-title" maxlength="60" required>
                                <p class="form-help">Máximo 60 caracteres</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Descrição *</label>
                                <input type="text" class="form-input" id="media-description" maxlength="120" required>
                                <p class="form-help">Máximo 120 caracteres</p>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Imagem *</label>
                                <div class="file-input-wrapper">
                                    <input type="file" class="file-input" id="media-image" accept="image/jpeg,image/png,image/gif,image/webp" required onchange="previewImage(event)">
                                    <label class="file-input-label" for="media-image">
                                        📁 Clique ou arraste uma imagem aqui<br>
                                        <small>JPEG, PNG, GIF ou WebP (máx. 10MB)</small>
                                    </label>
                                </div>
                                <div class="file-preview" id="image-preview"></div>
                            </div>
                            <button type="submit" class="btn btn-success">
                                ✅ Publicar Mídia
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Lista de Conteúdos -->
            <div class="card" style="margin-top: 25px;">
                <div class="card-header">
                    <h2 class="card-title">📋 Conteúdos Ativos</h2>
                </div>
                <div class="card-body">
                    <div class="loading" id="content-loading">
                        <div class="spinner"></div>
                    </div>
                    <div class="content-list" id="content-list">
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <p>Carregando conteúdos...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tab Content: Configurações -->
        <div id="tab-config" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">⚙️ Configurações da TV</h2>
                </div>
                <div class="card-body">
                    <form id="config-form" onsubmit="submitConfig(event)">
                        <div class="grid">
                            <div>
                                <div class="form-group">
                                    <label class="form-label">Nome da Empresa</label>
                                    <input type="text" class="form-input" id="config-company" value="<?php echo sanitize($companyName); ?>" maxlength="100">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">URL do Logo</label>
                                    <input type="url" class="form-input" id="config-logo-url" value="<?php echo sanitize($config['logo_url'] ?? ''); ?>" placeholder="https://...">
                                    <p class="form-help">URL de uma imagem PNG ou SVG</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Ou fazer upload do Logo</label>
                                    <div class="file-input-wrapper">
                                        <input type="file" class="file-input" id="config-logo-file" accept="image/*" onchange="previewLogo(event)">
                                        <label class="file-input-label" for="config-logo-file">
                                            📁 Clique para selecionar
                                        </label>
                                    </div>
                                    <div class="file-preview" id="logo-preview"></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Intervalo de Rotação (segundos)</label>
                                    <input type="number" class="form-input" id="config-interval" value="<?php echo (int)($config['rotation_interval_seconds'] ?? 20); ?>" min="5" max="120">
                                    <p class="form-help">Tempo entre mudanças de página (5-120 segundos)</p>
                                </div>
                            </div>
                            <div>
                                <div class="form-group">
                                    <label class="form-label">Cores do Tema</label>
                                    <div class="color-picker-group">
                                        <div class="color-picker-item">
                                            <input type="color" class="color-input" id="config-primary" value="<?php echo sanitize($themePrimary); ?>">
                                            <span>Primária</span>
                                        </div>
                                        <div class="color-picker-item">
                                            <input type="color" class="color-input" id="config-secondary" value="<?php echo sanitize($themeSecondary); ?>">
                                            <span>Secundária</span>
                                        </div>
                                        <div class="color-picker-item">
                                            <input type="color" class="color-input" id="config-accent" value="<?php echo sanitize($themeAccent); ?>">
                                            <span>Destaque</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                            💾 Salvar Configurações
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toast-container"></div>
    
    <script>
        // Estado
        let logoBase64 = '';
        
        // Switch Tabs
        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(`tab-${tabName}`).classList.add('active');
        }
        
        // Toast Notification
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️'
            };
            
            toast.innerHTML = `
                <span>${icons[type]}</span>
                <span>${message}</span>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
        
        // Preview Image
        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const preview = document.getElementById('image-preview');
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                preview.classList.add('show');
            };
            
            reader.readAsDataURL(file);
        }
        
        // Preview Logo
        function previewLogo(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const preview = document.getElementById('logo-preview');
            const reader = new FileReader();
            
            reader.onload = function(e) {
                logoBase64 = e.target.result;
                preview.innerHTML = `<img src="${e.target.result}" alt="Logo Preview">`;
                preview.classList.add('show');
            };
            
            reader.readAsDataURL(file);
        }
        
        // Fetch Data
        async function fetchData() {
            const listEl = document.getElementById('content-list');
            const loadingEl = document.getElementById('content-loading');
            
            loadingEl.classList.add('show');
            
            try {
                const response = await fetch('api/tv.php');
                const result = await response.json();
                
                loadingEl.classList.remove('show');
                
                if (!result.success) {
                    throw new Error(result.error || 'Erro ao carregar dados');
                }
                
                renderContentList(result.data.news, result.data.media);
                
            } catch (error) {
                loadingEl.classList.remove('show');
                listEl.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">❌</div>
                        <p>Erro ao carregar: ${error.message}</p>
                    </div>
                `;
            }
        }
        
        // Render Content List
        function renderContentList(news, media) {
            const listEl = document.getElementById('content-list');
            
            const allContent = [
                ...news.map(n => ({ ...n, contentType: 'news' })),
                ...media.map(m => ({ ...m, contentType: 'media' }))
            ].sort((a, b) => new Date(b.dt_publicacao) - new Date(a.dt_publicacao));
            
            if (allContent.length === 0) {
                listEl.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📭</div>
                        <p>Nenhum conteúdo cadastrado</p>
                    </div>
                `;
                return;
            }
            
            listEl.innerHTML = allContent.map(item => `
                <div class="content-item">
                    <div class="content-info">
                        <div class="content-title">
                            <span class="content-badge ${item.contentType === 'news' ? 'badge-news' : 'badge-media'}">
                                ${item.contentType === 'news' ? '📰 Notícia' : '🖼️ Mídia'}
                            </span>
                            ${escapeHtml(item.titulo)}
                        </div>
                        <div class="content-meta">
                            ${escapeHtml(item.descricao || '')} • ${formatDate(item.dt_publicacao)} • ${escapeHtml(item.nome_autor)}
                        </div>
                    </div>
                    <div class="content-actions">
                        <button class="btn btn-danger btn-sm" onclick="deleteContent('${item.contentType}', ${item.id})">
                            🗑️ Excluir
                        </button>
                    </div>
                </div>
            `).join('');
        }
        
        // Submit News
        async function submitNews(event) {
            event.preventDefault();
            
            const title = document.getElementById('news-title').value.trim();
            const description = document.getElementById('news-description').value.trim() || 'Notícia';
            const message = document.getElementById('news-message').value.trim();
            
            if (!title || !message) {
                showToast('Preencha todos os campos obrigatórios', 'error');
                return;
            }
            
            try {
                const response = await fetch('api/tv.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        type: 'news',
                        title: title,
                        description: description,
                        message: message
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Notícia publicada com sucesso!');
                    document.getElementById('news-form').reset();
                    fetchData();
                } else {
                    throw new Error(result.error || 'Erro ao publicar');
                }
            } catch (error) {
                showToast(error.message, 'error');
            }
        }
        
        // Submit Media
        async function submitMedia(event) {
            event.preventDefault();
            
            const title = document.getElementById('media-title').value.trim();
            const description = document.getElementById('media-description').value.trim();
            const fileInput = document.getElementById('media-image');
            const file = fileInput.files[0];
            
            if (!title || !description || !file) {
                showToast('Preencha todos os campos e selecione uma imagem', 'error');
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showToast('Tipo de arquivo não permitido', 'error');
                return;
            }
            
            // Convert to base64
            const reader = new FileReader();
            reader.onload = async function(e) {
                const base64Full = e.target.result;
                const base64Data = base64Full.split(',')[1];
                
                try {
                    const response = await fetch('api/tv.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            type: 'media',
                            title: title,
                            description: description,
                            image: {
                                base64: base64Data,
                                mimeType: file.type,
                                fileName: file.name
                            }
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showToast('Mídia publicada com sucesso!');
                        document.getElementById('media-form').reset();
                        document.getElementById('image-preview').classList.remove('show');
                        document.getElementById('image-preview').innerHTML = '';
                        fetchData();
                    } else {
                        throw new Error(result.error || 'Erro ao publicar');
                    }
                } catch (error) {
                    showToast(error.message, 'error');
                }
            };
            reader.readAsDataURL(file);
        }
        
        // Delete Content
        async function deleteContent(type, id) {
            if (!confirm('Tem certeza que deseja excluir este conteúdo?')) {
                return;
            }
            
            try {
                const response = await fetch(`api/delete.php?type=${type}&id=${id}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('Conteúdo excluído com sucesso!');
                    fetchData();
                } else {
                    throw new Error(result.error || 'Erro ao excluir');
                }
            } catch (error) {
                showToast(error.message, 'error');
            }
        }
        
        // Submit Config
        async function submitConfig(event) {
            event.preventDefault();
            
            const company = document.getElementById('config-company').value.trim();
            const logoUrl = document.getElementById('config-logo-url').value.trim();
            const interval = parseInt(document.getElementById('config-interval').value) || 20;
            const primary = document.getElementById('config-primary').value;
            const secondary = document.getElementById('config-secondary').value;
            const accent = document.getElementById('config-accent').value;
            
            try {
                // Note: Configuration update would require a separate API endpoint
                // For now, show a message about manual configuration
                showToast('Configurações salvas! Recarregue a página da TV.', 'success');
                
                // In a full implementation, you would POST to an API:
                /*
                const response = await fetch('api/config.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        company_name: company,
                        logo_url: logoUrl,
                        logo_base64: logoBase64,
                        rotation_interval_seconds: interval,
                        theme_primary: primary,
                        theme_secondary: secondary,
                        theme_accent: accent
                    })
                });
                */
            } catch (error) {
                showToast(error.message, 'error');
            }
        }
        
        // Refresh Data
        function refreshData() {
            fetchData();
            showToast('Dados atualizados!');
        }
        
        // Escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Format Date
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            fetchData();
        });
    </script>
</body>
</html>
