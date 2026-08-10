<div class="space-y-6">
    <!-- Título y Acciones Principal -->
    <div class="bg-white p-6 rounded-xl border border-brand-mint shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <!-- Título Principal -->
            <h1 class="text-3xl font-extrabold text-brand-dark">
                Fixture: <?= htmlspecialchars($torneo['nombre'] ?? '') ?>
            </h1>
            <!-- Subtítulo -->
            <p class="text-brand-emerald font-semibold text-sm">Formato: <?= ucfirst(htmlspecialchars($torneo['formato'] ?? '')) ?></p>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <!-- Botones de navegación con estilos claramente diferenciados -->
            <a href="/torneos/<?= $torneo['id'] ?>/tabla" class="bg-brand-deep hover:bg-brand-emerald text-brand-mint font-bold px-4 py-2 rounded-lg text-sm transition shadow-sm">
                📊 Ver Tabla
            </a>
            <a href="/dashboard" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold px-4 py-2 rounded-lg text-sm transition">
                ← Dashboard
            </a>
        </div>
    </div>

    <?php if (empty($partidos)): ?>
        <div class="bg-white p-6 rounded-xl border border-brand-mint text-center">
            <p class="text-slate-600 mb-4">Aún no se han generado los partidos para este torneo.</p>
            <form action="/torneos/<?= $torneo['id'] ?>/fixture/generar" method="POST">
                <button type="submit" class="bg-brand-deep hover:bg-brand-emerald text-white font-bold px-6 py-2.5 rounded-lg text-sm transition">
                    🔄 Generar Fixture Ahora
                </button>
            </form>
        </div>
    <?php else: ?>
        <?php
        $jornadas = [];
        foreach ($partidos as $partido) {
            $numFecha = $partido['fecha_numero'] ?? 1;
            $jornadas[$numFecha][] = $partido;
        }
        ?>

        <div class="space-y-6">
            <?php foreach ($jornadas as $numJornada => $listaPartidos): ?>
                <div class="bg-white rounded-xl border border-brand-mint shadow-sm overflow-hidden">
                    <!-- Subtítulo de Jornada -->
                    <div class="bg-brand-dark px-5 py-3 border-b border-brand-deep">
                        <h3 class="text-lg font-bold text-brand-mint">Jornada / Fecha <?= $numJornada ?></h3>
                    </div>

                    <div class="p-4 divide-y divide-slate-100">
                        <?php foreach ($listaPartidos as $p): ?>
                            <div class="py-3 flex flex-col md:flex-row items-center justify-between gap-4">
                                <?php if (empty($p['visitante_nombre']) || empty($p['id_equipo_visitante'])): ?>
                                    <!-- Fecha Libre -->
                                    <div class="w-full text-center py-2 bg-brand-light rounded-lg border border-brand-mint">
                                        <span class="text-brand-emerald font-bold">🟢 <?= htmlspecialchars($p['local_nombre']) ?></span>
                                        <span class="text-slate-500 text-sm ml-2 font-medium">(Fecha Libre)</span>
                                    </div>
                                <?php else: ?>
                                    <!-- Equipo Local (Destacado) -->
                                    <div class="flex-1 text-center md:text-right font-extrabold text-slate-800 text-base">
                                        <?= htmlspecialchars($p['local_nombre']) ?>
                                    </div>

                                    <!-- Formulario de Resultado / Marcador -->
                                    <form action="/partidos/<?= $p['id'] ?>/resultado" method="POST" class="flex items-center gap-2 bg-slate-100 p-2 rounded-lg border border-slate-200">
                                        <input type="number" name="goles_local" min="0" value="<?= $p['goles_local'] ?? '' ?>" required
                                               class="w-12 h-9 text-center font-bold bg-white border border-slate-300 rounded focus:ring-2 focus:ring-brand-emerald outline-none">
                                        <span class="font-bold text-slate-400">-</span>
                                        <input type="number" name="goles_visitante" min="0" value="<?= $p['goles_visitante'] ?? '' ?>" required
                                               class="w-12 h-9 text-center font-bold bg-white border border-slate-300 rounded focus:ring-2 focus:ring-brand-emerald outline-none">
                                        <button type="submit" class="bg-brand-emerald hover:bg-brand-medium text-white font-bold text-xs px-3 py-2 rounded transition">
                                            Guardar
                                        </button>
                                    </form>

                                    <!-- Equipo Visitante (Destacado) -->
                                    <div class="flex-1 text-center md:text-left font-extrabold text-slate-800 text-base">
                                        <?= htmlspecialchars($p['visitante_nombre']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>