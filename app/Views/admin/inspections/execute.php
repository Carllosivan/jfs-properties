<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="/inspections" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar às Inspecções
            </a>
            <div class="flex items-center space-x-3">
                <span class="font-mono text-sm font-bold text-sky-800 bg-sky-100 px-3 py-1 rounded-lg border border-sky-200">
                    <?= e($inspection['reference']) ?>
                </span>
                <h2 class="text-xl font-bold text-slate-900"><?= e($inspection['template_title']) ?></h2>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                Imóvel: <strong><?= e($inspection['property_name']) ?></strong> • Data: <?= format_date($inspection['inspection_date'], 'd/m/Y') ?>
            </p>
        </div>
    </div>

    <form action="/inspections/<?= $inspection['id'] ?>/submit" method="POST">
        <?= csrf_field() ?>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Checklist de Conformidade Técnica</h3>
                    <p class="text-[11px] text-slate-500">Qualquer não conformidade crítica gerará automaticamente um incidente operacional.</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                <?php foreach ($items as $item): ?>
                    <div class="p-5 hover:bg-slate-50/60 transition">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-slate-100 text-slate-500 mr-2">
                                    <?= e($item['section_name']) ?>
                                </span>
                                <span class="font-bold text-slate-900 text-xs"><?= e($item['item_name']) ?></span>
                            </div>

                            <!-- Radio Group Options -->
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center space-x-1.5 cursor-pointer">
                                    <input type="radio" name="results[<?= $item['id'] ?>]" value="compliant" required
                                        <?= ($item['result'] === 'compliant' || empty($item['result'])) ? 'checked' : '' ?>
                                        class="text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-emerald-700 font-semibold text-[11px]">Conforme</span>
                                </label>

                                <label class="flex items-center space-x-1.5 cursor-pointer">
                                    <input type="radio" name="results[<?= $item['id'] ?>]" value="non_compliant"
                                        <?= $item['result'] === 'non_compliant' ? 'checked' : '' ?>
                                        class="text-red-600 focus:ring-red-500">
                                    <span class="text-red-700 font-semibold text-[11px]">Não Conforme</span>
                                </label>

                                <label class="flex items-center space-x-1.5 cursor-pointer">
                                    <input type="radio" name="results[<?= $item['id'] ?>]" value="needs_attention"
                                        <?= $item['result'] === 'needs_attention' ? 'checked' : '' ?>
                                        class="text-amber-600 focus:ring-amber-500">
                                    <span class="text-amber-700 font-medium text-[11px]">Atenção</span>
                                </label>

                                <label class="flex items-center space-x-1.5 cursor-pointer">
                                    <input type="radio" name="results[<?= $item['id'] ?>]" value="na"
                                        <?= $item['result'] === 'na' ? 'checked' : '' ?>
                                        class="text-slate-400 focus:ring-slate-400">
                                    <span class="text-slate-400 text-[11px]">N/A</span>
                                </label>
                            </div>
                        </div>

                        <!-- Item Observation Input -->
                        <div class="mt-2">
                            <input type="text" name="notes[<?= $item['id'] ?>]" value="<?= e($item['result_notes'] ?? '') ?>"
                                placeholder="Observação técnica ou detalhes da inconformidade..."
                                class="w-full text-xs px-3 py-1.5 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Overall Summary & Submission -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Conclusões Gerais do Inspector</h4>
            <textarea name="overall_summary" rows="3" placeholder="Parecer técnico final da vistoria..."
                class="w-full text-xs p-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"><?= e($inspection['overall_summary']) ?></textarea>

            <div class="pt-2 flex justify-end space-x-3">
                <a href="/inspections" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Guardar e Sair
                </a>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Concluir Inspecção & Validar
                </button>
            </div>
        </div>
    </form>
</div>
