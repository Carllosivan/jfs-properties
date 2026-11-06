<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Controlo Financeiro & Custos Operacionais</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gestão de despesas de manutenção, fornecedores terceiros e controlo orçamental por imóvel.</p>
    </div>
    <button onclick="document.getElementById('modal-exp').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
        Registar Despesa
    </button>
</div>

<!-- BUDGET VS ACTUAL SUMMARY -->
<div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm mb-8">
    <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center justify-between">
        <span>Execução Orçamental por Imóvel (<?= $filters['year'] ?>)</span>
        <span class="text-xs text-slate-400 font-normal">Valores expressos em Meticais (MZN)</span>
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($budgetComparison as $b): ?>
            <?php 
            $budget = (float)$b['total_budgeted'];
            $actual = (float)$b['total_actual'];
            $pct = $budget > 0 ? min(round(($actual / $budget) * 100, 1), 100) : 0;
            $isOver = $actual > $budget;
            ?>
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50/70">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-slate-900 text-xs"><?= e($b['property_name']) ?></h4>
                    <span class="text-[11px] font-bold <?= $isOver ? 'text-red-600' : 'text-emerald-700' ?>">
                        <?= $pct ?>% Executado
                    </span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden mb-3">
                    <div class="h-full rounded-full <?= $isOver ? 'bg-red-500' : 'bg-sky-600' ?>" style="width: <?= $pct ?>%"></div>
                </div>
                <div class="flex justify-between text-xs text-slate-600">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Orçado:</span>
                        <span class="font-semibold text-slate-800"><?= format_money($budget) ?></span>
                    </div>
                    <div class="text-right">
                        <span class="text-slate-400 block text-[10px]">Realizado:</span>
                        <span class="font-bold text-slate-900"><?= format_money($actual) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- EXPENSES LIST TABLE -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Lançamentos de Despesas</h3>
            <p class="text-[11px] text-slate-500">Mão-de-obra, materiais, fornecedores e combustíveis</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-slate-400 uppercase font-bold">Total do Período:</span>
            <span class="text-base font-black text-slate-900 ml-2"><?= format_money($totalExpenses) ?></span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Ref. Despesa</th>
                    <th class="px-6 py-3.5">Descrição</th>
                    <th class="px-6 py-3.5">Categoria</th>
                    <th class="px-6 py-3.5">Imóvel</th>
                    <th class="px-6 py-3.5">Fornecedor / OS</th>
                    <th class="px-6 py-3.5">Data</th>
                    <th class="px-6 py-3.5 text-right">Valor (MZN)</th>
                    <th class="px-6 py-3.5 text-center">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($expenses)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">Nenhuma despesa registada para os filtros aplicados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($expenses as $e): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3.5 font-mono font-bold text-slate-900"><?= e($e['reference']) ?></td>
                            <td class="px-6 py-3.5 font-bold text-slate-900 max-w-xs truncate"><?= e($e['description']) ?></td>
                            <td class="px-6 py-3.5"><?= e($e['category_name']) ?></td>
                            <td class="px-6 py-3.5"><?= e($e['property_name']) ?></td>
                            <td class="px-6 py-3.5">
                                <?= e($e['supplier_name'] ?: ($e['work_order_ref'] ? 'OS ' . $e['work_order_ref'] : 'Interno')) ?>
                            </td>
                            <td class="px-6 py-3.5 text-slate-500"><?= format_date($e['expense_date'], 'd/m/Y') ?></td>
                            <td class="px-6 py-3.5 text-right font-black text-slate-900"><?= format_money($e['amount']) ?></td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <?= e($e['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Expense -->
<div id="modal-exp" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-bold text-slate-900">Registo de Despesa Operacional</h3>
            <button onclick="document.getElementById('modal-exp').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form action="/financial/expenses" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição da Despesa *</label>
                <input type="text" name="description" required placeholder="Ex.: Fornecimento de 200L gasóleo gerador" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Categoria de Custo *</label>
                    <select name="category_id" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Imóvel Afectado *</label>
                    <select name="property_id" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white">
                        <?php foreach ($properties as $prop): ?>
                            <option value="<?= $prop['id'] ?>"><?= e($prop['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Valor Total (MZN) *</label>
                    <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Data da Despesa *</label>
                    <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Fornecedor (Opcional)</label>
                <select name="supplier_id" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white">
                    <option value="">Nenhum fornecedor externo</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= e($sup['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-exp').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-xs font-semibold rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 text-white text-xs font-semibold rounded-xl">Gravar Despesa</button>
            </div>
        </form>
    </div>
</div>
