<?php
    $notas = [
        "Parcial 1" => [
            "metodos" => 2.10,
            "modelamiento" => 4,
            "sistemas" => 4.0,
            "base de datos" => 3.30,
            "programacion web" => 3.40,
            "estadistica" => 2.42,
            "sostenibilidad" => 3.50
        ],

        "Parcial 2" => [
            "metodos" => 3.50,
            "modelamiento" => 4.5,
            "sistemas" => 4.0,
            "base de datos" => 3.80,
            "programacion web" => 3.90,
            "estadistica" => 2.80,
            "sostenibilidad" => 3.70
        ],

        "Suspenso" => [
            "metodos" => 0,
            "modelamiento" => 0,
            "sistemas" => 0,
            "base de datos" => 0,
            "programacion web" => 0,
            "estadistica" => 8,
            "sostenibilidad" => 0
        ]
    ];

    function sumar($nota1, $nota2) {
        return $nota1 + $nota2;
    }

    function estado($nota) {
        if ($nota >= 7) {
            return "Aprobado";
        } elseif ($nota >= 5.5) {
            return "Suspenso";
        } else {
            return "Reprobado";
        }
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis notas</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #111318;
            color: #e5e7eb;
            margin: 0;
            padding: 40px 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 35px;
        }

        table {
            width: 100%;
            max-width: 1000px;
            margin: auto;
            border-collapse: collapse;
            background: #191c23;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #30343d;
            text-align: center;
        }

        th {
            background: #20232c;
            color: #a5b4fc;
        }

        td:first-child {
            text-align: left;
            text-transform: capitalize;
        }

        .aprobado {
            color: #4ade80;
        }

        .suspenso {
            color: #facc15;
        }

        .reprobado {
            color: #f87171;
        }
    </style>
</head>
<body>

    <h1>Mis notas</h1>

    <table>
        <thead>
            <tr>
                <th>Materia</th>
                <th>Parcial 1</th>
                <th>Parcial 2</th>
                <th>Nota total</th>
                <th>Nota final</th>
                <th>Suspenso</th>
                <th>Resultado</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($notas["Parcial 1"] as $materia => $nota1) { ?>

                <?php
                    $nota2 = $notas["Parcial 2"][$materia];

                    $total = sumar($nota1, $nota2);
                    $final = $total;

                    if ($final - floor($final) >= 0.50) {
                        $final = ceil($final);
                    }

                    $resultado = estado($final);
                ?>

                <tr>
                    <td><?php echo $materia; ?></td>
                    <td><?php echo number_format($nota1, 2); ?></td>
                    <td><?php echo number_format($nota2, 2); ?></td>
                    <td><?php echo number_format($total, 2); ?></td>
                    <td><?php echo number_format($final, 2); ?></td>
                    <td><?php echo number_format($notas["Suspenso"][$materia], 2); ?></td>
                    <td class="<?php echo strtolower($resultado); ?>">
                        <?php echo $resultado; ?>
                    </td>
                </tr>

            <?php } ?>
        </tbody>
    </table>

</body>
</html>