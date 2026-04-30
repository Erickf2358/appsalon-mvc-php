<h1 class="nombre-pagina"> login </h1>
<p class="descripcion-pagina">
    Bienvenido a nuestra aplicación, <br>
    <span>Inicia sesion <br> para continuar</span>
</p>

<?php include_once __DIR__ . '/../templates/alertas.php' ?>

<form class="formulario" method="post" action="/">
    <div class="campo">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" placeholder="Tu email">
        <span class="error"></span>
    </div>
    <div class="campo">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" placeholder="Tu password">
        <span class="error"></span>
    </div>
    
    <input class="boton" type="submit" value="Iniciar sesión">
    

</form>

<div class="acciones">
    <a href="/crear-cuenta">¿No tienes cuenta? Regístrate</a>
    <a href="/olvide-password">¿Olvidaste tu password? Recuperar</a>
</div>