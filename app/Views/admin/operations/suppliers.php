<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-slate-900">Fornecedores & Prestadores Externos</h2>
        <p class="text-xs text-slate-500 mt-0.5">Empresas subcontratadas, contratos de manutenção especializada e avaliação de serviços.</p>
    </div>
    <button onclick="document.getElementById('modal-sup').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-xl text-xs font-semibold shadow-sm transition">
        <i data-lucide="plus" class="w-4 h-4 mr-1.5"></i>
        Novo Fornecedor
    </button>
</div>

<!-- SUPPLIERS GRID -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($suppliers as $s): ?>
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between hover:border-sky-300 transition">
            <div>
                <div class="flex items-start justify-between mb-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                        NUIT: <?= e($s['nuit'] ?: 'N/D') ?>
                    </span>
                    <!-- Star Rating Badge -->
                    <div class="flex items-center text-amber-500 text-xs font-bold">
                        <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400 mr-1"></i>
                        <?= number_format($s['rating'], 1) ?>
                    </div>
                </div>

                <h3 class="font-bold text-slate-900 text-sm mt-1"><?= e($s['name']) ?></h3>
                <?php if ($s['trade_name']): ?>
                    <span class="text-slate-400 text-xs font-medium block">(<?= e($s['trade_name']) ?>)</span>
                <?php endif; ?>

                <div class="mt-4 p-3 bg-slate-50 rounded-xl space-y-2 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Tipo de Serviço</span>
                        <span class="font-semibold text-slate-800"><?= e($s['service_type']) ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Contacto Comercial</span>
                        <span class="text-slate-700"><?= e($s['contact_person'] ?: '-') ?></span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Telefone & Email</span>
                        <span class="text-slate-900 font-medium block"><?= e($s['phone']) ?></span>
                        <span class="text-slate-500 text-[11px] block"><?= e($s['email']) ?></span>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-400 text-[11px] truncate"><?= e($s['address']) ?></span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700">Activo</span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal: New Supplier -->
<div id="modal-sup" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-bold text-slate-900">Novo Fornecedor Especializado</h3>
            <button onclick="document.getElementById('modal-sup').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">&times;</button>
        </div>
        <form action="/operations/suppliers" method="POST" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Razão Social / Nome da Empresa *</label>
                <input type="text" name="name" required placeholder="Ex.: Schindler Moçambique, Lda." class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nome Comercial</label>
                    <input type="text" name="trade_name" placeholder="Ex.: Schindler" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">NUIT</label>
                    <input type="text" name="nuit" placeholder="400123987" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Especialidade / Ramo de Actividade *</label>
                <input type="text" name="service_type" required placeholder="Ex.: Manutenção Preventiva de Ascensores" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Telefone Principal *</label>
                    <input type="text" name="phone" required placeholder="+258 21..." class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">E-mail Comercial</label>
                    <input type="email" name="email" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pessoa de Contacto</label>
                <input type="text" name="contact_person" placeholder="Nome do gestor de conta..." class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Endereço da Empresa</label>
                <input type="text" name="address" placeholder="Av. 25 de Setembro, Maputo" class="w-full text-xs p-2.5 border border-slate-200 rounded-xl">
            </div>
            <div class="pt-3 flex justify-end space-x-2">
                <button type="button" onclick="document.getElementById('modal-sup').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-xs font-semibold rounded-xl">Cancelar</button>
                <button type="submit" class="px-5 py-2 bg-sky-600 text-white text-xs font-semibold rounded-xl">Gravar Fornecedor</button>
            </div>
        </form>
    </div>
</div>
