<div class="space-y-6">
    <!-- Encabezado de Sección -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-6 rounded-xl border border-brand-mint shadow-sm">
        <div>
            <h1 class="text-3xl font-extrabold text-brand-dark tracking-tight">Mi Panel</h1>
            <p class="text-brand-emerald font-medium text-sm mt-1">Bienvenido/a, <?= htmlspecialchars($nombre) ?></p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Botón: Unirse a un Equipo -->
            <a href="/equipos/unirse" class="inline-flex items-center justify-center gap-2 bg-brand-emerald hover:bg-brand-medium text-white px-5 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg transition flex-1 md:flex-none">
                🔑 Unirse a un Equipo
            </a>

            <!-- Botón: Crear Nuevo Torneo -->
            <a href="/torneos/create" class="inline-flex items-center justify-center gap-2 bg-brand-deep hover:bg-brand-emerald text-white px-5 py-2.5 rounded-lg font-bold shadow-md hover:shadow-lg transition flex-1 md:flex-none">
                ➕ Crear Nuevo Torneo
            </a>
        </div>
    </div>

    <!-- 🟢 SECCIÓN: MIS EQUIPOS (JUGADOR) -->
    <?php if (!empty($misEquipos)): ?>
        <div class="bg-white p-6 rounded-xl border border-brand-mint shadow-sm">
            <h2 class="text-xl font-bold text-brand-dark mb-4 flex items-center gap-2">
                ⚽ Mis Equipos como Jugador
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($misEquipos as $eq): ?>
                    <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg flex flex-col justify-between gap-3">
                        <div>
                            <span class="text-xs font-bold text-brand-emerald uppercase tracking-wider block mb-1">
                                Torneo: <?= htmlspecialchars($eq['torneo_nombre']) ?>
                            </span>
                            <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($eq['nombre']) ?></h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="/equipos/<?= $eq['id'] ?>/mi-equipo" class="flex-1 text-center bg-brand-emerald hover:bg-brand-medium text-white text-xs font-bold py-2 rounded transition">
                                👥 Ver Plantilla
                            </a>
                            <form action="/equipos/<?= $eq['id'] ?>/salir" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas salir de este equipo?');">
                                <button type="submit" class="text-rose-600 hover:text-rose-800 hover:bg-rose-100 p-2 rounded text-xs font-bold transition" title="Salir del equipo">
                                    🚪 Salir
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Lista de Torneos Creados (Organizador) -->
    <h2 class="text-xl font-bold text-brand-dark">Mis Torneos Administrados</h2>
    <?php if (empty($torneos)): ?>
        <div class="bg-white p-8 rounded-xl border border-dashed border-brand-medium text-center">
            <p class="text-slate-600 font-medium">Aún no has creado ningún torneo.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($torneos as $torneo): ?>
                <div class="bg-white rounded-xl border border-brand-mint shadow-sm hover:shadow-md transition flex flex-col justify-between overflow-hidden">
                    <div class="p-5">
                        <div class="flex justify-between items-start gap-2 mb-3">
                            <div class="flex flex-wrap gap-1.5">
                                <span class="bg-brand-mint/40 text-brand-dark font-bold text-xs uppercase px-2.5 py-1 rounded-md border border-brand-medium/30">
                                    <?= htmlspecialchars($torneo['formato']) ?>
                                </span>
                                <?php 
                                    $estado = $torneo['estado'] ?? 'pendiente';
                                    $badgeColor = match($estado) {
                                        'en_curso' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'finalizado' => 'bg-slate-200 text-slate-700 border-slate-300',
                                        default => 'bg-amber-100 text-amber-800 border-amber-300',
                                    };
                                    $estadoLabel = match($estado) {
                                        'en_curso' => 'En curso',
                                        'finalizado' => 'Finalizado',
                                        default => 'Pendiente',
                                    };
                                ?>
                                <span class="font-bold text-xs uppercase px-2.5 py-1 rounded-md border <?= $badgeColor ?>">
                                    <?= $estadoLabel ?>
                                </span>
                            </div>

                            <form action="/torneos/<?= $torneo['id'] ?>/delete" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este torneo?');">
                                <button type="submit" class="text-rose-500 hover:text-rose-700 p-1.5 rounded transition font-bold text-xs">🗑️</button>
                            </form>
                        </div>

                        <h2 class="text-xl font-bold text-brand-dark tracking-tight mb-2">
                            <?= htmlspecialchars($torneo['nombre']) ?>
                        </h2>

                        <p class="text-sm text-slate-600 font-medium mt-3 flex items-center gap-1.5">
                            👥 <span><strong><?= (int)($torneo['cantidad_equipos'] ?? 0) ?></strong> equipos inscriptos</span>
                        </p>
                    </div>

                    <div class="bg-slate-50 p-4 border-t border-slate-100 flex flex-wrap gap-2">
                        <a href="/torneos/<?= $torneo['id'] ?>/equipos" class="flex-1 text-center bg-brand-deep hover:bg-brand-emerald text-white font-semibold py-2 px-3 rounded-md text-xs transition">
                            👥 Equipos
                        </a>
                        <a href="/torneos/<?= $torneo['id'] ?>/fixture" class="flex-1 text-center bg-brand-emerald hover:bg-brand-medium text-white font-semibold py-2 px-3 rounded-md text-xs transition">
                            📅 Fixture
                        </a>
                        <?php if ($torneo['formato'] === 'liga'): ?>
                            <a href="/torneos/<?= $torneo['id'] ?>/tabla" class="flex-1 text-center bg-brand-dark hover:bg-slate-800 text-brand-mint font-semibold py-2 px-3 rounded-md text-xs transition">
                                📊 Tabla
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>