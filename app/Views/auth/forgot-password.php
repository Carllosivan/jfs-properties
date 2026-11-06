<div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-2xl backdrop-blur-xl">
    <div class="text-center mb-6">
        <h2 class="text-xl font-bold tracking-tight text-white">Recuperação de Acesso</h2>
        <p class="text-xs text-slate-400 mt-1">Introduza o e-mail associado à sua conta</p>
    </div>

    <form action="/forgot-password" method="POST" class="space-y-4">
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

        <button type="submit" class="w-full py-2.5 px-4 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-sm font-semibold shadow-md transition">
            Enviar Instruções
        </button>

        <div class="text-center mt-4">
            <a href="/login" class="text-xs text-slate-400 hover:text-white transition">← Voltar ao início de sessão</a>
        </div>
    </form>
</div>
