<!-- PROPERTY DETAIL HEADER -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <a href="/properties" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar à Lista de Imóveis
        </a>
        <div class="flex items-center space-x-3">
            <h2 class="text-2xl font-bold text-slate-900"><?= e($property['name']) ?></h2>
            <span class="font-mono text-xs font-bold text-sky-700 bg-sky-100 px-2.5 py-0.5 rounded-md border border-sky-200">
                <?= e($property['reference']) ?>
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">
                <?= e($property['status']) ?>
            </span>
        </div>
        <p class="text-xs text-slate-500 mt-1 flex items-center">
            <i data-lucide="map-pin" class="w-3.5 h-3.5 mr-1 text-slate-400"></i>
            <?= e($property['address']) ?>, <?= e($property['city']) ?> (<?= e($property['province']) ?>)
        </p>
    </div>

    <div class="flex items-center space-x-2">
        <a href="/incidents/create?property_id=<?= $property['id'] ?>" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center">
            <i data-lucide="alert-triangle" class="w-3.5 h-3.5 mr-1.5"></i>
            Registar Incidente
        </a>
        <a href="/work-orders/create?property_id=<?= $property['id'] ?>" class="px-3.5 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center">
            <i data-lucide="wrench" class="w-3.5 h-3.5 mr-1.5"></i>
            Nova Ordem de Serviço
        </a>
    </div>
</div>

<!-- TABS NAVIGATION -->
<div class="border-b border-slate-200 mb-6">
    <nav class="flex space-x-6 text-xs font-medium overflow-x-auto">
        <?php 
        $tabs = [
            'overview' => 'Visão Geral',
            'units' => 'Edifícios & Fracções (' . count($units) . ')',
            'incidents' => 'Incidentes (' . count($incidents) . ')',
            'work_orders' => 'Ordens de Serviço (' . count($workOrders) . ')',
            'assets' => 'Activos / Equipamentos (' . count($assets) . ')',
            'expenses' => 'Custos & Despesas (' . count($expenses) . ')',
            'documents' => 'Documentos (' . count($documents) . ')',
        ];
        foreach ($tabs as $key => $label): 
        ?>
            <a href="?tab=<?= $key ?>" class="pb-3 border-b-2 whitespace-nowrap transition <?= $tab === $key ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </nav>
</div>

<!-- TAB CONTENTS -->
<?php if ($tab === 'overview'): ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-3">Informação Descritiva</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    <?= nl2br(e($property['description'] ?: 'Sem descrição detalhada inserida.')) ?>
                </p>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 pt-6 border-t border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tipo de Imóvel</span>
                        <span class="font-semibold text-slate-800 uppercase"><?= e($property['type']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Área Total</span>
                        <span class="font-semibold text-slate-800"><?= $property['total_area'] ? number_format($property['total_area'], 2) . ' m²' : '-' ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Gestor Responsável</span>
                        <span class="font-semibold text-slate-800"><?= e($property['manager_name'] ?? 'Não atribuído') ?></span>
                    </div>
                </div>
            </div>

            <!-- Edifícios Summary -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Estrutura de Blocos e Edifícios</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php foreach ($buildings as $b): ?>
                        <div class="p-4 rounded-xl border border-slate-100 bg-slate-50">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-slate-800 text-xs"><?= e($b['name']) ?></h4>
                                <span class="font-mono text-[10px] text-slate-400"><?= e($b['code']) ?></span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-1"><?= $b['floors_count'] ?> pisos • <?= $b['units_count'] ?> fracções registadas</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Summary -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-4">Resumo Operacional</h3>
                <div class="space-y-3 text-xs">
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Total de Fracções:</span>
                        <span class="font-bold text-slate-800"><?= count($units) ?></span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Incidentes em Curso:</span>
                        <span class="font-bold text-amber-600">
                            <?= count(array_filter($incidents, fn($i) => !in_array($i['status'], ['resolved', 'closed', 'cancelled']))) ?>
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-slate-500">Ordens de Serviço:</span>
                        <span class="font-bold text-sky-600"><?= count($workOrders) ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-slate-500">Custos Registados:</span>
                        <span class="font-bold text-slate-900">
                            <?= format_money(array_sum(array_column($expenses, 'amount'))) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($tab === 'units'): ?>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">Fracções e Unidades Autonómas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Referência</th>
                        <th class="px-6 py-3">Designação</th>
                        <th class="px-6 py-3">Bloco / Piso</th>
                        <th class="px-6 py-3">Tipologia</th>
                        <th class="px-6 py-3">Área (m²)</th>
                        <th class="px-6 py-3">Ocupante Actual</th>
                        <th class="px-6 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($units as $u): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-mono font-bold text-sky-700"><?= e($u['reference']) ?></td>
                            <td class="px-6 py-3 font-medium text-slate-900"><?= e($u['name']) ?></td>
                            <td class="px-6 py-3"><?= e($u['building_name'] ?? '-') ?> • <?= e($u['floor_name'] ?? '-') ?></td>
                            <td class="px-6 py-3 uppercase text-[11px]"><?= e($u['type']) ?></td>
                            <td class="px-6 py-3"><?= $u['area'] ? $u['area'] . ' m²' : '-' ?></td>
                            <td class="px-6 py-3 font-medium text-slate-800"><?= e($u['occupant_name'] ?? '— Disponível —') ?></td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase 
                                    <?= $u['status'] === 'occupied' ? 'bg-emerald-50 text-emerald-700' : 'bg-sky-50 text-sky-700' ?>">
                                    <?= e($u['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($tab === 'incidents'): ?>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">Histórico de Incidentes no Imóvel</h3>
            <a href="/incidents/create?property_id=<?= $property['id'] ?>" class="text-xs font-semibold text-sky-600 hover:underline">
                + Registar Incidente
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Referência</th>
                        <th class="px-6 py-3">Assunto</th>
                        <th class="px-6 py-3">Categoria</th>
                        <th class="px-6 py-3">Prioridade</th>
                        <th class="px-6 py-3">Técnico</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($incidents as $inc): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-mono font-bold text-slate-900">
                                <a href="/incidents/<?= $inc['id'] ?>" class="text-sky-600 hover:underline"><?= e($inc['reference']) ?></a>
                            </td>
                            <td class="px-6 py-3 font-semibold text-slate-900"><?= e($inc['title']) ?></td>
                            <td class="px-6 py-3"><?= e($inc['category_name']) ?></td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase 
                                    <?= match ($inc['priority']) {
                                        'critical' => 'bg-red-100 text-red-700',
                                        'urgent' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-slate-100 text-slate-700'
                                    } ?>">
                                    <?= e($inc['priority']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3"><?= e($inc['technician_name'] ?? 'Por atribuir') ?></td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-100 text-slate-700">
                                    <?= e($inc['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-400"><?= format_date($inc['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($tab === 'work_orders'): ?>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">Ordens de Serviço</h3>
            <a href="/work-orders/create?property_id=<?= $property['id'] ?>" class="text-xs font-semibold text-sky-600 hover:underline">
                + Nova Ordem de Serviço
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Referência</th>
                        <th class="px-6 py-3">Descrição da Intervenção</th>
                        <th class="px-6 py-3">Técnico</th>
                        <th class="px-6 py-3">Horas</th>
                        <th class="px-6 py-3">Custo Total</th>
                        <th class="px-6 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($workOrders as $wo): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-mono font-bold">
                                <a href="/work-orders/<?= $wo['id'] ?>" class="text-sky-600 hover:underline"><?= e($wo['reference']) ?></a>
                            </td>
                            <td class="px-6 py-3 font-medium text-slate-900"><?= e($wo['title']) ?></td>
                            <td class="px-6 py-3"><?= e($wo['technician_name'] ?? 'Não atribuído') ?></td>
                            <td class="px-6 py-3"><?= $wo['total_hours'] ?>h</td>
                            <td class="px-6 py-3 font-semibold text-slate-800"><?= format_money($wo['actual_total_cost'] ?: $wo['estimated_cost']) ?></td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-sky-50 text-sky-700">
                                    <?= e($wo['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($tab === 'assets'): ?>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-sm font-bold text-slate-800">Equipamentos e Activos Instalados</h3>
            <a href="/assets/create?property_id=<?= $property['id'] ?>" class="text-xs font-semibold text-sky-600 hover:underline">+ Registar Activo</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6">
            <?php foreach ($assets as $ast): ?>
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 flex items-start justify-between">
                    <div>
                        <span class="font-mono text-[10px] font-bold text-sky-700 bg-sky-100 px-2 py-0.5 rounded">
                            <?= e($ast['code']) ?>
                        </span>
                        <h4 class="font-bold text-slate-900 text-xs mt-1.5"><?= e($ast['name']) ?></h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">Cat.: <?= e($ast['category_name']) ?> • Mod.: <?= e($ast['model'] ?? '-') ?></p>
                    </div>
                    <a href="/assets/<?= $ast['id'] ?>" class="text-xs font-semibold text-sky-600 hover:text-sky-800">Ver Ficha →</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

<?php elseif ($tab === 'expenses'): ?>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Registo de Despesas e Custos Operacionais</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Ref.</th>
                        <th class="px-6 py-3">Descrição</th>
                        <th class="px-6 py-3">Categoria</th>
                        <th class="px-6 py-3">Fornecedor</th>
                        <th class="px-6 py-3">Data</th>
                        <th class="px-6 py-3 text-right">Valor (MZN)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($expenses as $exp): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3 font-mono font-bold"><?= e($exp['reference']) ?></td>
                            <td class="px-6 py-3 font-medium text-slate-900"><?= e($exp['description']) ?></td>
                            <td class="px-6 py-3"><?= e($exp['category_name']) ?></td>
                            <td class="px-6 py-3"><?= e($exp['supplier_name'] ?? '-') ?></td>
                            <td class="px-6 py-3 text-slate-500"><?= format_date($exp['expense_date'], 'd/m/Y') ?></td>
                            <td class="px-6 py-3 text-right font-bold text-slate-900"><?= format_money($exp['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($tab === 'documents'): ?>
    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm text-center">
        <i data-lucide="file-text" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
        <h4 class="text-sm font-bold text-slate-700">Arquivo Documental Seguro</h4>
        <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
            Plantas arquitetónicas, apólices de seguro, contratos de manutenção e vistorias oficiais ficam armazenados de forma privada.
        </p>
    </div>
<?php endif; ?>
