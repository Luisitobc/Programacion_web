<?php

class Persona
{
    private string $dni;
    private string $nombres;
    private string $apellidos;

    public function __construct(string $dni, string $nombres, string $apellidos)
    {
        $this->dni = $dni;
        $this->nombres = $nombres;
        $this->apellidos = $apellidos;
    }

    public function getNombreCompleto(): string
    {
        return $this->nombres . ' ' . $this->apellidos;
    }

    public function presentarse(): string
    {
        return "Hola, soy " . $this->getNombreCompleto() . " (DNI: {$this->dni}).";
    }

    // Getters / setters
    public function getDni(): string { 
        return $this->dni; 
        }


    public function setDni(string $dni): void { 
        $this->dni = $dni; 
    }

    public function getNombres(): string {
        return $this->nombres; 
    }
    public function setNombres(string $nombres): void { $this->nombres = $nombres; }

    public function getApellidos(): string {
        return $this->apellidos; 
    }

    public function setApellidos(string $apellidos): void {
        $this->apellidos = $apellidos;
    }
}


class Estudiante extends Persona
{
    private string $carrera;
    private array $notas = [];

    public function __construct(string $dni, string $nombres, string $apellidos, string $carrera)
    {
        parent::__construct($dni, $nombres, $apellidos);
        $this->carrera = $carrera;
    }

    public function agregarNota(float $nota): void
    {
        $this->notas[] = $nota;
    }

    public function getPromedio(): float
    {
        if (count($this->notas) === 0) {
            return 0.0;
        }
        return array_sum($this->notas) / count($this->notas);
    }

    public function presentarse(): string
    {
        return parent::presentarse()
            . " Soy estudiante de {$this->carrera}"
            . " y mi promedio es " . number_format($this->getPromedio(), 2) . ".";
    }

    public function getCarrera(): string { return $this->carrera; }
    public function setCarrera(string $carrera): void { $this->carrera = $carrera; }
    public function getNotas(): array { return $this->notas; }
}

class Docente extends Persona
{
    public const PAGO_HORA = 50.0;

    private string $especialidad;
    private int $horas;

    public function __construct(string $dni, string $nombres, string $apellidos, string $especialidad, int $horas)
    {
        parent::__construct($dni, $nombres, $apellidos);
        $this->especialidad = $especialidad;
        $this->horas = $horas;
    }

    public function calcularSueldo(): float
    {
        return $this->horas * self::PAGO_HORA;
    }

    public function presentarse(): string
    {
        return parent::presentarse()
            . " Soy docente de {$this->especialidad}"
            . " y mi sueldo es $" . number_format($this->calcularSueldo(), 2) . ".";
    }

    public function getEspecialidad(): string { return $this->especialidad; }
    public function setEspecialidad(string $especialidad): void { $this->especialidad = $especialidad; }
    public function getHoras(): int { return $this->horas; }
    public function setHoras(int $horas): void { $this->horas = $horas; }
}

class Trabajador extends Persona
{
    private const ANIOS_MINIMOS_ASCENSO = 2;

    private int $nHoraTrabajo;
    private string $puesto;
    private DateTimeImmutable $fechaInicioTrabajo;
    private string $horario = 'Sin asignar';
    private bool $activo = true;

    public function __construct(
        string $dni,
        string $nombres,
        string $apellidos,
        string $puesto,
        int $nHoraTrabajo,
        string $fechaInicioTrabajo // formato Y-m-d
    ) {
        parent::__construct($dni, $nombres, $apellidos);
        $this->puesto = $puesto;
        $this->nHoraTrabajo = $nHoraTrabajo;
        $this->fechaInicioTrabajo = new DateTimeImmutable($fechaInicioTrabajo);
    }

    public function asignarHorario(string $horario): void
    {
        if (!$this->activo) {
            throw new LogicException("No se puede asignar horario: el trabajador fue despedido.");
        }
        $this->horario = $horario;
    }

    public function getAniosTrabajo(): int
    {
        return $this->fechaInicioTrabajo->diff(new DateTimeImmutable('today'))->y;
    }

    
    public function ascender(string $nuevoPuesto): bool
    {
        if (!$this->activo || $this->getAniosTrabajo() < self::ANIOS_MINIMOS_ASCENSO) {
            return false;
        }
        $this->puesto = $nuevoPuesto;
        return true;
    }

    public function serDespedido(): void
    {
        $this->activo = false;
        $this->horario = 'Sin asignar';
    }

    public function presentarse(): string
    {
        $estado = $this->activo ? 'activo' : 'despedido';
        return parent::presentarse()
            . " Soy trabajador ({$estado}), puesto: {$this->puesto},"
            . " {$this->nHoraTrabajo} horas de trabajo,"
            . " ingresé el " . $this->fechaInicioTrabajo->format('d/m/Y')
            . " ({$this->getAniosTrabajo()} años). Horario: {$this->horario}.";
    }

    public function getNHoraTrabajo(): int { return $this->nHoraTrabajo; }
    public function setNHoraTrabajo(int $n): void { $this->nHoraTrabajo = $n; }
    public function getPuesto(): string { return $this->puesto; }
    public function setPuesto(string $puesto): void { $this->puesto = $puesto; }
    public function getFechaInicioTrabajo(): DateTimeImmutable { return $this->fechaInicioTrabajo; }
    public function getHorario(): string { return $this->horario; }
    public function isActivo(): bool { return $this->activo; }
}

// ---------- Prueba ----------
$estudiante = new Estudiante("0201234567", "Ana María", "Pérez Lara", "Ingeniería de Software");
$estudiante->agregarNota(8.5);
$estudiante->agregarNota(9.0);
$estudiante->agregarNota(7.5);

$docente = new Docente("0207654321", "Carlos", "Mora Vega", "Programación PHP", 40);

echo "<pre>";
echo $estudiante->presentarse() . PHP_EOL;
echo $docente->presentarse() . PHP_EOL;

// Trabajador con más de 2 años: sí puede ascender
$t1 = new Trabajador("0209999999", "Luis", "Andrade Cruz", "Auxiliar", 8, "2022-03-01");
$t1->asignarHorario("Lunes a Viernes 08:00 - 16:00");
echo $t1->presentarse() . PHP_EOL;
echo $t1->ascender("Supervisor") ? "Ascenso realizado." . PHP_EOL : "No cumple 2 años para ascender." . PHP_EOL;
echo $t1->presentarse() . PHP_EOL;

// Trabajador reciente: no puede ascender
$t2 = new Trabajador("0208888888", "María", "Salazar Ríos", "Asistente", 6, date('Y-m-d', strtotime('-6 months')));
echo $t2->ascender("Jefe de área") ? "Ascenso realizado." . PHP_EOL : "No cumple 2 años para ascender." . PHP_EOL;

// Despido
$t2->serDespedido();
echo $t2->presentarse() . PHP_EOL;
echo "</pre>";