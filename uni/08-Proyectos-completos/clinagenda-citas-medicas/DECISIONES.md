# Decisiones · ClinAgenda

Equipo: *(nombre y rol de cada integrante)*
Repositorio: *(enlace)*, tag `entrega-final`

## 1. Qué entendimos del caso

El problema de la Dra. Paredes no es que le falte una agenda. Es que las citas se cruzan,
los datos se pierden y, cuando algo sale mal, nadie sabe quién hizo qué. Por eso no
construimos muchas pantallas: construimos pocas, que no pierdan una cita, y dejamos rastro
de todo.

## 2. La contradicción que encontramos

La clienta pidió dos cosas que no pueden ser verdad a la vez: que *cualquiera pueda cancelar
fácilmente* y que *no se pierdan citas*. Si cualquiera cancela sin dejar huella, una cita
cancelada por error es justamente la cita perdida que le quita el sueño.

Lo que hicimos: cancelar es fácil (dos toques), pero nunca es anónimo. Lo hace una persona
que entró con su usuario, tiene que escribir el motivo, y queda anotado quién fue y a qué
hora. La cita no se borra: cambia a "cancelada" y la hora queda libre para otro paciente.
Si fue un error, se puede deshacer, salvo que alguien ya haya tomado esa hora; en ese caso
el sistema lo explica y no le quita la cita a nadie.

Por lo mismo no hicimos un portal para que el paciente cancele solo.

## 3. Lo que no pidió y sí necesita

- **Cuentas con roles.** Sin usuarios no hay forma de saber quién canceló qué.
  Recepción, doctora y administración ven cosas distintas.
- **Respaldo descargable**, más la hoja impresa de la agenda del día para el plan en papel.
- **"No asistió", distinto de "cancelada".** No es lo mismo que el paciente avise a que no
  aparezca, y la doctora va a querer saberlo.
- **Teléfono de quien responde por el paciente**, para niños, adultos mayores o gente sin celular.
- **Un código por paciente** (PAC-000123), para no confundir a dos Juan Pérez.

## 4. Preguntas que resolvimos

**Dos recepcionistas dan la misma hora en el mismo segundo.**
Gana la primera que confirma. Al guardar, la transacción bloquea la fila del médico
(`SELECT … FOR UPDATE`), así que la segunda espera a que la primera termine y recién ahí
revisa si la hora sigue libre. Recibe *"Esa hora acaba de ser tomada por Rosa hace un
instante. Estas siguen libres: 10:30, 11:30 y 12:00"*, con las horas más cercanas a la que
pidió, y no pierde nada de lo que había elegido. Además, la tabla `citas` tiene la clave
única `uk_medico_slot`: aunque alguien se saltara el PHP y escribiera directo en la base,
MySQL rechaza la segunda cita con el error 1062.
*Qué probamos:* dos procesos guardando la misma hora en el mismo instante, cinco rondas
seguidas. En las cinco quedó una sola cita y la otra recepcionista recibió el aviso. También
intentamos el duplicado directo en MySQL y la base lo rechazó.

**Un médico en dos clínicas a la misma hora.**
Imposible. La regla es del médico, no del local: la clave única no incluye la clínica, y la
revisión de choques mira todas las clínicas. En la pantalla, la hora aparece en gris con la
etiqueta "en Sur". En los datos de demo, el Dr. Salazar tiene horario los lunes y miércoles
en la mañana en Centro y en Sur a la vez, que es justo el caso del enunciado.

**La hora que queda libre al cancelar.**
Vuelve a estar disponible al instante, y la cita cancelada sigue guardada. La columna
`slot_medico` se calcula sola: vale la hora de la cita, o NULL si está cancelada. Como MySQL
deja repetir los NULL en una clave única, la hora se libera sin borrar nada.

**Cancelar dos veces la misma cita.**
La segunda vez no pasa nada y se dice sin asustar: *"Esta cita ya estaba cancelada desde las
10:14 por Rosa"*. El `UPDATE` solo toca citas que siguen agendadas; si no cambió ninguna
fila, se muestra el estado real.

**Agendar en una fecha que ya pasó.**
No se puede. El calendario no deja elegir días pasados y el servidor lo revisa de nuevo
contra su propia hora.

**Doble clic, o la misma cita enviada dos veces.**
Cada formulario lleva un token único que se guarda con la cita. Si llega dos veces, la
segunda se reconoce y no se duplica.

**Horarios distintos por clínica, y duración distinta por médico.**
Están en la base, no en el código: `medico_horario` dice qué días y horas atiende cada
médico en cada clínica, con la pausa del almuerzo, y cada médico tiene su duración (20, 30
o 40 minutos). La administración cambia la duración desde su pantalla; las citas que ya
estaban no se mueven. Los feriados también son filas.

**500 citas en un día, o ninguna.**
La agenda filtra por día con índices en `(clinica_id, inicio)` y `(medico_id, inicio)`. Un
día vacío dice *"Hoy no hay citas"* con un botón para agendar, no una tabla en blanco.

**¿Es el mismo Juan Pérez de hace un mes?**
Decide la recepcionista, no el sistema. Antes de registrar a alguien, si ya hay una persona
con ese nombre, se muestran todas con su código, fecha de nacimiento y última cita, y hay
que elegir: abrir esa ficha o confirmar que es otra persona. El sistema nunca une a dos
pacientes por su cuenta, porque juntar las historias de dos personas distintas es peor que
tener un duplicado.

**Paciente sin cédula, sin correo y sin teléfono.**
Se registra igual. Hay tres tipos de documento: cédula, pasaporte o "no tiene". Sin
documento, la fecha de nacimiento pasa a ser obligatoria (es lo que lo distingue) y se pide
quién responde por él. Nadie se queda sin atención por falta de papeles.

**Datos mal escritos.**
Se valida el dato, no solo el formato. La cédula se revisa con su dígito verificador, así que
`0000000000` cae (la provincia 00 no existe) y también una cédula inventada que "parece"
real. El teléfono tiene que ser un celular de 10 dígitos o un fijo de 9; el correo se revisa
con `filter_var`; la fecha de nacimiento no puede estar en el futuro ni dar más de 120 años;
los nombres solo llevan letras, tildes, espacios, apóstrofo y guion. Cada error dice qué
corregir, y el formulario vuelve con todo lo que la persona ya había escrito. Lo que se pega
en un campo se guarda como texto y se muestra escapado, así que no rompe nada.

**"MARÍA lópez" y "Maria Lopez".**
Para buscar son lo mismo: se guarda una versión en mayúsculas y sin tildes del nombre.
También se puede buscar con los nombres en otro orden, por cédula, por teléfono (incluido el
del representante) o por código.

**¿Quién ve qué?**
Recepción ve la agenda y los datos de contacto de su clínica. La doctora ve además las tres
clínicas, el motivo de cada consulta y la auditoría. Administración ve todo y maneja las
cuentas. El motivo se escribe en un solo renglón al agendar y después solo lo ven la doctora
y administración. Los permisos se revisan en el servidor en cada pantalla y en cada acción:
esconder un botón no es seguridad.

**¿Qué pasa si alguien ajeno se sienta en la computadora?**
Nada se ve sin entrar. Las contraseñas se guardan con `password_hash`, después de cinco
intentos fallidos hay que esperar un minuto, y la sesión se cierra sola a los 20 minutos
sin uso.

**¿Quién cambió o canceló una cita, y cuándo?**
Todo cambio escribe en la tabla `auditoria`: quién, qué, cuándo, desde qué equipo, y la foto
del dato antes y después. No hay pantalla ni función para modificarla.

**¿Y si se borra todo por accidente?**
No se puede borrar: en el código no hay ni un `DELETE`. Las cosas se cancelan o se marcan
inactivas. Además, la doctora o administración pueden descargar un respaldo completo; lo
probamos cargándolo en una base vacía y quedó idéntico.

**Se cae el sistema un lunes a las 8.**
Hay plan B en papel: la agenda del día se imprime con un botón, con filas en blanco para
anotar a mano. Después, esas citas se pasan con el formulario normal.

**Se cae el internet a mitad de agendar en la Clínica Sur.**
El sistema corre en la computadora de cada clínica, así que agendar no depende de internet.
Si se corta la red, aparece una franja amarilla. Si además el servidor no responde al
guardar, la cita se queda en el navegador y se envía sola cuando vuelve la conexión; el
token evita que llegue duplicada. Si mientras tanto alguien tomó esa hora, se avisa.

**¿Se puede deshacer?**
Lo reversible, sí: una cancelación se deshace si la hora sigue libre, y una cita se puede
mover a otra hora. Borrar no existe, así que no hay nada que deshacer ahí.

**¿Se usa desde el celular?**
Sí, es el mismo sistema: una columna, botones grandes, nada de tablas con barra horizontal.
Además se puede instalar como aplicación desde Chrome.

**Cambio de hora por horario de verano.**
En Ecuador no hay, y aun así la zona horaria está fija en PHP (`America/Guayaquil`) y en la
conexión a MySQL (`-05:00`).

## 5. Preguntas que dejamos fuera, y cómo las haríamos

**Sincronización automática entre clínicas cuando vuelve el internet.**
Es el problema más difícil del reto y no lo resolvimos. En un día, una sincronización mal
hecha duplica citas, que es peor que no tenerla. Hoy cada clínica tiene su propia base, y
la regla de que un médico no esté en dos lugares a la vez solo se cumple entre las clínicas
que comparten servidor.
Cómo lo haríamos: cada cita tendría un identificador único generado en la clínica donde
nació; cada cambio iría a una cola con su hora; al volver la conexión se enviarían en orden a
un servidor central, y cuando dos clínicas hubieran dado la misma hora al mismo médico, no
se resolvería solo: una persona elegiría en una pantalla de choques antes de aplicar nada.

**Portal para que el paciente cancele solo.** Explicado en la sección 2.

**Historia clínica, diagnósticos y recetas.** La clienta pidió agendar. Más datos de salud
es más riesgo sin más valor hoy.

**Facturación y pagos.** No lo pidió y no frena el agendar.

**Avisos automáticos por WhatsApp, SMS o correo.** Necesitan una cuenta paga y el permiso
del paciente. Evitarían muchas faltas, así que es lo primero que haríamos después.

**App móvil nativa.** El sistema se ve bien en el navegador del celular y se puede instalar
desde Chrome. Una app es otro proyecto.

**Reportes y estadísticas.** El respaldo descargable permite sacarlos en Excel.

**Cargar el archivo de datos sucios de la organización.** No lo teníamos al construir esto.
Lo haríamos con un script que use las mismas validaciones del formulario: las filas buenas
entran, las dudosas entran con una nota ("teléfono por confirmar") y las imposibles van a un
`rechazados.csv` con el motivo en cada línea.

## 6. Lo que supusimos

- La cita dura lo que dice el médico (20, 30 o 40 minutos) y las horas se ofrecen en una
  rejilla desde el inicio de cada tramo de atención.
- Centro y Norte atienden de 08:00 a 18:00 con pausa de 13:00 a 14:00, y los sábados en la
  mañana; Sur atiende de 09:00 a 17:00 de lunes a viernes. Todo eso es editable en la base.
- Una recepcionista trabaja en una sola clínica.
- Las tres clínicas comparten un servidor cuando hay red. Sin red, cada una sigue con el suyo.

## 7. La prueba con una persona no técnica

*(Completar el día de la prueba. No inventar resultados.)*

- Quién: *(nombre, edad, cuánto usa la computadora)*
- Qué le pedimos: agendar una cita para Juan Pérez el martes, sin ayuda.
- Qué pasó: *(cuánto tardó, dónde se trabó)*
- Qué cambiamos por eso: *(…)*

Lo que ya está pensado para esa persona: la pantalla sencilla (letra y botones grandes,
cuadros con dibujo en el inicio) se activa por cuenta desde la administración, no por el
nombre ni la edad de nadie, y el manual de uso se abre desde cualquier pantalla.

## 8. Lo que sabemos que falta

- La clave única compara horas de inicio iguales, no traslapes parciales. Los traslapes los
  revisa el PHP con el médico bloqueado dentro de la transacción. Si alguien insertara a
  mano, desde fuera del sistema, una cita que empieza a otra hora y se monta encima, la base
  sola no la frenaría.
- El bloqueo por intentos fallidos es por sesión del navegador. Alguien que borre las
  cookies puede seguir probando; en producción iría por usuario y por IP.
- No hay recuperación automática de contraseña: la administración pone una nueva.
- Una cita que se dio en papel cuando el sistema estaba caído solo se puede cargar si su hora
  todavía no pasó. Para las que ya pasaron faltaría un "registro histórico" solo para
  administración.
- La cola sin conexión entra en juego cuando el navegador sabe que no hay red. Si la red
  está bien pero el servidor se apaga, el navegador muestra su página de error y hay que
  volver atrás.
- Los avisos al paciente no se envían solos.
- Sin HTTPS: en una red de clínica real haría falta un certificado y la cookie marcada como segura.

## 9. Datos sensibles

No hay datos reales de personas: todo es inventado. Las contraseñas se guardan con
`password_hash`. Las credenciales de MySQL van en `config/local.php`, que no se sube al
repositorio. El `.htaccess` impide abrir desde el navegador el registro de errores y los
archivos `.sql`. El motivo de la consulta es un solo renglón y no hay lugar para
diagnósticos.
