<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <a href="/assets" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar aos Equipamentos
        </a>
        <div class="flex items-center space-x-3">
            <span class="font-mono text-sm font-bold text-sky-800 bg-sky-100 px-3 py-1 rounded-lg border border-sky-200">
                <?= e($asset['code']) ?>
            </span>
            <h2 class="text-xl font-bold text-slate-900"><?= e($asset['name']) ?></h2>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase 
                <?= $asset['status'] === 'operational' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>">
                <?= e($asset['status']) ?>
            </span>
        </div>
        <p class="text-xs text-slate-500 mt-1">
            <?= e($asset['property_name']) ?> • <?= e($asset['location_details'] ?: 'Geral') ?>
        </p>
    </div>

    <div class="flex items-center space-x-2">
        <a href="/work-orders/create?property_id=<?= $asset['property_id'] ?>&asset_id=<?= $asset['id'] ?>"
            class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center">
            <i data-lucide="wrench" class="w-4 h-4 mr-1.5"></i>
            Abrir Ordem de Serviço
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- LEFT 2 COLS: Technical Specs & History -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Specs -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Ficha Técnica do Fabricante</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Fabricante</span>
                    <span class="font-bold text-slate-900"><?= e($asset['manufacturer'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Modelo</span>
                    <span class="font-bold text-slate-900"><?= e($asset['model'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Nº de Série</span>
                    <span class="font-mono font-bold text-slate-900"><?= e($asset['serial_number'] ?: '-') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Data de Instalação</span>
                    <span class="font-medium text-slate-800"><?= format_date($asset['installation_date'], 'd/m/Y') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Validade da Garantia</span>
                    <span class="font-medium text-slate-800"><?= format_date($asset['warranty_expiration'], 'd/m/Y') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Fornecedor / Assistência</span>
                    <span class="font-medium text-slate-800"><?= e($asset['supplier_name'] ?: 'Assistência Interna') ?></span>
                </div>
            </div>
        </div>

        <!-- Work Orders on this asset -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center">
                <i data-lucide="wrench" class="w-4 h-4 mr-2 text-sky-600"></i>
                Histórico de Intervenções e Ordens de Serviço
            </h3>
            <div class="divide-y divide-slate-100 text-xs">
                <?php if (empty($workOrders)): ?>
                    <p class="py-4 text-center text-slate-400">Nenhuma intervenção registada neste equipamento.</p>
                <?php else: ?>
                    <?php foreach ($workOrders as $wo): ?>
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <a href="/work-orders/<?= $wo['id'] ?>" class="font-mono font-bold text-sky-700 hover:underline">
                                    <?= e($wo['reference']) ?>
                                </a>
                                <p class="font-semibold text-slate-800 mt-0.5"><?= e($wo['title']) ?></p>
                                <span class="text-slate-400 text-[11px]">Técnico: <?= e($wo['technician_name'] ?? 'Não atribuído') ?></span>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-100 text-slate-700">
                                    <?= e($wo['status']) ?>
                                </span>
                                <span class="text-[11px] font-bold text-slate-800 block mt-1"><?= format_money($wo['actual_total_cost'] ?: $wo['estimated_cost']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT COL: Maintenance Plans -->
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Planos Preventivos Associados</h3>
            <?php if (empty($plans)): ?>
                <p class="text-slate-400">Nenhum plano periódico configurado especificamente para este activo.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($plans as $p): ?>
                        <div class="p-3 bg-slate-50 border border-slate-100 rounded-xl">
                            <span class="font-mono font-bold text-sky-700 text-[10px]"><?= e($p['reference']) ?></span>
                            <h5 class="font-bold text-slate-800 text-xs mt-0.5"><?= e($p['title']) ?></h5>
                            <span class="text-slate-400 text-[10px] block mt-1">Periodicidade: <?= e($p['frequency']) ?></span>
                            <span class="text-sky-700 font-semibold text-[10px] block">Próxima: <?= format_date($p['next_execution_date'], 'd/m/Y') ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
