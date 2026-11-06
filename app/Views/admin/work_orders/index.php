<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Ordens de Serviço</h2>
        <p class="text-xs text-slate-500 mt-0.5">Execução no terreno, mão-de-obra, aplicação de materiais e custos de intervenção.</p>
    </div>
    <a href="/work-orders/create" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
        Nova Ordem de Serviço
    </a>
</div>

<!-- TABS -->
<div class="border-b border-slate-200 mb-6">
    <nav class="flex space-x-6 text-xs font-medium overflow-x-auto">
        <a href="/work-orders" class="pb-3 border-b-2 whitespace-nowrap transition <?= empty($currentStatus) ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Todas as Ordens (<?= $counts['all'] ?>)
        </a>
        <a href="/work-orders?status=assigned" class="pb-3 border-b-2 whitespace-nowrap transition <?= $currentStatus === 'assigned' ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Atribuídas (<?= $counts['assigned'] ?>)
        </a>
        <a href="/work-orders?status=in_progress" class="pb-3 border-b-2 whitespace-nowrap transition <?= $currentStatus === 'in_progress' ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Em Execução (<?= $counts['in_progress'] ?>)
        </a>
        <a href="/work-orders?status=in_validation" class="pb-3 border-b-2 whitespace-nowrap transition <?= $currentStatus === 'in_validation' ? 'border-amber-600 text-amber-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Aguardando Validação (<?= $counts['in_validation'] ?>)
        </a>
        <a href="/work-orders?status=completed" class="pb-3 border-b-2 whitespace-nowrap transition <?= $currentStatus === 'completed' ? 'border-emerald-600 text-emerald-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Concluídas / Validadas (<?= $counts['completed'] ?>)
        </a>
    </nav>
</div>

<!-- Filters Bar -->
<div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-wrap items-center gap-3">
    <form method="GET" action="/work-orders" class="flex flex-wrap items-center gap-3 w-full">
        <?php if (!empty($currentStatus)): ?>
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
        <?php endif; ?>
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Pesquisar por referência, título ou imóvel..."
                class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div>
            <select name="property_id" class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Todos os Imóveis</option>
                <?php foreach ($properties as $prop): ?>
                    <option value="<?= $prop['id'] ?>" <?= (string)($filters['property_id'] ?? '') === (string)$prop['id'] ? 'selected' : '' ?>>
                        <?= e($prop['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
            Filtrar
        </button>
    </form>
</div>

<!-- Work Orders Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Referência</th>
                    <th class="px-6 py-3.5">Intervenção</th>
                    <th class="px-6 py-3.5">Origem</th>
                    <th class="px-6 py-3.5">Imóvel / Activo</th>
                    <th class="px-6 py-3.5">Técnico</th>
                    <th class="px-6 py-3.5">Horas</th>
                    <th class="px-6 py-3.5">Custo Total</th>
                    <th class="px-6 py-3.5">Estado</th>
                    <th class="px-6 py-3.5 text-right">Acções</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($workOrders)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400">Nenhuma ordem de serviço registada.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($workOrders as $wo): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3.5 font-mono font-bold">
                                <a href="/work-orders/<?= $wo['id'] ?>" class="text-sky-600 hover:underline"><?= e($wo['reference']) ?></a>
                            </td>
                            <td class="px-6 py-3.5 max-w-xs">
                                <a href="/work-orders/<?= $wo['id'] ?>" class="font-bold text-slate-900 hover:text-sky-600 transition block truncate">
                                    <?= e($wo['title']) ?>
                                </a>
                                <?php if ($wo['incident_ref']): ?>
                                    <span class="text-[10px] text-slate-400">Incidente: <?= e($wo['incident_ref']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3.5 uppercase text-[10px] font-semibold text-slate-500">
                                <?= e($wo['origin']) ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="font-medium text-slate-800 block"><?= e($wo['property_name']) ?></span>
                                <span class="text-[11px] text-slate-400"><?= e($wo['asset_name'] ?: 'Geral') ?></span>
                            </td>
                            <td class="px-6 py-3.5">
                                <?= e($wo['technician_name'] ?? '— Não atribuído —') ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <?= $wo['total_hours'] ?>h
                            </td>
                            <td class="px-6 py-3.5 font-semibold text-slate-900">
                                <?= format_money($wo['actual_total_cost'] ?: $wo['estimated_cost']) ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase 
                                    <?= match ($wo['status']) {
                                        'in_progress' => 'bg-sky-50 text-sky-700 border border-sky-200',
                                        'validated', 'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'in_validation' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                        default => 'bg-slate-100 text-slate-700'
                                    } ?>">
                                    <?= e($wo['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right space-x-2">
                                <a href="/work-orders/<?= $wo['id'] ?>" class="text-sky-600 hover:text-sky-800 font-semibold">Executar</a>
                                <a href="/work-orders/<?= $wo['id'] ?>/report" target="_blank" class="text-slate-400 hover:text-slate-700" title="Emitir Relatório Oficial">
                                    <i data-lucide="printer" class="w-3.5 h-3.5 inline"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
