<!-- views/auth/olvide.php -->
<h1 class="nombre-pagina">Recuperar Password</h1>
<p class="descripcion-pagina">
    Ingresa tu email <br>
    <span>para recuperar tu cuenta</span>
</p>

<?php include_once __DIR__ . '/../templates/alertas.php' ?>

<form class="formulario" method="post" action="/olvide-password">
    <div class="campo">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Tu email" required>
    </div>

    <input class="boton" type="submit" value="Recuperar Password">

</form>

<div class="acciones">
    <a href="/">¿Ya tienes cuenta? Inicia Sesión</a>
</div>