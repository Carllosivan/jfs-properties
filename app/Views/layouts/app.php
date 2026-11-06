<!DOCTYPE html>
<html lang="pt" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'JFS Properties - Gestão Imobiliária e Manutenção') ?></title>
    <!-- Tailwind CSS CDN and Lucide Icons -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              brand: {
                50: '#f0f9ff',
                100: '#e0f2fe',
                200: '#bae6fd',
                300: '#7dd3fc',
                400: '#38bdf8',
                500: '#0ea5e9',
                600: '#0284c7',
                700: '#0369a1',
                800: '#075985',
                900: '#0c4a6e',
                950: '#082f49',
              }
            }
          }
        }
      }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-800 flex overflow-hidden">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col flex-shrink-0 border-r border-slate-800 select-none">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-5 border-b border-slate-800 bg-slate-950/60">
            <div class="w-9 h-9 rounded-lg bg-sky-600 flex items-center justify-center text-white font-black text-xl shadow-md shadow-sky-600/30 mr-3">
                J
            </div>
            <div>
                <h1 class="text-base font-bold text-white tracking-wide">JFS PROPERTIES</h1>
                <p class="text-[10px] text-sky-400 font-medium tracking-wider uppercase">Facility Management</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6 text-xs">
            <!-- VISÃO GERAL -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Visão Geral</div>
                <a href="/dashboard" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= (($_SERVER['REQUEST_URI'] ?? '') === '/dashboard' || ($_SERVER['REQUEST_URI'] ?? '') === '/') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 mr-3"></i>
                    Dashboard
                </a>
            </div>

            <!-- GESTÃO IMOBILIÁRIA -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Gestão Imobiliária</div>
                <div class="space-y-1">
                    <a href="/properties" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/properties') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="building" class="w-4 h-4 mr-3"></i>
                        Imóveis e Unidades
                    </a>
                    <a href="/occupants" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/occupants') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="users" class="w-4 h-4 mr-3"></i>
                        Ocupantes e Residentes
                    </a>
                </div>
            </div>

            <!-- OPERAÇÕES & MANUTENÇÃO -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Incidentes & Ordens</div>
                <div class="space-y-1">
                    <a href="/incidents" class="flex items-center justify-between px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/incidents') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <div class="flex items-center">
                            <i data-lucide="alert-circle" class="w-4 h-4 mr-3"></i>
                            Incidentes
                        </div>
                    </a>
                    <a href="/work-orders" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/work-orders') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="wrench" class="w-4 h-4 mr-3"></i>
                        Ordens de Serviço
                    </a>
                    <a href="/maintenance" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/maintenance') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="calendar-check" class="w-4 h-4 mr-3"></i>
                        Manutenção Preventiva
                    </a>
                </div>
            </div>

            <!-- ACTIVOS E OPERAÇÕES -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Activos & Recursos</div>
                <div class="space-y-1">
                    <a href="/assets" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/assets') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="cpu" class="w-4 h-4 mr-3"></i>
                        Equipamentos & Activos
                    </a>
                    <a href="/inspections" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/inspections') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="clipboard-check" class="w-4 h-4 mr-3"></i>
                        Inspecções & Checklists
                    </a>
                    <a href="/operations/technicians" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/operations/technicians') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="hard-hat" class="w-4 h-4 mr-3"></i>
                        Técnicos & Equipas
                    </a>
                    <a href="/operations/suppliers" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/operations/suppliers') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="truck" class="w-4 h-4 mr-3"></i>
                        Fornecedores
                    </a>
                    <a href="/operations/stock" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/operations/stock') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="package" class="w-4 h-4 mr-3"></i>
                        Materiais & Stock
                    </a>
                </div>
            </div>

            <!-- FINANCEIRO E RELATÓRIOS -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Gestão & Controlo</div>
                <div class="space-y-1">
                    <a href="/financial" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/financial') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="dollar-sign" class="w-4 h-4 mr-3"></i>
                        Custos & Orçamentos
                    </a>
                    <a href="/reports" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/reports') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="bar-chart-3" class="w-4 h-4 mr-3"></i>
                        Relatórios & Métricas
                    </a>
                </div>
            </div>

            <!-- ADMINISTRAÇÃO -->
            <div>
                <div class="px-3 mb-2 text-[10px] font-semibold tracking-wider text-slate-400 uppercase">Sistema</div>
                <div class="space-y-1">
                    <a href="/admin/users" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/users') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="shield-check" class="w-4 h-4 mr-3"></i>
                        Utilizadores & Acessos
                    </a>
                    <a href="/admin/audit" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/audit') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="history" class="w-4 h-4 mr-3"></i>
                        Auditoria & Logs
                    </a>
                    <a href="/admin/settings" class="flex items-center px-3 py-2 rounded-lg font-medium transition <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/settings') ? 'bg-sky-600 text-white shadow-sm' : 'hover:bg-slate-800 hover:text-white text-slate-300' ?>">
                        <i data-lucide="settings" class="w-4 h-4 mr-3"></i>
                        Definições da Empresa
                    </a>
                </div>
            </div>
        </nav>

        <!-- Current User Footer -->
        <div class="p-3 border-t border-slate-800 bg-slate-950/40 flex items-center justify-between">
            <div class="flex items-center min-w-0">
                <div class="w-8 h-8 rounded-full bg-sky-700 text-white flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                    <?= substr($currentUser['name'] ?? 'A', 0, 1) ?>
                </div>
                <div class="ml-2.5 truncate">
                    <p class="text-xs font-medium text-white truncate"><?= e($currentUser['name'] ?? 'Administrador') ?></p>
                    <p class="text-[10px] text-slate-400 truncate"><?= e($currentUser['role_name'] ?? 'Super Admin') ?></p>
                </div>
            </div>
            <form action="/logout" method="POST" class="ml-2">
                <?= csrf_field() ?>
                <button type="submit" title="Terminar Sessão" class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-md transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <!-- TOP HEADER -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 flex-shrink-0 z-10">
            <div class="flex items-center space-x-4">
                <div class="flex items-center text-xs text-slate-500 font-medium">
                    <span class="text-slate-400">JFS Properties</span>
                    <span class="mx-2 text-slate-300">/</span>
                    <span class="text-slate-800 font-semibold"><?= e($title ?? 'Dashboard') ?></span>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex items-center space-x-4">
                <!-- Portal Link for Quick Preview -->
                <a href="/portal" target="_blank" class="hidden sm:inline-flex items-center px-3 py-1.5 text-xs font-medium text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg border border-sky-200 transition">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 mr-1.5"></i>
                    Ver Portal Ocupantes
                </a>

                <!-- Notification Bell -->
                <div class="relative" id="notif-container">
                    <button id="notif-btn" type="button" class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg relative transition">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <span id="notif-badge" class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full ring-2 ring-white"></span>
                    </button>
                    <!-- Notification Dropdown -->
                    <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">Notificações</span>
                            <button id="mark-all-read" class="text-[11px] text-sky-600 hover:underline">Marcar lidas</button>
                        </div>
                        <div id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-slate-50 text-xs">
                            <div class="p-4 text-center text-slate-400">A carregar notificações...</div>
                        </div>
                    </div>
                </div>

                <div class="h-6 w-px bg-slate-200"></div>

                <!-- Currency & Date Indicator -->
                <div class="text-right hidden md:block">
                    <div class="text-xs font-semibold text-slate-800"><?= date('d/m/Y') ?></div>
                    <div class="text-[10px] text-slate-500 font-mono">Moeda: MZN (Metical)</div>
                </div>
            </div>
        </header>

        <!-- FLASH MESSAGES -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="bg-emerald-50 border-b border-emerald-200 px-6 py-3 flex items-center justify-between text-emerald-800 text-xs font-medium animate-fadeIn">
                <div class="flex items-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 mr-2 flex-shrink-0"></i>
                    <?= e($flashSuccess) ?>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="bg-red-50 border-b border-red-200 px-6 py-3 flex items-center justify-between text-red-800 text-xs font-medium animate-fadeIn">
                <div class="flex items-center">
                    <i data-lucide="alert-octagon" class="w-4 h-4 text-red-600 mr-2 flex-shrink-0"></i>
                    <?= e($flashError) ?>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashInfo)): ?>
            <div class="bg-sky-50 border-b border-sky-200 px-6 py-3 flex items-center justify-between text-sky-800 text-xs font-medium">
                <div class="flex items-center">
                    <i data-lucide="info" class="w-4 h-4 text-sky-600 mr-2 flex-shrink-0"></i>
                    <?= e($flashInfo) ?>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-sky-600 hover:text-sky-900">&times;</button>
            </div>
        <?php endif; ?>

        <!-- CONTENT VIEW -->
        <main class="flex-1 overflow-y-auto p-6 md:p-8 bg-slate-50">
            <?= $content ?? '' ?>
        </main>
    </div>

    <!-- Initialize Lucide Icons and Notification Fetcher -->
    <script>
        lucide.createIcons();

        // Notification handler
        const notifBtn = document.getElementById('notif-btn');
        const notifDropdown = document.getElementById('notif-dropdown');
        const notifList = document.getElementById('notif-list');
        const notifBadge = document.getElementById('notif-badge');
        const markAllBtn = document.getElementById('mark-all-read');

        if (notifBtn) {
            notifBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notifDropdown.classList.toggle('hidden');
                if (!notifDropdown.classList.contains('hidden')) {
                    fetchNotifications();
                }
            });

            document.addEventListener('click', () => {
                if (notifDropdown) notifDropdown.classList.add('hidden');
            });

            if (markAllBtn) {
                markAllBtn.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    await fetch('/api/notifications/read-all', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '<?= csrf_token() ?>' }
                    });
                    if (notifBadge) notifBadge.classList.add('hidden');
                    fetchNotifications();
                });
            }
        }

        async function fetchNotifications() {
            try {
                const res = await fetch('/api/notifications');
                const data = await res.json();
                if (data.notifications && data.notifications.length > 0) {
                    notifList.innerHTML = data.notifications.map(n => `
                        <a href="${n.link || '#'}" class="block p-3 hover:bg-slate-50 transition ${n.read_at ? 'opacity-60' : 'bg-sky-50/40'}">
                            <p class="font-semibold text-slate-800">${n.title}</p>
                            <p class="text-slate-600 text-[11px] mt-0.5">${n.message}</p>
                            <span class="text-[9px] text-slate-400 mt-1 block">${n.created_at}</span>
                        </a>
                    `).join('');
                } else {
                    notifList.innerHTML = '<div class="p-4 text-center text-slate-400 text-xs">Sem notificações no momento</div>';
                }
            } catch (err) {
                notifList.innerHTML = '<div class="p-4 text-center text-red-500 text-xs">Erro ao carregar notificações</div>';
            }
        }
    </script>
</body>
</html>
