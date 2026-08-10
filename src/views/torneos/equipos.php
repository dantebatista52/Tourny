<div class="max-w-4xl mx-auto space-y-6">
    <!-- Encabezado con datos del Torneo -->
    <div class="bg-white p-6 rounded-xl border border-brand-mint shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <a href="/dashboard" class="inline-flex items-center gap-2 text-brand-emerald hover:text-brand-deep font-semibold text-sm transition mb-2">
                ← Volver al Dashboard
            </a>
            <h1 class="text-2xl font-extrabold text-brand-dark">
                Gestionar Equipos: <span class="text-brand-emerald"><?= htmlspecialchars($torneo['nombre']) ?></span>
            </h1>
            <p class="text-slate-500 text-sm mt-0.5">
                Formato seleccionado: <strong class="text-brand-dark uppercase text-xs tracking-wider bg-brand-mint/40 px-2 py-0.5 rounded border border-brand-medium/30"><?= htmlspecialchars($torneo['formato']) ?></strong>
            </p>
        </div>
    </div>

    <!-- Seccion Años / Formulario Añadir Equipo -->
    <div class="bg-white p-6 rounded-xl border border-brand-mint shadow-sm">
        <h2 class="text-lg font-bold text-brand-dark mb-4">Añadir Nuevo Equipo</h2>
        <form action="/torneos/<?= $torneo['id'] ?>/equipos" method="POST" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="nombre_equipo" placeholder="Ej: Boca Juniors" required
                   class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-emerald focus:bg-white transition">
            <button type="submit" class="bg-brand-deep hover:bg-brand-emerald text-white font-bold px-6 py-2.5 rounded-lg shadow transition">
                ➕ Agregar
            </button>
        </form>
    </div>

    <!-- Lista de Equipos Registrados -->
    <div class="bg-white p-6 rounded-xl border border-brand-mint shadow-sm space-y-4">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h2 class="text-lg font-bold text-brand-dark">
                Equipos Registrados <span class="text-brand-emerald text-sm">(<?= count($equipos) ?>)</span>
            </h2>
        </div>

        <?php if (empty($equipos)): ?>
            <p class="text-slate-500 text-sm italic text-center py-4">Todavía no hay equipos anotados en este torneo.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                <?php foreach ($equipos as $equipo): ?>
                    <!-- Cajita individual para cada equipo -->
                    <div class="flex justify-between items-center bg-slate-50 border border-slate-200 p-3 rounded-lg hover:border-brand-medium transition">
                        <span class="font-extrabold text-slate-800 text-sm">
                            <?= htmlspecialchars($equipo['nombre']) ?>
                        </span>
                        
                        <!-- Formulario/Botón para eliminar equipo -->
                        <form action="/torneos/<?= $torneo['id'] ?>/equipos/<?= $equipo['id'] ?>/eliminar" method="POST" onsubmit="return confirm('¿Seguro deseas eliminar este equipo?');">
                            <button type="submit" class="text-rose-600 hover:text-rose-800 hover:bg-rose-50 p-1.5 rounded transition text-xs font-bold" title="Eliminar equipo">
                                🗑️
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Panel Acciones de Fixture -->
    <div class="bg-brand-dark p-6 rounded-xl shadow-md border border-brand-deep flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="text-brand-mint font-bold text-lg">Generar Fixture / Calendario</h3>
            <p class="text-slate-400 text-xs mt-0.5">Asegúrate de haber cargado todos los equipos antes de continuar.</p>
        </div>
        
        <div class="flex flex-wrap gap-3 w-full sm:w-auto">
            <a href="/torneos/<?= $torneo['id'] ?>/fixture" class="flex-1 sm:flex-none text-center bg-brand-emerald hover:bg-brand-medium text-white font-bold px-5 py-2.5 rounded-lg text-sm transition shadow">
                👁️ Ver Fixture
            </a>
            
            <form action="/torneos/<?= $torneo['id'] ?>/fixture/generar" method="POST" class="flex-1 sm:flex-none">
                <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold px-5 py-2.5 rounded-lg text-sm transition shadow">
                    🔄 Regenerar Fixture
                </button>
            </form>
        </div>
    </div>
</div>