<?php
/**
 * Funções auxiliares
 * TV Corporativa PHP
 */

/**
 * Sanitiza uma string para saída HTML
 */
function sanitize(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitiza uma string Base64
 */
function sanitizeBase64(string $base64): string {
    // Remove espaços em branco e quebras de linha
    $base64 = preg_replace('/\s+/', '', $base64);
    
    // Remove o prefixo data:image/* se existir
    if (preg_match('/^data:image\/[a-z]+;base64,/i', $base64)) {
        $base64 = preg_replace('/^data:image\/[a-z]+;base64,/i', '', $base64);
    }
    
    // Valida se é um base64 válido
    if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $base64)) {
        return '';
    }
    
    return $base64;
}

/**
 * Valida MIME type para imagens
 */
function isValidImageMimeType(string $mimeType): bool {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    return in_array(strtolower($mimeType), $allowedTypes);
}

/**
 * Retorna a data por extenso em português
 */
function getDateExtensive(): string {
    $diasSemana = [
        'Sunday' => 'Domingo',
        'Monday' => 'Segunda-feira',
        'Tuesday' => 'Terça-feira',
        'Wednesday' => 'Quarta-feira',
        'Thursday' => 'Quinta-feira',
        'Friday' => 'Sexta-feira',
        'Saturday' => 'Sábado'
    ];
    
    $meses = [
        'January' => 'Janeiro',
        'February' => 'Fevereiro',
        'March' => 'Março',
        'April' => 'Abril',
        'May' => 'Maio',
        'June' => 'Junho',
        'July' => 'Julho',
        'August' => 'Agosto',
        'September' => 'Setembro',
        'October' => 'Outubro',
        'November' => 'Novembro',
        'December' => 'Dezembro'
    ];
    
    $diaSemana = $diasSemana[date('l')];
    $dia = date('d');
    $mes = $meses[date('F')];
    $ano = date('Y');
    
    return "{$diaSemana}, {$dia} de {$mes} de {$ano}";
}

/**
 * Retorna resposta JSON de sucesso
 */
function jsonSuccess($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => true,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Retorna resposta JSON de erro
 */
function jsonError(string $message, int $statusCode = 400): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'error' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Obtém as configurações da TV do banco
 */
function getTVConfig(): array {
    try {
        $db = Database::getInstance();
        $config = $db->fetch("SELECT * FROM tv_config WHERE id = 1");
        
        if (!$config) {
            return [
                'company_name' => 'ASTI',
                'logo_url' => '',
                'logo_base64' => '',
                'rotation_interval_seconds' => 20,
                'theme_primary' => '#1E3A8A',
                'theme_secondary' => '#0F172A',
                'theme_accent' => '#3B82F6'
            ];
        }
        
        return $config;
    } catch (Exception $e) {
        return [
            'company_name' => 'ASTI',
            'logo_url' => '',
            'logo_base64' => '',
            'rotation_interval_seconds' => 20,
            'theme_primary' => '#1E3A8A',
            'theme_secondary' => '#0F172A',
            'theme_accent' => '#3B82F6'
        ];
    }
}

/**
 * Obtém conteúdos ativos do tipo especificado
 */
function getActiveContents(string $type = null): array {
    try {
        $db = Database::getInstance();
        
        $sql = "SELECT c.*, a.nome_arquivo, a.caminho_arquivo, a.google_drive_link, 
                       a.tipo_arquivo, a.conteudo_arquivo 
                FROM conteudos c 
                LEFT JOIN anexo a ON c.id = a.conteudos_id 
                WHERE c.is_active = 1";
        
        $params = [];
        
        if ($type !== null) {
            $sql .= " AND c.tipo = ?";
            $params[] = $type;
        }
        
        $sql .= " ORDER BY c.dt_publicacao DESC";
        
        return $db->fetchAll($sql, $params);
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Formata o tamanho do arquivo
 */
function formatFileSize(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Gera um nome de arquivo único
 */
function generateUniqueFileName(string $originalName): string {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $uniqueName = uniqid('media_', true) . '.' . $extension;
    return $uniqueName;
}

/**
 * Valida e limpa dados de entrada
 */
function validateInput(array $required, array $data): array {
    $errors = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[] = "O campo '{$field}' é obrigatório.";
        }
    }
    
    return $errors;
}
