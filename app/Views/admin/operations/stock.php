<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Gestão de Materiais & Armazém</h2>
        <p class="text-xs text-slate-500 mt-0.5">Controlo transacional de stock, níveis de reposição mínima e saídas para ordens de serviço.</p>
    </div>
    <button onclick="document.getElementById('modal-move').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="arrow-left-right" class="w-4 h-4 mr-1.5"></i>
        Registar Entrada / Ajuste
    </button>
</div>

<!-- LOW STOCK WARNING -->
<?php if ($lowStockCount > 0): ?>
    <div class="mb-6 bg-amber-50 border border-amber-200 p-4 rounded-xl flex items-center justify-between text-xs text-amber-900">
        <div class="flex items-center">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mr-3 flex-shrink-0"></i>
            <div>
                <strong>Atenção ao Inventário:</strong> Existem <?= $lowStockCount ?> artigo(s) com quantidade igual ou inferior ao stock mínimo de segurança.
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- MATERIALS TABLE (2 cols) -->
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Catálogo de Artigos em Armazém</h3>
            <span class="text-xs text-slate-400"><?= count($materials) ?> artigos</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3.5">Código</th>
                        <th class="px-6 py-3.5">Designação do Artigo</th>
                        <th class="px-6 py-3.5">Categoria</th>
                        <th class="px-6 py-3.5 text-center">Stock Actual</th>
                        <th class="px-6 py-3.5 text-center">Mínimo</th>
                        <th class="px-6 py-3.5 text-right">Custo Unitário</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($materials as $m): ?>
                        <?php $isLow = $m['quantity_available'] <= $m['min_stock_level']; ?>
                        <tr class="hover:bg-slate-50 <?= $isLow ? 'bg-amber-50/40' : '' ?>">
                            <td class="px-6 py-3.5 font-mono font-bold text-sky-700"><?= e($m['code']) ?></td>
                            <td class="px-6 py-3.5">
                                <span class="font-bold text-slate-900 block"><?= e($m['name']) ?></span>
                                <span class="text-[11px] text-slate-400"><?= e($m['warehouse_name'] ?: 'Armazém Central') ?></span>
                            </td>
                            <td class="px-6 py-3.5"><?= e($m['category_name']) ?></td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="px-2.5 py-0.5 rounded-full font-bold text-xs 
                                    <?= $isLow ? 'bg-red-100 text-red-700' : 'bg-emerald-50 text-emerald-700' ?>">
                                    <?= $m['quantity_available'] ?> <?= e($m['unit_of_measure']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-center text-slate-500">
                                <?= $m['min_stock_level'] ?> <?= e($m['unit_of_measure']) ?>
                            </td>
                            <td class="px-6 py-3.5 text-right font-semibold text-slate-900">
                                <?= format_money($m['unit_cost']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MOVEMENTS HISTORY (1 col) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-800">Últimos Movimentos de Stock</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Rastreabilidade completa de entradas e saídas</p>
        </div>
        <div class="divide-y divide-slate-100 text-xs max-h-[500px] overflow-y-auto">
            <?php foreach ($movements as $mv): ?>
                <div class="p-4 hover:bg-slate-50">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase 
                            <?= $mv['movement_type'] === 'in' ? 'bg-emerald-100 text-emerald-800' : ($mv['movement_type'] === 'out' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800') ?>">
                            <?= match ($mv['movement_type']) {
                                'in' => 'Entrada / Compra',
                                'out' => 'Saída para OS',
                                'return' => 'Devolução',
                                'adjustment' => 'Ajuste Físico',
                                default => $mv['movement_type']
                            } ?>
                        </span>
                        <span class="font-bold text-slate-900">
                            <?= $mv['movement_type'] === 'out' ? '-' : '+' ?><?= $mv['quantity'] ?> <?= e($mv['unit_of_measure']) ?>
                        </span>
                    </div>
                    <h5 class="font-bold text-slate-800 mt-1"><?= e($mv['material_name']) ?></h5>
                    <p class="text-slate-500 text-[11px] mt-0.5"><?= e($mv['reason'] ?: 'Movimento operacional') ?></p>
                    <div class="mt-1 flex justify-between text-[10px] text-slate-400">
                        <span>Por: <?= e($mv['user_name'] ?? 'Técnico') ?></span>
                        <span><?= format_date($mv['created_at']) ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal: New Movement -->
<div id="modal-move" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-bold text-slate-900">Registo de Movimento de Stock</h3>
            <button onclick="document.getElementById('modal-move').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form action="/operations/stock/movement" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Artigo / Material *</label>
                <select name="material_id" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white">
                    <option value="">Selecione o artigo...</option>
                    <?php foreach ($materials as $m): ?>
                        <option value="<?= $m['id'] ?>">
                            [<?= e($m['code']) ?>] <?= e($m['name']) ?> (Actual: <?= $m['quantity_available'] ?> <?= e($m['unit_of_measure']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Movimento *</label>
                    <select name="movement_type" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white">
                        <option value="in">Entrada (Compra / Recepção)</option>
                        <option value="out">Saída (Consumo Interno)</option>
                        <option value="return">Devolução ao Armazém</option>
                        <option value="adjustment">Ajuste de Inventário Físico</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Quantidade *</label>
                    <input type="number" step="0.1" name="quantity" required placeholder="Ex.: 10" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Motivo / Justificação *</label>
                <input type="text" name="reason" required placeholder="Ex.: Factura Fornecedor #4892 ou Rectificação de contagem" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-move').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-xs font-semibold rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-xl">Confirmar Movimento</button>
            </div>
        </form>
    </div>
</div>
