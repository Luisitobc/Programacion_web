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
            <h1>Resultado de la eliminación</h1>
    <?php
    if ($errores) {
        mostrar_errores($errores);
    } else {
        try {
            $mbd = new PDO('mysql:host=localhost;dbname=bd-lenguajesprog;charset=utf8mb4', $usuario, $contraseña);
            $mbd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sentencia="DELETE FROM lenguajes WHERE id_lenguaje=?";

            //Verificación de la sentencia a ejecutar
            echo "<div class='sentencia'>La sentencia es: " . h($sentencia) . "<br>Valor: " . h($id) . "</div>";
            $st = $mbd->prepare($sentencia);
            $st->execute([$id]);
            $filas = $st->rowCount();

            if ($filas > 0) {
                echo "<div class='mensaje exito'>El registro ha sido eliminado correctamente</div>";
            } else {
                echo "<div class='mensaje aviso'>No se eliminó ningún registro (el id no existe)</div>";
            }
            $mbd = null;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                echo "<div class='mensaje error'>¡Error!: no se puede eliminar, el lenguaje tiene frameworks asociados</div>";
            } else {
                echo "<div class='mensaje error'>¡Error!: " . h($e->getMessage()) . "</div>";
            }
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
