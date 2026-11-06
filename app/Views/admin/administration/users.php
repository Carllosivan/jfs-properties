<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Utilizadores & Perfis de Acesso (RBAC)</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gestão de credenciais, funções administrativas e permissões do sistema.</p>
    </div>
    <button onclick="document.getElementById('modal-user').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="user-plus" class="w-4 h-4 mr-1.5"></i>
        Novo Utilizador
    </button>
</div>

<!-- USERS TABLE -->
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px] font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-3.5">Nome</th>
                    <th class="px-6 py-3.5">E-mail de Acesso</th>
                    <th class="px-6 py-3.5">Perfil de Função</th>
                    <th class="px-6 py-3.5">Telefone</th>
                    <th class="px-6 py-3.5">Último Acesso</th>
                    <th class="px-6 py-3.5">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-3.5 font-bold text-slate-900"><?= e($u['name']) ?></td>
                        <td class="px-6 py-3.5 font-mono text-slate-600"><?= e($u['email']) ?></td>
                        <td class="px-6 py-3.5">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase 
                                <?= $u['role_slug'] === 'super_admin' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700' ?>">
                                <?= e($u['role_name'] ?? 'Utilizador') ?>
                            </span>
                        </td>
                        <td class="px-6 py-3.5"><?= e($u['phone'] ?? '-') ?></td>
                        <td class="px-6 py-3.5 text-slate-400"><?= format_date($u['last_login_at']) ?></td>
                        <td class="px-6 py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700">
                                <?= e($u['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New User -->
<div id="modal-user" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-bold text-slate-900">Novo Utilizador da Plataforma</h3>
            <button onclick="document.getElementById('modal-user').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form action="/admin/users" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nome Completo *</label>
                <input type="text" name="name" required placeholder="Ex.: Bernardo Silva" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Endereço de E-mail *</label>
                <input type="email" name="email" required placeholder="utilizador@jfsproperties.co.mz" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Perfil / Função *</label>
                    <select name="role_id" required class="w-full text-xs p-2.5 border border-slate-200 rounded-xl bg-white">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Telefone</label>
                    <input type="text" name="phone" placeholder="+258..." class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Palavra-passe Provisória *</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-user').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-xs font-semibold rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 text-white text-xs font-semibold rounded-xl">Criar Utilizador</button>
            </div>
        </form>
    </div>
</div>
