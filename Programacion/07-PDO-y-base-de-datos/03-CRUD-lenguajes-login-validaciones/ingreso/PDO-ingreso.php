<?php
require '../sesion.php';
exigir_sesion();
require '../validaciones.php';
$usuario="root";
$contraseña="";
$nombre=post('nombre');
$anio=post('anio');
$creador=post('creador');
$tipado=post('tipado');
$uso=post('uso');
$errores=validar_campos(['nombre','anio','creador','tipado','uso']);
?>
<html>

<head>
    <title> *********Resultado *****</title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="../estilos.css" />
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
            <h1>Resultado del ingreso</h1>
    <?php
    if ($errores) {
        mostrar_errores($errores);
    } else {
        try {
            $mbd = new PDO('mysql:host=localhost;dbname=bd-lenguajesprog;charset=utf8mb4', $usuario, $contraseña);
            $mbd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sentencia="INSERT INTO lenguajes(nombre, fecha_creacion, creador, tipado, uso_principal) VALUES (?,?,?,?,?)";
            $params=[$nombre, $anio, $creador, $tipado, $uso];

            //Verificación de la sentencia a ejecutar
            echo "<div class='sentencia'>La sentencia es: " . h($sentencia) . "<br>Valores: " . h(implode(", ", $params)) . "</div>";
            $st = $mbd->prepare($sentencia);
            $st->execute($params);
            echo "<div class='mensaje exito'>Los datos han sido ingresados correctamente</div>";
            $mbd = null;
        } catch (PDOException $e) {
                echo "<div class='mensaje error'>¡Error!: " . h($e->getMessage()) . "</div>";
        }
    }
    ?>
            <a class="volver" href="../index.html">← Volver al menú</a>
        </div>
    </main>

    <footer class="pie">
        <span>Base de datos: bd-lenguajesprog</span>
        <span>PHP + PDO + MySQL · Ejercicio 3</span>
    </footer>
</body>

</html>
