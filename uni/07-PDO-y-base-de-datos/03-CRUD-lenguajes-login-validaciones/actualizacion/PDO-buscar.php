<?php
require '../sesion.php';
exigir_sesion();
require '../validaciones.php';
$usuario="root";
$contraseña="";
$id=post('id');
$errores=validar_campos(['id']);
?>
<html>

<head>
    <title> *********Datos del lenguaje *****</title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="../estilos.css" />
    <script src="../validaciones.js"></script>
    <script src="../sesion.js"></script>
</head>

<body>
    <header class="encabezado">
        <a class="marca" href="../index.html"><span class="logo">&lt;/&gt;</span> Lenguajes de programación</a>
        <nav class="navegacion">
            <a href="../index.html">Inicio</a>
            <a href="../ingreso/ingresar-datos.html">Ingresar</a>
            <a href="../consulta/consultar-datos.html">Consultar</a>
            <a href="../actualizacion/actualizar-datos.html">Actualizar</a>
            <a href="../eliminar/eliminar-datos.html">Eliminar</a>
        </nav>
    </header>

    <main class="cuerpo">
        <div class="tarjeta">
            <a class="volver" href="actualizar-datos.html">← Buscar otro id</a>
    <?php
    $fila = null;
    if ($errores) {
        mostrar_errores($errores);
    } else {
        try {
            $mbd = new PDO('mysql:host=localhost;dbname=bd-lenguajesprog;charset=utf8mb4', $usuario, $contraseña);
            $mbd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $st = $mbd->prepare("SELECT * FROM lenguajes WHERE id_lenguaje=?");
            $st->execute([$id]);
            $fila = $st->fetch(PDO::FETCH_ASSOC);
            $mbd = null;
            if (!$fila) {
                echo "<div class='mensaje aviso'>No se encontró ningún lenguaje con ese id</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='mensaje error'>¡Error!: " . h($e->getMessage()) . "</div>";
        }
    }
    if ($fila) {
    ?>
            <h1>Actualizar el lenguaje con id <?php echo h($fila['id_lenguaje']); ?></h1>
            <form action="PDO-actualizar.php" method="post">
                <input type="hidden" name="id" value="<?php echo h($fila['id_lenguaje']); ?>" />

                <label>Nombre del lenguaje</label>
                <input type="text" name="nombre" data-regla="nombre" value="<?php echo h($fila['nombre']); ?>" placeholder="Ej.: C++" required />
                <small class="ayuda">Una sola palabra, sin espacios.</small>
                <label>Fecha de creación</label>
                <input type="date" name="anio" data-regla="fecha" value="<?php echo h($fila['fecha_creacion']); ?>" required />
                <small class="ayuda">Entre 1940 y hoy.</small>
                <label>Creador</label>
                <input type="text" name="creador" data-regla="creador" value="<?php echo h($fila['creador']); ?>" placeholder="Ej.: Dennis Ritchie" required />
                <small class="ayuda">Solo letras y espacios simples.</small>
                <label>Tipado</label>
                <input type="text" name="tipado" data-regla="tipado" value="<?php echo h($fila['tipado']); ?>" placeholder="Ej.: Estático, fuerte" required />
                <small class="ayuda">Solo letras, coma y guion.</small>
                <label>Uso principal</label>
                <input type="text" name="uso" data-regla="uso" value="<?php echo h($fila['uso_principal']); ?>" placeholder="Ej.: Sistemas operativos" required />
                <small class="ayuda">Letras, números y . , / ( ) + # -</small>
                <input type="submit" value="actualizar" />
            </form>
    <?php } ?>
            <a class="volver" href="../index.html">← Volver al menú</a>
        </div>
    </main>

    <footer class="pie">
        <span>Base de datos: bd-lenguajesprog</span>
        <span>PHP + PDO + MySQL · Ejercicio 3</span>
    </footer>
</body>

</html>
