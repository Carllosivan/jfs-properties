<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Gestão de Incidentes</h2>
        <p class="text-xs text-slate-500 mt-0.5">Triagem, ciclo de vida, acompanhamento de SLAs e intervenções.</p>
    </div>
    <a href="/incidents/create" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
        Registar Incidente
    </a>
</div>

<!-- STATUS TABS -->
<div class="border-b border-slate-200 mb-6">
    <nav class="flex space-x-6 text-xs font-medium overflow-x-auto">
        <a href="/incidents" class="pb-3 border-b-2 whitespace-nowrap transition <?= empty($currentStatus) ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Todos os Incidentes (<?= $counts['all'] ?>)
        </a>
        <a href="/incidents?status=new" class="pb-3 border-b-2 whitespace-nowrap transition <?= $currentStatus === 'new' ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Novos (<?= $counts['new'] ?>)
        </a>
        <a href="/incidents?status=critical" class="pb-3 border-b-2 whitespace-nowrap transition <?= $currentStatus === 'critical' ? 'border-red-600 text-red-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            <span class="inline-flex items-center">
                <i data-lucide="flame" class="w-3.5 h-3.5 mr-1 text-red-500"></i>
                Críticos / Urgentes (<?= $counts['critical'] ?>)
            </span>
        </a>
        <a href="/incidents?status=in_progress" class="pb-3 border-b-2 whitespace-nowrap transition <?= $currentStatus === 'in_progress' ? 'border-sky-600 text-sky-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Em Resolução (<?= $counts['in_progress'] ?>)
        </a>
        <a href="/incidents?status=resolved" class="pb-3 border-b-2 whitespace-nowrap transition <?= $currentStatus === 'resolved' ? 'border-emerald-600 text-emerald-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Resolvidos (<?= $counts['resolved'] ?>)
        </a>
        <a href="/incidents?status=closed" class="pb-3 border-b-2 whitespace-nowrap transition <?= $currentStatus === 'closed' ? 'border-slate-600 text-slate-600 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            Encerrados (<?= $counts['closed'] ?>)
        </a>
    </nav>
</div>

<!-- Filters Bar -->
<div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-wrap items-center gap-3">
    <form method="GET" action="/incidents" class="flex flex-wrap items-center gap-3 w-full">
        <?php if (!empty($currentStatus)): ?>
            <input type="hidden" name="status" value="<?= e($currentStatus) ?>">
        <?php endif; ?>
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Pesquisar por título, código ou imóvel..."
                class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div>
            <select name="priority" class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Todas as Prioridades</option>
                <option value="critical" <?= ($filters['priority'] ?? '') === 'critical' ? 'selected' : '' ?>>Crítica</option>
                <option value="urgent" <?= ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                <option value="high" <?= ($filters['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Alta</option>
                <option value="normal" <?= ($filters['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normal</option>
                <option value="low" <?= ($filters['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Baixa</option>
            </select>
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
        <?php if (!empty($filters['q']) || !empty($filters['priority']) || !empty($filters['property_id'])): ?>
            <a href="/incidents" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">Limpar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Incidents Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Referência</th>
                    <th class="px-6 py-3.5">Título & Descrição</th>
                    <th class="px-6 py-3.5">Categoria</th>
                    <th class="px-6 py-3.5">Imóvel / Fracção</th>
                    <th class="px-6 py-3.5">Prioridade</th>
                    <th class="px-6 py-3.5">Técnico / Equipa</th>
                    <th class="px-6 py-3.5">SLA Resolução</th>
                    <th class="px-6 py-3.5">Estado</th>
                    <th class="px-6 py-3.5 text-right">Acção</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($incidents)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400">Nenhum incidente encontrado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($incidents as $inc): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3.5 font-mono font-bold text-slate-900">
                                <a href="/incidents/<?= $inc['id'] ?>" class="text-sky-600 hover:underline"><?= e($inc['reference']) ?></a>
                            </td>
                            <td class="px-6 py-3.5 max-w-xs">
                                <a href="/incidents/<?= $inc['id'] ?>" class="font-bold text-slate-900 hover:text-sky-600 transition block truncate">
                                    <?= e($inc['title']) ?>
                                </a>
                                <span class="text-slate-400 text-[11px] block truncate"><?= e($inc['description']) ?></span>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center text-xs font-medium text-slate-700">
                                    <span class="w-2 h-2 rounded-full mr-1.5" style="background-color: <?= e($inc['category_color'] ?? '#0284c7') ?>"></span>
                                    <?= e($inc['category_name']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="font-medium text-slate-800 block"><?= e($inc['property_name']) ?></span>
                                <span class="text-slate-400 text-[11px]"><?= e($inc['unit_ref'] ?: 'Área Geral / Comum') ?></span>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase 
                                    <?= match ($inc['priority']) {
                                        'critical' => 'bg-red-100 text-red-700 border border-red-200',
                                        'urgent' => 'bg-amber-100 text-amber-700 border border-amber-200',
                                        'high' => 'bg-orange-100 text-orange-700 border border-orange-200',
                                        'low' => 'bg-slate-100 text-slate-600',
                                        default => 'bg-sky-100 text-sky-700'
                                    } ?>">
                                    <?= e($inc['priority']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <?= e($inc['technician_name'] ?? '— Não atribuído —') ?>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <?php if ($inc['sla_resolution_deadline']): ?>
                                    <span class="text-[11px] font-medium <?= strtotime($inc['sla_resolution_deadline']) < time() && !in_array($inc['status'], ['resolved', 'closed']) ? 'text-red-600 font-bold' : 'text-slate-600' ?>">
                                        <?= format_date($inc['sla_resolution_deadline']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase 
                                    <?= match ($inc['status']) {
                                        'new' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                        'assigned' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                        'in_progress' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                        'resolved' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                        'closed' => 'bg-slate-100 text-slate-700',
                                        default => 'bg-slate-100 text-slate-700'
                                    } ?>">
                                    <?= e($inc['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <a href="/incidents/<?= $inc['id'] ?>" class="text-sky-600 hover:text-sky-800 font-semibold">
                                    Ver Detalhes →
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
