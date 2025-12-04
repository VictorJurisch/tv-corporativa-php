-- =============================================
-- Script SQL Complementar para TV Corporativa
-- Banco de dados: u711845530_tv_asti
-- =============================================

-- Tabela de configurações da TV
CREATE TABLE IF NOT EXISTS `tv_config` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `company_name` VARCHAR(100) NOT NULL,
    `logo_url` VARCHAR(500) NOT NULL,
    `logo_base64` LONGTEXT NULL,
    `rotation_interval_seconds` INT NOT NULL DEFAULT 20,
    `theme_primary` VARCHAR(7) DEFAULT '#1E3A8A',
    `theme_secondary` VARCHAR(7) DEFAULT '#0F172A',
    `theme_accent` VARCHAR(7) DEFAULT '#3B82F6',
    `updated_at` DATETIME NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- Inserir configuração padrão
INSERT INTO `tv_config` (`company_name`, `logo_url`, `rotation_interval_seconds`) 
VALUES ('ASTI', '', 20)
ON DUPLICATE KEY UPDATE `company_name` = 'ASTI';

-- Adicionar colunas na tabela conteudos (se não existirem)
-- Nota: Execute cada ALTER separadamente se necessário

-- Adicionar coluna tipo
ALTER TABLE `conteudos` 
ADD COLUMN IF NOT EXISTS `tipo` ENUM('news', 'media') NOT NULL DEFAULT 'media' AFTER `id`;

-- Adicionar coluna mensagem
ALTER TABLE `conteudos` 
ADD COLUMN IF NOT EXISTS `mensagem` TEXT NULL AFTER `descricao`;

-- Adicionar coluna is_active
ALTER TABLE `conteudos` 
ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `id_anexo`;

-- Dados de exemplo para notícias
INSERT INTO `conteudos` (`tipo`, `titulo`, `descricao`, `mensagem`, `nome_autor`, `email_autor`, `dt_publicacao`, `id_anexo`, `is_active`) VALUES
('news', 'Bem-vindos ao novo sistema!', 'TV Corporativa', 'Estamos lançando nossa nova TV Corporativa para manter todos informados sobre as novidades e eventos da empresa.', 'Administrador', 'admin@empresa.com', NOW(), 0, 1),
('news', 'Reunião Geral - Sexta-feira', 'Lembrete Importante', 'Reunião geral na sexta-feira às 14h no auditório principal. Presença obrigatória de todos os colaboradores.', 'RH', 'rh@empresa.com', NOW(), 0, 1),
('news', 'Novo Programa de Benefícios', 'Comunicado RH', 'A partir do próximo mês, teremos novos benefícios para todos os colaboradores. Mais detalhes serão enviados por email.', 'RH', 'rh@empresa.com', NOW(), 0, 1);

-- Índices para melhorar performance
CREATE INDEX IF NOT EXISTS idx_conteudos_tipo ON conteudos(tipo);
CREATE INDEX IF NOT EXISTS idx_conteudos_is_active ON conteudos(is_active);
CREATE INDEX IF NOT EXISTS idx_conteudos_dt_publicacao ON conteudos(dt_publicacao);
