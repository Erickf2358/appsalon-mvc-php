<h1 class="nombre-pagina">Mis Citas</h1>
<p class="descripcion-pagina">
    Administra tus citas <br>
    <span>selecciona los servicios y fecha</span>

</p>

<?php include_once __DIR__ . '/../templates/barra.php'; ?>



<nav class="tabs">
    <button class="tab activo" data-tab="paso-1">Servicios</button>
    <button class="tab" data-tab="paso-2">Tus Datos</button>
    <button class="tab" data-tab="paso-3">Resumen</button>
</nav>


<div id="app">
    <div id="paso-1" class="seccion">
        <h2>Servicios</h2>
        <p class="text-center">Selecciona los servicios que quieres citar</p>
        <div id="servicios" class="listado-servicios">
            
            
        </div>
    </div>
    
    <div id="paso-2" class="seccion">
        <h2>Tus datos y cita</h2>
        <p class="text-center">Coloca tus datos y fecha de cita</p>
        <form class="formulario">
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" placeholder="Tu nombre" value="<?php echo htmlspecialchars($nombre ?? '', ENT_QUOTES, 'UTF-8'); ?>" disabled> 
                <span class="error"></span>
            </div>
            
            <div class="campo">
                <label for="fecha">Fecha</label>
                <input type="date" name="fecha" id="fecha" placeholder="Tu fecha">
                <span class="error"></span>
            </div>
            <div class="campo">
                <label for="hora">Hora</label>
                <select name="hora" id="hora"></select>
                <span class="error"></span>
            </div>
            <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($id ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            
        </form>

        
    </div>
    
    <div id="paso-3" class="seccion">
        <h2>Resumen</h2>
        <p class="text-center">Verirfica que la cita es correcta</p>
        
    </div>
</div>

<?php
    $script = "<script src='/build/js/app.js'></script>";
?>

<section class="proximas-citas">
    <h2 class="nombre-pagina">Mis Próximas Citas</h2>
    <p class="descripcion-pagina">
        Gestiona tus citas programadas <br>
        <span>reagenda o cancela cuando necesites</span>
    </p>
    <div id="listado-citas" class="listado-citas">
        <p class="text-center citas-cargando">Cargando citas...</p>
    </div>
</section>