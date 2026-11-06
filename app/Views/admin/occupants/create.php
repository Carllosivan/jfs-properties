<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="/occupants" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar à Lista de Ocupantes
        </a>
        <h2 class="text-xl font-bold text-slate-900">Novo Registo de Ocupante</h2>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="/occupants" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome Completo *</label>
                    <input type="text" name="name" required placeholder="Ex.: Ana Lúcia Cossa"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Ocupante *</label>
                    <select name="type" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="tenant">Arrendatário Residencial</option>
                        <option value="commercial_tenant">Arrendatário Comercial</option>
                        <option value="owner">Proprietário / Condómino</option>
                        <option value="authorized_user">Utilizador Autorizado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Telefone Principal *</label>
                    <input type="text" name="phone" required placeholder="+258 84 000 0000"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Endereço de E-mail *</label>
                    <input type="email" name="email" required placeholder="ocupante@exemplo.com"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nº de Identificação (BI / DIRE / Passaporte)</label>
                    <input type="text" name="id_card_number" placeholder="110293847592M"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NUIT (se aplicável)</label>
                    <input type="text" name="nuit" placeholder="400123456"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Unit Association -->
                <div class="sm:col-span-2 pt-3 border-t border-slate-100">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3">Atribuição de Imóvel e Fracção</h3>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Seleccionar Fracção Disponível</label>
                    <select name="unit_id" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Nenhuma fracção atribuída no momento</option>
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u['id'] ?>"><?= e($u['property_name']) ?> — <?= e($u['reference']) ?> (<?= e($u['name']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Valor de Renda Contratual (MZN)</label>
                    <input type="number" step="0.01" name="rent_amount" placeholder="Ex.: 85000.00"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <!-- Portal Access Switch -->
                <div class="sm:col-span-2 pt-3 border-t border-slate-100">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="create_portal_access" value="1" checked class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500">
                        <div>
                            <span class="text-xs font-semibold text-slate-800 block">Criar Conta de Acesso ao Portal do Ocupante</span>
                            <span class="text-[11px] text-slate-500">Permite ao ocupante autenticar-se, reportar incidentes e acompanhar resoluções em tempo real.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="/occupants" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Gravar Ocupante
                </button>
            </div>
        </form>
    </div>
</div>
