<?php
require( "libreria.php" );

$formulario = TRUE;

if( isset($_POST["calcular"]) ) {
    $formulario = FALSE;
    $a = $_POST["a"];
    $b = $_POST["b"];
    $c = $_POST["c"];
    if( fn_VerificarTriangulo( $a, $b, $c ) ) {
        $area = fn_CalcularAreaTriangulo( $a, $b, $c );
        $msg = "Proceso Ok.";
    } else {
        $area = "Error";
        $msg = "No existe el triangulo.";
    }
}

$pagina = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
?>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Capitulo 07 - Ejemplo 11</title>
    <link rel="stylesheet" href="estilo.css" type="text/css"/>
</head>
<body>
    <?php require( "cabecera.html" ); ?>
    <h2>Calculo del Area de un Triangulo</h2>
    <?php if( $formulario ) { ?>
    <h3>Ingrese el Valor de los Lados</h3>
    <form action="<?php echo $pagina; ?>" method="post">
        <table width="156">
            <tr>
                <td width="87">Lado A</td>
                <td width="57">
                    <input type="text" name="a" size="5" maxlength="3">
                </td>
            </tr>
            <tr>
                <td>Lado B</td>
                <td><input type="text" name="b" size="5" maxlength="3"></td>
            </tr>
            <tr>
                <td>Lado C</td>
                <td><input type="text" name="c" size="5" maxlength="3"></td>
            </tr>
        </table>
        <input type="submit" value="Enviar" name="calcular">
        <input type="reset" value="Limpiar">
    </form>
    <?php } else { ?>
    <h3>Resultado del Proceso</h3>
    <table width="429">
        <tr>
            <td width="87">Lado A</td>
            <td width="330"><?php echo($a) ?></td>
        </tr>
        <tr>
            <td>Lado B</td>
            <td><?php echo($b) ?></td>
        </tr>
        <tr>
            <td>Lado C</td>
            <td><?php echo($c) ?></td>
        </tr>
        <tr>
            <td>Área</td>
            <td><?php echo($area) ?></td>
        </tr>
        <tr>
            <td>Mensaje</td>
            <td><?php echo($msg) ?></td>
        </tr>
    </table>
    <a href="<?php echo($pagina) ?>">Otra Prueba</a>
    <?php } ?>
<?php require( "pie.html" ); ?>
</body>