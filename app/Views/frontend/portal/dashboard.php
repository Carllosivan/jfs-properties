<div class="space-y-8">
    <!-- HERO HEADER -->
    <div class="bg-gradient-to-r from-slate-900 to-sky-950 text-white p-6 sm:p-8 rounded-3xl shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
        <div>
            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase bg-sky-500/20 text-sky-300 border border-sky-400/30">
                Atendimento Técnico ao Ocupante
            </span>
            <h2 class="text-2xl font-bold tracking-tight mt-2">Bem-vindo ao seu Portal</h2>
            <p class="text-xs text-slate-300 mt-1 max-w-lg leading-relaxed">
                Comunique anomalias na sua fracção ou áreas comuns, acompanhe a chegada dos nossos técnicos e confirme a conclusão dos trabalhos.
            </p>
        </div>
        <button onclick="document.getElementById('modal-new-ticket').classList.remove('hidden')" 
            class="px-5 py-3 bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition flex items-center justify-center flex-shrink-0">
            <i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>
            Comunicar Novo Problema
        </button>
    </div>

    <!-- MY UNITS -->
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">As Minhas Unidades / Fracções</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php foreach ($units as $u): ?>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-start justify-between">
                    <div>
                        <span class="font-mono text-xs font-bold text-sky-700 bg-sky-50 px-2.5 py-1 rounded-md border border-sky-100">
                            <?= e($u['reference']) ?>
                        </span>
                        <h4 class="font-bold text-slate-900 text-sm mt-2"><?= e($u['name']) ?></h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            <?= e($u['property_name']) ?> • <?= e($u['building_name'] ?? '') ?>
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1"><?= e($u['property_address']) ?></p>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700">
                        Ocupante Activo
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- REPORTED INCIDENTS -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Ocorrências & Solicitações de Assistência</h3>
                <p class="text-[11px] text-slate-500">Histórico de anomalias comunicadas e estado da resolução</p>
            </div>
        </div>

        <div class="divide-y divide-slate-100 text-xs">
            <?php if (empty($incidents)): ?>
                <div class="p-8 text-center text-slate-400">
                    <i data-lucide="check-circle" class="w-10 h-10 text-emerald-400 mx-auto mb-2 opacity-60"></i>
                    <p class="font-medium text-slate-700">Tudo em ordem!</p>
                    <p class="text-slate-400 text-[11px] mt-0.5">Não existem incidentes pendentes para a sua fracção.</p>
                </div>
            <?php else: ?>
                <?php foreach ($incidents as $inc): ?>
                    <a href="/portal/incidents/<?= $inc['id'] ?>" class="p-5 hover:bg-slate-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition block">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="font-mono font-bold text-slate-800"><?= e($inc['reference']) ?></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    <?= e($inc['category_name']) ?>
                                </span>
                            </div>
                            <h4 class="font-bold text-slate-900 text-sm mt-1"><?= e($inc['title']) ?></h4>
                            <p class="text-slate-500 text-xs mt-0.5 line-clamp-1"><?= e($inc['description']) ?></p>
                            <span class="text-[11px] text-slate-400 mt-1 block">Submetido em <?= format_date($inc['created_at']) ?></span>
                        </div>

                        <div class="flex items-center space-x-4 flex-shrink-0">
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase 
                                <?= match ($inc['status']) {
                                    'new' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                    'in_progress', 'assigned' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                    'resolved' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                    'closed' => 'bg-slate-100 text-slate-600',
                                    default => 'bg-slate-100 text-slate-700'
                                } ?>">
                                <?= match ($inc['status']) {
                                    'new' => 'Recebido',
                                    'in_progress', 'assigned' => 'Técnico a Caminho',
                                    'resolved' => 'Resolvido',
                                    'closed' => 'Concluído',
                                    default => $inc['status']
                                } ?>
                            </span>
                            <span class="text-sky-600 font-semibold text-xs flex items-center">
                                Acompanhar <i data-lucide="chevron-right" class="w-4 h-4 ml-1"></i>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal: New Ticket / Problem -->
<div id="modal-new-ticket" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="text-base font-bold text-slate-900">Comunicar Incidente / Avaria</h3>
                <p class="text-xs text-slate-500 mt-0.5">A nossa central técnica dará seguimento imediato</p>
            </div>
            <button onclick="document.getElementById('modal-new-ticket').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
        </div>

        <form action="/portal/incidents" method="POST" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Qual a sua Fracção? *</label>
                <select name="unit_id" required class="w-full text-xs p-3 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <?php foreach ($units as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= e($u['property_name']) ?> — <?= e($u['reference']) ?> (<?= e($u['name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Avaria / Categoria *</label>
                <select name="category_id" required class="w-full text-xs p-3 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Selecione o tipo de problema...</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Assunto Breve *</label>
                <input type="text" name="title" required placeholder="Ex.: Fuga de água debaixo da banca da cozinha"
                    class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição Pormenorizada *</label>
                <textarea name="description" rows="3" required placeholder="Indique o compartimento, sintomas e qualquer informação útil para o técnico..."
                    class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Urgência</label>
                <select name="priority" class="w-full text-xs p-3 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="normal" selected>Normal (Intervenção de rotina)</option>
                    <option value="high">Alta (Causa transtorno considerável)</option>
                    <option value="critical">Crítica (Risco de alagamento, incêndio ou perigo)</option>
                </select>
            </div>

            <div class="pt-3 flex justify-end space-x-3">
                <button type="button" onclick="document.getElementById('modal-new-ticket').classList.add('hidden')" 
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl">
                    Cancelar
                </button>
                <button type="submit" 
                    class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow-md transition">
                    Submeter Pedido de Assistência
                </button>
            </div>
        </form>
    </div>
</div>
