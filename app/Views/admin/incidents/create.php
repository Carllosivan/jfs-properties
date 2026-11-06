<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="/incidents" class="text-xs text-slate-500 hover:text-slate-800 flex items-center mb-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 mr-1"></i> Voltar à Lista de Incidentes
        </a>
        <h2 class="text-xl font-bold text-slate-900">Novo Registo de Ocorrência / Incidente</h2>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
        <form action="/incidents" method="POST" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Título do Incidente / Avaria *</label>
                    <input type="text" name="title" required placeholder="Ex.: Rotura de tubo flexível de água com fuga activa"
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Categoria Técnica *</label>
                    <select name="category_id" id="category_id" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Selecione uma categoria...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Prioridade Operacional *</label>
                    <select name="priority" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="normal" selected>Normal (SLA Resolução: 24h)</option>
                        <option value="low">Baixa (SLA Resolução: 72h)</option>
                        <option value="high">Alta (SLA Resolução: 16h)</option>
                        <option value="urgent">Urgente (SLA Resolução: 8h)</option>
                        <option value="critical">Crítica (SLA Resolução: 4h)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Imóvel Afectado *</label>
                    <select name="property_id" id="property_id" required class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Selecione o imóvel...</option>
                        <?php foreach ($properties as $prop): ?>
                            <option value="<?= $prop['id'] ?>"><?= e($prop['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Fracção / Espaço (Opcional)</label>
                    <select name="unit_id" id="unit_id" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Selecione primeiro o imóvel...</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Atribuir Directamente a Técnico (Opcional)</label>
                    <select name="technician_id" class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">Nenhum técnico no momento (fica pendente de atribuição)</option>
                        <?php foreach ($technicians as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (<?= e($t['specialization']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Descrição Detalhada do Problema *</label>
                    <textarea name="description" rows="4" required placeholder="Descreva os sintomas, localização precisa e impacto nas operações ou ocupantes..."
                        class="w-full text-xs px-3 py-2.5 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500"></textarea>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end space-x-3">
                <a href="/incidents" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancelar
                </a>
                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded-xl shadow-sm transition">
                    Registar Incidente e Disparar SLA
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Dynamic Unit Fetcher Script -->
<script>
    document.getElementById('property_id').addEventListener('change', async function() {
        const propId = this.value;
        const unitSelect = document.getElementById('unit_id');
        unitSelect.innerHTML = '<option value="">A carregar fracções...</option>';

        if (!propId) {
            unitSelect.innerHTML = '<option value="">Selecione primeiro o imóvel...</option>';
            return;
        }

        try {
            const res = await fetch(`/api/units?property_id=${propId}`);
            const data = await res.json();
            if (data.units && data.units.length > 0) {
                unitSelect.innerHTML = '<option value="">Área Comum / Não especificado</option>' +
                    data.units.map(u => `<option value="${u.id}">${u.reference} - ${u.name}</option>`).join('');
            } else {
                unitSelect.innerHTML = '<option value="">Nenhuma fracção registada neste imóvel</option>';
            }
        } catch(err) {
            unitSelect.innerHTML = '<option value="">Erro ao carregar fracções</option>';
        }
    });
</script>
