<div class="mb-6">
    <h2 class="text-xl font-bold text-slate-900">Auditoria & Rastreabilidade de Operações</h2>
    <p class="text-xs text-slate-500 mt-0.5">Registo imutável de todas as acções, acessos, alterações de estado e movimentações críticas.</p>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-bold text-slate-800">Trilha de Auditoria (Últimos 100 Registos)</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Data & Hora</th>
                    <th class="px-6 py-3.5">Utilizador</th>
                    <th class="px-6 py-3.5">Módulo</th>
                    <th class="px-6 py-3.5">Acção</th>
                    <th class="px-6 py-3.5">Detalhes da Operação</th>
                    <th class="px-6 py-3.5">Endereço IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">Nenhum registo de auditoria até ao momento.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-3.5 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                <?= format_date($log['created_at']) ?>
                            </td>
                            <td class="px-6 py-3.5 font-bold text-slate-900 whitespace-nowrap">
                                <?= e($log['user_name'] ?? 'Sistema') ?>
                            </td>
                            <td class="px-6 py-3.5 uppercase text-[10px] font-semibold text-slate-600">
                                <?= e($log['module']) ?>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase 
                                    <?= match ($log['action']) {
                                        'login' => 'bg-emerald-50 text-emerald-700',
                                        'logout' => 'bg-slate-100 text-slate-600',
                                        'create' => 'bg-sky-50 text-sky-700',
                                        'update', 'status_update' => 'bg-amber-50 text-amber-700',
                                        'delete', 'archive' => 'bg-red-50 text-red-700',
                                        default => 'bg-slate-100 text-slate-700'
                                    } ?>">
                                    <?= e($log['action']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-slate-800">
                                <?= e($log['details']) ?>
                            </td>
                            <td class="px-6 py-3.5 font-mono text-[11px] text-slate-400">
                                <?= e($log['ip_address'] ?? '127.0.0.1') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
