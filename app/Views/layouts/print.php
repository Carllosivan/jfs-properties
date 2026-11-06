<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title><?= e($title ?? 'Relatório de Serviço - JFS Properties') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background-color: #fff !important; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased p-4 md:p-8 font-sans">
    <div class="no-print max-w-4xl mx-auto mb-6 flex justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <div>
            <span class="text-sm font-semibold text-slate-800">Modo de Impressão / Emissão Oficial</span>
            <p class="text-xs text-slate-500">Documento formal de intervenção técnica com validade operacional.</p>
        </div>
        <div class="space-x-3">
            <button onclick="window.print()" class="px-4 py-2 bg-sky-600 text-white rounded-lg text-xs font-semibold hover:bg-sky-700 transition">
                Imprimir / Guardar como PDF
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg text-xs font-medium hover:bg-slate-300 transition">
                Fechar
            </button>
        </div>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-8 md:p-12 rounded-2xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0">
        <?= $content ?? '' ?>
    </div>
</body>
</html>
