<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="/assets" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar aos Activos
        </a>
        <h2 class="text-xl font-bold text-slate-900">Novo Registo de Equipamento / Activo</h2>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="/assets" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome / Designação do Activo *</label>
                    <input type="text" name="name" required placeholder="Ex.: Grupo Gerador Diesel 250kVA Cummins"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Categoria de Activo *</label>
                    <select name="category_id" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Imóvel Onde Está Instalado *</label>
                    <select name="property_id" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <?php foreach ($properties as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Localização Específica no Edifício</label>
                    <input type="text" name="location_details" placeholder="Ex.: Sala Técnica do Gerador, Cave 1, junto ao barrilete"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fabricante / Marca</label>
                    <input type="text" name="manufacturer" placeholder="Ex.: Cummins / Daikin / Schindler"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Modelo Comercial</label>
                    <input type="text" name="model" placeholder="Ex.: C250 D5"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Número de Série / Chassi</label>
                    <input type="text" name="serial_number" placeholder="Ex.: CUM-MZ-998811"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Data de Instalação</label>
                    <input type="date" name="installation_date" value="<?= date('Y-m-d') ?>"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Data Limite de Garantia</label>
                    <input type="date" name="warranty_expiration"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fornecedor / Instalador</label>
                    <select name="supplier_id" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Selecione o fornecedor...</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= e($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="/assets" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Gravar e Gerar Código de Activo
                </button>
            </div>
        </form>
    </div>
</div>
