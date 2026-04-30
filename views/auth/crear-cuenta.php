<!-- views/auth/crear-cuenta.php -->
<h1 class="nombre-pagina">Crear Cuenta</h1>
<p class="descripcion-pagina">
    Completa el formulario <br>
    <span>para crear tu cuenta</span>
</p>
<?php include_once __DIR__ . '/../templates/alertas.php' ?>

<form class="formulario" method="POST" action="/crear-cuenta">
    <div class="campo">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" value="<?php echo $usuario->nombre ?>" placeholder="Tu nombre">
        <span class="error"></span>
    </div>
    <div class="campo">
        <label for="nombre">Apellido</label>
        <input type="text" name="apellido" id="apellido" Value="<?php echo $usuario->apellido ?>" placeholder="Tu apellido">
        <span class="error"></span>
    </div>
    <div class="campo">
        <label for="nombre">Telefono</label>
        <input type="tel" name="telefono" id="telefono" value="<?php echo $usuario->telefono ?>" placeholder="Tu telefono">
        <span class="error"></span>
    </div>
    <div class="campo">
        <label for="email">E-mail</label>
        <input type="email" name="email" id="email" value="<?php echo $usuario->email ?>" placeholder="Tu e-mail">
        <span class="error"></span>
    </div>
    <div class="campo">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Tu password">
        <span class="error"></span>
    </div>

    <input class="boton" type="submit" value="Crear Cuenta">

</form>

<div class="acciones">
    <a href="/">¿Ya tienes cuenta? Inicia Sesión</a>
</div>