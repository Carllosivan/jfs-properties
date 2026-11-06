<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Inspecções Técnicas & Checklists</h2>
        <p class="text-xs text-slate-500 mt-0.5">Vistorias preventivas, auditorias de segurança, rondas prediais e checklists de conformidade.</p>
    </div>
    <a href="/inspections/create" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
        Nova Inspecção
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Inspections History (2 cols) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Histórico de Inspecções Realizadas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Referência</th>
                        <th class="px-6 py-3.5">Modelo de Inspecção</th>
                        <th class="px-6 py-3.5">Imóvel</th>
                        <th class="px-6 py-3.5">Inspector</th>
                        <th class="px-6 py-3.5">Data</th>
                        <th class="px-6 py-3.5">Resultado</th>
                        <th class="px-6 py-3.5 text-right">Acção</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($inspections)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">Nenhuma inspecção registada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inspections as $insp): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-3.5 font-mono font-bold text-sky-700">
                                    <a href="/inspections/<?= $insp['id'] ?>/execute" class="hover:underline"><?= e($insp['reference']) ?></a>
                                </td>
                                <td class="px-6 py-3.5 font-bold text-slate-900"><?= e($insp['template_title']) ?></td>
                                <td class="px-6 py-3.5"><?= e($insp['property_name']) ?></td>
                                <td class="px-6 py-3.5"><?= e($insp['inspector_name'] ?? 'Administrador') ?></td>
                                <td class="px-6 py-3.5"><?= format_date($insp['inspection_date'], 'd/m/Y') ?></td>
                                <td class="px-6 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase 
                                        <?= match ($insp['status']) {
                                            'completed' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                            'issues_found' => 'bg-red-50 text-red-700 border border-red-200 font-bold',
                                            default => 'bg-amber-50 text-amber-700'
                                        } ?>">
                                        <?= match ($insp['status']) {
                                            'completed' => 'Conforme (OK)',
                                            'issues_found' => 'Não Conforme (Incidente Gerado)',
                                            default => 'Em Curso'
                                        } ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3.5 text-right">
                                    <a href="/inspections/<?= $insp['id'] ?>/execute" class="text-sky-600 hover:text-sky-800 font-semibold">
                                        <?= $insp['status'] === 'in_progress' ? 'Continuar →' : 'Ver Ficha →' ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inspection Templates (1 col) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Modelos de Checklists Activos</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Critérios técnicos padronizados</p>
        </div>
        <div class="divide-y divide-slate-100 text-xs">
            <?php foreach ($templates as $t): ?>
                <div class="p-4 hover:bg-slate-50">
                    <span class="px-2 py-0.5 rounded uppercase font-bold text-[9px] bg-slate-100 text-slate-600">
                        <?= e($t['type']) ?>
                    </span>
                    <h4 class="font-bold text-slate-800 text-xs mt-1"><?= e($t['title']) ?></h4>
                    <p class="text-[11px] text-slate-500 mt-1"><?= e($t['description']) ?></p>
                    <span class="text-[10px] font-semibold text-sky-700 block mt-2">
                        <?= $t['items_count'] ?> itens de verificação técnica
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
