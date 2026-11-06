<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Técnicos & Equipas Operacionais</h2>
        <p class="text-xs text-slate-500 mt-0.5">Especializações, brigadas de intervenção, cargas de trabalho e métricas.</p>
    </div>
    <button onclick="document.getElementById('modal-tech').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="user-plus" class="w-4 h-4 mr-1.5"></i>
        Novo Técnico
    </button>
</div>

<!-- TEAMS CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
    <?php foreach ($teams as $tm): ?>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="font-mono text-[10px] font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded">
                    <?= e($tm['code']) ?>
                </span>
                <h4 class="font-bold text-slate-900 text-sm mt-1"><?= e($tm['name']) ?></h4>
                <p class="text-xs text-slate-500 mt-0.5"><?= e($tm['description']) ?></p>
            </div>
            <div class="text-right">
                <span class="text-2xl font-black text-slate-800"><?= $tm['members_count'] ?></span>
                <span class="text-[10px] text-slate-400 block uppercase font-bold">Membros</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- TECHNICIANS TABLE -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-bold text-slate-800">Quadro de Técnicos Efectivos</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Nome do Técnico</th>
                    <th class="px-6 py-3.5">Especialidade</th>
                    <th class="px-6 py-3.5">Equipa</th>
                    <th class="px-6 py-3.5">Contacto Telefónico</th>
                    <th class="px-6 py-3.5">Custo Horário (MZN)</th>
                    <th class="px-6 py-3.5">Intervenções</th>
                    <th class="px-6 py-3.5">Disponibilidade</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php foreach ($technicians as $t): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3.5">
                            <span class="font-bold text-slate-900 block"><?= e($t['name']) ?></span>
                            <span class="text-slate-400 text-[11px]"><?= e($t['email'] ?? '-') ?></span>
                        </td>
                        <td class="px-6 py-3.5 font-medium text-slate-800"><?= e($t['specialization']) ?></td>
                        <td class="px-6 py-3.5"><?= e($t['team_name'] ?: 'Geral') ?></td>
                        <td class="px-6 py-3.5 font-mono"><?= e($t['phone']) ?></td>
                        <td class="px-6 py-3.5 font-semibold text-slate-900"><?= format_money($t['hourly_rate']) ?>/h</td>
                        <td class="px-6 py-3.5">
                            <span class="font-bold text-emerald-600"><?= $t['completed_orders'] ?></span>
                            <span class="text-slate-400 text-[11px]">/ <?= $t['assigned_orders'] ?> OS</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase 
                                <?= $t['status'] === 'available' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' ?>">
                                <?= e($t['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Technician -->
<div id="modal-tech" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-bold text-slate-900">Novo Técnico de Manutenção</h3>
            <button onclick="document.getElementById('modal-tech').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form action="/operations/technicians" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nome Completo *</label>
                <input type="text" name="name" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Telefone Directo *</label>
                    <input type="text" name="phone" required placeholder="+258 84..." class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">E-mail</label>
                    <input type="email" name="email" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Especialização *</label>
                    <select name="specialization" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white">
                        <option value="Electricidade">Electricidade</option>
                        <option value="Canalização e Hidráulica">Canalização e Hidráulica</option>
                        <option value="Climatização e AVAC">Climatização e AVAC</option>
                        <option value="Construção Civil e Pintura">Construção Civil e Pintura</option>
                        <option value="Elevadores">Elevadores</option>
                        <option value="Sistemas de Segurança">Sistemas de Segurança</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Custo Horário (MZN)</label>
                    <input type="number" step="10" name="hourly_rate" value="450" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Equipa / Brigada</label>
                <select name="team_id" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white">
                    <option value="">Sem equipa fixa</option>
                    <?php foreach ($teams as $tm): ?>
                        <option value="<?= $tm['id'] ?>"><?= e($tm['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-tech').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-xs font-semibold rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 text-white text-xs font-semibold rounded-xl">Gravar Técnico</button>
            </div>
        </form>
    </div>
</div>
