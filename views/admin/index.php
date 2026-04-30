<h1 class="nombre-pagina">Dashboard</h1>
<p class="descripcion-pagina">
    Bienvenido al panel de administración
</p>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>
<div class="busqueda">
    <form class="formulario" method="GET" action="/admin">
        <div class="campo">
            <label for="fecha">Fecha</label>
            <input type="date" name="fecha" id="fecha" placeholder="Fecha" value="<?php echo s($fecha ?? ''); ?>">
            <button type="submit" class="boton">Buscar</button>
        </div>
    </form>
</div>

<div id="citas-admin">
    <?php if(empty($citas)) : ?>
        <p class="text-center">No hay citas para esta fecha</p>
    <?php else : ?>

        <?php
        // Group by cita ID so each cita becomes one card
        $citasAgrupadas = [];
        foreach ($citas as $cita) {
            $id = $cita->id;
            if (!isset($citasAgrupadas[$id])) {
                $citasAgrupadas[$id] = [
                    'id'        => $id,
                    'hora'      => $cita->hora,
                    'cliente'   => $cita->cliente,
                    'email'     => $cita->email,
                    'telefono'  => $cita->telefono,
                    'servicios' => [],
                    'total'     => 0,
                ];
            }
            $citasAgrupadas[$id]['servicios'][] = [
                'nombre' => $cita->servicio,
                'precio' => $cita->precio,
            ];
            $citasAgrupadas[$id]['total'] += $cita->precio;
        }
        ?>

        <div class="listado-citas-admin">
            <?php foreach ($citasAgrupadas as $datos) : ?>
            <div class="cita-card-admin" data-id="<?php echo s($datos['id']); ?>">
                <div class="cita-info">
                    <p class="cita-hora"><span>Hora:</span> <?php echo s(substr($datos['hora'], 0, 5)); ?></p>
                    <p><span>Cliente:</span> <?php echo s($datos['cliente']); ?></p>
                    <p><span>Email:</span> <?php echo s($datos['email']); ?></p>
                    <p><span>Teléfono:</span> <?php echo s($datos['telefono']); ?></p>
                    <ul class="lista-servicios-card">
                        <?php foreach ($datos['servicios'] as $srv) : ?>
                            <li>
                                <?php echo s($srv['nombre']); ?>
                                <span class="precio-servicio">$ <?php echo s($srv['precio']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="cita-total"><span>Total:</span> $ <?php echo s($datos['total']); ?></p>
                </div>
                <div class="cita-acciones">
                    <button class="boton boton-editar" data-id="<?php echo s($datos['id']); ?>">Editar</button>
                    <button class="boton boton-cancelar" data-id="<?php echo s($datos['id']); ?>">Cancelar</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<!-- Edit modal -->
<div id="modal-editar" class="modal-overlay">
    <div class="modal-cita">
        <h3>Editar Cita</h3>
        <div class="formulario">
            <input type="hidden" id="edit-id">
            <div class="campo">
                <label for="edit-fecha">Fecha</label>
                <input type="date" id="edit-fecha">
            </div>
            <div class="campo">
                <label for="edit-hora">Hora</label>
                <input type="time" id="edit-hora" step="900">
            </div>
            <div class="campo">
                <label>Servicios</label>
                <div id="edit-servicios" class="checkboxes-servicios"></div>
            </div>
            <div class="modal-acciones">
                <button type="button" class="boton boton-guardar-edicion">Guardar cambios</button>
                <button type="button" class="boton boton-cerrar-modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.boton-cancelar').forEach(function(btn) {
        btn.addEventListener('click', cancelarCitaAdmin);
    });
    document.querySelectorAll('.boton-editar').forEach(function(btn) {
        btn.addEventListener('click', abrirModalEditar);
    });
    document.querySelector('.boton-cerrar-modal').addEventListener('click', cerrarModal);
    document.querySelector('#modal-editar').addEventListener('click', function(e) {
        if(e.target === this) cerrarModal();
    });
    document.querySelector('.boton-guardar-edicion').addEventListener('click', guardarEdicion);
});

async function cancelarCitaAdmin(e) {
    const id = e.currentTarget.dataset.id;
    if(!confirm('¿Confirmas cancelar esta cita? Esta acción no se puede deshacer.')) return;

    const datos = new FormData();
    datos.append('id', id);

    try {
        const resp  = await fetch('/api/admin/citas/eliminar', { method: 'POST', body: datos });
        const res   = await resp.json();
        if(res.error) { alert('Error: ' + res.error); return; }

        const card = document.querySelector(`.cita-card-admin[data-id="${id}"]`);
        card.remove();

        const cont = document.querySelector('.listado-citas-admin');
        if(!cont || !cont.querySelector('.cita-card-admin')) {
            document.querySelector('#citas-admin').innerHTML = '<p class="text-center">No hay citas para esta fecha</p>';
        }
    } catch(err) {
        alert('Error al conectar con el servidor');
    }
}

let todosServicios = [];

async function abrirModalEditar(e) {
    const id = e.currentTarget.dataset.id;

    if(!todosServicios.length) {
        const r = await fetch('/api/servicios');
        todosServicios = await r.json();
    }

    const r    = await fetch('/api/admin/cita-detalle?id=' + id);
    const cita = await r.json();
    if(cita.error) { alert(cita.error); return; }

    document.querySelector('#edit-id').value    = cita.id;
    document.querySelector('#edit-fecha').value = cita.fecha;
    document.querySelector('#edit-hora').value  = cita.hora.slice(0, 5);

    const seleccionados = cita.servicios.map(function(s) { return String(s.id); });
    const cont = document.querySelector('#edit-servicios');
    cont.innerHTML = '';

    todosServicios.forEach(function(s) {
        const checked = seleccionados.includes(String(s.id)) ? 'checked' : '';
        const div = document.createElement('div');
        div.classList.add('checkbox-servicio');
        div.innerHTML = '<label><input type="checkbox" value="' + s.id + '" ' + checked + '> ' + s.nombre + ' <span class="precio-servicio">$ ' + s.precio + '</span></label>';
        cont.appendChild(div);
    });

    document.querySelector('#modal-editar').classList.add('activo');
}

function cerrarModal() {
    document.querySelector('#modal-editar').classList.remove('activo');
}

async function guardarEdicion() {
    const id       = document.querySelector('#edit-id').value;
    const fecha    = document.querySelector('#edit-fecha').value;
    const hora     = document.querySelector('#edit-hora').value;
    const checked  = document.querySelectorAll('#edit-servicios input[type="checkbox"]:checked');
    const servicios = Array.from(checked).map(function(cb) { return cb.value; });

    if(!servicios.length) { alert('Selecciona al menos un servicio'); return; }
    if(!fecha || !hora)   { alert('Completa la fecha y hora'); return; }

    const datos = new FormData();
    datos.append('id',       id);
    datos.append('fecha',    fecha);
    datos.append('hora',     hora);
    datos.append('servicios', servicios.join(','));

    try {
        const resp = await fetch('/api/admin/citas/actualizar', { method: 'POST', body: datos });
        const res  = await resp.json();
        if(res.error) { alert('Error: ' + res.error); return; }
        cerrarModal();
        window.location.reload();
    } catch(err) {
        alert('Error al conectar con el servidor');
    }
}
</script>
