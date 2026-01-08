INSERT INTO category (name, slug, description, color, created_at) VALUES 
('Habits', 'habits', 'Vêtements et mode', '#8B5CF6', NOW()),
('Meubles', 'meubles', 'Mobilier et ameublement', '#10B981', NOW()),
('Appareils', 'appareils', 'Électroménager et high-tech', '#3B82F6', NOW()),
('Autres', 'autres', 'Divers', '#6B7280', NOW())
ON DUPLICATE KEY UPDATE name=name;
