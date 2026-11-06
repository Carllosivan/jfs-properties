<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Relatórios & Exportações</h2>
        <p class="text-xs text-slate-500 mt-0.5">Extracção de dados analíticos, cumprimento de SLAs, intervenções e custos operacionais.</p>
    </div>

    <!-- Export Button -->
    <a href="/reports?<?= http_build_query(array_merge($filters, ['export' => 'csv'])) ?>" 
       class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="download" class="w-4 h-4 mr-1.5"></i>
        Exportar para CSV (Excel)
    </a>
</div>

<!-- FILTERS CARD -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-6">
    <form method="GET" action="/reports" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Módulo / Tipo de Relatório</label>
            <select name="report_type" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg bg-white">
                <option value="incidents" <?= $type === 'incidents' ? 'selected' : '' ?>>Incidentes e Avarias</option>
                <option value="work_orders" <?= $type === 'work_orders' ? 'selected' : '' ?>>Ordens de Serviço e Manutenção</option>
                <option value="expenses" <?= $type === 'expenses' ? 'selected' : '' ?>>Custos e Despesas Operacionais</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Imóvel</label>
            <select name="property_id" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg bg-white">
                <option value="">Todos os Imóveis</option>
                <?php foreach ($properties as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (string)($filters['property_id'] ?? '') === (string)$p['id'] ? 'selected' : '' ?>>
                        <?= e($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Data Inicial</label>
            <input type="date" name="start_date" value="<?= e($filters['start_date']) ?>" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg">
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Data Final</label>
            <div class="flex space-x-2">
                <input type="date" name="end_date" value="<?= e($filters['end_date']) ?>" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-lg transition">
                    Gerar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- RESULTS TABLE -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Resultados Analíticos (<?= count($data) ?> registos)</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th class="px-6 py-3.5 whitespace-nowrap"><?= e($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="<?= count($columns) ?>" class="px-6 py-12 text-center text-slate-400">
                            Nenhum registo encontrado para os parâmetros seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr class="hover:bg-slate-50">
                            <?php foreach ($row as $k => $val): ?>
                                <td class="px-6 py-3.5 whitespace-nowrap">
                                    <?php if (is_numeric($val) && str_contains($k, 'cost') || str_contains($k, 'amount')): ?>
                                        <span class="font-bold text-slate-900"><?= format_money((float)$val) ?></span>
                                    <?php else: ?>
                                        <?= e((string)$val) ?>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
