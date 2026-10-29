<div class="max-w-4xl mx-auto space-y-8">
    <!-- Título del Equipo y Botón para Salir -->
    <div class="bg-white p-6 rounded-xl border border-brand-mint shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-brand-dark"><?= htmlspecialchars($equipo['nombre']) ?></h1>
            <p class="text-sm text-slate-500">Plantilla de Jugadores</p>
        </div>

        <div class="flex items-center gap-3">
            <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-3 py-1.5 rounded-full">
                🔑 Código: <?= htmlspecialchars($equipo['codigo_invitacion']) ?>
            </span>

            <!-- 🟢 Botón: Salir del Equipo -->
            <form action="/equipos/<?= $equipo['id'] ?>/salir" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas abandonar este equipo?');">
                <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-bold px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                    🚪 Salir del equipo
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- FORMULARIO: Mis Datos en el Equipo -->
        <div class="bg-white p-6 rounded-xl border border-brand-mint shadow-sm h-fit">
            <h2 class="text-lg font-bold text-brand-dark mb-4">Mis Datos de Jugador</h2>
            <form action="/equipos/<?= $equipo['id'] ?>/mi-equipo/perfil" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dorsal / Número</label>
                    <input type="number" name="dorsal" min="1" max="99" 
                           value="<?= htmlspecialchars($miFichaje['dorsal'] ?? '') ?>" 
                           placeholder="Ej: 10" 
                           class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-emerald">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Posición</label>
                    <select name="posicion" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-brand-emerald">
                        <option value="">-- Seleccionar --</option>
                        <option value="Arquero" <?= ($miFichaje['posicion'] ?? '') === 'Arquero' ? 'selected' : '' ?>>Arquero</option>
                        <option value="Defensor" <?= ($miFichaje['posicion'] ?? '') === 'Defensor' ? 'selected' : '' ?>>Defensor</option>
                        <option value="Mediocampista" <?= ($miFichaje['posicion'] ?? '') === 'Mediocampista' ? 'selected' : '' ?>>Mediocampista</option>
                        <option value="Delantero" <?= ($miFichaje['posicion'] ?? '') === 'Delantero' ? 'selected' : '' ?>>Delantero</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-brand-emerald hover:bg-brand-medium text-white font-bold py-2.5 rounded-lg transition">
                    Guardar Mis Datos
                </button>
            </form>
        </div>

        <!-- TABLA: Lista de Jugadores Inscriptos -->
        <div class="md:col-span-2 bg-white p-6 rounded-xl border border-brand-mint shadow-sm">
            <h2 class="text-lg font-bold text-brand-dark mb-4">Compañeros de Equipo</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs text-slate-500 uppercase">
                            <th class="py-2.5">Dorsal</th>
                            <th class="py-2.5">Nombre</th>
                            <th class="py-2.5">Posición</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        <?php foreach ($jugadores as $j): ?>
                            <tr>
                                <td class="py-3 font-extrabold text-brand-emerald">
                                    <?= $j['dorsal'] ? '#' . htmlspecialchars($j['dorsal']) : '-' ?>
                                </td>
                                <td class="py-3 font-semibold text-slate-800">
                                    <?= htmlspecialchars($j['nombre']) ?>
                                    <?php if ($j['id_usuario'] == $_SESSION['usuario_id']): ?>
                                        <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-normal ms-1">(Tú)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 text-slate-600">
                                    <?= htmlspecialchars($j['posicion'] ?? 'Sin definir') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>