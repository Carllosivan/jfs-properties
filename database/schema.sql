-- ============================================================================
-- JFS PROPERTIES - SISTEMA INTEGRADO DE GESTÃO IMOBILIÁRIA, INCIDENTES E MANUTENÇÃO
-- Base de Dados SQLite Relacional com Chaves Estrangeiras e Índices
-- ============================================================================

PRAGMA foreign_keys = ON;

-- 1. ORGANIZAÇÃO E ADMINISTRAÇÃO
CREATE TABLE IF NOT EXISTS organizations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    nuit TEXT,
    email TEXT,
    phone TEXT,
    address TEXT,
    logo_path TEXT,
    currency TEXT DEFAULT 'MZN',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS permissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    module TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    organization_id INTEGER REFERENCES organizations(id) ON DELETE SET NULL,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    phone TEXT,
    avatar_path TEXT,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'suspended')),
    last_login_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_roles (
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    PRIMARY KEY (user_id, role_id)
);

CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permission_id INTEGER NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action TEXT NOT NULL,
    module TEXT NOT NULL,
    entity TEXT,
    entity_id INTEGER,
    details TEXT,
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    type TEXT DEFAULT 'info' CHECK (type IN ('info', 'success', 'warning', 'danger', 'sla')),
    link TEXT,
    read_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    key TEXT NOT NULL UNIQUE,
    value TEXT,
    category TEXT DEFAULT 'general',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. GESTÃO IMOBILIÁRIA (Imóveis -> Edifícios -> Pisos -> Unidades)
CREATE TABLE IF NOT EXISTS properties (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    type TEXT NOT NULL CHECK (type IN ('residential', 'commercial', 'industrial', 'institutional', 'mixed')),
    description TEXT,
    address TEXT NOT NULL,
    province TEXT,
    district TEXT,
    city TEXT DEFAULT 'Maputo',
    total_area REAL,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'archived', 'maintenance')),
    manager_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    cover_image TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS buildings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    code TEXT NOT NULL,
    name TEXT NOT NULL,
    floors_count INTEGER DEFAULT 1,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS floors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    building_id INTEGER NOT NULL REFERENCES buildings(id) ON DELETE CASCADE,
    floor_number INTEGER NOT NULL,
    name TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS units (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    building_id INTEGER REFERENCES buildings(id) ON DELETE SET NULL,
    floor_id INTEGER REFERENCES floors(id) ON DELETE SET NULL,
    reference TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    type TEXT NOT NULL CHECK (type IN ('apartment', 'office', 'store', 'warehouse', 'common_area', 'parking', 'technical_room')),
    area REAL,
    rooms_count INTEGER,
    bathrooms_count INTEGER,
    status TEXT DEFAULT 'available' CHECK (status IN ('available', 'occupied', 'maintenance', 'reserved')),
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS occupants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    name TEXT NOT NULL,
    type TEXT DEFAULT 'tenant' CHECK (type IN ('tenant', 'owner', 'resident', 'authorized_user', 'commercial_tenant')),
    email TEXT NOT NULL,
    phone TEXT NOT NULL,
    id_card_number TEXT,
    nuit TEXT,
    emergency_contact_name TEXT,
    emergency_contact_phone TEXT,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS property_occupants (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    occupant_id INTEGER NOT NULL REFERENCES occupants(id) ON DELETE CASCADE,
    unit_id INTEGER NOT NULL REFERENCES units(id) ON DELETE CASCADE,
    start_date DATE NOT NULL,
    end_date DATE,
    rent_amount REAL,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'terminated')),
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS property_documents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    property_id INTEGER REFERENCES properties(id) ON DELETE CASCADE,
    unit_id INTEGER REFERENCES units(id) ON DELETE CASCADE,
    category TEXT NOT NULL CHECK (category IN ('contract', 'plan', 'certificate', 'insurance', 'manual', 'photo', 'inspection', 'other')),
    title TEXT NOT NULL,
    file_path TEXT NOT NULL,
    file_size INTEGER,
    file_type TEXT,
    is_private INTEGER DEFAULT 0,
    uploaded_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 3. GESTÃO DE EQUIPAMENTOS E ACTIVOS
CREATE TABLE IF NOT EXISTS asset_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    code TEXT NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE IF NOT EXISTS assets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    category_id INTEGER NOT NULL REFERENCES asset_categories(id),
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    building_id INTEGER REFERENCES buildings(id) ON DELETE SET NULL,
    floor_id INTEGER REFERENCES floors(id) ON DELETE SET NULL,
    unit_id INTEGER REFERENCES units(id) ON DELETE SET NULL,
    location_details TEXT,
    manufacturer TEXT,
    model TEXT,
    serial_number TEXT,
    purchase_date DATE,
    installation_date DATE,
    warranty_expiration DATE,
    supplier_id INTEGER,
    maintenance_frequency_days INTEGER DEFAULT 90,
    status TEXT DEFAULT 'operational' CHECK (status IN ('operational', 'maintenance_needed', 'under_repair', 'inactive', 'decommissioned')),
    notes TEXT,
    photo_path TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 4. TÉCNICOS, EQUIPAS E FORNECEDORES
CREATE TABLE IF NOT EXISTS teams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    code TEXT NOT NULL UNIQUE,
    description TEXT,
    leader_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS technicians (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    email TEXT,
    phone TEXT NOT NULL,
    specialization TEXT NOT NULL, -- 'Electricidade', 'Canalização', 'Climatização', 'Civil', etc.
    team_id INTEGER REFERENCES teams(id) ON DELETE SET NULL,
    status TEXT DEFAULT 'available' CHECK (status IN ('available', 'busy', 'on_leave', 'inactive')),
    hourly_rate REAL DEFAULT 0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS suppliers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    trade_name TEXT,
    nuit TEXT,
    service_type TEXT NOT NULL,
    contact_person TEXT,
    email TEXT,
    phone TEXT NOT NULL,
    address TEXT,
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    rating REAL DEFAULT 5.0,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS supplier_evaluations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    supplier_id INTEGER NOT NULL REFERENCES suppliers(id) ON DELETE CASCADE,
    work_order_id INTEGER,
    evaluator_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    quality_score INTEGER CHECK (quality_score BETWEEN 1 AND 5),
    deadline_score INTEGER CHECK (deadline_score BETWEEN 1 AND 5),
    cost_score INTEGER CHECK (cost_score BETWEEN 1 AND 5),
    communication_score INTEGER CHECK (communication_score BETWEEN 1 AND 5),
    comments TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 5. GESTÃO DE INCIDENTES
CREATE TABLE IF NOT EXISTS incident_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    color TEXT DEFAULT '#0284c7',
    icon TEXT DEFAULT 'alert-circle',
    sla_response_hours INTEGER DEFAULT 4,
    sla_resolution_hours INTEGER DEFAULT 24
);

CREATE TABLE IF NOT EXISTS incident_subcategories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL REFERENCES incident_categories(id) ON DELETE CASCADE,
    name TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS incidents (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    category_id INTEGER NOT NULL REFERENCES incident_categories(id),
    subcategory_id INTEGER REFERENCES incident_subcategories(id) ON DELETE SET NULL,
    priority TEXT DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent', 'critical')),
    severity TEXT DEFAULT 'medium' CHECK (severity IN ('low', 'medium', 'high', 'critical')),
    origin TEXT DEFAULT 'portal' CHECK (origin IN ('portal', 'admin', 'inspection', 'technician', 'system')),
    status TEXT DEFAULT 'new' CHECK (status IN ('new', 'under_review', 'pending_approval', 'approved', 'assigned', 'in_progress', 'waiting_materials', 'waiting_supplier', 'resolved', 'validation_pending', 'closed', 'rejected', 'cancelled')),
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    building_id INTEGER REFERENCES buildings(id) ON DELETE SET NULL,
    floor_id INTEGER REFERENCES floors(id) ON DELETE SET NULL,
    unit_id INTEGER REFERENCES units(id) ON DELETE SET NULL,
    asset_id INTEGER REFERENCES assets(id) ON DELETE SET NULL,
    occupant_id INTEGER REFERENCES occupants(id) ON DELETE SET NULL,
    reported_by_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    assigned_technician_id INTEGER REFERENCES technicians(id) ON DELETE SET NULL,
    assigned_team_id INTEGER REFERENCES teams(id) ON DELETE SET NULL,
    sla_response_deadline DATETIME,
    sla_resolution_deadline DATETIME,
    first_response_at DATETIME,
    resolved_at DATETIME,
    closed_at DATETIME,
    reopen_reason TEXT,
    internal_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS incident_comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    incident_id INTEGER NOT NULL REFERENCES incidents(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    is_internal INTEGER DEFAULT 0,
    comment TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS incident_attachments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    incident_id INTEGER NOT NULL REFERENCES incidents(id) ON DELETE CASCADE,
    file_path TEXT NOT NULL,
    file_name TEXT NOT NULL,
    file_type TEXT,
    file_size INTEGER,
    uploaded_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS incident_status_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    incident_id INTEGER NOT NULL REFERENCES incidents(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    previous_status TEXT,
    new_status TEXT NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 6. GESTÃO DE MANUTENÇÃO (Preventiva e Correctiva)
CREATE TABLE IF NOT EXISTS maintenance_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE IF NOT EXISTS maintenance_plans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    description TEXT,
    type TEXT DEFAULT 'preventive' CHECK (type IN ('preventive', 'predictive', 'routine')),
    category_id INTEGER REFERENCES maintenance_categories(id) ON DELETE SET NULL,
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    building_id INTEGER REFERENCES buildings(id) ON DELETE SET NULL,
    asset_id INTEGER REFERENCES assets(id) ON DELETE SET NULL,
    frequency TEXT DEFAULT 'monthly' CHECK (frequency IN ('daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'semiannual', 'annual', 'custom')),
    frequency_days INTEGER DEFAULT 30,
    start_date DATE NOT NULL,
    next_execution_date DATE NOT NULL,
    estimated_duration_hours REAL DEFAULT 2.0,
    estimated_cost REAL DEFAULT 0,
    team_id INTEGER REFERENCES teams(id) ON DELETE SET NULL,
    technician_id INTEGER REFERENCES technicians(id) ON DELETE SET NULL,
    checklist_template TEXT, -- JSON structure of checklist items
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'paused', 'cancelled')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS maintenance_tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    plan_id INTEGER REFERENCES maintenance_plans(id) ON DELETE CASCADE,
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    asset_id INTEGER REFERENCES assets(id) ON DELETE SET NULL,
    scheduled_date DATE NOT NULL,
    status TEXT DEFAULT 'scheduled' CHECK (status IN ('scheduled', 'in_progress', 'completed', 'overdue', 'cancelled')),
    completed_at DATETIME,
    notes TEXT,
    work_order_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 7. ORDENS DE SERVIÇO (OS)
CREATE TABLE IF NOT EXISTS work_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    title TEXT NOT NULL,
    description TEXT,
    origin TEXT DEFAULT 'incident' CHECK (origin IN ('incident', 'maintenance_plan', 'inspection', 'direct')),
    incident_id INTEGER REFERENCES incidents(id) ON DELETE SET NULL,
    maintenance_plan_id INTEGER REFERENCES maintenance_plans(id) ON DELETE SET NULL,
    inspection_id INTEGER,
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    building_id INTEGER REFERENCES buildings(id) ON DELETE SET NULL,
    floor_id INTEGER REFERENCES floors(id) ON DELETE SET NULL,
    unit_id INTEGER REFERENCES units(id) ON DELETE SET NULL,
    asset_id INTEGER REFERENCES assets(id) ON DELETE SET NULL,
    priority TEXT DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent', 'critical')),
    technician_id INTEGER REFERENCES technicians(id) ON DELETE SET NULL,
    team_id INTEGER REFERENCES teams(id) ON DELETE SET NULL,
    supplier_id INTEGER REFERENCES suppliers(id) ON DELETE SET NULL,
    status TEXT DEFAULT 'new' CHECK (status IN ('new', 'pending_approval', 'approved', 'assigned', 'scheduled', 'in_progress', 'waiting_materials', 'waiting_supplier', 'completed', 'in_validation', 'validated', 'cancelled')),
    scheduled_date DATE,
    scheduled_time TEXT,
    start_time DATETIME,
    completion_time DATETIME,
    diagnosis TEXT,
    actions_taken TEXT,
    technician_notes TEXT,
    total_hours REAL DEFAULT 0,
    estimated_cost REAL DEFAULT 0,
    actual_labor_cost REAL DEFAULT 0,
    actual_material_cost REAL DEFAULT 0,
    actual_total_cost REAL DEFAULT 0,
    currency TEXT DEFAULT 'MZN',
    photo_before_path TEXT,
    photo_after_path TEXT,
    approved_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    approved_at DATETIME,
    validated_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    validated_at DATETIME,
    cancellation_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS work_order_status_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    previous_status TEXT,
    new_status TEXT NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS work_order_activities (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    technician_id INTEGER REFERENCES technicians(id) ON DELETE SET NULL,
    description TEXT NOT NULL,
    hours_spent REAL NOT NULL,
    date_performed DATE NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 8. MATERIAIS, ARMAZÉNS E STOCK
CREATE TABLE IF NOT EXISTS material_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    code TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS warehouses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    code TEXT NOT NULL UNIQUE,
    location TEXT,
    property_id INTEGER REFERENCES properties(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS materials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    category_id INTEGER NOT NULL REFERENCES material_categories(id),
    warehouse_id INTEGER REFERENCES warehouses(id) ON DELETE SET NULL,
    unit_of_measure TEXT NOT NULL, -- 'un', 'm', 'kg', 'litro', 'caixa', etc.
    quantity_available REAL DEFAULT 0,
    min_stock_level REAL DEFAULT 5,
    unit_cost REAL DEFAULT 0, -- em MZN
    currency TEXT DEFAULT 'MZN',
    status TEXT DEFAULT 'active' CHECK (status IN ('active', 'discontinued')),
    supplier_id INTEGER REFERENCES suppliers(id) ON DELETE SET NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS stock_movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    material_id INTEGER NOT NULL REFERENCES materials(id) ON DELETE CASCADE,
    movement_type TEXT NOT NULL CHECK (movement_type IN ('in', 'out', 'return', 'adjustment', 'transfer')),
    quantity REAL NOT NULL,
    unit_price REAL NOT NULL,
    total_price REAL NOT NULL,
    work_order_id INTEGER REFERENCES work_orders(id) ON DELETE SET NULL,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS work_order_materials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    work_order_id INTEGER NOT NULL REFERENCES work_orders(id) ON DELETE CASCADE,
    material_id INTEGER NOT NULL REFERENCES materials(id),
    quantity_used REAL NOT NULL,
    unit_cost REAL NOT NULL,
    total_cost REAL NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 9. INSPECÇÕES E CHECKLISTS
CREATE TABLE IF NOT EXISTS inspection_templates (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    type TEXT NOT NULL CHECK (type IN ('routine', 'preventive', 'safety', 'structural', 'move_in', 'move_out', 'assets')),
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inspection_template_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    template_id INTEGER NOT NULL REFERENCES inspection_templates(id) ON DELETE CASCADE,
    section_name TEXT NOT NULL,
    item_name TEXT NOT NULL,
    is_required INTEGER DEFAULT 1,
    sort_order INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS inspections (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    template_id INTEGER NOT NULL REFERENCES inspection_templates(id),
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    building_id INTEGER REFERENCES buildings(id) ON DELETE SET NULL,
    unit_id INTEGER REFERENCES units(id) ON DELETE SET NULL,
    inspector_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    inspection_date DATE NOT NULL,
    status TEXT DEFAULT 'pending' CHECK (status IN ('pending', 'in_progress', 'completed', 'issues_found')),
    overall_summary TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inspection_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    inspection_id INTEGER NOT NULL REFERENCES inspections(id) ON DELETE CASCADE,
    item_id INTEGER NOT NULL REFERENCES inspection_template_items(id),
    result TEXT NOT NULL CHECK (result IN ('compliant', 'non_compliant', 'needs_attention', 'na')),
    notes TEXT,
    photo_path TEXT,
    generated_incident_id INTEGER REFERENCES incidents(id) ON DELETE SET NULL
);

-- 10. CONTROLO FINANCEIRO OPERACIONAL
CREATE TABLE IF NOT EXISTS expense_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    code TEXT NOT NULL UNIQUE,
    description TEXT
);

CREATE TABLE IF NOT EXISTS expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    reference TEXT NOT NULL UNIQUE,
    description TEXT NOT NULL,
    category_id INTEGER NOT NULL REFERENCES expense_categories(id),
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    building_id INTEGER REFERENCES buildings(id) ON DELETE SET NULL,
    unit_id INTEGER REFERENCES units(id) ON DELETE SET NULL,
    asset_id INTEGER REFERENCES assets(id) ON DELETE SET NULL,
    work_order_id INTEGER REFERENCES work_orders(id) ON DELETE SET NULL,
    supplier_id INTEGER REFERENCES suppliers(id) ON DELETE SET NULL,
    expense_date DATE NOT NULL,
    amount REAL NOT NULL,
    currency TEXT DEFAULT 'MZN',
    tax_amount REAL DEFAULT 0,
    receipt_path TEXT,
    status TEXT DEFAULT 'paid' CHECK (status IN ('pending', 'approved', 'paid', 'cancelled')),
    registered_by INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS budgets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    property_id INTEGER NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    category_id INTEGER REFERENCES expense_categories(id) ON DELETE SET NULL,
    year INTEGER NOT NULL,
    month INTEGER, -- NULL para orçamento anual
    budgeted_amount REAL NOT NULL,
    currency TEXT DEFAULT 'MZN',
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ÍNDICES DE PERFORMANCE
CREATE INDEX IF NOT EXISTS idx_properties_status ON properties(status);
CREATE INDEX IF NOT EXISTS idx_units_property ON units(property_id);
CREATE INDEX IF NOT EXISTS idx_incidents_property ON incidents(property_id);
CREATE INDEX IF NOT EXISTS idx_incidents_status ON incidents(status);
CREATE INDEX IF NOT EXISTS idx_incidents_priority ON incidents(priority);
CREATE INDEX IF NOT EXISTS idx_work_orders_status ON work_orders(status);
CREATE INDEX IF NOT EXISTS idx_work_orders_property ON work_orders(property_id);
CREATE INDEX IF NOT EXISTS idx_assets_property ON assets(property_id);
CREATE INDEX IF NOT EXISTS idx_expenses_property ON expenses(property_id);
CREATE INDEX IF NOT EXISTS idx_stock_movements_mat ON stock_movements(material_id);
