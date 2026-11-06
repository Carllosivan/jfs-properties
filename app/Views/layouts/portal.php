<!DOCTYPE html>
<html lang="pt" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Portal do Ocupante - JFS Properties') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full font-sans antialiased text-slate-800 flex flex-col bg-slate-50">

    <!-- PORTAL NAV -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-sky-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                    J
                </div>
                <div>
                    <h1 class="text-sm font-bold text-slate-900 tracking-tight">JFS PROPERTIES</h1>
                    <p class="text-[10px] text-sky-600 font-semibold tracking-wide uppercase">Portal do Ocupante</p>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <span class="text-xs text-slate-600 font-medium hidden sm:inline">
                    Olá, <strong class="text-slate-900"><?= e($currentUser['name'] ?? 'Ocupante') ?></strong>
                </span>
                <form action="/logout" method="POST" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-red-600 hover:bg-slate-100 rounded-lg transition">
                        <i data-lucide="log-out" class="w-4 h-4 mr-1.5"></i>
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- FLASH NOTICES -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 w-full mt-4">
            <div class="bg-emerald-50 border border-emerald-200 px-4 py-3 rounded-xl flex items-center text-emerald-800 text-xs font-medium">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 mr-2 flex-shrink-0"></i>
                <?= e($flashSuccess) ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 w-full mt-4">
            <div class="bg-red-50 border border-red-200 px-4 py-3 rounded-xl flex items-center text-red-800 text-xs font-medium">
                <i data-lucide="alert-octagon" class="w-4 h-4 text-red-600 mr-2 flex-shrink-0"></i>
                <?= e($flashError) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- MAIN BODY -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <?= $content ?? '' ?>
    </main>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
        <div class="max-w-6xl mx-auto px-4 text-center text-xs text-slate-400">
            &copy; <?= date('Y') ?> JFS Properties - Suporte e Gestão de Manutenção. Linha de Apoio: +258 84 123 4567
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
