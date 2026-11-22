<?php
  session_start();
  $idSesion = session_id();
?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Ejemplo de Sesión</title>
  </head>
  <body bgcolor="#D2EBF7" style="font-family: Tahoma;">
    <h3>Ejemplo de Sesión</h3>
    <p>Estoy en una sesión</p>
    <p>El Id de sesión es: <?php echo($idSesion) ?></p>
  </body>
</html>