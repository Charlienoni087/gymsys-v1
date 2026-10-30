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
    <div class="row g-3">

        <!-- Formulario de registro -->
        <div class="col-12 col-lg-5">
            <div class="rep-panel h-100">
                <h3 class="h5 text-white mb-1">Registrar asistencia</h3>
                <p class="mb-3" style="color:#a8a8a8; font-size:.9rem;">
                    Deja la salida vacía si el cliente sigue en el gimnasio; luego podrás marcarla en la lista.
                </p>

                <?php if (empty($listaClientes)): ?>
                    <p class="mb-0">No hay clientes activos. Registra un cliente antes de anotar asistencias.</p>
                <?php else: ?>
                    <form method="POST" action="../Controllers/AsistenciasController.php" id="formAsistencia">
                        <div class="mb-3">
                            <label for="asisCliente">Cliente</label>
                            <select class="form-select" id="asisCliente" name="id_cliente" required>
                                <option value="" selected disabled>Selecciona un cliente</option>
                                <?php foreach ($listaClientes as $c): ?>
                                    <option value="<?= (int) $c['id_cliente'] ?>">
                                        <?= $asE($c['nombre_cliente']) ?> (<?= $asE($c['cedula']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label for="asisFecha">Fecha</label>
                                <input type="date" class="form-control" id="asisFecha" name="fecha_asistencia"
                                       value="<?= $hoy ?>" max="<?= $hoy ?>" required>
                            </div>
                            <div class="col-6">
                                <label for="asisEntrada">Hora de entrada</label>
                                <input type="time" class="form-control" id="asisEntrada" name="hora_entrada"
                                       value="<?= $ahora ?>" required>
                            </div>
                            <div class="col-6">
                                <label for="asisSalida">Hora de salida</label>
                                <input type="time" class="form-control" id="asisSalida" name="hora_salida">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="asisObservaciones">Observaciones</label>
                            <textarea class="form-control" id="asisObservaciones" name="observaciones"
                                      rows="2" maxlength="255"
                                      placeholder="Opcional"></textarea>
                        </div>

                        <button type="submit" name="registrar_asistencia" class="btn btn-gym w-100">
                            <i class="bi bi-check2-circle"></i> Registrar asistencia
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Últimas asistencias -->
        <div class="col-12 col-lg-7">
            <div class="rep-panel h-100">
                <h3 class="h5 text-white mb-3">Últimas asistencias</h3>

                <?php if (empty($listaAsistencias)): ?>
                    <p class="mb-0">Todavía no hay asistencias registradas. Usa el formulario para anotar la primera.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 rep-historial" style="--bs-table-bg: transparent;">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                    <th>Observaciones</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listaAsistencias as $a): ?>
                                    <tr>
                                        <td><?= $asFecha($a['fecha_asistencia']) ?></td>
                                        <td>
                                            <?= $asE($a['nombre_cliente']) ?>
                                            <div style="color:#a8a8a8; font-size:.8rem;"><?= $asE($a['cedula']) ?></div>
                                        </td>
                                        <td><?= $asHora($a['hora_entrada']) ?></td>
                                        <td>
                                            <?php if ($a['hora_salida']): ?>
                                                <?= $asHora($a['hora_salida']) ?>
                                            <?php else: ?>
                                                <form method="POST" action="../Controllers/AsistenciasController.php" class="d-inline">
                                                    <input type="hidden" name="id_asistencia" value="<?= (int) $a['id_asistencia'] ?>">
                                                    <button type="submit" name="registrar_salida"
                                                            class="btn btn-sm btn-gym"
                                                            title="Registrar la salida con la hora actual">
                                                        Marcar salida
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $a['observaciones'] ? $asE($a['observaciones']) : '—' ?></td>
                                        <td class="text-end">
                                            <?php if ($esAdmin): ?>
                                                <form method="POST" action="../Controllers/AsistenciasController.php" class="d-inline"
                                                      onsubmit="return confirm('¿Eliminar esta asistencia?');">
                                                    <input type="hidden" name="id_asistencia" value="<?= (int) $a['id_asistencia'] ?>">
                                                    <button type="submit" name="eliminar_asistencia"
                                                            class="btn btn-sm btn-outline-danger"
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

    // La salida no puede ser anterior a la entrada
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