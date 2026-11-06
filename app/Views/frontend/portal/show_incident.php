<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <a href="/portal" class="text-xs text-slate-500 hover:text-slate-800 flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Voltar ao Meu Portal
        </a>
        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase 
            <?= match ($incident['status']) {
                'new' => 'bg-blue-100 text-blue-800',
                'in_progress', 'assigned' => 'bg-amber-100 text-amber-800',
                'resolved' => 'bg-emerald-100 text-emerald-800',
                default => 'bg-slate-100 text-slate-700'
            } ?>">
            <?= match ($incident['status']) {
                'new' => 'Recebido pela Central',
                'in_progress', 'assigned' => 'Em Resolução / Técnico a Caminho',
                'resolved' => 'Resolvido pelo Técnico',
                'closed' => 'Encerrado',
                default => $incident['status']
            } ?>
        </span>
    </div>

    <!-- TICKET HEADER -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex items-center space-x-2 text-xs text-slate-400 mb-2">
            <span class="font-mono font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-100"><?= e($incident['reference']) ?></span>
            <span>•</span>
            <span><?= e($incident['category_name']) ?></span>
            <span>•</span>
            <span><?= format_date($incident['created_at']) ?></span>
        </div>

        <h2 class="text-xl font-bold text-slate-900"><?= e($incident['title']) ?></h2>
        <p class="text-xs text-slate-700 mt-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 whitespace-pre-line leading-relaxed">
            <?= e($incident['description']) ?>
        </p>

        <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500 flex justify-between">
            <span>Imóvel: <strong class="text-slate-800"><?= e($incident['property_name']) ?></strong></span>
            <span>Fracção: <strong class="text-slate-800"><?= e($incident['unit_ref'] ?: 'Geral') ?></strong></span>
        </div>
    </div>

    <!-- PUBLIC MESSAGES & TECHNICAL UPDATES -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <h3 class="text-sm font-bold text-slate-800">Mensagens e Atualizações da Equipa Técnica</h3>

        <div class="space-y-3">
            <?php if (empty($comments)): ?>
                <p class="text-xs text-slate-400 text-center py-4">Ainda sem mensagens directas da equipa.</p>
            <?php else: ?>
                <?php foreach ($comments as $c): ?>
                    <div class="p-4 rounded-2xl text-xs bg-slate-50 border border-slate-100">
                        <div class="flex justify-between items-center mb-1">
                            <span class="font-bold text-slate-900"><?= e($c['author_name'] ?? 'Suporte JFS') ?></span>
                            <span class="text-[10px] text-slate-400"><?= format_date($c['created_at']) ?></span>
                        </div>
                        <p class="text-slate-700 whitespace-pre-line"><?= e($c['comment']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Add message to technician -->
        <form action="/portal/incidents/<?= $incident['id'] ?>/comment" method="POST" class="pt-4 border-t border-slate-100 space-y-3">
            <?= csrf_field() ?>
            <label class="block text-xs font-semibold text-slate-700">Enviar mensagem ou esclarecimento para a equipa:</label>
            <textarea name="comment" rows="2" required placeholder="Escreva a sua mensagem aqui..."
                class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    Enviar Mensagem
                </button>
            </div>
        </form>
    </div>
</div>
