<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-slate-900">Configurações & Parâmetros do Sistema</h2>
        <p class="text-xs text-slate-500 mt-0.5">Identidade institucional da organização, moeda e regras operacionais.</p>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="/admin/settings" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <!-- INSTITUTIONAL IDENTITY -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 mb-4">
                    1. Identidade Institucional (JFS Properties)
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nome da Organização / Empresa</label>
                        <input type="text" name="company_name" value="<?= e($settings['company_name'] ?? 'JFS Properties, Lda.') ?>"
                            class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NUIT da Organização</label>
                        <input type="text" name="company_nuit" value="<?= e($settings['company_nuit'] ?? '400987654') ?>"
                            class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">E-mail de Notificações / Suporte</label>
                        <input type="email" name="email_from_address" value="<?= e($settings['email_from_address'] ?? 'suporte@jfsproperties.co.mz') ?>"
                            class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                    </div>
                </div>
            </div>

            <!-- FINANCIAL PARAMETERS -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 mb-4">
                    2. Parâmetros Financeiros & Moeda
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Código da Moeda</label>
                        <input type="text" name="system_currency" value="<?= e($settings['system_currency'] ?? 'MZN') ?>"
                            class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Símbolo Monetário</label>
                        <input type="text" name="currency_symbol" value="<?= e($settings['currency_symbol'] ?? 'MT') ?>"
                            class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 font-mono">
                    </div>
                </div>
            </div>

            <!-- SLA PARAMETERS -->
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100 mb-4">
                    3. Alertas e Gestão de SLA
                </h3>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Aviso de Proximidade de Vencimento de SLA (Minutos)</label>
                    <input type="number" name="sla_warning_threshold_minutes" value="<?= e($settings['sla_warning_threshold_minutes'] ?? '60') ?>"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <p class="text-[11px] text-slate-400 mt-1">Dispara alerta no centro de notificações quando faltar este tempo para expirar o prazo de resolução.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Guardar Todas as Configurações
                </button>
            </div>
        </form>
    </div>
</div>
