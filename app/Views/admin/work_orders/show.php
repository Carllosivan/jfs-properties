<!-- WORK ORDER DETAIL HEADER -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <a href="/work-orders" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar às Ordens de Serviço
        </a>
        <div class="flex items-center space-x-3">
            <span class="font-mono text-sm font-bold text-sky-800 bg-sky-100 px-3 py-1 rounded-lg border border-sky-200">
                <?= e($workOrder['reference']) ?>
            </span>
            <h2 class="text-xl font-bold text-slate-900"><?= e($workOrder['title']) ?></h2>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase 
                <?= match ($workOrder['status']) {
                    'in_progress' => 'bg-sky-100 text-sky-800',
                    'in_validation' => 'bg-amber-100 text-amber-800',
                    'validated', 'completed' => 'bg-emerald-100 text-emerald-800',
                    default => 'bg-slate-100 text-slate-700'
                } ?>">
                <?= e($workOrder['status']) ?>
            </span>
        </div>
        <p class="text-xs text-slate-500 mt-1">
            Agendada para: <?= format_date($workOrder['scheduled_date'], 'd/m/Y') ?> • Imóvel: <strong><?= e($workOrder['property_name']) ?></strong>
        </p>
    </div>

    <div class="flex items-center space-x-2">
        <a href="/work-orders/<?= $workOrder['id'] ?>/report" target="_blank"
            class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center">
            <i data-lucide="printer" class="w-4 h-4 mr-1.5"></i>
            Relatório de Intervenção
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- LEFT 2 COLS: Intervention Execution & Materials -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Intervention Summary -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Instruções da Intervenção</h3>
            <p class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100">
                <?= nl2br(e($workOrder['description'] ?: 'Sem instruções adicionais registadas.')) ?>
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Técnico</span>
                    <span class="font-bold text-slate-800"><?= e($workOrder['technician_name'] ?? 'Não atribuído') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Equipamento / Activo</span>
                    <span class="font-bold text-slate-800"><?= e($workOrder['asset_name'] ?: 'Instalação Predial') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Horas Trabalhadas</span>
                    <span class="font-bold text-sky-700"><?= $workOrder['total_hours'] ?> horas</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Custo Total Realizado</span>
                    <span class="font-bold text-slate-900"><?= format_money($workOrder['actual_total_cost'] ?: $workOrder['estimated_cost']) ?></span>
                </div>
            </div>
        </div>

        <!-- FORM: Record Field Intervention (Diagnosis & Work Performed) -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <i data-lucide="check-square" class="w-4 h-4 mr-2 text-sky-600"></i>
                    Registo de Execução Técnica no Terreno
                </h3>
            </div>

            <form action="/work-orders/<?= $workOrder['id'] ?>/intervention" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Diagnóstico Técnico Efectuado</label>
                    <textarea name="diagnosis" rows="2" placeholder="Identificação da causa raiz da avaria ou estado verificado..."
                        class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"><?= e($workOrder['diagnosis']) ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Trabalhos Realizados / Ações Corretivas</label>
                    <textarea name="actions_taken" rows="3" placeholder="Descrição detalhada dos procedimentos, testes e regulações executados..."
                        class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"><?= e($workOrder['actions_taken']) ?></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Horas de Trabalho Dedicadas</label>
                        <input type="number" step="0.5" name="hours_spent" placeholder="Ex.: 2.5"
                            class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Estado da Ordem após Intervenção</label>
                        <select name="status" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="in_progress" <?= $workOrder['status'] === 'in_progress' ? 'selected' : '' ?>>Em Execução (Intervenção Parcial)</option>
                            <option value="in_validation" <?= $workOrder['status'] === 'in_validation' ? 'selected' : '' ?>>Aguardando Validação do Supervisor</option>
                            <option value="validated" <?= in_array($workOrder['status'], ['validated', 'completed']) ? 'selected' : '' ?>>Validada & Concluída</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Notas Adicionais do Técnico</label>
                    <input type="text" name="technician_notes" value="<?= e($workOrder['technician_notes']) ?>" placeholder="Recomendações futuras, observações do equipamento..."
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                        Gravar Intervenção Técnica
                    </button>
                </div>
            </form>
        </div>

        <!-- MATERIALS CONSUMED & STOCK DEDUCTION -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center">
                        <i data-lucide="package" class="w-4 h-4 mr-2 text-indigo-600"></i>
                        Materiais e Peças Consumidas
                    </h3>
                    <p class="text-[11px] text-slate-500">Aplicação directa na obra com dedução automática no stock do armazém.</p>
                </div>
            </div>

            <!-- Add Material Form -->
            <form action="/work-orders/<?= $workOrder['id'] ?>/material" method="POST" class="bg-slate-50 p-4 rounded-xl border border-slate-200/80 mb-4 flex flex-wrap items-end gap-3">
                <?= csrf_field() ?>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Seleccionar Artigo de Stock</label>
                    <select name="material_id" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Escolha o artigo...</option>
                        <?php foreach ($availableMaterials as $mat): ?>
                            <option value="<?= $mat['id'] ?>">
                                [<?= e($mat['code']) ?>] <?= e($mat['name']) ?> — Disp: <?= $mat['quantity_available'] ?> <?= e($mat['unit_of_measure']) ?> (<?= format_money($mat['unit_cost']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w-24">
                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Qtd.</label>
                    <input type="number" step="0.1" name="quantity" required placeholder="1"
                        class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                    + Deduzir do Stock
                </button>
            </form>

            <!-- Consumed Materials List -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2.5">Código</th>
                            <th class="px-4 py-2.5">Artigo / Peça</th>
                            <th class="px-4 py-2.5">Qtd. Consumida</th>
                            <th class="px-4 py-2.5">Custo Unitário</th>
                            <th class="px-4 py-2.5 text-right">Custo Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php if (empty($materials)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-400">Nenhum material consumido nesta ordem.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($materials as $m): ?>
                                <tr>
                                    <td class="px-4 py-2.5 font-mono font-bold text-slate-800"><?= e($m['material_code']) ?></td>
                                    <td class="px-4 py-2.5 font-medium text-slate-900"><?= e($m['material_name']) ?></td>
                                    <td class="px-4 py-2.5"><?= $m['quantity_used'] ?> <?= e($m['unit_of_measure']) ?></td>
                                    <td class="px-4 py-2.5"><?= format_money($m['unit_cost']) ?></td>
                                    <td class="px-4 py-2.5 text-right font-bold text-slate-900"><?= format_money($m['total_cost']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- RIGHT COL: Financial Breakdown & Status History -->
    <div class="space-y-6">
        <!-- Financial Summary Card -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-4">Composição de Custos</h3>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Mão-de-Obra (<?= $workOrder['total_hours'] ?>h):</span>
                    <span class="font-semibold text-slate-800"><?= format_money($workOrder['actual_labor_cost']) ?></span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-slate-500">Materiais & Peças:</span>
                    <span class="font-semibold text-slate-800"><?= format_money($workOrder['actual_material_cost']) ?></span>
                </div>
                <div class="flex justify-between py-2.5 text-sm font-bold bg-slate-50 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-800">Custo Total:</span>
                    <span class="text-sky-700"><?= format_money($workOrder['actual_total_cost'] ?: $workOrder['estimated_cost']) ?></span>
                </div>
            </div>
        </div>

        <!-- History -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Histórico de Alterações</h3>
            <div class="space-y-3">
                <?php foreach ($history as $h): ?>
                    <div class="border-l-2 border-sky-400 pl-3 py-1">
                        <span class="font-bold text-slate-800"><?= e($h['user_name'] ?? 'Sistema') ?></span>
                        <p class="text-slate-600 text-[11px]"><?= e($h['notes'] ?: $h['new_status']) ?></p>
                        <span class="text-[9px] text-slate-400"><?= format_date($h['created_at']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
