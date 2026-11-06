<!-- OFFICIAL SERVICE REPORT HEADER -->
<div class="border-b-2 border-slate-900 pb-6 mb-8 flex justify-between items-start">
    <div>
        <div class="flex items-center space-x-3 mb-2">
            <div class="w-10 h-10 rounded-lg bg-sky-600 text-white flex items-center justify-center font-black text-xl">
                J
            </div>
            <div>
                <h1 class="text-xl font-black tracking-wider text-slate-900">JFS PROPERTIES, LDA.</h1>
                <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Facility Management & Property Services</p>
            </div>
        </div>
        <p class="text-xs text-slate-600 mt-2">
            Av. Julius Nyerere, Edifício Jat V, Maputo • NUIT: 400987654<br>
            Contacto Operacional: +258 84 123 4567 • suporte@jfsproperties.co.mz
        </p>
    </div>

    <div class="text-right">
        <span class="inline-block px-3 py-1 bg-slate-900 text-white text-xs font-bold uppercase tracking-wider rounded mb-2">
            Folha de Intervenção Técnica
        </span>
        <div class="text-lg font-black font-mono text-slate-900"><?= e($wo['reference']) ?></div>
        <div class="text-xs text-slate-500">Emitido em: <?= date('d/m/Y H:i') ?></div>
    </div>
</div>

<!-- INFO GRID -->
<div class="grid grid-cols-2 gap-6 text-xs mb-8 p-4 bg-slate-50 rounded-xl border border-slate-200">
    <div class="space-y-2">
        <div>
            <span class="text-slate-400 block text-[10px] uppercase font-bold">Imóvel & Localização</span>
            <span class="font-bold text-slate-900 text-sm"><?= e($wo['property_name']) ?> (<?= e($wo['prop_ref']) ?>)</span>
            <span class="text-slate-600 block"><?= e($wo['property_address']) ?></span>
            <span class="text-slate-700 block font-medium mt-0.5"><?= e($wo['unit_ref'] ?: 'Áreas Comuns / Técnicas') ?></span>
        </div>
        <div>
            <span class="text-slate-400 block text-[10px] uppercase font-bold">Incidente de Origem</span>
            <span class="font-mono font-semibold text-slate-800"><?= e($wo['incident_ref'] ?: 'Intervenção Directa / Plano') ?></span>
        </div>
    </div>

    <div class="space-y-2">
        <div>
            <span class="text-slate-400 block text-[10px] uppercase font-bold">Técnico Responsável</span>
            <span class="font-bold text-slate-900 text-sm"><?= e($wo['technician_name'] ?? 'Equipa Técnica JFS') ?></span>
            <span class="text-slate-600 block">Especialidade: <?= e($wo['specialization'] ?? 'Manutenção Geral') ?></span>
        </div>
        <div>
            <span class="text-slate-400 block text-[10px] uppercase font-bold">Equipamento / Activo</span>
            <span class="font-semibold text-slate-800"><?= e($wo['asset_name'] ?: 'Instalação Predial') ?></span>
        </div>
        <div>
            <span class="text-slate-400 block text-[10px] uppercase font-bold">Data de Execução</span>
            <span class="font-medium text-slate-800"><?= format_date($wo['scheduled_date'], 'd/m/Y') ?></span>
        </div>
    </div>
</div>

<!-- DIAGNOSIS & ACTIONS -->
<div class="space-y-6 text-xs mb-8">
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-300 pb-1 mb-2">
            1. Diagnóstico Técnico Inicial
        </h3>
        <p class="p-3 bg-white border border-slate-200 rounded-lg text-slate-800 leading-relaxed min-h-[48px]">
            <?= nl2br(e($wo['diagnosis'] ?: 'Diagnóstico em conformidade com as anomalias reportadas.')) ?>
        </p>
    </div>

    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-300 pb-1 mb-2">
            2. Trabalhos Realizados & Reparações
        </h3>
        <p class="p-3 bg-white border border-slate-200 rounded-lg text-slate-800 leading-relaxed min-h-[60px]">
            <?= nl2br(e($wo['actions_taken'] ?: $wo['title'])) ?>
        </p>
    </div>
</div>

<!-- MATERIALS CONSUMED TABLE -->
<div class="text-xs mb-8">
    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-300 pb-1 mb-3">
        3. Peças e Materiais Aplicados
    </h3>
    <table class="w-full text-left border border-slate-200">
        <thead class="bg-slate-100 text-[10px] font-bold uppercase text-slate-700">
            <tr>
                <th class="p-2 border-b">Código</th>
                <th class="p-2 border-b">Designação do Material</th>
                <th class="p-2 border-b text-center">Quantidade</th>
                <th class="p-2 border-b text-right">P. Unitário (MZN)</th>
                <th class="p-2 border-b text-right">Total (MZN)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
            <?php if (empty($materials)): ?>
                <tr>
                    <td colspan="5" class="p-3 text-center text-slate-400">Nenhum material de stock facturado nesta ordem.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($materials as $m): ?>
                    <tr>
                        <td class="p-2 font-mono font-bold"><?= e($m['material_code']) ?></td>
                        <td class="p-2"><?= e($m['material_name']) ?></td>
                        <td class="p-2 text-center"><?= $m['quantity_used'] ?> <?= e($m['unit_of_measure']) ?></td>
                        <td class="p-2 text-right"><?= format_money($m['unit_cost']) ?></td>
                        <td class="p-2 text-right font-bold"><?= format_money($m['total_cost']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- COST TOTALS -->
<div class="flex justify-end mb-12">
    <div class="w-72 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs space-y-2">
        <div class="flex justify-between">
            <span class="text-slate-600">Mão-de-Obra (<?= $wo['total_hours'] ?>h):</span>
            <span class="font-semibold text-slate-900"><?= format_money($wo['actual_labor_cost']) ?></span>
        </div>
        <div class="flex justify-between">
            <span class="text-slate-600">Total Materiais:</span>
            <span class="font-semibold text-slate-900"><?= format_money($wo['actual_material_cost']) ?></span>
        </div>
        <div class="flex justify-between pt-2 border-t border-slate-300 font-bold text-sm text-slate-900">
            <span>CUSTO TOTAL:</span>
            <span><?= format_money($wo['actual_total_cost'] ?: $wo['estimated_cost']) ?></span>
        </div>
    </div>
</div>

<!-- SIGNATURE BLOCKS -->
<div class="grid grid-cols-3 gap-8 pt-8 border-t-2 border-slate-200 text-xs text-center">
    <div>
        <div class="border-b border-slate-400 h-12 mb-2"></div>
        <span class="font-bold text-slate-800 block"><?= e($wo['technician_name'] ?? 'Técnico Responsável') ?></span>
        <span class="text-[10px] text-slate-500 uppercase">Técnico Executor</span>
    </div>
    <div>
        <div class="border-b border-slate-400 h-12 mb-2"></div>
        <span class="font-bold text-slate-800 block">Supervisor de Facilities</span>
        <span class="text-[10px] text-slate-500 uppercase">Validação Técnica</span>
    </div>
    <div>
        <div class="border-b border-slate-400 h-12 mb-2"></div>
        <span class="font-bold text-slate-800 block">Ocupante / Gestor do Imóvel</span>
        <span class="text-[10px] text-slate-500 uppercase">Conformidade e Recepção</span>
    </div>
</div>
