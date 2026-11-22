# Probar ClinAgenda en cinco minutos

Primero arránquelo como dice el [README](README.md). Después, siga estos pasos en orden.

## Las cuentas

| Usuario | Contraseña | Quién es | Qué puede hacer |
|---|---|---|---|
| `admin` | `admin2026` | Administración | Todo, incluidas las cuentas, los médicos y los feriados |
| `aparedes` | `clinica2026` | Dra. Ana Paredes, doctora | Las tres clínicas, el motivo de cada consulta, la auditoría y el respaldo |
| `rosa` | `rosa2026` | Rosa, recepción en Centro | Agendar, cancelar y registrar pacientes. Usa la pantalla sencilla |
| `maria` | `maria2026` | María, recepción en Centro | Lo mismo que Rosa, con la pantalla normal |
| `tania` | `tania2026` | Tania, recepción en Sur | Lo mismo, pero en la Clínica Sur |

Son cuentas de demostración. Todos los datos son inventados.

## El recorrido

**1. Agendar una cita (menos de un minuto).**
Entre como `rosa`. Toque *Agendar*, busque `juan perez` (sin tildes): salen dos personas
distintas con el mismo nombre, cada una con su código PAC. Elija una, elija médico, día y
hora, y guarde.

**2. Dos recepcionistas, la misma hora.**
Abra una ventana normal con `rosa` y otra de incógnito con `maria`. En las dos, elija el
mismo médico, el mismo día y la misma hora, y deje todo listo para guardar. Toque *Guardar*
en las dos casi a la vez. Una se guarda; la otra recibe *"Esa hora acaba de ser tomada por…"*
con las horas que siguen libres, y no pierde nada de lo que había elegido.

**3. Un médico en dos clínicas.**
Con `maria`, intente agendar al **Dr. Luis Salazar** en Clínica Centro el próximo lunes. La
hora de las 10:00 aparece en gris con la etiqueta *"en Sur"*: ya está atendiendo allá.

**4. Cancelar y deshacer.**
En la agenda, toque *Cancelar cita*. El sistema pide el motivo. La hora vuelve a aparecer
libre al instante y la cita sigue ahí, en gris, con el motivo y el nombre de quien la canceló.
Toque *Deshacer cancelación*. Si cancela la misma cita dos veces (por ejemplo, desde otra
pestaña), avisa que ya estaba cancelada, desde qué hora y por quién.

**5. Datos malos.**
En *Pacientes → Registrar un paciente nuevo*, pruebe con una cédula de 9 dígitos,
`0000000000`, `0602154875` (parece real, pero falla el dígito verificador), el correo
`juan@@gmail` y un nacimiento en 2090. Cada campo dice qué está mal, y el formulario no
pierde nada de lo escrito.

**6. Un niño sin cédula.**
Elija *No tiene documento*, ponga su fecha de nacimiento y, en el recuadro de abajo, el
nombre y el teléfono de la mamá. Se registra sin problema.

**7. Lo que ve la doctora.**
Salga y entre como `aparedes`. Ve las tres clínicas, el motivo de cada consulta (que
recepción no ve) y el cuadro *Auditoría*, donde está todo lo que hizo Rosa, con la hora y lo
que cambió. Si Rosa escribe a mano `auditoria.php` en la dirección, recibe *"Esta parte no es
para su usuario"*, y ese intento también queda anotado.

**8. Sin conexión, respaldo y papel.**
Apague el wifi: aparece la franja amarilla de *Sin conexión*. Como el sistema corre en la
misma computadora, se puede seguir agendando. Para ver la cola de verdad, deje el wifi
apagado, abra la pantalla de agendar hasta el último paso, apague también Apache en el panel
de XAMPP y toque *Guardar*: la cita queda en el equipo y la franja dice cuántas hay
pendientes. Encienda Apache y el wifi otra vez: en unos segundos se envía sola. Después, en *Respaldo*, descargue la base y, en
la agenda, toque *Imprimir la hoja del día*.
