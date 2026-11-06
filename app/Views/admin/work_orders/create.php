<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="/work-orders" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar às Ordens de Serviço
        </a>
        <h2 class="text-xl font-bold text-slate-900">Nova Ordem de Serviço (OS)</h2>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="/work-orders" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <?php if ($incident): ?>
                <input type="hidden" name="incident_id" value="<?= $incident['id'] ?>">
                <input type="hidden" name="origin" value="incident">
                <div class="p-3 bg-sky-50 border border-sky-100 rounded-xl text-xs text-sky-800 flex items-center justify-between">
                    <span>Originada do Incidente <strong><?= e($incident['reference']) ?></strong>: <?= e($incident['title']) ?></span>
                    <span class="px-2 py-0.5 rounded uppercase font-bold text-[10px] bg-sky-200"><?= e($incident['priority']) ?></span>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Título da Ordem de Serviço *</label>
                    <input type="text" name="title" required value="<?= $incident ? 'Reparação: ' . e($incident['title']) : '' ?>"
                        placeholder="Ex.: Reparação urgente de canalização e estancamento de fuga"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Imóvel da Intervenção *</label>
                    <select name="property_id" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <?php foreach ($properties as $prop): ?>
                            <option value="<?= $prop['id'] ?>" <?= ($incident && $incident['property_id'] == $prop['id']) ? 'selected' : '' ?>>
                                <?= e($prop['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Equipamento / Activo Afectado</label>
                    <select name="asset_id" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Nenhum equipamento específico (Intervenção predial geral)</option>
                        <?php foreach ($assets as $ast): ?>
                            <option value="<?= $ast['id'] ?>"><?= e($ast['name']) ?> (<?= e($ast['code']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Técnico Responsável</label>
                    <select name="technician_id" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Selecione um técnico...</option>
                        <?php foreach ($technicians as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (<?= e($t['specialization']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prioridade</label>
                    <select name="priority" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="normal">Normal</option>
                        <option value="low">Baixa</option>
                        <option value="high" <?= ($incident && $incident['priority'] === 'high') ? 'selected' : '' ?>>Alta</option>
                        <option value="urgent" <?= ($incident && in_array($incident['priority'], ['urgent', 'critical'])) ? 'selected' : '' ?>>Urgente</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Data Prevista de Execução</label>
                    <input type="date" name="scheduled_date" value="<?= date('Y-m-d') ?>"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Custo Estimado (MZN)</label>
                    <input type="number" step="0.01" name="estimated_cost" placeholder="Ex.: 3500.00"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição e Instruções de Intervenção</label>
                    <textarea name="description" rows="3" placeholder="Instruções para a equipa, procedimentos de segurança e material necessário..."
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"><?= $incident ? e($incident['description']) : '' ?></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="/work-orders" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Emitir Ordem de Serviço
                </button>
            </div>
        </form>
    </div>
</div>
