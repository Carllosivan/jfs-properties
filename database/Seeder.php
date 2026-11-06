<?php

namespace Database;

use App\Core\Database;
use App\Core\Logger;
use PDO;

class Seeder
{
    public static function run(): void
    {
        $db = Database::getConnection();

        // 1. Organization
        $db->exec("
            INSERT OR IGNORE INTO organizations (id, name, nuit, email, phone, address, currency)
            VALUES (1, 'JFS Properties, Lda.', '400987654', 'suporte@jfsproperties.co.mz', '+258 84 123 4567', 'Av. Julius Nyerere, Edifício Jat V, Maputo', 'MZN');
        ");

        // 2. Roles
        $roles = [
            ['Super Administrador', 'super_admin', 'Acesso total a todas as funções e definições'],
            ['Administrador', 'admin', 'Gestão operacional de imóveis, incidentes e manutenção'],
            ['Gestor Imobiliário', 'property_manager', 'Administração de imóveis, unidades e ocupantes'],
            ['Gestor de Facilities', 'facilities_manager', 'Supervisão técnica de edifícios, activos e serviços'],
            ['Supervisor de Manutenção', 'maintenance_supervisor', 'Acompanhamento de planos, tarefas e aprovações'],
            ['Técnico', 'technician', 'Execução no terreno de ordens de serviço e registo de intervenções'],
            ['Inspector', 'inspector', 'Realização de checklists e inspecções técnicas periódicas'],
            ['Gestor Financeiro', 'financial_manager', 'Controlo de orçamentos, custos operacionais e despesas'],
            ['Fornecedor', 'supplier', 'Acesso a ordens de serviço subcontratadas e orçamentação'],
            ['Ocupante', 'occupant', 'Portal exclusivo para submissão e acompanhamento de incidentes'],
        ];

        $roleStmt = $db->prepare("INSERT OR IGNORE INTO roles (name, slug, description) VALUES (?, ?, ?)");
        foreach ($roles as $r) {
            $roleStmt->execute($r);
        }

        // 3. Permissions
        $modules = [
            'properties' => ['view', 'create', 'edit', 'archive'],
            'occupants' => ['view', 'create', 'edit', 'archive'],
            'incidents' => ['view', 'create', 'edit', 'assign', 'resolve', 'validate', 'close', 'reopen'],
            'work_orders' => ['view', 'create', 'edit', 'assign', 'execute', 'approve', 'validate', 'cancel'],
            'maintenance' => ['view', 'create', 'edit', 'schedule', 'generate'],
            'assets' => ['view', 'create', 'edit', 'archive'],
            'inspections' => ['view', 'create', 'execute', 'report'],
            'technicians' => ['view', 'create', 'edit', 'assign'],
            'suppliers' => ['view', 'create', 'edit', 'evaluate'],
            'stock' => ['view', 'create', 'edit', 'move', 'adjust'],
            'financial' => ['view', 'create', 'edit', 'approve', 'export'],
            'reports' => ['view', 'export'],
            'documents' => ['view', 'upload', 'download', 'delete'],
            'users' => ['view', 'create', 'edit', 'permissions'],
            'audit' => ['view'],
            'settings' => ['view', 'edit'],
        ];

        $permStmt = $db->prepare("INSERT OR IGNORE INTO permissions (name, slug, module) VALUES (?, ?, ?)");
        foreach ($modules as $mod => $actions) {
            foreach ($actions as $act) {
                $slug = "{$mod}.{$act}";
                $name = ucfirst($act) . ' ' . ucfirst($mod);
                $permStmt->execute([$name, $slug, $mod]);
            }
        }

        // Link Super Admin to all permissions
        $superAdminRoleId = $db->query("SELECT id FROM roles WHERE slug = 'super_admin'")->fetchColumn();
        if ($superAdminRoleId) {
            $perms = $db->query("SELECT id FROM permissions")->fetchAll(PDO::FETCH_COLUMN);
            $rpStmt = $db->prepare("INSERT OR IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($perms as $pId) {
                $rpStmt->execute([$superAdminRoleId, $pId]);
            }
        }

        // 4. Default Users
        $defaultPasswordHash = password_hash('Admin123!', PASSWORD_BCRYPT);
        $userStmt = $db->prepare("
            INSERT OR IGNORE INTO users (id, organization_id, name, email, password_hash, phone, status)
            VALUES (?, 1, ?, ?, ?, ?, 'active')
        ");

        $userStmt->execute([1, 'Administrador JFS', 'admin@jfsproperties.co.mz', $defaultPasswordHash, '+258 84 000 0001']);
        $userStmt->execute([2, 'Carlos Mondlane', 'carlos.tecnico@jfsproperties.co.mz', $defaultPasswordHash, '+258 84 111 2233']);
        $userStmt->execute([3, 'Mateus Nhaca', 'mateus.tecnico@jfsproperties.co.mz', $defaultPasswordHash, '+258 84 222 3344']);
        $userStmt->execute([4, 'Ana Lúcia Cossa', 'ana.cossa@gmail.com', $defaultPasswordHash, '+258 84 333 4455']);

        // Link User Roles
        $urStmt = $db->prepare("INSERT OR IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $adminRoleId = $db->query("SELECT id FROM roles WHERE slug = 'super_admin'")->fetchColumn();
        $techRoleId = $db->query("SELECT id FROM roles WHERE slug = 'technician'")->fetchColumn();
        $occRoleId = $db->query("SELECT id FROM roles WHERE slug = 'occupant'")->fetchColumn();

        if ($adminRoleId) $urStmt->execute([1, $adminRoleId]);
        if ($techRoleId) {
            $urStmt->execute([2, $techRoleId]);
            $urStmt->execute([3, $techRoleId]);
        }
        if ($occRoleId) $urStmt->execute([4, $occRoleId]);

        // 5. Teams & Technicians
        $db->exec("
            INSERT OR IGNORE INTO teams (id, name, code, description)
            VALUES 
            (1, 'Equipa de Manutenção Eléctrica', 'EQ-ELEC', 'Responsável por quadros, geradores, iluminação e alimentação'),
            (2, 'Equipa de AVAC e Hidráulica', 'EQ-AVAC', 'Responsável por climatização, bombas de água e saneamento');

            INSERT OR IGNORE INTO technicians (id, user_id, name, email, phone, specialization, team_id, status, hourly_rate)
            VALUES 
            (1, 2, 'Carlos Mondlane', 'carlos.tecnico@jfsproperties.co.mz', '+258 84 111 2233', 'Electricidade', 1, 'available', 450.00),
            (2, 3, 'Mateus Nhaca', 'mateus.tecnico@jfsproperties.co.mz', '+258 84 222 3344', 'Canalização e Climatização', 2, 'available', 400.00);
        ");

        // 6. Suppliers
        $db->exec("
            INSERT OR IGNORE INTO suppliers (id, name, trade_name, nuit, service_type, contact_person, email, phone, address, rating)
            VALUES 
            (1, 'Schindler Moçambique, Lda.', 'Schindler Elevadores', '400123987', 'Manutenção de Elevadores e Escadas', 'Eng. Pedro Sitoe', 'contacto@schindler.co.mz', '+258 21 300 400', 'Av. 25 de Setembro, Maputo', 4.9),
            (2, 'ElectroTec Moçambique, SA', 'ElectroTec', '400555666', 'Sistemas Eléctricos e Geradores', 'Dra. Marta Bila', 'vendas@electrotec.co.mz', '+258 21 411 500', 'Bairro do Jardim, Maputo', 4.7),
            (3, 'ClimaFrio Serviços e Engenharia', 'ClimaFrio', '400777888', 'Assistência Técnica de AVAC / Chillers', 'Eng. Armando Langa', 'assistencia@climafrio.co.mz', '+258 82 999 1111', 'Av. das FPLM, Maputo', 4.8);
        ");

        // 7. Incident Categories & Subcategories
        $db->exec("
            INSERT OR IGNORE INTO incident_categories (id, name, color, icon, sla_response_hours, sla_resolution_hours)
            VALUES 
            (1, 'Electricidade', '#f59e0b', 'zap', 2, 8),
            (2, 'Canalização e Esgotos', '#0284c7', 'droplets', 2, 12),
            (3, 'Climatização (AVAC)', '#06b6d4', 'thermometer', 4, 24),
            (4, 'Elevadores e Acessos', '#ef4444', 'arrow-up-down', 1, 4),
            (5, 'Segurança e Incêndio', '#dc2626', 'shield-alert', 1, 4),
            (6, 'Estrutura e Edifício', '#64748b', 'building-2', 8, 48),
            (7, 'Limpeza e Áreas Comuns', '#10b981', 'sparkles', 4, 12);

            INSERT OR IGNORE INTO incident_subcategories (id, category_id, name)
            VALUES 
            (1, 1, 'Quadro Eléctrico / Disjuntor Disparado'),
            (2, 1, 'Falta de Iluminação em Área Comum'),
            (3, 1, 'Tomadas / Interruptores Danificados'),
            (4, 2, 'Rotura de Tubagem com Fuga Activa'),
            (5, 2, 'Infiltração em Tecto ou Parede'),
            (6, 2, 'Sanitário ou Ralo Entupido'),
            (7, 3, 'Unidade AC Sem Fazer Frio'),
            (8, 3, 'Fuga de Água no Split'),
            (9, 4, 'Elevador Parado no Piso'),
            (10, 4, 'Portão Automático Bloqueado');
        ");

        // 8. Maintenance Categories
        $db->exec("
            INSERT OR IGNORE INTO maintenance_categories (id, name, description)
            VALUES 
            (1, 'Manutenção Eléctrica Preventiva', 'Verificação de quadros, termografia, geradores e grupos de emergência'),
            (2, 'Manutenção AVAC / Climatização', 'Limpeza química de baterias, substituição de filtros e medição de gás'),
            (3, 'Manutenção Hidráulica', 'Inspecção de bombas de pressurização, reservatórios e válvulas redutoras'),
            (4, 'Manutenção de Elevadores', 'Inspecção técnica legal mensal das máquinas de tracção e cabos'),
            (5, 'Sistemas Contra Incêndios', 'Teste de centrais de detecção, bombas de incêndio e carretéis');
        ");

        // 9. Asset Categories
        $db->exec("
            INSERT OR IGNORE INTO asset_categories (id, name, code, description)
            VALUES 
            (1, 'Geradores de Emergência', 'GER', 'Grupos electrogéneos a diesel de reserva'),
            (2, 'Elevadores de Passageiros', 'ELEV', 'Ascensores eléctricos e hidráulicos'),
            (3, 'Grupos Hidropressores', 'BOMB', 'Bombas de água potável e recirculação'),
            (4, 'Sistemas VRF / AVAC', 'AVAC', 'Centrais de ar condicionado e chillers'),
            (5, 'Quadros Eléctricos Gerais', 'QE', 'Quadros gerais de baixa tensão');
        ");

        // 10. Material Categories & Warehouses
        $db->exec("
            INSERT OR IGNORE INTO material_categories (id, name, code)
            VALUES 
            (1, 'Material Eléctrico', 'ELEC'),
            (2, 'Material Hidráulico', 'HIDR'),
            (3, 'Climatização e Gás', 'AVAC'),
            (4, 'Fitas, Vedantes e Consumíveis', 'CONS');

            INSERT OR IGNORE INTO warehouses (id, name, code, location)
            VALUES 
            (1, 'Armazém Central JFS - Polana', 'ARM-01', 'Cave - Edifício Polana Prime');

            INSERT OR IGNORE INTO materials (id, code, name, category_id, warehouse_id, unit_of_measure, quantity_available, min_stock_level, unit_cost)
            VALUES 
            (1, 'MAT-E01', 'Lâmpada LED Tubular T8 18W 6500K', 1, 1, 'un', 48, 15, 350.00),
            (2, 'MAT-E02', 'Disjuntor Bipolar 20A Schneider', 1, 1, 'un', 12, 5, 850.00),
            (3, 'MAT-H01', 'Torneira Misturadora Monocomando Lavatório', 2, 1, 'un', 8, 4, 2800.00),
            (4, 'MAT-H02', 'Válvula de Esfera Latão 1\"', 2, 1, 'un', 14, 6, 650.00),
            (5, 'MAT-A01', 'Botija Gás Refrigerante R410A (11.3kg)', 3, 1, 'un', 4, 2, 8500.00),
            (6, 'MAT-A02', 'Filtro de Ar Sintético AVAC G4', 3, 1, 'un', 20, 8, 450.00);
        ");

        // 11. Expense Categories
        $db->exec("
            INSERT OR IGNORE INTO expense_categories (id, name, code, description)
            VALUES 
            (1, 'Mão-de-Obra Técnica Própria', 'MOD', 'Custo horário de técnicos e assistentes internos'),
            (2, 'Materiais e Consumíveis', 'MAT', 'Consumo directo de peças e materiais de stock'),
            (3, 'Contratos e Serviços de Terceiros', 'TERC', 'Serviços prestados por fornecedores externos certificados'),
            (4, 'Combustível e Energia', 'COMB', 'Gasóleo para geradores de emergência'),
            (5, 'Inspecções e Certificações', 'CERT', 'Taxas técnicas e vistorias oficiais');
        ");

        // 12. Inspection Templates
        $db->exec("
            INSERT OR IGNORE INTO inspection_templates (id, title, type, description)
            VALUES 
            (1, 'Checklist Semanal de Áreas Comuns e Segurança', 'routine', 'Inspecção visual de iluminação, portas de emergência, extintores e elevadores'),
            (2, 'Inspecção Técnica Mensal de Grupos de Bombas e Gerador', 'preventive', 'Verificação de níveis de óleo, baterias, pressões do barrilete e estanqueidade');

            INSERT OR IGNORE INTO inspection_template_items (template_id, section_name, item_name, sort_order)
            VALUES 
            (1, 'Iluminação', 'Luzes dos halls e escadas de emergência operacionais', 1),
            (1, 'Iluminação', 'Sinalizadores de saída de emergência iluminados', 2),
            (1, 'Segurança', 'Portas corta-fogo desobstruídas e fechando normalmente', 3),
            (1, 'Segurança', 'Extintores dentro da validade e manómetro no verde', 4),
            (1, 'Elevadores', 'Cabines limpas, nivelamento de piso correcto e alarme testado', 5),
            (2, 'Gerador', 'Nível de óleo do motor e líquido refrigerante adequados', 1),
            (2, 'Gerador', 'Tensão do banco de baterias de arranque (>12.6V / >25.2V)', 2),
            (2, 'Bombas', 'Pressão da rede de água estável entre 3.5 e 4.5 bar', 3),
            (2, 'Bombas', 'Ausência de fugas nas juntas e empanques mecânicos', 4);
        ");

        // 13. Sample Properties, Buildings & Units
        $db->exec("
            INSERT OR IGNORE INTO properties (id, reference, name, type, description, address, province, city, total_area, status, manager_id)
            VALUES 
            (1, 'PRP-2026-001', 'Edifício Polana Prime', 'residential', 'Torre residencial de alto padrão com 10 pisos e estacionamento subterrâneo', 'Av. Julius Nyerere, nº 1420', 'Maputo Cidade', 'Maputo', 4200.0, 'active', 1),
            (2, 'PRP-2026-002', 'Maputo Business Center', 'commercial', 'Centro de negócios com escritórios corporativos e galerias comerciais', 'Av. 24 de Julho, nº 850', 'Maputo Cidade', 'Maputo', 6800.0, 'active', 1),
            (3, 'PRP-2026-003', 'Condomínio Residencial Costa do Sol', 'mixed', 'Condomínio privado à beira-mar com moradias e blocos de apartamentos', 'Av. Marginal, Circular de Maputo', 'Maputo Cidade', 'Maputo', 12500.0, 'active', 1);

            INSERT OR IGNORE INTO buildings (id, property_id, code, name, floors_count)
            VALUES 
            (1, 1, 'BLOCO-A', 'Torre Principal Polana', 10),
            (2, 2, 'BLOCO-1', 'Edifício Comercial MBC', 6),
            (3, 3, 'BLOCO-NORTE', 'Villas do Mar', 2);

            INSERT OR IGNORE INTO floors (id, building_id, floor_number, name)
            VALUES 
            (1, 1, 0, 'Rés-do-Chão & Recepção'),
            (2, 1, 1, '1º Andar'),
            (3, 1, 2, '2º Andar'),
            (4, 1, 3, '3º Andar'),
            (5, 2, 0, 'Piso 0 - Lojas Comerciais'),
            (6, 2, 1, 'Piso 1 - Escritórios');

            INSERT OR IGNORE INTO units (id, property_id, building_id, floor_id, reference, name, type, area, rooms_count, bathrooms_count, status)
            VALUES 
            (1, 1, 1, 4, 'POL-301', 'Apartamento T3 - Fracção 3A', 'apartment', 185.0, 3, 3, 'occupied'),
            (2, 1, 1, 4, 'POL-302', 'Apartamento T3 - Fracção 3B', 'apartment', 185.0, 3, 3, 'available'),
            (3, 1, 1, 3, 'POL-201', 'Apartamento T2 - Fracção 2A', 'apartment', 125.0, 2, 2, 'occupied'),
            (4, 2, 2, 5, 'MBC-LJ01', 'Loja Comercial 01 (Espaço Bancário)', 'store', 220.0, 4, 2, 'occupied'),
            (5, 2, 2, 6, 'MBC-ESC101', 'Escritório Executivo 101', 'office', 340.0, 8, 4, 'occupied');

            INSERT OR IGNORE INTO occupants (id, user_id, name, type, email, phone, id_card_number, nuit, emergency_contact_name, emergency_contact_phone, status)
            VALUES 
            (1, 4, 'Ana Lúcia Cossa', 'tenant', 'ana.cossa@gmail.com', '+258 84 333 4455', '110293847592M', '109823471', 'Dr. Jorge Cossa', '+258 82 444 5566', 'active'),
            (2, NULL, 'Standard Bank Moçambique (Agência)', 'commercial_tenant', 'facilities@standardbank.co.mz', '+258 21 555 666', 'N/A', '400111222', 'Gestor de Agência', '+258 84 999 8888', 'active');

            INSERT OR IGNORE INTO property_occupants (occupant_id, unit_id, start_date, rent_amount, status)
            VALUES 
            (1, 1, '2025-01-01', 85000.00, 'active'),
            (2, 4, '2024-06-01', 250000.00, 'active');
        ");

        // 14. Assets
        $db->exec("
            INSERT OR IGNORE INTO assets (id, code, name, category_id, property_id, building_id, floor_id, location_details, manufacturer, model, serial_number, installation_date, warranty_expiration, supplier_id, status)
            VALUES 
            (1, 'AST-POL-GER01', 'Gerador Principal 250kVA Cummins', 1, 1, 1, 1, 'Sala Técnica do Gerador / Cave', 'Cummins Power', 'C250 D5', 'CUM-2024-99881', '2024-02-15', '2027-02-15', 2, 'operational'),
            (2, 'AST-POL-ELV01', 'Elevador Principal de Passageiros (8P)', 2, 1, 1, 1, 'Caixa do Elevador A / Casa das Máquinas', 'Schindler', '3300 Smart', 'SCH-MZ-2024-01', '2024-03-01', '2026-03-01', 1, 'operational'),
            (3, 'AST-MBC-BOM01', 'Grupo Hidropressor Duplex Grundfos', 3, 2, 2, 5, 'Central de Água Potável / Cave', 'Grundfos', 'Hydro MPC-E 2 CRE', 'GRF-884920', '2024-01-10', '2026-06-10', 2, 'operational'),
            (4, 'AST-MBC-VRF01', 'Sistema Central de Ar Condicionado VRV 30TR', 4, 2, 2, 6, 'Cobertura Técnica do Edifício MBC', 'Daikin', 'VRV IV RXYQ30T', 'DAIK-774411', '2024-04-20', '2027-04-20', 3, 'operational');
        ");

        // 15. Maintenance Plans
        $db->exec("
            INSERT OR IGNORE INTO maintenance_plans (id, reference, title, description, type, category_id, property_id, building_id, asset_id, frequency, frequency_days, start_date, next_execution_date, estimated_duration_hours, estimated_cost, team_id, technician_id, status)
            VALUES 
            (1, 'PLN-2026-001', 'Manutenção Preventiva Mensal do Gerador Cummins', 'Teste sob carga simulada, verificação de baterias, filtros de gasóleo e níveis', 'preventive', 1, 1, 1, 1, 'monthly', 30, '2026-01-01', '2026-11-20', 3.0, 7500.00, 1, 1, 'active'),
            (2, 'PLN-2026-002', 'Inspecção Legal e Lubrificação Mensal de Elevador', 'Lubrificação de guias, teste de travão de segurança e fecho de portas', 'routine', 4, 1, 1, 2, 'monthly', 30, '2026-01-01', '2026-11-15', 2.0, 12000.00, NULL, NULL, 'active'),
            (3, 'PLN-2026-003', 'Limpeza Trimestral de Baterias e Filtros AVAC', 'Limpeza com desincrustante das baterias exteriores e higienização de filtros', 'preventive', 2, 2, 2, 4, 'quarterly', 90, '2026-01-15', '2026-12-05', 4.0, 15000.00, 2, 2, 'active');
        ");

        // 16. Incidents (Real records with references, priority, SLA)
        $db->exec("
            INSERT OR IGNORE INTO incidents (id, reference, title, description, category_id, priority, severity, origin, status, property_id, building_id, floor_id, unit_id, asset_id, occupant_id, reported_by_id, assigned_technician_id, sla_response_deadline, sla_resolution_deadline, created_at)
            VALUES 
            (1, 'INC-2026-0001', 'Fuga de água sob lava-loiça na cozinha', 'Ocupante detectou gotejamento contínuo na válvula de escoamento e flexível sob a bancada da cozinha com risco de dano no armário embutido.', 2, 'high', 'high', 'portal', 'in_progress', 1, 1, 4, 1, NULL, 1, 4, 2, datetime('now', '+1 hour'), datetime('now', '+8 hours'), datetime('now', '-2 hours')),
            (2, 'INC-2026-0002', 'Quadro eléctrico geral do piso 1 disparou', 'Disjuntor diferencial disparou cortando alimentação de iluminação da recepção e corredor do 1º andar.', 1, 'urgent', 'high', 'admin', 'assigned', 1, 1, 2, NULL, NULL, NULL, 1, 1, datetime('now', '+30 minutes'), datetime('now', '+4 hours'), datetime('now', '-1 hour')),
            (3, 'INC-2026-0003', 'Elevador A com ruído anormal no piso 3', 'Utilizadores reportaram vibração e ruído metálico ao passar pelo 3º andar.', 4, 'critical', 'critical', 'portal', 'under_review', 1, 1, 4, NULL, 2, 1, 4, NULL, datetime('now', '+45 minutes'), datetime('now', '+3 hours'), datetime('now', '-30 minutes')),
            (4, 'INC-2026-0004', 'Ar condicionado do Gabinete 101 não arrefece', 'AC split liga e ventila mas o compressor exterior não arranca. Temperatura ambiente elevada.', 3, 'normal', 'medium', 'portal', 'resolved', 2, 2, 6, 5, 4, 2, 2, 2, datetime('now', '-2 days'), datetime('now', '-1 day'), datetime('now', '-3 days'));

            INSERT OR IGNORE INTO incident_status_history (incident_id, user_id, previous_status, new_status, comment)
            VALUES 
            (1, 4, NULL, 'new', 'Incidente reportado via Portal do Ocupante'),
            (1, 1, 'new', 'in_progress', 'Técnico Mateus Nhaca atribuído para intervenção com canalização'),
            (4, 2, 'in_progress', 'resolved', 'Substituído condensador de arranque e recarga de 350g de gás refrigerante');

            INSERT OR IGNORE INTO incident_comments (incident_id, user_id, is_internal, comment)
            VALUES 
            (1, 4, 0, 'Por favor, avisem antes de subir para autorizarmos na portaria.'),
            (1, 1, 1, 'Técnico Mateus já foi destacado com kit de vedantes e chave de tubos.');
        ");

        // 17. Work Orders
        $db->exec("
            INSERT OR IGNORE INTO work_orders (id, reference, title, description, origin, incident_id, property_id, building_id, floor_id, unit_id, priority, technician_id, status, scheduled_date, diagnosis, actions_taken, total_hours, estimated_cost, actual_labor_cost, actual_material_cost, actual_total_cost, created_at)
            VALUES 
            (1, 'OS-2026-0001', 'Reparação de fuga hidráulica em lava-loiça', 'Intervenção urgente para estancar fuga na tubagem flexível da bancada T3', 'incident', 1, 1, 1, 4, 1, 'high', 2, 'in_progress', date('now'), 'Flexível de malha de aço com junta ressequida', 'Substituição do flexível 1/2\" e colocação de teflon', 1.5, 3200.00, 600.00, 650.00, 1250.00, datetime('now', '-1 hour')),
            (2, 'OS-2026-0002', 'Revisão e substituição de condensador AC Gabinete 101', 'Reparação de ar condicionado que não gelava', 'incident', 4, 2, 2, 6, 5, 'normal', 2, 'validated', date('now', '-1 day'), 'Condensador de arranque danificado', 'Substituído condensador 45uF e teste de pressão OK', 2.0, 4500.00, 800.00, 1200.00, 2000.00, datetime('now', '-2 days'));

            INSERT OR IGNORE INTO work_order_materials (work_order_id, material_id, quantity_used, unit_cost, total_cost)
            VALUES 
            (1, 4, 1, 650.00, 650.00);
        ");

        // 18. Expenses
        $db->exec("
            INSERT OR IGNORE INTO expenses (id, reference, description, category_id, property_id, building_id, work_order_id, supplier_id, expense_date, amount, status, registered_by)
            VALUES 
            (1, 'EXP-2026-001', 'Peças e materiais hidráulicos para reparação OS-2026-0001', 2, 1, 1, 1, NULL, date('now'), 650.00, 'paid', 1),
            (2, 'EXP-2026-002', 'Manutenção mensal certificada de elevadores Polana Prime', 3, 1, 1, NULL, 1, date('now', '-5 days'), 12500.00, 'paid', 1),
            (3, 'EXP-2026-003', 'Abastecimento de 300 Litros de Gasóleo para Gerador Cummins', 4, 1, 1, NULL, NULL, date('now', '-10 days'), 27600.00, 'paid', 1),
            (4, 'EXP-2026-004', 'Reparação de AVAC Loja MBC', 3, 2, 2, 2, 3, date('now', '-2 days'), 2000.00, 'paid', 1);

            INSERT OR IGNORE INTO budgets (property_id, category_id, year, month, budgeted_amount)
            VALUES 
            (1, 1, 2026, 11, 25000.00),
            (1, 2, 2026, 11, 15000.00),
            (1, 3, 2026, 11, 30000.00),
            (1, 4, 2026, 11, 35000.00),
            (2, 3, 2026, 11, 40000.00);
        ");

        // 19. Notifications
        $db->exec("
            INSERT OR IGNORE INTO notifications (user_id, title, message, type, link)
            VALUES 
            (1, 'Incidente Crítico Registado', 'O incidente INC-2026-0003 (Elevador A com ruído anormal) requer atenção urgente.', 'danger', '/incidents/3'),
            (1, 'Ordem de Serviço em Execução', 'A OS-2026-0001 foi iniciada pelo técnico Mateus Nhaca.', 'info', '/work-orders/1'),
            (4, 'Técnico a Caminho', 'O seu incidente INC-2026-0001 foi atribuído e está a ser intervencionado.', 'success', '/portal');
        ");

        // 20. Settings
        $db->exec("
            INSERT OR IGNORE INTO settings (key, value, category)
            VALUES 
            ('company_name', 'JFS Properties, Lda.', 'general'),
            ('company_nuit', '400987654', 'general'),
            ('system_currency', 'MZN', 'financial'),
            ('currency_symbol', 'MT', 'financial'),
            ('sla_warning_threshold_minutes', '60', 'sla'),
            ('email_from_address', 'notificacoes@jfsproperties.co.mz', 'email');
        ");

        Logger::info("Database seed completed successfully.");
    }
}
