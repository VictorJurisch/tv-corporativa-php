<?php
/**
 * Endpoint para atualização das configurações da TV
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/functions.php';

setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Método não permitido', 405);
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    jsonError('Dados inválidos ou JSON malformado');
}

$companyName = trim($data['company_name'] ?? '');
$logoUrl = trim($data['logo_url'] ?? '');
$logoBase64 = trim($data['logo_base64'] ?? '');
$interval = (int)($data['rotation_interval_seconds'] ?? 20);
$themePrimary = trim($data['theme_primary'] ?? '');
$themeSecondary = trim($data['theme_secondary'] ?? '');
$themeAccent = trim($data['theme_accent'] ?? '');

if ($companyName === '') {
    jsonError('Nome da empresa é obrigatório');
}

if ($logoUrl !== '' && !filter_var($logoUrl, FILTER_VALIDATE_URL)) {
    jsonError('URL do logo inválida');
}

function ensureHexColor(string $color, string $field): string {
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
        jsonError("Cor inválida para {$field}. Use o formato HEX #RRGGBB.");
    }
    return strtoupper($color);
}

$themePrimary = ensureHexColor($themePrimary, 'cor primária');
$themeSecondary = ensureHexColor($themeSecondary, 'cor secundária');
$themeAccent = ensureHexColor($themeAccent, 'cor de destaque');

if ($interval < 5 || $interval > 120) {
    $interval = max(5, min(120, $interval));
}

if ($logoBase64 !== '') {
    if (preg_match('/^data:image\/[a-z0-9.+-]+;base64,/i', $logoBase64)) {
        $parts = explode(',', $logoBase64, 2);
        $clean = sanitizeBase64($parts[1] ?? '');
        if ($clean === '') {
            jsonError('Logo em Base64 inválido.');
        }
        $logoBase64 = $parts[0] . ',' . $clean;
    } else {
        $clean = sanitizeBase64($logoBase64);
        if ($clean === '') {
            jsonError('Logo em Base64 inválido.');
        }
        $logoBase64 = 'data:image/png;base64,' . $clean;
    }
}

try {
    $db = Database::getInstance();
    $existing = $db->fetch('SELECT id FROM tv_config WHERE id = 1');

    $payload = [
        'company_name' => $companyName,
        'logo_url' => $logoUrl,
        'logo_base64' => $logoBase64 !== '' ? $logoBase64 : null,
        'rotation_interval_seconds' => $interval,
        'theme_primary' => $themePrimary,
        'theme_secondary' => $themeSecondary,
        'theme_accent' => $themeAccent,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    if ($existing) {
        $db->update('tv_config', $payload, 'id = ?', [1]);
    } else {
        $payload['id'] = 1;
        $db->insert('tv_config', $payload);
    }

    $config = $db->fetch('SELECT * FROM tv_config WHERE id = 1');

    jsonSuccess([
        'message' => 'Configurações atualizadas com sucesso',
        'config' => $config
    ]);
} catch (Exception $e) {
    jsonError('Erro ao salvar configurações: ' . $e->getMessage(), 500);
}
