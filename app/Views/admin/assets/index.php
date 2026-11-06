<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Equipamentos & Activos Técnicos</h2>
        <p class="text-xs text-slate-500 mt-0.5">Inventário de máquinas, geradores, elevadores, centrais térmicas e bombas dos imóveis.</p>
    </div>
    <a href="/assets/create" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
        Registar Activo
    </a>
</div>

<!-- Filters Bar -->
<div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-wrap items-center gap-3">
    <form method="GET" action="/assets" class="flex flex-wrap items-center gap-3 w-full">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Pesquisar por código, designação, marca ou modelo..."
                class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div>
            <select name="category_id" class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Todas as Categorias</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (string)($filters['category_id'] ?? '') === (string)$cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <select name="property_id" class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Todos os Imóveis</option>
                <?php foreach ($properties as $prop): ?>
                    <option value="<?= $prop['id'] ?>" <?= (string)($filters['property_id'] ?? '') === (string)$prop['id'] ? 'selected' : '' ?>>
                        <?= e($prop['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
            Filtrar
        </button>
    </form>
</div>

<!-- Assets Table -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Tag / Código</th>
                    <th class="px-6 py-3.5">Nome do Equipamento</th>
                    <th class="px-6 py-3.5">Categoria</th>
                    <th class="px-6 py-3.5">Imóvel & Localização</th>
                    <th class="px-6 py-3.5">Marca / Modelo</th>
                    <th class="px-6 py-3.5">Garantia</th>
                    <th class="px-6 py-3.5">Estado</th>
                    <th class="px-6 py-3.5 text-right">Acções</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($assets)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-slate-400">Nenhum activo registado.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($assets as $ast): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3.5 font-mono font-bold text-sky-700">
                                <a href="/assets/<?= $ast['id'] ?>" class="hover:underline"><?= e($ast['code']) ?></a>
                            </td>
                            <td class="px-6 py-3.5 font-bold text-slate-900">
                                <a href="/assets/<?= $ast['id'] ?>" class="hover:text-sky-600 transition"><?= e($ast['name']) ?></a>
                            </td>
                            <td class="px-6 py-3.5"><?= e($ast['category_name']) ?></td>
                            <td class="px-6 py-3.5">
                                <span class="font-medium text-slate-800 block"><?= e($ast['property_name']) ?></span>
                                <span class="text-slate-400 text-[11px]"><?= e($ast['location_details'] ?: 'Geral') ?></span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-slate-800 font-medium block"><?= e($ast['manufacturer'] ?: '-') ?></span>
                                <span class="text-slate-400 text-[11px]"><?= e($ast['model'] ?: '-') ?></span>
                            </td>
                            <td class="px-6 py-3.5">
                                <?php if ($ast['warranty_expiration']): ?>
                                    <span class="text-[11px] font-semibold <?= strtotime($ast['warranty_expiration']) > time() ? 'text-emerald-600' : 'text-slate-400' ?>">
                                        <?= format_date($ast['warranty_expiration'], 'd/m/Y') ?>
                                        <?= strtotime($ast['warranty_expiration']) > time() ? '(Válida)' : '(Expirada)' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase 
                                    <?= $ast['status'] === 'operational' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' ?>">
                                    <?= e($ast['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="/assets/<?= $ast['id'] ?>" class="text-sky-600 hover:text-sky-800 font-semibold">
                                    Ver Ficha →
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
