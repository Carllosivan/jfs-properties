<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="/inspections" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar às Inspecções
        </a>
        <h2 class="text-xl font-bold text-slate-900">Iniciar Nova Inspecção Técnica</h2>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="/inspections" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Modelo de Checklist / Tipo de Inspecção *</label>
                <select name="template_id" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Selecione o modelo de inspecção...</option>
                    <?php foreach ($templates as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= e($t['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Imóvel Objecto da Vistoria *</label>
                <select name="property_id" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Selecione o imóvel...</option>
                    <?php foreach ($properties as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Data da Vistoria *</label>
                <input type="date" name="inspection_date" value="<?= date('Y-m-d') ?>" required
                    class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="/inspections" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Carregar Checklist & Iniciar
                </button>
            </div>
        </form>
    </div>
</div>
