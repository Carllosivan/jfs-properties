<!-- DASHBOARD HEADER & FILTER -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-slate-900">Visão Operacional & Executiva</h2>
        <p class="text-xs text-slate-500 mt-1">Indicadores em tempo real consolidados para a carteira de imóveis da JFS Properties.</p>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="/dashboard" class="flex items-center space-x-2">
        <div class="relative">
            <select name="property_id" onchange="this.form.submit()" class="text-xs font-medium bg-white border border-slate-300 rounded-xl px-3 py-2 pr-8 text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Todos os Imóveis da Organização</option>
                <?php foreach ($properties as $prop): ?>
                    <option value="<?= $prop['id'] ?>" <?= (string)$selectedProperty === (string)$prop['id'] ? 'selected' : '' ?>>
                        <?= e($prop['name']) ?> (<?= e($prop['reference']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (!empty($selectedProperty)): ?>
            <a href="/dashboard" class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800 bg-white border border-slate-300 rounded-xl shadow-sm" title="Limpar Filtro">
                Limpar
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- 1. STATISTICAL KPI CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <!-- Card 1: Imóveis & Unidades -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Património Imobiliário</span>
            <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                <i data-lucide="building" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="flex items-baseline space-x-2">
            <span class="text-2xl font-bold text-slate-900"><?= $kpi['properties_count'] ?></span>
            <span class="text-xs text-slate-500">imóveis activos</span>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-500">
            <span><?= $kpi['buildings_count'] ?> edifícios</span>
            <span class="font-medium text-slate-700"><?= $kpi['units_count'] ?> fracções</span>
            <span><?= $kpi['occupants_count'] ?> ocupantes</span>
        </div>
    </div>

    <!-- Card 2: Incidentes Pendentes & Críticos -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Incidentes Operacionais</span>
            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="flex items-baseline space-x-2">
            <span class="text-2xl font-bold text-slate-900"><?= $kpi['incidents']['pending'] ?? 0 ?></span>
            <span class="text-xs text-slate-500">pendentes de acção</span>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between text-xs">
            <span class="text-red-600 font-semibold flex items-center">
                <i data-lucide="flame" class="w-3 h-3 mr-1"></i>
                <?= $kpi['incidents']['critical'] ?? 0 ?> Críticos / Urgentes
            </span>
            <span class="text-slate-500"><?= $kpi['incidents']['in_progress'] ?? 0 ?> em resolução</span>
        </div>
    </div>

    <!-- Card 3: Ordens de Serviço & Manutenção -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Intervenções Técnicas</span>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="wrench" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="flex items-baseline space-x-2">
            <span class="text-2xl font-bold text-slate-900"><?= $kpi['work_orders']['open'] ?? 0 ?></span>
            <span class="text-xs text-slate-500">OS abertas</span>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-500">
            <span class="text-emerald-600 font-medium"><?= $kpi['work_orders']['completed'] ?? 0 ?> concluídas</span>
            <span class="<?= ($kpi['maintenance']['overdue'] ?? 0) > 0 ? 'text-red-600 font-bold' : 'text-slate-500' ?>">
                <?= $kpi['maintenance']['overdue'] ?? 0 ?> atrasadas
            </span>
        </div>
    </div>

    <!-- Card 4: Custos Operacionais do Mês (MZN) -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:shadow transition">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Despesas do Mês</span>
            <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <i data-lucide="banknote" class="w-4 h-4"></i>
            </div>
        </div>
        <div class="flex items-baseline space-x-1">
            <span class="text-xl font-bold text-slate-900"><?= format_money($kpi['financial']['month_expenses'] ?? 0) ?></span>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-500">
            <span>Acumulado Total:</span>
            <span class="font-semibold text-slate-800"><?= format_money($kpi['financial']['total_expenses'] ?? 0) ?></span>
        </div>
    </div>
</div>

<!-- 2. CHARTS SECTION -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Chart: Incidentes por Prioridade -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Incidentes por Prioridade</h3>
            <i data-lucide="pie-chart" class="w-4 h-4 text-slate-400"></i>
        </div>
        <div class="h-60 flex items-center justify-center">
            <canvas id="chartPriority"></canvas>
        </div>
    </div>

    <!-- Chart: Incidentes por Categoria -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Incidentes por Categoria</h3>
            <i data-lucide="bar-chart-2" class="w-4 h-4 text-slate-400"></i>
        </div>
        <div class="h-60">
            <canvas id="chartCategory"></canvas>
        </div>
    </div>

    <!-- Chart: Evolução Mensal de Custos (MZN) -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Evolução Mensal de Custos (MT)</h3>
            <i data-lucide="trending-up" class="w-4 h-4 text-slate-400"></i>
        </div>
        <div class="h-60">
            <canvas id="chartCosts"></canvas>
        </div>
    </div>
</div>

<!-- 3. RECENT ACTIVITY TIMELINES -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Incidents -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Incidentes Recentes</h3>
            <a href="/incidents" class="text-xs text-sky-600 font-medium hover:underline">Ver Todos →</a>
        </div>
        <div class="divide-y divide-slate-100 text-xs">
            <?php if (empty($recentIncidents)): ?>
                <div class="p-6 text-center text-slate-400">Nenhum incidente registado até ao momento.</div>
            <?php else: ?>
                <?php foreach ($recentIncidents as $inc): ?>
                    <a href="/incidents/<?= $inc['id'] ?>" class="p-4 hover:bg-slate-50 flex items-start justify-between transition block">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono font-bold text-slate-800"><?= e($inc['reference']) ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase 
                                    <?= match ($inc['priority']) {
                                        'critical' => 'bg-red-100 text-red-700',
                                        'urgent' => 'bg-amber-100 text-amber-700',
                                        'high' => 'bg-orange-100 text-orange-700',
                                        default => 'bg-slate-100 text-slate-700'
                                    } ?>">
                                    <?= e($inc['priority']) ?>
                                </span>
                            </div>
                            <p class="font-semibold text-slate-800 mt-1"><?= e($inc['title']) ?></p>
                            <p class="text-slate-500 text-[11px] mt-0.5"><?= e($inc['property_name']) ?> • <?= e($inc['category_name']) ?></p>
                        </div>
                        <span class="text-[10px] text-slate-400 flex-shrink-0"><?= format_date($inc['created_at']) ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Work Orders -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Ordens de Serviço em Curso</h3>
            <a href="/work-orders" class="text-xs text-sky-600 font-medium hover:underline">Ver Todas →</a>
        </div>
        <div class="divide-y divide-slate-100 text-xs">
            <?php if (empty($recentWorkOrders)): ?>
                <div class="p-6 text-center text-slate-400">Nenhuma ordem de serviço aberta.</div>
            <?php else: ?>
                <?php foreach ($recentWorkOrders as $wo): ?>
                    <a href="/work-orders/<?= $wo['id'] ?>" class="p-4 hover:bg-slate-50 flex items-start justify-between transition block">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono font-bold text-slate-800"><?= e($wo['reference']) ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase 
                                    <?= match ($wo['status']) {
                                        'in_progress' => 'bg-sky-100 text-sky-700',
                                        'validated', 'completed' => 'bg-emerald-100 text-emerald-700',
                                        'assigned' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-slate-100 text-slate-700'
                                    } ?>">
                                    <?= e($wo['status']) ?>
                                </span>
                            </div>
                            <p class="font-semibold text-slate-800 mt-1"><?= e($wo['title']) ?></p>
                            <p class="text-slate-500 text-[11px] mt-0.5">
                                <?= e($wo['property_name']) ?> • Técnico: <?= e($wo['technician_name'] ?? 'Não atribuído') ?>
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="font-semibold text-slate-900 block"><?= format_money($wo['actual_total_cost'] ?: $wo['estimated_cost']) ?></span>
                            <span class="text-[10px] text-slate-400"><?= format_date($wo['created_at'], 'd/m/Y') ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Priority Doughnut
        const priorityLabels = <?= json_encode(array_column($incidentsByPriority, 'priority')) ?>;
        const priorityData = <?= json_encode(array_column($incidentsByPriority, 'count')) ?>;

        const ctxPriority = document.getElementById('chartPriority');
        if (ctxPriority) {
            new Chart(ctxPriority, {
                type: 'doughnut',
                data: {
                    labels: priorityLabels.map(p => p.toUpperCase()),
                    datasets: [{
                        data: priorityData,
                        backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#64748b'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
                    }
                }
            });
        }

        // 2. Category Bar
        const catLabels = <?= json_encode(array_column($incidentsByCategory, 'name')) ?>;
        const catData = <?= json_encode(array_column($incidentsByCategory, 'count')) ?>;
        const catColors = <?= json_encode(array_column($incidentsByCategory, 'color')) ?>;

        const ctxCat = document.getElementById('chartCategory');
        if (ctxCat) {
            new Chart(ctxCat, {
                type: 'bar',
                data: {
                    labels: catLabels,
                    datasets: [{
                        label: 'Incidentes',
                        data: catData,
                        backgroundColor: catColors.length ? catColors : '#0284c7',
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                        x: { ticks: { font: { size: 9 } } }
                    }
                }
            });
        }

        // 3. Monthly Costs Line
        const costMonths = <?= json_encode(array_column($monthlyCosts, 'month')) ?>;
        const costValues = <?= json_encode(array_column($monthlyCosts, 'total')) ?>;

        const ctxCosts = document.getElementById('chartCosts');
        if (ctxCosts) {
            new Chart(ctxCosts, {
                type: 'line',
                data: {
                    labels: costMonths,
                    datasets: [{
                        label: 'Custos (MZN)',
                        data: costValues,
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: val => val + ' MT'
                            }
                        }
                    }
                }
            });
        }
    });
</script>
