<?php
/**
 * API para TV Corporativa
 * GET: Retorna conteúdos ativos (news e media) + configurações
 * POST: Cria novo conteúdo
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Configurar headers CORS
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance();
    
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
            
        case 'POST':
            handlePost($db);
            break;
            
        default:
            jsonError('Método não permitido', 405);
    }
} catch (Exception $e) {
    jsonError('Erro interno do servidor: ' . $e->getMessage(), 500);
}

/**
 * GET: Retorna todos os conteúdos ativos e configurações
 */
function handleGet(Database $db): void {
    // Obtém configurações da TV
    $config = getTVConfig();
    
    // Obtém notícias ativas
    $newsQuery = "SELECT c.id, c.tipo, c.titulo, c.descricao, c.mensagem, c.nome_autor, 
                         c.email_autor, c.dt_publicacao, c.is_active
                  FROM conteudos c 
                  WHERE c.tipo = 'news' AND c.is_active = 1 
                  ORDER BY c.dt_publicacao DESC";
    $news = $db->fetchAll($newsQuery);
    
    // Obtém mídias ativas com anexos
    $mediaQuery = "SELECT c.id, c.tipo, c.titulo, c.descricao, c.nome_autor, 
                          c.email_autor, c.dt_publicacao, c.is_active,
                          a.nome_arquivo, a.caminho_arquivo, a.google_drive_link, 
                          a.tipo_arquivo, a.conteudo_arquivo
                   FROM conteudos c 
                   LEFT JOIN anexo a ON c.id = a.conteudos_id 
                   WHERE c.tipo = 'media' AND c.is_active = 1 
                   ORDER BY c.dt_publicacao DESC";
    $media = $db->fetchAll($mediaQuery);
    
    // Formata as mídias para incluir a imagem em base64
    $formattedMedia = array_map(function($item) {
        $imageData = null;
        
        if (!empty($item['conteudo_arquivo'])) {
            $imageData = $item['conteudo_arquivo'];
        } elseif (!empty($item['google_drive_link'])) {
            $imageData = $item['google_drive_link'];
        } elseif (!empty($item['caminho_arquivo'])) {
            $imageData = $item['caminho_arquivo'];
        }
        
        return [
            'id' => $item['id'],
            'tipo' => $item['tipo'],
            'titulo' => $item['titulo'],
            'descricao' => $item['descricao'],
            'nome_autor' => $item['nome_autor'],
            'email_autor' => $item['email_autor'],
            'dt_publicacao' => $item['dt_publicacao'],
            'is_active' => $item['is_active'],
            'image' => $imageData,
            'mime_type' => $item['tipo_arquivo'] ?? 'image/jpeg',
            'file_name' => $item['nome_arquivo'] ?? null
        ];
    }, $media);
    
    jsonSuccess([
        'config' => $config,
        'news' => $news,
        'media' => $formattedMedia,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * POST: Cria novo conteúdo
 */
function handlePost(Database $db): void {
    // Obtém dados do corpo da requisição
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        jsonError('Dados inválidos ou JSON malformado');
    }
    
    $type = $data['type'] ?? '';
    
    if (!in_array($type, ['news', 'media'])) {
        jsonError('Tipo de conteúdo inválido. Use "news" ou "media"');
    }
    
    if ($type === 'news') {
        createNews($db, $data);
    } else {
        createMedia($db, $data);
    }
}

/**
 * Cria uma notícia
 */
function createNews(Database $db, array $data): void {
    $required = ['title', 'message'];
    $errors = validateInput($required, $data);
    
    if (!empty($errors)) {
        jsonError(implode(' ', $errors));
    }
    
    $title = trim($data['title']);
    $message = trim($data['message']);
    $description = trim($data['description'] ?? 'Notícia');
    $authorName = trim($data['author_name'] ?? 'Sistema');
    $authorEmail = trim($data['author_email'] ?? 'sistema@empresa.com');
    
    // Valida tamanho dos campos
    if (strlen($title) > 60) {
        jsonError('O título deve ter no máximo 60 caracteres');
    }
    
    if (strlen($description) > 120) {
        jsonError('A descrição deve ter no máximo 120 caracteres');
    }
    
    $id = $db->insert('conteudos', [
        'tipo' => 'news',
        'titulo' => $title,
        'descricao' => $description,
        'mensagem' => $message,
        'nome_autor' => $authorName,
        'email_autor' => $authorEmail,
        'dt_publicacao' => date('Y-m-d H:i:s'),
        'id_anexo' => 0,
        'is_active' => 1
    ]);
    
    jsonSuccess([
        'id' => $id,
        'message' => 'Notícia criada com sucesso'
    ], 201);
}

/**
 * Cria uma mídia
 */
function createMedia(Database $db, array $data): void {
    $required = ['title', 'description'];
    $errors = validateInput($required, $data);
    
    if (!empty($errors)) {
        jsonError(implode(' ', $errors));
    }
    
    // Verifica se tem imagem
    if (!isset($data['image']) || !is_array($data['image'])) {
        jsonError('Imagem é obrigatória para conteúdo do tipo media');
    }
    
    $image = $data['image'];
    
    if (!isset($image['base64']) || !isset($image['mimeType']) || !isset($image['fileName'])) {
        jsonError('Dados da imagem incompletos. Campos obrigatórios: base64, mimeType, fileName');
    }
    
    // Valida MIME type
    if (!isValidImageMimeType($image['mimeType'])) {
        jsonError('Tipo de arquivo não permitido. Tipos aceitos: JPEG, PNG, GIF, WebP');
    }
    
    // Sanitiza Base64
    $base64Content = sanitizeBase64($image['base64']);
    
    if (empty($base64Content)) {
        jsonError('Conteúdo Base64 inválido');
    }
    
    $title = trim($data['title']);
    $description = trim($data['description']);
    $authorName = trim($data['author_name'] ?? 'Sistema');
    $authorEmail = trim($data['author_email'] ?? 'sistema@empresa.com');
    
    // Valida tamanho dos campos
    if (strlen($title) > 60) {
        jsonError('O título deve ter no máximo 60 caracteres');
    }
    
    if (strlen($description) > 120) {
        jsonError('A descrição deve ter no máximo 120 caracteres');
    }
    
    // Inicia transação
    $pdo = $db->getConnection();
    $pdo->beginTransaction();
    
    try {
        // Insere o conteúdo
        $contentId = $db->insert('conteudos', [
            'tipo' => 'media',
            'titulo' => $title,
            'descricao' => $description,
            'mensagem' => null,
            'nome_autor' => $authorName,
            'email_autor' => $authorEmail,
            'dt_publicacao' => date('Y-m-d H:i:s'),
            'id_anexo' => 0,
            'is_active' => 1
        ]);
        
        // Gera nome único para o arquivo
        $fileName = generateUniqueFileName($image['fileName']);
        
        // Calcula tamanho em bytes
        $decodedContent = base64_decode($base64Content);
        $fileSize = strlen($decodedContent);
        
        // Insere o anexo
        $anexoId = $db->insert('anexo', [
            'conteudos_id' => $contentId,
            'nome_arquivo' => $fileName,
            'caminho_arquivo' => 'uploads/' . $fileName,
            'google_drive_id' => '',
            'google_drive_link' => '',
            'tipo_arquivo' => $image['mimeType'],
            'tamanho_bytes' => $fileSize,
            'dt_upload' => date('Y-m-d H:i:s'),
            'usuario_email' => $authorEmail,
            'conteudo_arquivo' => 'data:' . $image['mimeType'] . ';base64,' . $base64Content
        ]);
        
        // Atualiza o id_anexo no conteúdo
        $db->update('conteudos', ['id_anexo' => $anexoId], 'id = ?', [$contentId]);
        
        $pdo->commit();
        
        jsonSuccess([
            'id' => $contentId,
            'anexo_id' => $anexoId,
            'message' => 'Mídia criada com sucesso'
        ], 201);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
