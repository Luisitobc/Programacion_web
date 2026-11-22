<?php
require '../sesion.php';
exigir_sesion();
require '../validaciones.php';
$usuario="root";
$contraseña="";
$id=post('id');
$errores=validar_campos(['id'], ['id']);
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
            <h1>Resultado de la consulta</h1>
            <h2><?php echo $id === '' ? 'Todos los lenguajes' : 'Búsqueda del id: ' . h($id); ?></h2>
    <?php
    if ($errores) {
        mostrar_errores($errores);
    } else {
        try {
            $mbd = new PDO('mysql:host=localhost;dbname=bd-lenguajesprog;charset=utf8mb4', $usuario, $contraseña);
            $mbd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($id === '') {
                $sentencia="SELECT * FROM lenguajes ORDER BY id_lenguaje";
                $params=[];
            } else {
                $sentencia="SELECT * FROM lenguajes WHERE id_lenguaje=?";
                $params=[$id];
            }

            //Verificación de la sentencia a ejecutar
            echo "<div class='sentencia'>La sentencia es: " . h($sentencia) . ($params ? "<br>Valor: " . h($id) : "") . "</div>";

            $st = $mbd->prepare($sentencia);
            $st->execute($params);
            $encontrado = false;
            foreach($st as $fila) {
                $encontrado = true;
                echo "<div class='dato'>";
                echo "<p><strong>ID:</strong> " . h($fila['id_lenguaje']) . "</p>";
                echo "<p><strong>Nombre:</strong> " . h($fila['nombre']) . "</p>";
                echo "<p><strong>Año de creación:</strong> " . h($fila['fecha_creacion']) . "</p>";
                echo "<p><strong>Creador:</strong> " . h($fila['creador']) . "</p>";
                echo "<p><strong>Tipado:</strong> " . h($fila['tipado']) . "</p>";
                echo "<p><strong>Uso principal:</strong> " . h($fila['uso_principal']) . "</p>";
                echo "</div>";
            }

            if (!$encontrado) {
                echo "<div class='mensaje aviso'>" . ($id === '' ? "No hay lenguajes registrados" : "No se encontró ningún lenguaje con ese id") . "</div>";
            }
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
