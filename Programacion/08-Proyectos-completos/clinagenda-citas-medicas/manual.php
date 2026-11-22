<?php
// Manual de uso, escrito para alguien que nunca usó un sistema.
// Se puede abrir sin haber entrado.
require __DIR__ . '/lib/auth.php';

$u = usuario_actual();
cabecera('Manual de uso', ['volver' => $u ? 'inicio.php' : 'index.php']);
?>

<div class="tarjeta">
  <h1>Manual de uso</h1>
  <p class="sin-margen">Cada cosa que se puede hacer, paso a paso. Al final está qué hacer si algo no sale.</p>
</div>

<div class="tarjeta">
  <h2>1. Entrar al sistema</h2>
  <ol class="limpia">
    <li>Escriba su <strong>usuario</strong> y su <strong>contraseña</strong>.</li>
    <li>Si no está segura de lo que escribió, marque <em>Mostrar la contraseña</em>.</li>
    <li>Toque <strong>Entrar</strong>.</li>
  </ol>
  <p class="ayuda-campo">Si se equivoca cinco veces seguidas, el sistema la hace esperar un minuto.
     No está dañado: es para protegerla. Y si deja el sistema 20 minutos sin usar, se cierra solo.</p>
</div>

<div class="tarjeta">
  <h2>2. Ver las citas del día</h2>
  <ol class="limpia">
    <li>Toque el cuadro <strong>Ver las citas</strong>.</li>
    <li>Aparecen las citas del día, de la más temprana a la más tarde.</li>
    <li>Para ver otro día use <strong>Anterior</strong> y <strong>Siguiente</strong>, o elija la fecha.
        También puede ver un solo médico.</li>
  </ol>
  <p>Cuando el paciente pasa a consulta, toque <strong>Marcar atendida</strong>. Si no vino,
     toque <strong>No asistió</strong>. Así la doctora sabe después qué pasó.</p>
  <p class="ayuda-campo">Si el día sale vacío no es un error: simplemente no hay citas.</p>
</div>

<div class="tarjeta">
  <h2>3. Agendar una cita</h2>
  <ol class="limpia">
    <li>Toque el cuadro <strong>Agendar</strong>.</li>
    <li><strong>Paso 1:</strong> escriba el nombre, la cédula o el teléfono y toque <em>Buscar</em>.
        Toque a la persona correcta. Si nunca ha venido, toque <em>Registrar un paciente nuevo</em>.</li>
    <li><strong>Paso 2:</strong> elija el médico y la fecha.</li>
    <li><strong>Paso 3:</strong> toque una hora. Las grises no se pueden elegir: dicen <em>ocupado</em>,
        <em>ya pasó</em>, o en qué otra clínica está ese médico a esa hora.</li>
    <li>Revise el resumen y toque <strong>Guardar la cita</strong>.</li>
  </ol>
  <p>Si ese día no queda ninguna hora, toque <strong>Buscar la próxima hora libre</strong> y el sistema
     salta solo al primer día con espacio.</p>
</div>

<div class="tarjeta">
  <h2>4. Cambiar la hora de una cita</h2>
  <ol class="limpia">
    <li>En la agenda, toque <strong>Cambiar la hora</strong> en la cita.</li>
    <li>Elija el nuevo día y la nueva hora, y toque <strong>Guardar la nueva hora</strong>.</li>
  </ol>
  <p>La hora anterior queda libre para otro paciente, y queda anotado el cambio.</p>
</div>

<div class="tarjeta">
  <h2>5. Cancelar una cita</h2>
  <ol class="limpia">
    <li>Toque el cuadro <strong>Cancelar</strong>, o <strong>Cancelar cita</strong> en la agenda.</li>
    <li>Escriba por qué se cancela, por ejemplo: <em>el paciente llamó, no puede ir</em>.</li>
    <li>Toque <strong>Sí, cancelar la cita</strong>.</li>
  </ol>
  <p>La hora queda libre y la cita <strong>no desaparece</strong>: queda guardada con el motivo y con el
     nombre de quien la canceló. Si se equivocó, toque <strong>Deshacer cancelación</strong>. Si otra
     persona ya tomó esa hora, el sistema avisa y no se la quita: hay que darle otra hora al paciente.</p>
</div>

<div class="tarjeta">
  <h2>6. Registrar un paciente nuevo</h2>
  <ol class="limpia">
    <li>En <strong>Pacientes</strong>, toque <em>Registrar un paciente nuevo</em>.</li>
    <li>Elija el tipo de documento. Para un bebé o alguien sin papeles, elija <strong>No tiene
        documento</strong>, escriba su fecha de nacimiento y anote quién responde por él.</li>
    <li>Escriba nombres y apellidos. El teléfono y el correo son opcionales.</li>
    <li>Toque <strong>Guardar</strong>.</li>
  </ol>
  <p>Si ya hay alguien con el mismo nombre, el sistema se lo muestra antes de guardar, con su código y
     su fecha de nacimiento. Si es la misma persona, abra su ficha. Si es otra, toque
     <em>Es otra persona: registrarla</em>.</p>
  <p class="ayuda-campo">Cada paciente tiene un código, como PAC-000123. Sirve para no confundir a dos
     personas que se llaman igual.</p>
</div>

<div class="tarjeta">
  <h2>7. Cuando algo sale mal</h2>
  <p>Los avisos dicen qué pasó y qué hacer. Los más comunes:</p>
  <p><strong>«Esa cédula no existe»</strong><br>
     El número está mal copiado. Pida la cédula y revise dígito por dígito: el sistema comprueba el
     último número, así que se da cuenta de un error de tipeo.</p>
  <p><strong>«Esa hora acaba de ser tomada»</strong><br>
     Otra recepcionista guardó esa misma hora un instante antes. El aviso le dice qué horas siguen libres.</p>
  <p><strong>«Ya está atendiendo en otra clínica a esa hora»</strong><br>
     El médico trabaja en dos clínicas y no puede estar en las dos a la vez. Elija otra hora u otro médico.</p>
  <p><strong>Una franja amarilla arriba que dice «Sin conexión»</strong><br>
     Se cayó la red. Siga trabajando: si el sistema no responde al guardar, la cita se queda en esta
     computadora y se envía sola cuando vuelva la conexión. La franja dice cuántas hay pendientes.</p>
  <p><strong>«La página caducó»</strong><br>
     Estuvo mucho rato en la misma pantalla. Vuelva atrás y hágalo otra vez; no se dañó nada.</p>
</div>

<div class="tarjeta">
  <h2>8. Dos cosas importantes</h2>
  <ul class="limpia">
    <li><strong>Nada se borra.</strong> Las citas canceladas quedan guardadas. Si alguien se equivoca,
        siempre se puede ver qué pasó y arreglarlo.</li>
    <li><strong>Al terminar el turno, toque Salir.</strong> Lo que se haga con su usuario queda anotado
        a su nombre.</li>
  </ul>
</div>

<?php pie(['activo' => 'manual']); ?>
