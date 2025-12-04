<?php
/**
 * API para exclusão de conteúdos
 * DELETE: Exclui conteúdo por tipo e ID
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Configurar headers CORS
setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'DELETE') {
    jsonError('Método não permitido. Use DELETE.', 405);
}

try {
    $db = Database::getInstance();
    
    // Obtém parâmetros da query string
    $type = $_GET['type'] ?? '';
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $permanent = isset($_GET['permanent']) && $_GET['permanent'] === '1';
    
    // Validações
    if (!in_array($type, ['news', 'media'])) {
        jsonError('Tipo inválido. Use "news" ou "media"');
    }
    
    if ($id <= 0) {
        jsonError('ID inválido');
    }
    
    // Verifica se o conteúdo existe
    $content = $db->fetch(
        "SELECT id, tipo, titulo FROM conteudos WHERE id = ? AND tipo = ?",
        [$id, $type]
    );
    
    if (!$content) {
        jsonError('Conteúdo não encontrado', 404);
    }
    
    if ($permanent) {
        // Exclusão permanente
        // Primeiro exclui os anexos relacionados
        $db->delete('anexo', 'conteudos_id = ?', [$id]);
        
        // Depois exclui o conteúdo
        $deleted = $db->delete('conteudos', 'id = ? AND tipo = ?', [$id, $type]);
        
        if ($deleted > 0) {
            jsonSuccess([
                'id' => $id,
                'type' => $type,
                'message' => 'Conteúdo excluído permanentemente'
            ]);
        }
    } else {
        // Soft delete (desativa o conteúdo)
        $updated = $db->update(
            'conteudos',
            ['is_active' => 0],
            'id = ? AND tipo = ?',
            [$id, $type]
        );
        
        if ($updated > 0) {
            jsonSuccess([
                'id' => $id,
                'type' => $type,
                'message' => 'Conteúdo desativado com sucesso'
            ]);
        }
    }
    
    jsonError('Falha ao excluir conteúdo', 500);
    
} catch (Exception $e) {
    jsonError('Erro interno do servidor: ' . $e->getMessage(), 500);
}
