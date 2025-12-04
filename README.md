# TV Corporativa PHP

Sistema de TV Interna Corporativa em PHP para exibição de notícias e informações relevantes em tela cheia.

## 📺 Descrição

Sistema completo de TV corporativa que funciona em fullscreen (1920x1080), exibindo:
- Relógio em tempo real (HH:MM:SS)
- Data por extenso em português
- Logo da empresa
- Grid dinâmico com notícias e mídias
- Rotação automática de páginas com transições suaves
- Ticker de notícias na parte inferior

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL/MariaDB
- Extensão PDO habilitada
- Servidor web (Apache, Nginx, etc.)

## 🚀 Instalação

### 1. Clone ou baixe os arquivos

```bash
git clone https://github.com/seu-usuario/tv-corporativa-php.git
cd tv-corporativa-php
```

### 2. Configure o banco de dados

Edite o arquivo `api/config.php` com suas credenciais:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'u711845530_tv_asti');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 3. Execute o script SQL complementar

Execute o arquivo `database_complementar.sql` no seu banco de dados para criar as tabelas necessárias:

```bash
mysql -u seu_usuario -p u711845530_tv_asti < database_complementar.sql
```

Ou importe via phpMyAdmin ou outro gerenciador de banco de dados.

### 4. Configure o servidor web

Aponte o document root para a pasta do projeto ou acesse via URL.

## 📁 Estrutura de Arquivos

```
├── api/
│   ├── config.php          # Configurações do banco e aplicação
│   ├── tv.php              # API GET/POST para conteúdos
│   └── delete.php          # API DELETE para exclusão
├── includes/
│   ├── database.php        # Classe de conexão PDO (singleton)
│   └── functions.php       # Funções auxiliares
├── index.php               # Página principal da TV (fullscreen)
├── admin.php               # Painel de administração
├── database_complementar.sql # Script SQL complementar
└── README.md               # Esta documentação
```

## 🔌 API

### GET /api/tv.php

Retorna todos os conteúdos ativos e configurações.

**Resposta:**
```json
{
  "success": true,
  "data": {
    "config": {
      "company_name": "ASTI",
      "logo_url": "",
      "rotation_interval_seconds": 20,
      "theme_primary": "#1E3A8A",
      "theme_secondary": "#0F172A",
      "theme_accent": "#3B82F6"
    },
    "news": [...],
    "media": [...],
    "timestamp": "2024-01-01 12:00:00"
  }
}
```

### POST /api/tv.php

Cria um novo conteúdo (notícia ou mídia).

**Criar Notícia:**
```json
{
  "type": "news",
  "title": "Título da notícia",
  "description": "Descrição curta",
  "message": "Conteúdo completo da notícia"
}
```

**Criar Mídia:**
```json
{
  "type": "media",
  "title": "Título da mídia",
  "description": "Descrição da mídia",
  "image": {
    "base64": "conteudo_base64_da_imagem",
    "mimeType": "image/jpeg",
    "fileName": "imagem.jpg"
  }
}
```

### DELETE /api/delete.php

Exclui um conteúdo por tipo e ID.

**Parâmetros:**
- `type`: `news` ou `media`
- `id`: ID do conteúdo
- `permanent`: `1` para exclusão permanente (opcional)

**Exemplo:**
```
DELETE /api/delete.php?type=news&id=123
DELETE /api/delete.php?type=media&id=456&permanent=1
```

## 🎨 Configurações do Tema

As cores e configurações da TV são armazenadas na tabela `tv_config`:

| Campo | Descrição |
|-------|-----------|
| `company_name` | Nome da empresa exibido no header |
| `logo_url` | URL do logo (PNG, SVG) |
| `logo_base64` | Logo em formato Base64 |
| `rotation_interval_seconds` | Intervalo de rotação (padrão: 20s) |
| `theme_primary` | Cor primária (hex) |
| `theme_secondary` | Cor secundária (hex) |
| `theme_accent` | Cor de destaque (hex) |

## 🖥️ Uso

### Visualização da TV

Acesse `index.php` no navegador e pressione F11 para tela cheia.

- **Auto-refresh**: A página recarrega automaticamente a cada 5 minutos
- **Rotação**: As páginas de notícias e mídias rotacionam conforme configurado
- **Cursor oculto**: O cursor é automaticamente escondido para exibição em TV

### Painel de Administração

Acesse `admin.php` para gerenciar conteúdos:

1. **Adicionar Notícia**: Preencha título, descrição e mensagem
2. **Adicionar Mídia**: Preencha título, descrição e faça upload de imagem
3. **Excluir Conteúdo**: Clique no botão excluir na lista de conteúdos
4. **Configurações**: Altere nome da empresa, logo e cores do tema

## 📊 Banco de Dados

### Tabela `conteudos`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID único |
| `tipo` | ENUM('news', 'media') | Tipo do conteúdo |
| `titulo` | VARCHAR(60) | Título |
| `descricao` | VARCHAR(120) | Descrição curta |
| `mensagem` | TEXT | Conteúdo completo (para news) |
| `nome_autor` | VARCHAR(255) | Nome do autor |
| `email_autor` | VARCHAR(255) | Email do autor |
| `dt_publicacao` | DATETIME | Data de publicação |
| `id_anexo` | INT | ID do anexo relacionado |
| `is_active` | TINYINT(1) | Status ativo (1) ou inativo (0) |

### Tabela `anexo`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID único |
| `conteudos_id` | INT | ID do conteúdo relacionado |
| `nome_arquivo` | VARCHAR(255) | Nome do arquivo |
| `caminho_arquivo` | VARCHAR(255) | Caminho do arquivo |
| `google_drive_id` | VARCHAR(255) | ID do Google Drive |
| `google_drive_link` | VARCHAR(512) | Link do Google Drive |
| `tipo_arquivo` | VARCHAR(100) | MIME type |
| `tamanho_bytes` | BIGINT | Tamanho em bytes |
| `dt_upload` | DATETIME | Data do upload |
| `usuario_email` | VARCHAR(255) | Email do usuário |
| `conteudo_arquivo` | LONGTEXT | Conteúdo em Base64 |

### Tabela `tv_config`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | INT | ID único |
| `company_name` | VARCHAR(100) | Nome da empresa |
| `logo_url` | VARCHAR(500) | URL do logo |
| `logo_base64` | LONGTEXT | Logo em Base64 |
| `rotation_interval_seconds` | INT | Intervalo de rotação |
| `theme_primary` | VARCHAR(7) | Cor primária |
| `theme_secondary` | VARCHAR(7) | Cor secundária |
| `theme_accent` | VARCHAR(7) | Cor de destaque |
| `updated_at` | DATETIME | Data de atualização |

## 🔧 Compatibilidade

- Compatível com API Next.js existente
- Headers CORS configurados para acesso de diferentes origens
- Layout responsivo (otimizado para 1920x1080)
- Funciona em navegadores modernos (Chrome, Firefox, Edge, Safari)

## 📝 Licença

Este projeto está sob a licença MIT.
