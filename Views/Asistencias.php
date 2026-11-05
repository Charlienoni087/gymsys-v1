<?php
// Views/Asistencias.php
// Se incluye dentro de Views/Reportes.php (comparte sus estilos y variables).

/** @var array $listaClientes */
/** @var array $listaAsistencias */
/** @var bool $esAdmin */

$asE = static fn($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

$asFecha = static function (?string $f): string {
    $t = $f ? strtotime($f) : false;
    return $t ? date('d/m/Y', $t) : '—';
};

$asHora = static fn(?string $h): string => $h ? htmlspecialchars(substr($h, 0, 5), ENT_QUOTES, 'UTF-8') : '—';

$hoy   = date('Y-m-d');
$ahora = date('H:i');
?>

<section id="panelAsistencia" class="no-imprimir d-none mb-4" aria-label="Registro de asistencia">
    <div class="row g-4">

        <!-- Formulario de Registro -->
        <div class="col-12 col-lg-5">
            <div class="rep-panel h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="venc-avatar" style="width: 34px; height: 34px; border-radius: 8px; font-size: 0.9rem; background: var(--gym-primary-subtle); color: var(--gym-primary); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3 class="h5 fw-bold text-dark m-0">Registrar Entrada / Salida</h3>
                </div>
                <p class="text-muted small mb-3">
                    Deja la hora de salida en blanco si el cliente se encuentra entrenando actualmente; podrás marcarla luego.
                </p>

                <?php if (empty($listaClientes)): ?>
                    <div class="alert alert-warning small py-2 px-3 rounded-3" style="background-color: var(--gym-champagne-light); color: var(--gym-champagne-dark); border: 1px solid #E8D5AC;">
                        <i class="bi bi-info-circle me-1"></i> No hay clientes activos. Registra clientes antes de anotar asistencias.
                    </div>
                <?php else: ?>
                    <form method="POST" action="../Controllers/AsistenciasController.php" id="formAsistencia">
                        <div class="mb-3">
                            <label for="asisCliente" class="form-label fw-semibold text-dark">Cliente</label>
                            <select class="form-select formInput" id="asisCliente" name="id_cliente" required>
                                <option value="" selected disabled>Selecciona un cliente</option>
                                <?php foreach ($listaClientes as $c): ?>
                                    <option value="<?= (int) $c['id_cliente'] ?>">
                                        <?= $asE($c['nombre_cliente']) ?> (<?= $asE($c['cedula']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <label for="asisFecha" class="form-label fw-semibold text-dark">Fecha</label>
                                <input type="date" class="form-control formInput" id="asisFecha" name="fecha_asistencia"
                                       value="<?= $hoy ?>" max="<?= $hoy ?>" required>
                            </div>
                            <div class="col-6">
                                <label for="asisEntrada" class="form-label fw-semibold text-dark">Hora entrada</label>
                                <input type="time" class="form-control formInput" id="asisEntrada" name="hora_entrada"
                                       value="<?= $ahora ?>" required>
                            </div>
                            <div class="col-6">
                                <label for="asisSalida" class="form-label fw-semibold text-dark">Hora salida</label>
                                <input type="time" class="form-control formInput" id="asisSalida" name="hora_salida">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="asisObservaciones" class="form-label fw-semibold text-dark">Observaciones</label>
                            <textarea class="form-control formInput" id="asisObservaciones" name="observaciones"
                                      rows="2" maxlength="255"
                                      placeholder="Opcional (ej: casillero 12, pase de cortesía...)"></textarea>
                        </div>

                        <button type="submit" name="registrar_asistencia" class="btn btn-gym w-100 py-2">
                            <i class="bi bi-check2-circle me-1"></i> Registrar asistencia
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Últimas Asistencias -->
        <div class="col-12 col-lg-7">
            <div class="rep-panel h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h5 fw-bold text-dark m-0">Últimas Asistencias Registradas</h3>
                    <span class="badge-gym-active">Hoy</span>
                </div>

                <?php if (empty($listaAsistencias)): ?>
                    <p class="text-muted small py-4 text-center">Todavía no hay asistencias registradas. Usa el formulario para anotar la primera.</p>
                <?php else: ?>
                    <div class="table-responsive rounded-3 border" style="background-color: #FFFFFF;">
                        <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.875rem;">
                            <thead style="background-color: #FAF8F3;">
                                <tr>
                                    <th class="text-start py-2 px-3 text-muted text-uppercase small fw-bold">Cliente</th>
                                    <th class="py-2 text-muted text-uppercase small fw-bold">Fecha</th>
                                    <th class="py-2 text-muted text-uppercase small fw-bold">Entrada</th>
                                    <th class="py-2 text-muted text-uppercase small fw-bold">Salida</th>
                                    <th class="text-end py-2 px-3 text-muted text-uppercase small fw-bold">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaAsistencias as $a): ?>
                                    <tr>
                                        <td class="text-start py-2 px-3">
                                            <div class="fw-semibold text-dark"><?= $asE($a['nombre_cliente']) ?></div>
                                            <div class="text-muted small font-monospace"><?= $asE($a['cedula']) ?></div>
                                        </td>
                                        <td class="text-muted"><?= $asFecha($a['fecha_asistencia']) ?></td>
                                        <td class="font-monospace fw-semibold text-dark"><?= $asHora($a['hora_entrada']) ?></td>
                                        <td>
                                            <?php if ($a['hora_salida']): ?>
                                                <span class="font-monospace text-muted"><?= $asHora($a['hora_salida']) ?></span>
                                            <?php else: ?>
                                                <form method="POST" action="../Controllers/AsistenciasController.php" class="d-inline">
                                                    <input type="hidden" name="id_asistencia" value="<?= (int) $a['id_asistencia'] ?>">
                                                    <button type="submit" name="registrar_salida"
                                                            class="btn btn-sm btn-outline-success py-1 px-2"
                                                            style="font-size: 0.775rem; border-radius: 6px;"
                                                            title="Registrar la salida con la hora actual">
                                                        <i class="bi bi-box-arrow-right me-1"></i> Marcar salida
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end py-2 px-3">
                                            <?php if ($esAdmin): ?>
                                                <form method="POST" action="../Controllers/AsistenciasController.php" class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar este registro de asistencia?');">
                                                    <input type="hidden" name="id_asistencia" value="<?= (int) $a['id_asistencia'] ?>">
                                                    <button type="submit" name="eliminar_asistencia"
                                                            class="btn btn-sm btn-outline-danger py-1 px-2"
                                                            style="border-radius: 6px;"
                                                            aria-label="Eliminar asistencia">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    const form = document.getElementById('formAsistencia');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        const entrada = document.getElementById('asisEntrada').value;
        const salida  = document.getElementById('asisSalida').value;

        if (entrada && salida && salida < entrada) {
            e.preventDefault();
            alert('La hora de salida no puede ser anterior a la hora de entrada.');
        }
    });
})();
</script>
