<?php
// index.php - Un solo archivo: botones + errores con try/catch.
// Ponlo en C:\xampp\htdocs\errores\index.php y abre http://localhost/errores/
// Necesita Apache y MySQL encendidos en XAMPP.

if (isset($_GET['accion'])) {
    header('Content-Type: application/json; charset=utf-8');
    $accion = $_GET['accion'];

    $res = [
        'ok'          => false,
        'tipo'        => '',
        'titulo'      => '',
        'ejecutado'   => '',
        'codigo'      => '',
        'mensaje'     => '',
        'explicacion' => '',
    ];

    function conectar($host = 'localhost', $bd = 'bd-programacion', $user = 'root', $pass = '', $puerto = 3306)
    {
        return new PDO(
            "mysql:host=$host;port=$puerto;dbname=$bd;charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    try {
        switch ($accion) {

            case 'ok':
                $res['tipo'] = 'OK';
                $res['titulo'] = 'Conexion correcta';
                $res['ejecutado'] = "new PDO('mysql:host=localhost;dbname=bd-programacion', 'root', '')";
                conectar();
                $res['ok'] = true;
                $res['mensaje'] = 'Se conecto bien con bd-programacion. No hubo error.';
                break;

            case 'clave':
                $res['tipo'] = 'CONEXION';
                $res['titulo'] = 'Contrasena incorrecta';
                $res['ejecutado'] = "new PDO('mysql:host=127.0.0.1;dbname=bd-programacion', 'root', 'clave_mala')";
                $res['explicacion'] = 'Usuario o contrasena mal escritos, o sin permisos.';
                conectar('127.0.0.1', 'bd-programacion', 'root', 'clave_mala');
                break;

            case 'bd':
                $res['tipo'] = 'CONEXION';
                $res['titulo'] = 'Base de datos inexistente';
                $res['ejecutado'] = "new PDO('mysql:host=localhost;dbname=bd_que_no_existe', 'root', '')";
                $res['explicacion'] = 'El nombre de la base esta mal escrito o todavia no se creo.';
                conectar('localhost', 'bd_que_no_existe');
                break;

            case 'servidor':
                $res['tipo'] = 'CONEXION';
                $res['titulo'] = 'Servidor apagado o puerto incorrecto';
                $res['ejecutado'] = "new PDO('mysql:host=127.0.0.1;port=3399;dbname=bd-programacion', 'root', '')";
                $res['explicacion'] = 'MySQL apagado, o host/puerto equivocado.';
                conectar('127.0.0.1', 'bd-programacion', 'root', '', 3399);
                break;

            case 'tabla':
                $res['tipo'] = 'CONSULTA';
                $res['titulo'] = 'Tabla inexistente';
                $res['ejecutado'] = 'SELECT * FROM tabla_que_no_existe';
                $res['explicacion'] = 'Nombre de tabla mal escrito (singular/plural, mayusculas).';
                conectar()->query($res['ejecutado']);
                break;

            case 'columna':
                $res['tipo'] = 'CONSULTA';
                $res['titulo'] = 'Columna inexistente';
                $res['ejecutado'] = 'SELECT columna_falsa';
                $res['explicacion'] = 'La columna no existe o esta mal escrita.';
                conectar()->query($res['ejecutado']);
                break;

            case 'sintaxis':
                $res['tipo'] = 'CONSULTA';
                $res['titulo'] = 'Error de sintaxis SQL';
                $res['ejecutado'] = 'SELEC 1';
                $res['explicacion'] = 'Palabra reservada mal escrita, o falta una coma, comilla o parentesis.';
                conectar()->query($res['ejecutado']);
                break;

            case '403':
                $res['tipo'] = 'HTTP';
                $res['titulo'] = '403 Forbidden';
                $res['ejecutado'] = "throw new Exception('Prohibido', 403)";
                $res['explicacion'] = 'El servidor te entiende pero no tienes permiso para entrar.';
                throw new Exception('Prohibido: no tienes permiso para acceder a este recurso', 403);

            case '404':
                $res['tipo'] = 'HTTP';
                $res['titulo'] = '404 Not Found';
                $res['ejecutado'] = "throw new Exception('No encontrado', 404)";
                $res['explicacion'] = 'La pagina o ruta no existe, o esta mal escrita.';
                throw new Exception('No encontrado: la pagina que pediste no existe', 404);

            case '500':
                $res['tipo'] = 'HTTP';
                $res['titulo'] = '500 Internal Server Error';
                $res['ejecutado'] = "throw new Exception('Error interno', 500)";
                $res['explicacion'] = 'Falla del codigo PHP o de la base de datos en el servidor.';
                throw new Exception('Error interno del servidor', 500);

            case '500bd':
                $res['tipo'] = 'HTTP';
                $res['titulo'] = '500 por fallo de base de datos';
                $res['ejecutado'] = 'SELECT * FROM tabla_que_no_existe  (y el servidor responde 500)';
                $res['explicacion'] = 'Una consulta fallo y el servidor no pudo responder la pagina.';
                try {
                    conectar()->query('SELECT * FROM tabla_que_no_existe');
                } catch (PDOException $e) {
                    throw new Exception($e->getMessage(), 500);
                }
                break;

            default:
                $res['tipo'] = 'HTTP';
                $res['titulo'] = '404 Not Found';
                $res['ejecutado'] = "accion = $accion";
                $res['explicacion'] = 'La accion pedida no existe.';
                throw new Exception('Accion desconocida', 404);
        }

    } catch (PDOException $e) {
        // Errores de base de datos: se capturan y se responde 200 con el detalle
        $res['codigo'] = isset($e->errorInfo[1]) ? $e->errorInfo[1] : $e->getCode();
        $res['mensaje'] = $e->getMessage();

    } catch (Exception $e) {
        // Errores HTTP: se responde con el codigo real (403, 404, 500)
        $c = $e->getCode();
        if ($c == 403 || $c == 404 || $c == 500) {
            http_response_code($c);
        }
        $res['codigo'] = $c;
        $res['mensaje'] = $e->getMessage();
    }

    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Errores comunes PDO y HTTP</title>
<style>
    body { font-family: Arial, sans-serif; background: #f3f4f6; color: #1f2937; max-width: 820px; margin: 30px auto; padding: 0 16px; }
    h1 { font-size: 22px; }
    h2 { font-size: 15px; margin: 22px 0 8px; color: #4b5563; }
    .botones { display: flex; flex-wrap: wrap; gap: 8px; }
    button { padding: 9px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; color: #fff; background: #2563eb; }
    button:hover { filter: brightness(1.12); }
    button.ok { background: #16a34a; }
    button.http { background: #dc2626; }
    button.consulta { background: #d97706; }
    #resultado { margin-top: 26px; background: #fff; border-radius: 8px; padding: 18px; border-left: 6px solid #9ca3af; min-height: 60px; }
    #resultado.error { border-left-color: #dc2626; }
    #resultado.bien { border-left-color: #16a34a; }
    .fila { margin: 8px 0; }
    .etiqueta { font-weight: bold; }
    pre { background: #111827; color: #e5e7eb; padding: 10px; border-radius: 6px; white-space: pre-wrap; word-break: break-word; margin: 4px 0 0; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; background: #e5e7eb; font-size: 12px; margin-right: 6px; }
</style>
</head>
<body>

<h1>Errores comunes: PDO y HTTP</h1>
<p>Pulsa un boton: se ejecuta el error y abajo aparece lo que paso.</p>

<h2>Todo bien</h2>
<div class="botones">
    <button class="ok" data-accion="ok">Conexion correcta</button>
</div>

<h2>Errores de conexion</h2>
<div class="botones">
    <button data-accion="clave">Contrasena incorrecta</button>
    <button data-accion="bd">Base de datos inexistente</button>
    <button data-accion="servidor">Servidor apagado / puerto</button>
</div>

<h2>Errores de consultas</h2>
<div class="botones">
    <button class="consulta" data-accion="tabla">Tabla inexistente</button>
    <button class="consulta" data-accion="columna">Columna inexistente</button>
    <button class="consulta" data-accion="sintaxis">Sintaxis SQL</button>
</div>

<h2>Errores HTTP</h2>
<div class="botones">
    <button class="http" data-accion="403">Error 403</button>
    <button class="http" data-accion="404">Error 404</button>
    <button class="http" data-accion="500">Error 500</button>
    <button class="http" data-accion="500bd">500 por base de datos</button>
</div>

<div id="resultado">Todavia no has ejecutado nada.</div>

<script>
const caja = document.getElementById('resultado');

function fila(etiqueta, texto, comoCodigo) {
    const div = document.createElement('div');
    div.className = 'fila';
    const e = document.createElement('span');
    e.className = 'etiqueta';
    e.textContent = etiqueta + ': ';
    div.appendChild(e);
    if (comoCodigo) {
        const pre = document.createElement('pre');
        pre.textContent = texto;
        div.appendChild(pre);
    } else {
        div.appendChild(document.createTextNode(texto));
    }
    return div;
}

function mostrar(d, statusHttp) {
    caja.innerHTML = '';
    caja.className = d.ok ? 'bien' : 'error';

    const titulo = document.createElement('h3');
    titulo.style.margin = '0 0 10px';
    const badge = document.createElement('span');
    badge.className = 'badge';
    badge.textContent = d.tipo;
    titulo.appendChild(badge);
    titulo.appendChild(document.createTextNode(d.titulo));
    caja.appendChild(titulo);

    caja.appendChild(fila('Se ejecuto', d.ejecutado, true));
    if (!d.ok) {
        caja.appendChild(fila('Codigo del error', String(d.codigo)));
    }
    caja.appendChild(fila('Estado HTTP de la respuesta', String(statusHttp)));
    caja.appendChild(fila('Mensaje', d.mensaje));
    if (d.explicacion) {
        caja.appendChild(fila('Causa comun', d.explicacion));
    }
}

document.querySelectorAll('button[data-accion]').forEach(function (btn) {
    btn.addEventListener('click', async function () {
        caja.className = '';
        caja.textContent = 'Ejecutando...';
        try {
            const resp = await fetch('?accion=' + btn.dataset.accion);
            const datos = await resp.json();
            mostrar(datos, resp.status);
        } catch (err) {
            caja.className = 'error';
            caja.textContent = 'No se pudo hablar con el servidor. Abre esta pagina desde http://localhost/errores/ (con Apache encendido en XAMPP), no haciendo doble clic en el archivo.';
        }
    });
});
</script>

</body>
</html>