<!-- INCIDENT SHOW HEADER -->
<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <a href="/incidents" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar à Lista de Incidentes
        </a>
        <div class="flex items-center space-x-3">
            <span class="font-mono text-sm font-bold text-sky-800 bg-sky-100 px-3 py-1 rounded-lg border border-sky-200">
                <?= e($incident['reference']) ?>
            </span>
            <h2 class="text-xl font-bold text-slate-900"><?= e($incident['title']) ?></h2>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase 
                <?= match ($incident['status']) {
                    'new' => 'bg-blue-100 text-blue-800',
                    'in_progress' => 'bg-amber-100 text-amber-800',
                    'resolved' => 'bg-emerald-100 text-emerald-800',
                    'closed' => 'bg-slate-200 text-slate-800',
                    default => 'bg-slate-100 text-slate-700'
                } ?>">
                <?= e($incident['status']) ?>
            </span>
        </div>
        <p class="text-xs text-slate-500 mt-1">
            Reportado em <?= format_date($incident['created_at']) ?> • Origem: <strong class="uppercase"><?= e($incident['origin']) ?></strong>
        </p>
    </div>

    <!-- Actions Bar -->
    <div class="flex items-center space-x-2">
        <a href="/work-orders/create?incident_id=<?= $incident['id'] ?>&property_id=<?= $incident['property_id'] ?>" 
            class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center">
            <i data-lucide="wrench" class="w-4 h-4 mr-1.5"></i>
            Criar Ordem de Serviço
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- LEFT 2 COLS: Details, History, Comments -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Main Description -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Descrição da Ocorrência</h3>
            <p class="text-xs text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100 whitespace-pre-line">
                <?= e($incident['description']) ?>
            </p>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-slate-100 text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Categoria</span>
                    <span class="font-bold text-slate-800"><?= e($incident['category_name']) ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Prioridade</span>
                    <span class="font-bold uppercase <?= in_array($incident['priority'], ['critical', 'urgent']) ? 'text-red-600' : 'text-slate-800' ?>">
                        <?= e($incident['priority']) ?>
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">Localização</span>
                    <span class="font-semibold text-slate-800"><?= e($incident['property_name']) ?></span>
                    <span class="text-[11px] text-slate-500 block"><?= e($incident['unit_ref'] ?: 'Áreas Comuns') ?></span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-semibold">SLA Resolução</span>
                    <span class="font-medium text-slate-800 block"><?= format_date($incident['sla_resolution_deadline']) ?></span>
                </div>
            </div>
        </div>

        <!-- Associated Work Orders -->
        <?php if (!empty($workOrders)): ?>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center">
                    <i data-lucide="wrench" class="w-4 h-4 mr-2 text-sky-600"></i>
                    Ordens de Serviço Originadas deste Incidente
                </h3>
                <div class="divide-y divide-slate-100 text-xs">
                    <?php foreach ($workOrders as $wo): ?>
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <a href="/work-orders/<?= $wo['id'] ?>" class="font-mono font-bold text-sky-700 hover:underline">
                                    <?= e($wo['reference']) ?>
                                </a>
                                <p class="font-semibold text-slate-800 mt-0.5"><?= e($wo['title']) ?></p>
                                <span class="text-slate-400 text-[11px]">Técnico: <?= e($wo['technician_name'] ?? 'Por atribuir') ?></span>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-slate-100 text-slate-700">
                                    <?= e($wo['status']) ?>
                                </span>
                                <span class="text-[11px] font-bold text-slate-800 block mt-1"><?= format_money($wo['actual_total_cost'] ?: $wo['estimated_cost']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Discussion & Comments -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center">
                <i data-lucide="message-square" class="w-4 h-4 mr-2 text-slate-500"></i>
                Comentários e Notas Técnicas
            </h3>

            <div class="space-y-4 mb-6">
                <?php if (empty($comments)): ?>
                    <p class="text-xs text-slate-400 text-center py-4">Sem notas registadas.</p>
                <?php else: ?>
                    <?php foreach ($comments as $c): ?>
                        <div class="p-3.5 rounded-xl text-xs <?= $c['is_internal'] ? 'bg-amber-50/70 border border-amber-200/60' : 'bg-slate-50 border border-slate-100' ?>">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-slate-800"><?= e($c['author_name'] ?? 'Utilizador') ?></span>
                                    <?php if ($c['is_internal']): ?>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase bg-amber-200 text-amber-900">Nota Interna</span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-[10px] text-slate-400"><?= format_date($c['created_at']) ?></span>
                            </div>
                            <p class="text-slate-700 whitespace-pre-line"><?= e($c['comment']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Add Comment Form -->
            <form action="/incidents/<?= $incident['id'] ?>/comment" method="POST" class="space-y-3">
                <?= csrf_field() ?>
                <textarea name="comment" rows="3" required placeholder="Adicionar actualização ou nota para a equipa..."
                    class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                <div class="flex items-center justify-between">
                    <label class="flex items-center space-x-2 text-xs text-slate-600 cursor-pointer">
                        <input type="checkbox" name="is_internal" value="1" class="rounded border-slate-300 text-sky-600">
                        <span>Marcar como Nota Interna (invisível ao ocupante)</span>
                    </label>
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-xl transition">
                        Publicar Comentário
                    </button>
                </div>
            </form>
        </div>

        <!-- Audited Status History -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center">
                <i data-lucide="history" class="w-4 h-4 mr-2 text-slate-500"></i>
                Histórico Auditável de Alterações de Estado
            </h3>
            <div class="space-y-3 text-xs">
                <?php foreach ($history as $h): ?>
                    <div class="flex items-start space-x-3 text-slate-600 py-1.5 border-l-2 border-sky-400 pl-3">
                        <div class="flex-1">
                            <span class="font-bold text-slate-800"><?= e($h['user_name'] ?? 'Sistema') ?></span>
                            alterou para <span class="font-semibold uppercase text-sky-700"><?= e($h['new_status']) ?></span>
                            <?php if ($h['comment']): ?>
                                <p class="text-slate-500 mt-0.5 italic">"<?= e($h['comment']) ?>"</p>
                            <?php endif; ?>
                        </div>
                        <span class="text-[10px] text-slate-400"><?= format_date($h['created_at']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- RIGHT COL: Lifecycle Controls & Assignments -->
    <div class="space-y-6">
        <!-- Status Transition Box -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Actualizar Ciclo de Vida</h3>
            <form action="/incidents/<?= $incident['id'] ?>/status" method="POST" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Novo Estado Operacional</label>
                    <select name="status" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="under_review" <?= $incident['status'] === 'under_review' ? 'selected' : '' ?>>Em Análise</option>
                        <option value="approved" <?= $incident['status'] === 'approved' ? 'selected' : '' ?>>Aprovado</option>
                        <option value="assigned" <?= $incident['status'] === 'assigned' ? 'selected' : '' ?>>Atribuído</option>
                        <option value="in_progress" <?= $incident['status'] === 'in_progress' ? 'selected' : '' ?>>Em Resolução</option>
                        <option value="waiting_materials" <?= $incident['status'] === 'waiting_materials' ? 'selected' : '' ?>>Aguardando Materiais</option>
                        <option value="waiting_supplier" <?= $incident['status'] === 'waiting_supplier' ? 'selected' : '' ?>>Aguardando Fornecedor</option>
                        <option value="resolved" <?= $incident['status'] === 'resolved' ? 'selected' : '' ?>>Resolvido</option>
                        <option value="validation_pending" <?= $incident['status'] === 'validation_pending' ? 'selected' : '' ?>>Aguardando Validação</option>
                        <option value="closed" <?= $incident['status'] === 'closed' ? 'selected' : '' ?>>Encerrado</option>
                        <option value="cancelled" <?= $incident['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">Motivo / Justificação</label>
                    <input type="text" name="comment" placeholder="Registe o motivo da alteração..."
                        class="w-full text-xs p-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>
                <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Confirmar Transição
                </button>
            </form>
        </div>

        <!-- Assign Technician -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Atribuir Técnico</h3>
            <form action="/incidents/<?= $incident['id'] ?>/assign" method="POST" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <select name="technician_id" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Selecione um técnico...</option>
                        <?php foreach ($technicians as $tech): ?>
                            <option value="<?= $tech['id'] ?>" <?= $incident['assigned_technician_id'] == $tech['id'] ? 'selected' : '' ?>>
                                <?= e($tech['name']) ?> (<?= e($tech['specialization']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-xl transition">
                    Actualizar Atribuição
                </button>
            </form>
        </div>

        <!-- Occupant & Contact Information -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3">Dados do Solicitante</h3>
            <?php if ($incident['occupant_name']): ?>
                <div class="space-y-2">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Ocupante</span>
                        <span class="font-bold text-slate-900"><?= e($incident['occupant_name']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Telefone</span>
                        <span class="font-semibold text-slate-800"><?= e($incident['occupant_phone']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Email</span>
                        <span class="text-slate-600"><?= e($incident['occupant_email']) ?></span>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-slate-400">Registo interno efectuado pela administração.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
