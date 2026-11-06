<div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-2xl backdrop-blur-xl">
    <div class="text-center mb-8">
        <div class="w-14 h-14 bg-sky-600 rounded-2xl flex items-center justify-center text-white font-black text-2xl mx-auto mb-4 shadow-lg shadow-sky-600/30">
            J
        </div>
        <h2 class="text-2xl font-bold tracking-tight text-white">JFS PROPERTIES</h2>
        <p class="text-xs text-sky-400 mt-1 font-medium">Gestão Integrada de Imóveis e Manutenção</p>
    </div>

    <?php if (!empty($flashError)): ?>
        <div class="mb-5 p-3 rounded-lg bg-red-950/60 border border-red-800 text-red-200 text-xs flex items-center">
            <i data-lucide="alert-circle" class="w-4 h-4 mr-2 flex-shrink-0 text-red-400"></i>
            <?= e($flashError) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashSuccess)): ?>
        <div class="mb-5 p-3 rounded-lg bg-emerald-950/60 border border-emerald-800 text-emerald-200 text-xs flex items-center">
            <i data-lucide="check-circle-2" class="w-4 h-4 mr-2 flex-shrink-0 text-emerald-400"></i>
            <?= e($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <form action="/login" method="POST" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Endereço de E-mail</label>
            <div class="relative">
                <i data-lucide="mail" class="w-4 h-4 text-slate-500 absolute left-3 top-3"></i>
                <input type="email" id="email" name="email" required autofocus
                    placeholder="exemplo@jfsproperties.co.mz"
                    class="w-full pl-9 pr-4 py-2.5 bg-slate-950 border border-slate-750 text-white rounded-xl text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs font-semibold text-slate-300">Palavra-passe</label>
                <a href="/forgot-password" class="text-xs text-sky-400 hover:text-sky-300 transition">Esqueceu-se?</a>
            </div>
            <div class="relative">
                <i data-lucide="lock" class="w-4 h-4 text-slate-500 absolute left-3 top-3"></i>
                <input type="password" id="password" name="password" required
                    placeholder="••••••••"
                    class="w-full pl-9 pr-4 py-2.5 bg-slate-950 border border-slate-750 text-white rounded-xl text-sm placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
            </div>
        </div>

        <button type="submit" class="w-full py-3 px-4 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-sky-600/30 transition duration-150">
            Entrar na Plataforma
        </button>
    </form>

    <!-- Quick Demo Access Pills -->
    <div class="mt-8 pt-6 border-t border-slate-800 text-center">
        <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wider mb-3">Acesso Rápido de Teste</p>
        <div class="grid grid-cols-3 gap-2">
            <button type="button" onclick="fillCreds('admin@jfsproperties.co.mz', 'Admin123!')" class="px-2 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-300 rounded-lg text-[11px] font-medium transition border border-slate-700">
                Administrador
            </button>
            <button type="button" onclick="fillCreds('carlos.tecnico@jfsproperties.co.mz', 'Admin123!')" class="px-2 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-300 rounded-lg text-[11px] font-medium transition border border-slate-700">
                Técnico
            </button>
            <button type="button" onclick="fillCreds('ana.cossa@gmail.com', 'Admin123!')" class="px-2 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-300 rounded-lg text-[11px] font-medium transition border border-slate-700">
                Ocupante
            </button>
        </div>
    </div>
</div>

<script>
    function fillCreds(email, pass) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = pass;
    }
</script>
