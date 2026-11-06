<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Gestão de Ocupantes</h2>
        <p class="text-xs text-slate-500 mt-0.5">Arrendatários residenciais, comerciais e utilizadores autorizados das fracções.</p>
    </div>
    <a href="/occupants/create" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="user-plus" class="w-4 h-4 mr-1.5"></i>
        Registar Ocupante
    </a>
</div>

<!-- Search Bar -->
<div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6">
    <form method="GET" action="/occupants" class="flex items-center gap-3">
        <div class="flex-1">
            <input type="text" name="q" value="<?= e($search ?? '') ?>" placeholder="Pesquisar por nome, telefone, email ou fracção..."
                class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
            Pesquisar
        </button>
        <?php if (!empty($search)): ?>
            <a href="/occupants" class="text-xs text-slate-500 hover:text-slate-800">Limpar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Occupants Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Nome Completo</th>
                    <th class="px-6 py-3.5">Contactos</th>
                    <th class="px-6 py-3.5">Tipo</th>
                    <th class="px-6 py-3.5">Imóvel & Fracção</th>
                    <th class="px-6 py-3.5">Renda Mensal</th>
                    <th class="px-6 py-3.5">Acesso Portal</th>
                    <th class="px-6 py-3.5">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($occupants)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">Nenhum ocupante registado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($occupants as $occ): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3.5">
                                <span class="font-bold text-slate-900 block"><?= e($occ['name']) ?></span>
                                <span class="text-[11px] text-slate-400">BI: <?= e($occ['id_card_number'] ?? 'N/D') ?></span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-slate-800 block"><?= e($occ['phone']) ?></span>
                                <span class="text-slate-400 text-[11px]"><?= e($occ['email']) ?></span>
                            </td>
                            <td class="px-6 py-3.5 uppercase text-[10px] font-semibold text-slate-600">
                                <?= e($occ['type']) ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <?php if ($occ['unit_ref']): ?>
                                    <span class="font-mono font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded text-[11px]">
                                        <?= e($occ['unit_ref']) ?>
                                    </span>
                                    <span class="text-slate-500 text-[11px] block mt-0.5"><?= e($occ['property_name']) ?></span>
                                <?php else: ?>
                                    <span class="text-slate-400 italic">Sem fracção atribuída</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3.5 font-semibold text-slate-800">
                                <?= $occ['rent_amount'] ? format_money($occ['rent_amount']) : '-' ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <?php if ($occ['user_id']): ?>
                                    <span class="inline-flex items-center text-emerald-600 font-semibold text-[11px]">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 mr-1"></i> Activo
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 text-[11px]">Sem Acesso</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase 
                                    <?= $occ['status'] === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' ?>">
                                    <?= e($occ['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
