<?php

return [
    'roles' => [
        'super_admin' => 'Super Administrador',
        'admin' => 'Administrador',
        'property_manager' => 'Gestor Imobiliário',
        'facilities_manager' => 'Gestor de Facilities',
        'maintenance_supervisor' => 'Supervisor de Manutenção',
        'technician' => 'Técnico',
        'inspector' => 'Inspector',
        'financial_manager' => 'Gestor Financeiro',
        'supplier' => 'Fornecedor',
        'occupant' => 'Ocupante',
    ],
    'modules' => [
        'properties' => ['view', 'create', 'edit', 'archive', 'delete'],
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
    ]
];
