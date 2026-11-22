<?php
$primera = 0;
if( !isset($_COOKIE["nombre"]) ) {
    setcookie("nombre","Claudia",time()+3600);
    $primera = 1;
}
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body bgcolor="#D2EBF7">
        <?php
        if($primera){
            echo "<h1>Hola, esta es tu primera visita.</h1>";
        }else{
            echo "<h1>Hola " . $_COOKIE["nombre"] . "</h1>";
        }
        ?>
    </body>
</html>