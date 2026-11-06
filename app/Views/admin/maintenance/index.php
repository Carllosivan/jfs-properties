<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Manutenção Preventiva & Planos Periódicos</h2>
        <p class="text-xs text-slate-500 mt-0.5">Agendamento cíclico, geração idempotente de tarefas e acompanhamento de rotinas.</p>
    </div>
    <div class="flex items-center space-x-2">
        <form action="/maintenance/generate-tasks" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="days_ahead" value="30">
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center">
                <i data-lucide="refresh-cw" class="w-4 h-4 mr-1.5"></i>
                Gerar Tarefas (Próximos 30 dias)
            </button>
        </form>
        <a href="/maintenance/create" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
            Novo Plano
        </a>
    </div>
</div>

<!-- STATS CARDS -->
<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-[10px] uppercase font-bold text-slate-400">Planos Activos</span>
        <div class="text-xl font-bold text-slate-900 mt-1"><?= $stats['total_plans'] ?></div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-[10px] uppercase font-bold text-slate-400">Tarefas Agendadas</span>
        <div class="text-xl font-bold text-sky-700 mt-1"><?= $stats['scheduled_tasks'] ?></div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-[10px] uppercase font-bold text-slate-400">Tarefas Atrasadas</span>
        <div class="text-xl font-bold text-red-600 mt-1"><?= $stats['overdue_tasks'] ?></div>
    </div>
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <span class="text-[10px] uppercase font-bold text-slate-400">Tarefas Concluídas</span>
        <div class="text-xl font-bold text-emerald-600 mt-1"><?= $stats['completed_tasks'] ?></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- PLANS LIST (2 cols) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Planos de Manutenção Preventiva</h3>
            <span class="text-xs text-slate-400"><?= count($plans) ?> planos configurados</span>
        </div>
        <div class="divide-y divide-slate-100 text-xs">
            <?php foreach ($plans as $p): ?>
                <div class="p-5 hover:bg-slate-50 transition">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded text-[10px]">
                                    <?= e($p['reference']) ?>
                                </span>
                                <span class="px-2 py-0.5 rounded uppercase font-bold text-[9px] bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    <?= e($p['frequency']) ?>
                                </span>
                                <span class="text-slate-400 text-[11px]">• Categoria: <?= e($p['category_name']) ?></span>
                            </div>
                            <h4 class="font-bold text-slate-900 text-sm mt-1.5"><?= e($p['title']) ?></h4>
                            <p class="text-slate-600 text-xs mt-1 leading-relaxed"><?= e($p['description']) ?></p>
                            <div class="mt-3 flex flex-wrap items-center gap-4 text-[11px] text-slate-500">
                                <span><strong class="text-slate-700">Imóvel:</strong> <?= e($p['property_name']) ?></span>
                                <span><strong class="text-slate-700">Activo:</strong> <?= e($p['asset_name'] ?: 'Predial') ?></span>
                                <span><strong class="text-slate-700">Técnico:</strong> <?= e($p['technician_name'] ?? 'Equipa') ?></span>
                                <span><strong class="text-slate-700">Estimativa:</strong> <?= format_money($p['estimated_cost']) ?></span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-[10px] text-slate-400 uppercase font-bold block">Próxima Execução</span>
                            <span class="font-bold text-sky-700 text-xs mt-0.5 block"><?= format_date($p['next_execution_date'], 'd/m/Y') ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SCHEDULED TASKS FEED (1 col) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Próximas Tarefas Operacionais</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Geradas a partir dos planos cíclicos</p>
        </div>
        <div class="divide-y divide-slate-100 text-xs max-h-[500px] overflow-y-auto">
            <?php if (empty($tasks)): ?>
                <div class="p-6 text-center text-slate-400">Nenhuma tarefa gerada. Clique em "Gerar Tarefas" no topo.</div>
            <?php else: ?>
                <?php foreach ($tasks as $t): ?>
                    <div class="p-4 hover:bg-slate-50 flex items-start justify-between">
                        <div>
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase 
                                <?= $t['status'] === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-sky-50 text-sky-700' ?>">
                                <?= e($t['status']) ?>
                            </span>
                            <h5 class="font-bold text-slate-800 mt-1"><?= e($t['plan_title']) ?></h5>
                            <span class="text-slate-400 text-[11px] block mt-0.5"><?= e($t['property_name']) ?></span>
                        </div>
                        <span class="text-[11px] font-bold <?= $t['status'] === 'overdue' ? 'text-red-600' : 'text-slate-700' ?>">
                            <?= format_date($t['scheduled_date'], 'd/m/Y') ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
