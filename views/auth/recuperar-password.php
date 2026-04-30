<h1 class="nombre-pagina">Recuperar Password</h1>
<p class="descripcion-pagina">
    Ingresa tu nuevo password <br>
</p>

<?php include_once __DIR__ . '/../templates/alertas.php' ?>

<form class="formulario" method="post" action="/recuperar?token=<?php echo $token ?>">
    <div class="campo">
        <label for="password">Nuevo Password</label>
        <input type="password" name="password" id="password" placeholder="Tu nuevo password">
    </div>

    <input class="boton" type="submit" value="Guardar Password">
</form>

<div class="acciones">
    <a href="/">¿Ya tienes cuenta? Inicia Sesión</a>
</div>