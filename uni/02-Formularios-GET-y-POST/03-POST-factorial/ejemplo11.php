<?php

$num = $_POST["num"];

$fact = 1;

for ($i = 2; $i <= $num; $i++) {
    $fact *= $i;
}

?>

<html>
<head>
    <meta charset="utf-8">
    <title>Resultado</title>
</head>

<body bgcolor="#D2EBF7">

    <h2>Cálculo de Factorial</h2>

    <div id="resultado">
        <h3>Resultado</h3>
        <table width="189">
            <tr>
                <td width="82">
                    <strong>Número</strong>
                </td>
                <td width="95">
                    <?php echo($num); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Factorial</strong>
                </td>
                <td>
                    <?php echo($fact); ?>
                </td>
            </tr>
        </table>
        <p><a href="inicio.html">Nueva Prueba</a></p>

    </div>

</body>
</html>