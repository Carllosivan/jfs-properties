<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Carteira de Imóveis</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gestão de edifícios, blocos residenciais, comerciais e industriais.</p>
    </div>
    <a href="/properties/create" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
        Novo Imóvel
    </a>
</div>

<!-- Filters Bar -->
<div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6 flex flex-wrap items-center gap-3">
    <form method="GET" action="/properties" class="flex flex-wrap items-center gap-3 w-full">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Pesquisar por nome, referência ou morada..."
                class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
        </div>
        <div>
            <select name="type" class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Todos os Tipos</option>
                <option value="residential" <?= ($filters['type'] ?? '') === 'residential' ? 'selected' : '' ?>>Residencial</option>
                <option value="commercial" <?= ($filters['type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Comercial</option>
                <option value="mixed" <?= ($filters['type'] ?? '') === 'mixed' ? 'selected' : '' ?>>Misto</option>
                <option value="industrial" <?= ($filters['type'] ?? '') === 'industrial' ? 'selected' : '' ?>>Industrial</option>
            </select>
        </div>
        <div>
            <select name="status" class="text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-500">
                <option value="">Todos os Estados</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Activo</option>
                <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Arquivado</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-slate-800 text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition">
            Filtrar
        </button>
        <?php if (!empty($filters['q']) || !empty($filters['type']) || !empty($filters['status'])): ?>
            <a href="/properties" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800">Limpar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Properties Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php if (empty($properties)): ?>
        <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-slate-200">
            <i data-lucide="building" class="w-12 h-12 text-slate-300 mx-auto mb-3"></i>
            <h3 class="text-sm font-bold text-slate-700">Nenhum imóvel encontrado</h3>
            <p class="text-xs text-slate-500 mt-1">Ajuste os filtros ou registe um novo imóvel no sistema.</p>
        </div>
    <?php else: ?>
        <?php foreach ($properties as $prop): ?>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col hover:border-sky-300 transition group">
                <div class="p-5 flex-1">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-xs font-bold text-sky-600 bg-sky-50 px-2.5 py-1 rounded-md border border-sky-100">
                            <?= e($prop['reference']) ?>
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase 
                            <?= $prop['status'] === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-slate-100 text-slate-600' ?>">
                            <?= e($prop['status']) ?>
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-slate-900 group-hover:text-sky-600 transition">
                        <?= e($prop['name']) ?>
                    </h3>
                    <p class="text-xs text-slate-500 flex items-center mt-1">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 mr-1 text-slate-400 flex-shrink-0"></i>
                        <?= e($prop['address']) ?>, <?= e($prop['city']) ?>
                    </p>

                    <div class="grid grid-cols-3 gap-2 my-4 p-3 bg-slate-50 rounded-xl text-center">
                        <div>
                            <span class="text-base font-bold text-slate-800"><?= $prop['buildings_count'] ?></span>
                            <span class="text-[10px] text-slate-400 block">Edifícios</span>
                        </div>
                        <div>
                            <span class="text-base font-bold text-slate-800"><?= $prop['units_count'] ?></span>
                            <span class="text-[10px] text-slate-400 block">Fracções</span>
                        </div>
                        <div>
                            <span class="text-base font-bold text-slate-800"><?= $prop['assets_count'] ?></span>
                            <span class="text-[10px] text-slate-400 block">Activos</span>
                        </div>
                    </div>

                    <p class="text-xs text-slate-600 line-clamp-2">
                        <?= e($prop['description'] ?: 'Sem descrição adicional.') ?>
                    </p>
                </div>

                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px] uppercase font-semibold">Tipo: <?= e($prop['type']) ?></span>
                    <a href="/properties/<?= $prop['id'] ?>" class="font-semibold text-sky-600 hover:text-sky-800 flex items-center">
                        Abrir Ficha Técnica <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-1"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
