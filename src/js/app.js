let paso = 1;
const pasoInicial = 1;
const pasoFinal = 3;

const cita = {
    id: '',
    nombre: '',
    fecha: '',
    hora: '',
    servicios: []
}

// ID de la cita a reagendar (null si es una cita nueva)
let citaReagendar = null;

document.addEventListener('DOMContentLoaded', function() {
    iniciarApp();
});

function iniciarApp() {
    mostrarSeccion();
    tabs();
    obtenerServicios();
    datosCita();
    idCliente();
    obtenerCitasUsuario();
    //resumenCita();
}

function tabs() {
    const botones = document.querySelectorAll('.tab');
    botones.forEach(boton => {
        boton.addEventListener('click', function() {
            const id = this.dataset.tab;

            const pasos = document.querySelectorAll('.seccion');
            pasos.forEach(p => p.style.display = 'none');
            document.querySelector(`#${id}`).style.display = 'block';

            botones.forEach(b => b.classList.remove('activo'));
            this.classList.add('activo');

            if(id === 'paso-3') {
                mostrarResumen();
            }
        });
    });
}

function mostrarSeccion() {
    const pasos = document.querySelectorAll('.seccion');
    pasos.forEach(p => p.style.display = 'none');
    document.querySelector(`#paso-${paso}`).style.display = 'block';
}

async function obtenerServicios() {
    const respuesta = await fetch('/api/servicios');
    const servicios = await respuesta.json();
    mostrarServicios(servicios);
}

function mostrarServicios(servicios) {
    const contenedor = document.querySelector('#servicios');
    servicios.forEach(servicio => {
        const { id, nombre, precio } = servicio;
        const servicioHTML = document.createElement('DIV');
        servicioHTML.classList.add('servicio');
        servicioHTML.dataset.idServicio = id;
        servicioHTML.innerHTML = `
            <h3>${nombre}</h3>
            <p class="precio-servicio">$ ${precio}</p>
        `;
        servicioHTML.addEventListener('click', seleccionarServicio);
        contenedor.appendChild(servicioHTML);
    });
}

function seleccionarServicio(e) {
    const { idServicio } = e.currentTarget.dataset;
    const { nombre, precio } = e.currentTarget.querySelector('h3, .precio-servicio');
    
    if(cita.servicios.some(s => s.id === idServicio)) {
        cita.servicios = cita.servicios.filter(s => s.id !== idServicio);
        e.currentTarget.classList.remove('seleccionado');
    } else {
        cita.servicios.push({ id: idServicio, nombre: e.currentTarget.querySelector('h3').textContent, precio: e.currentTarget.querySelector('.precio-servicio').textContent });
        e.currentTarget.classList.add('seleccionado');
    }
}

function datosCita() {
    const nombre = document.querySelector('#nombre');
    const fecha  = document.querySelector('#fecha');
    const hora   = document.querySelector('#hora');

    // Populate select with slots 10:00–22:00 every 15 min
    for (let h = 10; h <= 22; h++) {
        const maxMin = (h === 22) ? 0 : 45;
        for (let m = 0; m <= maxMin; m += 15) {
            const val  = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;
            const opt  = document.createElement('option');
            opt.value       = val;
            opt.textContent = val;
            hora.appendChild(opt);
        }
    }

    // Default to next valid 15-min slot within working hours
    const ahora    = new Date();
    const fechaHoy = ahora.toLocaleDateString('en-CA');
    let h = ahora.getHours();
    let m = Math.ceil(ahora.getMinutes() / 15) * 15;
    if (m === 60) { h += 1; m = 0; }
    if (h < 10 || h > 22 || (h === 22 && m > 0)) { h = 10; m = 0; }
    const horaSnapped = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}`;

    fecha.value = fechaHoy;
    hora.value  = horaSnapped;
    cita.fecha  = fechaHoy;
    cita.hora   = horaSnapped;

    nombre.addEventListener('change', function() { cita.nombre = this.value; });
    fecha.addEventListener('change',  function() { cita.fecha  = this.value; });
    hora.addEventListener('change',   function() { cita.hora   = this.value; });
}

// function resumenCita() {
//     const resumen = document.querySelector('#paso-3');
    
//     resumen.addEventListener('click', function() {
//         mostrarResumen();
//     });
// }

function validarHora(hora) {
    if (!hora) return 'La hora es obligatoria';
    return null;
}

function mostrarResumen() {
    cita.nombre = document.querySelector('#nombre').value;
    const resumen = document.querySelector('.resumen-cita');
    if(resumen) resumen.remove();
    
    const { nombre, fecha, hora, servicios } = cita;
    
    const div = document.createElement('DIV');
    div.classList.add('resumen-cita');
    div.innerHTML = `
        <h3>Resumen de tu cita</h3>
        <p><span>Nombre:</span> ${nombre}</p>
        <p><span>Fecha:</span> ${fecha}</p>
        <p><span>Hora:</span> ${hora}</p>
        <h4>Servicios:</h4>
        ${servicios.map(s => `<p>${s.nombre} - ${s.precio}</p>`).join('')}
    `;

    //boton para crear cita
    const botonReservar = document.createElement('BUTTON');
    botonReservar.classList.add('boton');
    botonReservar.textContent = 'Crear Cita';
    botonReservar.onclick = reservarCita;

    div.appendChild(botonReservar);

    
    document.querySelector('#paso-3').appendChild(div);
}

function idCliente() {
    cita.id = document.querySelector('#id').value;
}


async function reservarCita() {
    const {nombre, fecha, hora, servicios, id} = cita;

    if(!servicios.length) {
        alert('Debes seleccionar al menos un servicio');
        return;
    }

    if(!fecha || !hora) {
        alert('Debes seleccionar fecha y hora');
        return;
    }

    const errorHora = validarHora(hora);
    if (errorHora) {
        alert(errorHora);
        return;
    }

    const idServicio = servicios.map(servicio => servicio.id);
    const datos = new FormData();
    datos.append('fecha', fecha);
    datos.append('hora', hora);
    datos.append('id', id);
    datos.append('servicios', idServicio);

    // Si es un reagendamiento, primero eliminamos la cita anterior
    if(citaReagendar) {
        const eliminarDatos = new FormData();
        eliminarDatos.append('id', citaReagendar);
        try {
            await fetch('/api/citas/eliminar', { method: 'POST', body: eliminarDatos });
        } catch(e) {
            console.error('No se pudo eliminar la cita anterior:', e);
        }
        citaReagendar = null;
    }

    try {
        const respuesta = await fetch('/api/citas', {
            method: 'POST',
            body: datos
        });
        const resultado = await respuesta.json();

        if(resultado.error) {
            alert('Error: ' + resultado.error);
            return;
        }

        if(resultado.resultado) {
            alert('Cita creada correctamente');
            window.location.href = '/cita';
        } else {
            alert('No se pudo guardar la cita, intente de nuevo');
        }
    } catch(error) {
        console.error('Error al crear la cita:', error);
        alert('Error al conectar con el servidor');
    }
}

// ── Próximas citas ────────────────────────────────────────────────────────────

async function obtenerCitasUsuario() {
    const contenedor = document.querySelector('#listado-citas');
    try {
        const respuesta = await fetch('/api/citas');
        const citas = await respuesta.json();
        mostrarCitasUsuario(citas, contenedor);
    } catch(error) {
        contenedor.innerHTML = '<p class="text-center">Error al cargar las citas.</p>';
    }
}

function mostrarCitasUsuario(citas, contenedor) {
    contenedor.innerHTML = '';

    if(!citas.length) {
        contenedor.innerHTML = '<p class="text-center no-citas">No tienes citas próximas agendadas.</p>';
        return;
    }

    citas.forEach(function(c) {
        const fechaFormateada = new Date(c.fecha + 'T00:00:00').toLocaleDateString('es-MX', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

        const horaFormateada = c.hora ? c.hora.slice(0, 5) : '';

        const card = document.createElement('DIV');
        card.classList.add('cita-card');
        card.innerHTML = `
            <div class="cita-info">
                <p class="cita-fecha"><span>Fecha:</span> ${fechaFormateada}</p>
                <p class="cita-hora"><span>Hora:</span> ${horaFormateada}</p>
                <p class="cita-servicios"><span>Servicios:</span> ${c.servicios}</p>
                <p class="cita-total"><span>Total:</span> $${parseFloat(c.total).toFixed(2)}</p>
            </div>
            <div class="cita-acciones">
                <button class="boton boton-reagendar" data-id="${c.id}">Reagendar</button>
                <button class="boton boton-eliminar" data-id="${c.id}">Cancelar</button>
            </div>
        `;

        card.querySelector('.boton-reagendar').addEventListener('click', () => reagendarCita(c.id));
        card.querySelector('.boton-eliminar').addEventListener('click', () => confirmarEliminarCita(c.id, card));

        contenedor.appendChild(card);
    });
}

async function confirmarEliminarCita(id, cardEl) {
    if(!confirm('¿Seguro que deseas cancelar esta cita?')) return;

    const datos = new FormData();
    datos.append('id', id);

    try {
        const respuesta = await fetch('/api/citas/eliminar', { method: 'POST', body: datos });
        const resultado = await respuesta.json();

        if(resultado.error) {
            alert('Error: ' + resultado.error);
            return;
        }

        if(resultado.resultado) {
            cardEl.remove();
            const contenedor = document.querySelector('#listado-citas');
            if(!contenedor.querySelector('.cita-card')) {
                contenedor.innerHTML = '<p class="text-center no-citas">No tienes citas próximas agendadas.</p>';
            }
        }
    } catch(error) {
        alert('Error al conectar con el servidor');
    }
}

async function reagendarCita(id) {
    try {
        const respuesta = await fetch(`/api/cita-detalle?id=${id}`);
        const detalle = await respuesta.json();

        if(detalle.error) {
            alert('Error: ' + detalle.error);
            return;
        }

        // Guardar el ID de la cita a reemplazar
        citaReagendar = id;

        // Pre-llenar fecha y hora
        const fechaInput = document.querySelector('#fecha');
        const horaInput  = document.querySelector('#hora');
        fechaInput.value = detalle.fecha;
        horaInput.value  = detalle.hora.slice(0, 5);
        cita.fecha = detalle.fecha;
        cita.hora  = detalle.hora.slice(0, 5);

        // Limpiar servicios actuales y pre-seleccionar los de la cita
        cita.servicios = [];
        document.querySelectorAll('.servicio.seleccionado').forEach(el => el.classList.remove('seleccionado'));

        detalle.servicios.forEach(function(s) {
            cita.servicios.push({
                id: String(s.id),
                nombre: s.nombre,
                precio: `$ ${s.precio}`
            });
            const el = document.querySelector(`.servicio[data-id-servicio="${s.id}"]`);
            if(el) el.classList.add('seleccionado');
        });

        // Navegar a paso-1 y actualizar el botón de confirmación
        document.querySelectorAll('.seccion').forEach(p => p.style.display = 'none');
        document.querySelector('#paso-1').style.display = 'block';
        document.querySelectorAll('.tab').forEach(b => b.classList.remove('activo'));
        document.querySelector('[data-tab="paso-1"]').classList.add('activo');

        // Scroll hacia arriba
        window.scrollTo({ top: 0, behavior: 'smooth' });

        alert('Modifica los servicios, fecha y hora. Luego confirma en el Resumen.');
    } catch(error) {
        alert('Error al cargar los datos de la cita');
    }
}