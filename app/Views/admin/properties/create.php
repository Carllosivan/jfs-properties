<div class="max-w-3xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="/properties" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar à Lista
            </a>
            <h2 class="text-xl font-bold text-slate-900">Novo Registo de Imóvel</h2>
        </div>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="/properties" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome do Imóvel *</label>
                    <input type="text" name="name" required placeholder="Ex.: Edifício Polana Grand Tower"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Propriedade *</label>
                    <select name="type" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="residential">Residencial</option>
                        <option value="commercial">Comercial</option>
                        <option value="mixed">Misto (Residencial + Comercial)</option>
                        <option value="industrial">Industrial / Logística</option>
                        <option value="institutional">Institucional</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Área Total Bruta (m²)</label>
                    <input type="number" step="0.01" name="total_area" placeholder="Ex.: 4500.00"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Endereço Completo *</label>
                    <input type="text" name="address" required placeholder="Ex.: Av. Julius Nyerere, nº 1420"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Cidade</label>
                    <input type="text" name="city" value="Maputo"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Província</label>
                    <input type="text" name="province" value="Maputo Cidade"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gestor do Imóvel Responsável</label>
                    <select name="manager_id" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Selecione um gestor...</option>
                        <?php foreach ($managers as $mgr): ?>
                            <option value="<?= $mgr['id'] ?>"><?= e($mgr['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição e Características</label>
                    <textarea name="description" rows="3" placeholder="Informação técnica, tipologias, áreas de serviço, estacionamentos..."
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="/properties" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Gravar e Gerar Referência
                </button>
            </div>
        </form>
    </div>
</div>
