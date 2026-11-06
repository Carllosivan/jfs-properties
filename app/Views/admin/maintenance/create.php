<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="/maintenance" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar à Lista de Planos
        </a>
        <h2 class="text-xl font-bold text-slate-900">Novo Plano de Manutenção Preventiva</h2>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="/maintenance" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Título do Plano de Manutenção *</label>
                    <input type="text" name="title" required placeholder="Ex.: Manutenção Preventiva Mensal do Grupo Gerador"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Categoria de Manutenção *</label>
                    <select name="category_id" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Periodicidade *</label>
                    <select name="frequency" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="daily">Diária</option>
                        <option value="weekly">Semanal</option>
                        <option value="biweekly">Quinzenal</option>
                        <option value="monthly" selected>Mensal (30 dias)</option>
                        <option value="quarterly">Trimestral (90 dias)</option>
                        <option value="semiannual">Semestral (180 dias)</option>
                        <option value="annual">Anual (365 dias)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Imóvel Afectado *</label>
                    <select name="property_id" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <?php foreach ($properties as $prop): ?>
                            <option value="<?= $prop['id'] ?>"><?= e($prop['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Equipamento / Activo Específico</label>
                    <select name="asset_id" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Nenhum (Manutenção geral do imóvel)</option>
                        <?php foreach ($assets as $ast): ?>
                            <option value="<?= $ast['id'] ?>"><?= e($ast['name']) ?> (<?= e($ast['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Data da Primeira / Próxima Execução *</label>
                    <input type="date" name="next_execution_date" value="<?= date('Y-m-d') ?>" required
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Técnico ou Líder Atribuído</label>
                    <select name="technician_id" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Equipa geral de turno</option>
                        <?php foreach ($technicians as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (<?= e($t['specialization']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Custo Estimado por Execução (MZN)</label>
                    <input type="number" step="0.01" name="estimated_cost" placeholder="Ex.: 5000.00"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Duração Estimada (Horas)</label>
                    <input type="number" step="0.5" name="estimated_duration_hours" value="2.0"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Procedimento Técnico e Descrição do Checklist</label>
                    <textarea name="description" rows="3" placeholder="Pontos de verificação obrigatórios, medições necessárias, parâmetros normais..."
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="/maintenance" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Gravar Plano Preventivo
                </button>
            </div>
        </form>
    </div>
</div>
