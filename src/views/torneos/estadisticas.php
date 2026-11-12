<div class="max-w-5xl mx-auto my-8 px-4">
    <!-- Encabezado y navegación del torneo -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded">
                Estadísticas Individuales
            </span>
            <h1 class="text-2xl font-bold text-brand-dark mt-1.5">
                <?= htmlspecialchars($torneo['nombre']) ?>
            </h1>
            <p class="text-xs text-slate-500">
                Tabla de goleadores, asistidores, efectividad y disciplina de partidos finalizados.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="/torneos/<?= (int)$torneo['id'] ?>/fixture"
               class="px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                Fixture
            </a>
            <a href="/torneos/<?= (int)$torneo['id'] ?>/tabla"
               class="px-3.5 py-2 bg-white border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                Tabla de Posiciones
            </a>
            <a href="/dashboard"
               class="px-3.5 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                Dashboard
            </a>
        </div>
    </div>

    <!-- Contenedor de Tabla -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <?php if (empty($estadisticas)): ?>
            <div class="p-10 text-center">
                <p class="text-sm font-semibold text-slate-700 mb-1">Todavía no hay estadísticas registradas</p>
                <p class="text-xs text-slate-500">
                    Las estadísticas aparecerán aquí una vez que los partidos estén finalizados y el organizador cargue sus planillas.
                </p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900 text-white text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4">Jugador</th>
                            <th class="py-3 px-4">Equipo</th>
                            <th class="py-3 px-3 text-center" title="Partidos Jugados">PJ</th>
                            <th class="py-3 px-3 text-center" title="Partidos Ganados">PG</th>
                            <th class="py-3 px-3 text-center" title="Partidos Perdidos">PP</th>
                            <th class="py-3 px-3 text-center bg-slate-800 text-brand-mint" title="Goles">Goles</th>
                            <th class="py-3 px-3 text-center" title="Asistencias">Asist.</th>
                            <th class="py-3 px-3 text-center" title="Tarjetas Amarillas">🟨</th>
                            <th class="py-3 px-3 text-center" title="Tarjetas Rojas">🟥</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        <?php foreach ($estadisticas as $index => $row): 
                            $pos = $index + 1;
                        ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-500">
                                    <?php if ($pos === 1): ?>
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-800 text-xs">1</span>
                                    <?php elseif ($pos === 2): ?>
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-200 text-slate-700 text-xs">2</span>
                                    <?php elseif ($pos === 3): ?>
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-orange-100 text-orange-800 text-xs">3</span>
                                    <?php else: ?>
                                        <?= $pos ?>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 font-bold text-slate-800">
                                    <?= htmlspecialchars($row['jugador_nombre']) ?>
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    <?= htmlspecialchars($row['equipo_nombre']) ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono text-slate-700">
                                    <?= (int)$row['pj'] ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono text-emerald-700">
                                    <?= (int)$row['pg'] ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono text-rose-600">
                                    <?= (int)$row['pp'] ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-extrabold text-sm text-slate-900 bg-slate-50">
                                    <?= (int)$row['goles'] ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono font-semibold text-slate-700">
                                    <?= (int)$row['asistencias'] ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono">
                                    <?php if ((int)$row['amarillas'] > 0): ?>
                                        <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 rounded font-bold">
                                            <?= (int)$row['amarillas'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-300">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-3 text-center font-mono">
                                    <?php if ((int)$row['rojas'] > 0): ?>
                                        <span class="px-1.5 py-0.5 bg-rose-100 text-rose-800 rounded font-bold">
                                            <?= (int)$row['rojas'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-300">0</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Leyenda de referencias -->
            <div class="bg-slate-50 px-4 py-2.5 border-t border-slate-200 flex flex-wrap gap-x-5 gap-y-1 text-[11px] text-slate-500">
                <span><strong>PJ:</strong> Partidos Jugados</span>
                <span><strong>PG:</strong> Partidos Ganados</span>
                <span><strong>PP:</strong> Partidos Perdidos</span>
                <span><strong>Asist.:</strong> Asistencias</span>
                <span><strong>🟨:</strong> Tarjetas Amarillas</span>
                <span><strong>🟥:</strong> Tarjetas Rojas</span>
            </div>
        <?php endif; ?>
    </div>
</div>