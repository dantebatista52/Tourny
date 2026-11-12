<div class="max-w-5xl mx-auto my-8 px-4">
    <!-- Encabezado y botón volver -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-brand-mint bg-slate-900 px-2.5 py-1 rounded">
                Fecha <?= htmlspecialchars($partido['fecha_numero']) ?> • <?= htmlspecialchars($partido['torneo_nombre']) ?>
            </span>
            <h1 class="text-2xl font-bold text-brand-dark mt-2">
                Planilla de Partido
            </h1>
            <p class="text-sm text-slate-600">
                <strong class="text-slate-900"><?= htmlspecialchars($partido['local_nombre'] ?? 'Local') ?></strong>
                <span class="mx-2 px-2 py-0.5 bg-slate-100 rounded font-mono font-bold text-slate-800">
                    <?= $partido['goles_local'] !== null ? (int)$partido['goles_local'] : '-' ?>
                    :
                    <?= $partido['goles_visitante'] !== null ? (int)$partido['goles_visitante'] : '-' ?>
                </span>
                <strong class="text-slate-900"><?= htmlspecialchars($partido['visitante_nombre'] ?? 'Visitante') ?></strong>
            </p>
        </div>

        <a href="/torneos/<?= (int)$partido['id_torneo'] ?>/fixture"
           class="inline-flex items-center justify-center px-4 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
            ← Volver al Fixture
        </a>
    </div>

    <!-- Mensajes Flash -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg">
            <?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <form action="/partidos/<?= (int)$partido['id'] ?>/planilla" method="POST" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- EQUIPO LOCAL -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between">
                    <h2 class="font-bold text-sm uppercase tracking-wide">
                        <?= htmlspecialchars($partido['local_nombre'] ?? 'Equipo Local') ?>
                    </h2>
                    <span class="text-[11px] bg-white/10 px-2 py-0.5 rounded text-slate-200">Local</span>
                </div>

                <?php if (empty($jugadoresLocal)): ?>
                    <div class="p-8 text-center flex-1 flex flex-col items-center justify-center bg-slate-50/50">
                        <span class="text-2xl mb-2">👥</span>
                        <p class="text-sm font-bold text-slate-700">No hay jugadores en este equipo</p>
                        <p class="text-xs text-slate-500 mt-1 max-w-xs">
                            Aún no se ha fichado ningún jugador en <strong><?= htmlspecialchars($partido['local_nombre'] ?? 'el equipo local') ?></strong>. Comparte su código de invitación para habilitar la carga de estadísticas.
                        </p>
                        <a href="/torneos/<?= (int)$partido['id_torneo'] ?>/equipos"
                           class="mt-4 inline-flex items-center px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100 transition shadow-sm">
                            Ver código de invitación →
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase text-slate-500">
                                    <th class="py-2.5 px-3">Jugador</th>
                                    <th class="py-2.5 px-2 text-center w-14" title="Goles">⚽</th>
                                    <th class="py-2.5 px-2 text-center w-14" title="Asistencias">👟</th>
                                    <th class="py-2.5 px-2 text-center w-14" title="Tarjetas Amarillas">🟨</th>
                                    <th class="py-2.5 px-2 text-center w-14" title="Tarjetas Rojas">🟥</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php foreach ($jugadoresLocal as $jug): 
                                    $uid = (int)$jug['id'];
                                    $st = $statsGuardadas[$uid] ?? [];
                                ?>
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="py-2.5 px-3">
                                            <input type="hidden" name="stats[<?= $uid ?>][id_equipo]" value="<?= (int)$partido['id_equipo_local'] ?>">
                                            <div class="font-semibold text-slate-800">
                                                <?php if (!empty($jug['dorsal'])): ?>
                                                    <span class="inline-block w-5 text-center font-mono text-[11px] bg-slate-200 text-slate-700 rounded mr-1">
                                                        <?= (int)$jug['dorsal'] ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($jug['nombre']) ?>
                                            </div>
                                            <?php if (!empty($jug['posicion'])): ?>
                                                <span class="text-[10px] text-slate-400"><?= htmlspecialchars($jug['posicion']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2 px-1.5 text-center">
                                            <input type="number" min="0" name="stats[<?= $uid ?>][goles]"
                                                   value="<?= (int)($st['goles'] ?? 0) ?>"
                                                   class="w-12 px-1 py-1 text-center border border-slate-300 rounded font-mono text-xs focus:outline-none focus:border-brand-mint">
                                        </td>
                                        <td class="py-2 px-1.5 text-center">
                                            <input type="number" min="0" name="stats[<?= $uid ?>][asistencias]"
                                                   value="<?= (int)($st['asistencias'] ?? 0) ?>"
                                                   class="w-12 px-1 py-1 text-center border border-slate-300 rounded font-mono text-xs focus:outline-none focus:border-brand-mint">
                                        </td>
                                        <td class="py-2 px-1.5 text-center">
                                            <input type="number" min="0" max="2" name="stats[<?= $uid ?>][amarillas]"
                                                   value="<?= (int)($st['amarillas'] ?? 0) ?>"
                                                   class="w-12 px-1 py-1 text-center border border-slate-300 rounded font-mono text-xs focus:outline-none focus:border-amber-400">
                                        </td>
                                        <td class="py-2 px-1.5 text-center">
                                            <input type="number" min="0" max="1" name="stats[<?= $uid ?>][rojas]"
                                                   value="<?= (int)($st['rojas'] ?? 0) ?>"
                                                   class="w-12 px-1 py-1 text-center border border-slate-300 rounded font-mono text-xs focus:outline-none focus:border-rose-500">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- EQUIPO VISITANTE -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
                <div class="bg-slate-800 text-white px-4 py-3 flex items-center justify-between">
                    <h2 class="font-bold text-sm uppercase tracking-wide">
                        <?= htmlspecialchars($partido['visitante_nombre'] ?? 'Equipo Visitante') ?>
                    </h2>
                    <span class="text-[11px] bg-white/10 px-2 py-0.5 rounded text-slate-200">Visitante</span>
                </div>

                <?php if (empty($jugadoresVisitante)): ?>
                    <div class="p-8 text-center flex-1 flex flex-col items-center justify-center bg-slate-50/50">
                        <span class="text-2xl mb-2">👥</span>
                        <p class="text-sm font-bold text-slate-700">No hay jugadores en este equipo</p>
                        <p class="text-xs text-slate-500 mt-1 max-w-xs">
                            Aún no se ha fichado ningún jugador en <strong><?= htmlspecialchars($partido['visitante_nombre'] ?? 'el equipo visitante') ?></strong>. Comparte su código de invitación para habilitar la carga de estadísticas.
                        </p>
                        <a href="/torneos/<?= (int)$partido['id_torneo'] ?>/equipos"
                           class="mt-4 inline-flex items-center px-3 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100 transition shadow-sm">
                            Ver código de invitación →
                        </a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase text-slate-500">
                                    <th class="py-2.5 px-3">Jugador</th>
                                    <th class="py-2.5 px-2 text-center w-14" title="Goles">⚽</th>
                                    <th class="py-2.5 px-2 text-center w-14" title="Asistencias">👟</th>
                                    <th class="py-2.5 px-2 text-center w-14" title="Tarjetas Amarillas">🟨</th>
                                    <th class="py-2.5 px-2 text-center w-14" title="Tarjetas Rojas">🟥</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                <?php foreach ($jugadoresVisitante as $jug): 
                                    $uid = (int)$jug['id'];
                                    $st = $statsGuardadas[$uid] ?? [];
                                ?>
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="py-2.5 px-3">
                                            <input type="hidden" name="stats[<?= $uid ?>][id_equipo]" value="<?= (int)$partido['id_equipo_visitante'] ?>">
                                            <div class="font-semibold text-slate-800">
                                                <?php if (!empty($jug['dorsal'])): ?>
                                                    <span class="inline-block w-5 text-center font-mono text-[11px] bg-slate-200 text-slate-700 rounded mr-1">
                                                        <?= (int)$jug['dorsal'] ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($jug['nombre']) ?>
                                            </div>
                                            <?php if (!empty($jug['posicion'])): ?>
                                                <span class="text-[10px] text-slate-400"><?= htmlspecialchars($jug['posicion']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-2 px-1.5 text-center">
                                            <input type="number" min="0" name="stats[<?= $uid ?>][goles]"
                                                   value="<?= (int)($st['goles'] ?? 0) ?>"
                                                   class="w-12 px-1 py-1 text-center border border-slate-300 rounded font-mono text-xs focus:outline-none focus:border-brand-mint">
                                        </td>
                                        <td class="py-2 px-1.5 text-center">
                                            <input type="number" min="0" name="stats[<?= $uid ?>][asistencias]"
                                                   value="<?= (int)($st['asistencias'] ?? 0) ?>"
                                                   class="w-12 px-1 py-1 text-center border border-slate-300 rounded font-mono text-xs focus:outline-none focus:border-brand-mint">
                                        </td>
                                        <td class="py-2 px-1.5 text-center">
                                            <input type="number" min="0" max="2" name="stats[<?= $uid ?>][amarillas]"
                                                   value="<?= (int)($st['amarillas'] ?? 0) ?>"
                                                   class="w-12 px-1 py-1 text-center border border-slate-300 rounded font-mono text-xs focus:outline-none focus:border-amber-400">
                                        </td>
                                        <td class="py-2 px-1.5 text-center">
                                            <input type="number" min="0" max="1" name="stats[<?= $uid ?>][rojas]"
                                                   value="<?= (int)($st['rojas'] ?? 0) ?>"
                                                   class="w-12 px-1 py-1 text-center border border-slate-300 rounded font-mono text-xs focus:outline-none focus:border-rose-500">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botón de Guardado -->
        <?php if (!empty($jugadoresLocal) || !empty($jugadoresVisitante)): ?>
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="/torneos/<?= (int)$partido['id_torneo'] ?>/fixture"
                   class="px-5 py-2.5 bg-slate-100 text-slate-700 font-semibold text-xs rounded-lg hover:bg-slate-200 transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-brand-mint text-slate-900 font-bold text-sm rounded-lg shadow-sm hover:bg-opacity-90 transition">
                    Guardar Planilla de Estadísticas
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>